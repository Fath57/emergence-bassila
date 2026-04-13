# CGU, Politique de confidentialité et suppression de compte — Design

**Date** : 2026-04-14
**Statut** : design validé, prêt pour rédaction du plan d'implémentation

## Contexte

La plateforme Bassila Emergence collecte des données personnelles (identification, profils publics d'annuaire, articles, commentaires, newsletter, logs) sans disposer à ce jour de CGU, de politique de confidentialité, de mentions légales, ni d'un dispositif permettant à un membre de demander la suppression de son compte. L'absence de ces éléments expose le projet à un risque juridique (RGPD art. 12-22, code numérique béninois titre V) et prive les membres de l'exercice de leurs droits fondamentaux sur leurs données.

Ce document définit la conception des trois pages légales et du dispositif de suppression de compte associé.

## Cadre légal visé

Les documents et le flux couvrent conjointement :

- **RGPD** (règlement UE 2016/679) — pour les membres résidents de l'Union européenne (diaspora)
- **Code numérique du Bénin** (loi n°2017-20), titre V relatif à la protection des données à caractère personnel — pour les membres résidents au Bénin
- **Acte additionnel A/SA.1/01/10 de la CEDEAO** — mention de la portée régionale ouest-africaine

L'autorité de contrôle de référence est l'APDP (Autorité de Protection des Données Personnelles du Bénin) ; la CNIL et équivalents nationaux UE sont mentionnés comme recours pour les résidents européens.

## Responsable de traitement

- **Nom affiché** : Bassila Emergence (collectif communautaire informel, sans entité juridique formelle)
- **Contact vie privée** : contact@bassila-emergence.org
- **Adresse** : Bassila, Département de la Donga, République du Bénin
- **Représentant** : à désigner dans les mentions légales au moment du déploiement
- **Hébergeur** : à renseigner dans les mentions légales au moment du déploiement

## Décisions clés

| Décision | Choix retenu |
|---|---|
| Cadre légal | RGPD + code numérique Bénin + mention CEDEAO |
| Identité responsable | Collectif informel nommé « Bassila Emergence » |
| Flux de suppression | Auto-suppression avec délai de grâce 30 jours + supervision admin |
| Sort des contenus à la purge | Différencié : profil supprimé, articles/commentaires anonymisés, logs gardés 1 an |
| Cookies | Uniquement strictement nécessaires (pas de bandeau) |
| Stockage des documents légaux | Fichiers Blade statiques versionnés par git (pas d'admin UI) |

## Architecture

### 1. Pages légales statiques

Trois routes ajoutées dans `routes/web.php`, sur le modèle existant `/qui-sommes-nous` :

| URL | Vue | Nom de route |
|---|---|---|
| `/cgu` | `resources/views/pages/cgu.blade.php` | `pages.cgu` |
| `/politique-de-confidentialite` | `resources/views/pages/politique-confidentialite.blade.php` | `pages.privacy` |
| `/mentions-legales` | `resources/views/pages/mentions-legales.blade.php` | `pages.legal` |

Chaque page :

- étend `layouts.app`
- affiche un sommaire ancré au début
- affiche une date « Dernière mise à jour » et un numéro de version hardcodés en haut
- est écrite en français

Le footer global (`layouts.partials.footer` ou équivalent à localiser) reçoit trois liens vers ces pages.

La page d'inscription (`App\Livewire\Auth\Register`) ajoute une case à cocher obligatoire « J'accepte les [CGU](/cgu) et la [Politique de confidentialité](/politique-de-confidentialite) » ; la validation échoue si la case n'est pas cochée. L'horodatage de l'acceptation correspond à `users.created_at` (pas de colonne dédiée).

### 2. Suppression de compte — machine à états

```
[aucune] ──request──▶ requested ──confirm (email)──▶ confirmed ──J+30──▶ purged
                         │                                │
                         │                                └──cancel──▶ cancelled
                         └──admin cancel──▶ cancelled
```

- `requested` : l'utilisateur a soumis sa demande ; email de confirmation envoyé ; **le compte reste actif**
- `confirmed` : l'utilisateur a cliqué sur le lien signé dans les 24 h ; `users.is_active = false`, session invalidée, `scheduled_purge_at = now() + 30 jours`
- `cancelled` : annulée par l'utilisateur (à la reconnexion) ou par un admin ; `users.is_active` restauré à `true`
- `purged` : purge effective selon les règles de la section « Purge différenciée »

### 3. Schéma de base de données

**Migration : `create_account_deletion_requests_table`**

| Colonne | Type | Notes |
|---|---|---|
| `id` | bigint PK | |
| `user_id` | bigint FK `users.id`, unique, cascade delete | une seule demande active par compte |
| `status` | enum(`requested`,`confirmed`,`cancelled`,`purged`) | |
| `confirmation_token` | string(64), nullable, indexed | effacé après confirmation ou expiration |
| `requested_at` | timestamp | |
| `confirmed_at` | timestamp nullable | |
| `scheduled_purge_at` | timestamp nullable, indexed | défini au passage en `confirmed` |
| `cancelled_at` | timestamp nullable | |
| `cancelled_by` | bigint FK `users.id`, nullable | NULL si annulé par le propriétaire |
| `cancel_reason` | text nullable | libre ; saisi si annulation admin |
| `admin_notes` | text nullable | notes internes |
| `purged_at` | timestamp nullable | |
| `created_at`, `updated_at` | timestamps | |

**Migration : `add_author_display_name_to_blog_posts_table`**

Ajoute `author_display_name VARCHAR(120) NULL` à `blog_posts`. Utilisé dans le rendu quand `user_id IS NULL`.

**Migration : `add_author_display_name_to_blog_comments_table`**

Même colonne sur `blog_comments` si elle n'existe pas déjà (à vérifier au moment de l'implémentation).

Aucune modification du schéma `users` : la colonne `is_active` existe déjà.

### 4. Composants applicatifs

**Modèle `App\Models\AccountDeletionRequest`**

- Relations : `user()`, `canceller()`
- Scopes : `pending()`, `confirmed()`, `dueForPurge()` (= `confirmed` et `scheduled_purge_at <= now()` et `cancelled_at IS NULL`)
- Méthodes de transition (encapsulent la logique + email + transaction) :
  - `confirm()` : requested → confirmed
  - `cancel(User $by, ?string $reason = null)` : requested|confirmed → cancelled
  - `purge()` : confirmed → purged (transaction décrite plus bas)

**Modèles étendus**

- `BlogPost` et `BlogComment` : accesseur `displayAuthorName` renvoyant `user?->display_name` si présent, sinon `author_display_name`, sinon libellé par défaut (`Ancien membre` pour articles, `Membre supprimé` pour commentaires). Les vues qui affichent l'auteur sont mises à jour pour utiliser cet accesseur.
- `User` : relation `deletionRequest()` (hasOne), méthode `hasPendingDeletion(): bool` (`requested` ou `confirmed` non annulée).

**Policy `AccountDeletionRequestPolicy`**

- `create(User $user, User $target)` : `$user->is($target)` et non dernier admin
- `cancel(User $user, AccountDeletionRequest $r)` : propriétaire OU admin
- `forcePurge(User $user, AccountDeletionRequest $r)` : admin uniquement

**Composant Livewire `App\Livewire\Profile\DeleteAccount`**

- Rendu : bouton rouge « Supprimer mon compte » dans une section « Zone dangereuse » en bas de `EditProfile`
- Modal de confirmation : texte d'avertissement (conséquences listées), champ mot de passe, case « je confirme avoir lu »
- Submit : validation mot de passe + case + policy → crée la demande (`requested`), envoie `AccountDeletionRequested` → écran de succès

**Routes additionnelles**

```
GET  /compte/suppression/confirmer/{token}   → DeleteAccount confirm (signed, throttle 6/1)
GET  /compte/suppression/annuler             → DeleteAccount cancel (auth)
GET  /admin/suppressions                     → App\Livewire\Admin\DeletionRequests
```

**Middleware / redirection pendant la grâce**

Lorsqu'un utilisateur dont `is_active=false` et qui a une `deletionRequest` en `confirmed` tente de se connecter, il est redirigé vers une page dédiée annonçant la purge planifiée et proposant un bouton d'annulation. Le reste du site lui reste inaccessible tant qu'il n'a pas annulé.

**Commande artisan `accounts:purge-expired`**

- Itère sur les demandes `confirmed` avec `scheduled_purge_at <= now()` et `cancelled_at IS NULL`
- Appelle `AccountDeletionRequest::purge()` pour chacune
- Planifiée `dailyAt('03:00')` dans `routes/console.php`

**Page admin `App\Livewire\Admin\DeletionRequests`**

Liste paginée avec filtres par statut, colonnes (utilisateur, date demande, date purge planifiée, statut), actions par ligne : voir détails, annuler (avec raison), forcer la purge (demande double confirmation).

### 5. Purge différenciée — transaction unique par compte

Dans `AccountDeletionRequest::purge()`, au sein d'une transaction :

1. **Profil annuaire** : suppression cascade (`profiles`, `profile_skills`)
2. **Articles publiés** : `UPDATE blog_posts SET user_id = NULL, author_display_name = 'Ancien membre' WHERE user_id = ?`
3. **Commentaires** : `UPDATE blog_comments SET user_id = NULL, author_display_name = 'Membre supprimé' WHERE user_id = ?`
4. **Newsletter** : suppression de la souscription dont l'email correspond
5. **Invitations émises** par l'utilisateur : conservées (trace d'audit), `invited_by_id` mis à NULL
6. **`activity_log`** : non purgé ici ; `causer_id` reste référencé. La purge à 365 jours est traitée hors du scope de cette fonctionnalité (à implémenter ou documenter séparément via la configuration spatie/activitylog — `activitylog.delete_records_older_than_days`)
7. **Utilisateur** : `users.delete()` en dernier
8. **Demande** : `status = purged`, `purged_at = now()`, `confirmation_token = null`
9. **Email final** `AccountDeletionCompleted` : capturer l'adresse de l'utilisateur dans une variable avant l'étape 7 puis envoyer via `Mail::to($email)->queue(...)->afterCommit()` pour garantir que l'email ne part que si la transaction a commit (évite un envoi en cas de rollback)

### 6. Cas particuliers

- **Admin demandant sa suppression** : bloqué tant qu'il a le rôle `admin`, message explicite invitant à retirer son rôle via un autre admin
- **Dernier admin de la plateforme** : blocage absolu ; seul un autre admin peut retirer ce rôle, ce qui rend la règle précédente applicable
- **Utilisateur avec contenus en modération** : email interne `AdminDeletionPendingWithPublishedContent` à `contact@bassila-emergence.org` au passage en `confirmed` pour que l'équipe vérifie avant la purge
- **Token de confirmation expiré (>24 h)** : rejet, invitation à refaire une demande (la demande existante passe en `cancelled` automatiquement)

### 7. Emails (FR, template existant `mail.layouts.main`)

| Mail | Destinataire | Déclencheur |
|---|---|---|
| `AccountDeletionRequested` | utilisateur | création de la demande |
| `AccountDeletionConfirmed` | utilisateur | clic sur lien signé |
| `AccountDeletionCancelled` | utilisateur | annulation (par lui ou admin) |
| `AccountDeletionCompleted` | utilisateur | juste avant purge effective |
| `AdminDeletionPendingWithPublishedContent` | équipe (`contact@…`) | demande `confirmed` sur compte avec articles publiés |

## Contenu des documents légaux

### CGU — plan

1. Objet et éditeur du site
2. Accès au service (gratuit ; invitation requise pour publier ; libre pour annuaire)
3. Inscription et compte (16 ans min., véracité, lien avec Bassila requis pour annuaire)
4. Contenus publiés par les membres (licence non-exclusive accordée à Bassila Emergence ; auteur reste propriétaire)
5. Règles de conduite (pas de haine, diffamation, spam, usurpation)
6. Modération (droit de retrait/suspension ; procédure de contestation)
7. Propriété intellectuelle du site
8. Responsabilité (plateforme « en l'état », limites loi Bénin)
9. Durée, résiliation, suppression de compte (renvoi vers procédure)
10. Modifications des CGU (préavis 30 j par email pour changements majeurs)
11. Droit applicable (droit béninois), juridiction (tribunaux de Cotonou), médiation préalable
12. Contact

### Politique de confidentialité — plan

1. Identité du responsable de traitement
2. Données collectées (tableau : catégorie / exemples / source / obligatoire ou non)
3. Finalités et bases légales (RGPD art. 6 / code num. Bénin art. 388)
4. Destinataires (personnel sous confidentialité ; sous-traitants techniques ; pas de revente)
5. Transferts hors UE / hors Bénin (clauses contractuelles types, équivalences)
6. Durées de conservation (compte actif / compte inactif >3 ans / logs 1 an / newsletter jusqu'à désinscription)
7. Droits (accès, rectification, effacement, limitation, opposition, portabilité, retrait consentement, directives post-mortem) et modalités
8. Suppression de compte (procédure détaillée)
9. Cookies et traceurs (session, XSRF, remember_web_*, pixel newsletter — tous nécessaires)
10. Sécurité (HTTPS, hash, accès admin restreint, audit)
11. Réclamations (APDP Bénin + CNIL + mention CEDEAO)
12. Modifications de la politique
13. Contact DPO / référent vie privée

### Mentions légales — plan

1. Éditeur (Bassila Emergence, adresse, email, représentant)
2. Directeur de la publication
3. Hébergeur (nom, adresse à renseigner au déploiement)
4. Propriété intellectuelle
5. Signalement de contenu illicite

## Tests (Pest)

À écrire en TDD au fur et à mesure de l'implémentation.

**Feature**

- `tests/Feature/LegalPagesTest.php` : les trois URLs renvoient 200, contiennent le nom du responsable et une date de mise à jour, sont accessibles sans authentification
- `tests/Feature/Auth/RegistrationAcceptsTermsTest.php` : inscription sans case CGU cochée → échec de validation ; inscription valide → compte créé
- `tests/Feature/Account/DeleteAccountFlowTest.php` :
  - demande créée, email envoyé
  - lien de confirmation signé valide → `confirmed`, session invalidée, purge planifiée J+30
  - lien > 24 h → rejet, statut repasse en `cancelled`
  - utilisateur reconnecté pendant la grâce → voit la page dédiée et peut annuler → `is_active=true`
  - mot de passe incorrect → rejet
  - dernier admin → rejet
- `tests/Feature/Account/PurgeExpiredAccountsCommandTest.php` :
  - commande purge les demandes dues ; profil supprimé ; articles anonymisés (user_id null, author_display_name rempli) ; commentaires anonymisés ; user supprimé
  - demande `cancelled` non purgée
  - demande avec `scheduled_purge_at` future non purgée
- `tests/Feature/Admin/DeletionRequestsAdminTest.php` :
  - admin voit la liste, peut annuler, peut forcer la purge ; non-admin 403

**Unit**

- `tests/Unit/AccountDeletionRequestTest.php` : transitions d'états valides et invalides, immutabilité après `purged`

## Ordre des commits

1. Migrations (`account_deletion_requests`, `author_display_name` sur `blog_posts` et au besoin `blog_comments`) + modèle `AccountDeletionRequest` + tests unitaires transitions
2. Pages statiques CGU / Politique / Mentions légales + liens footer + case CGU à l'inscription + tests feature pages légales et inscription
3. Composant Livewire `DeleteAccount` + emails utilisateurs + routes de confirmation/annulation + tests feature flux utilisateur
4. Commande `accounts:purge-expired` + scheduling + accesseurs `displayAuthorName` + mise à jour des vues + tests feature purge
5. Page admin `DeletionRequests` + email interne contenus publiés + tests feature admin

## Hors-scope (YAGNI)

- Export RGPD automatisé (art. 20 portabilité) — géré à la demande par email, mentionné dans la politique
- Versioning en base des documents légaux — git sert d'historique
- Bandeau de consentement cookies — tous les cookies posés sont strictement nécessaires ou soumis à consentement opt-in explicite (newsletter)
- Interface multilingue — FR uniquement

## Dépendances d'implémentation à vérifier

- Localisation exacte du footer partial (nom et chemin) dans `resources/views/layouts/`
- Présence ou non de `author_display_name` dans `blog_comments` (à vérifier à l'étape 1)
- Structure exacte des vues qui affichent l'auteur d'un article ou d'un commentaire (liste à dresser avant modification pour garantir qu'aucune n'est oubliée)
- Présence et configuration d'une file d'attente email (pour garantir que `AccountDeletionCompleted` part avant le rollback éventuel de la transaction de purge)
