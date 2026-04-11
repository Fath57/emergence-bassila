# Feature Specification: Plateforme Réseau Communautaire Bassiloise - MVP

**Feature Branch**: `001-bassila-network-platform`
**Created**: 2026-04-07
**Status**: Draft
**Input**: User description: "analyse le cdc et setup les specs"

---

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Inscription et création de profil (Priority: P1)

Un membre de la communauté bassiloise (étudiant, résident, diaspora) s'inscrit sur la plateforme, vérifie son email, puis complète son profil professionnel avec photo, titre, compétences et localisation. Son profil est ensuite soumis à vérification par les admins.

**Why this priority**: Sans profils, aucune autre fonctionnalité n'a de valeur. C'est le socle de la plateforme. Les membres doivent pouvoir exister dans l'annuaire avant toute interaction.

**Independent Test**: Peut être testé de bout en bout en créant un compte, vérifiant l'email, remplissant le profil et consultant la page publique du profil — sans aucune autre fonctionnalité.

**Acceptance Scenarios**:

1. **Given** un visiteur sur la page d'inscription, **When** il saisit email et mot de passe valides, **Then** un email de vérification est envoyé et le compte est créé en statut "non vérifié"
2. **Given** un utilisateur avec email non vérifié, **When** il clique sur le lien de vérification dans l'email, **Then** son accès complet est activé
3. **Given** un utilisateur connecté sans profil complet, **When** il remplit tous les champs obligatoires (nom, titre, localisation, secteur) — la photo est optionnelle, un avatar est auto-généré depuis les initiales par défaut, **Then** son profil est visible publiquement avec statut "non vérifié"
4. **Given** un profil existant, **When** un autre utilisateur visite la page publique de ce profil, **Then** il voit nom, titre, entreprise, localisation, compétences et badge de vérification si applicable
5. **Given** un utilisateur connecté, **When** il tente d'éditer le profil d'un autre utilisateur, **Then** l'accès est refusé

---

### User Story 2 - Recherche et découverte de membres (Priority: P2)

Un membre cherche d'autres bassilois dans son domaine ou sa région. Il utilise les filtres de recherche pour trouver des profils pertinents et découvrir des connexions potentielles.

**Why this priority**: La recherche est le cœur de l'annuaire communautaire. Sans elle, la plateforme n'est qu'une liste statique de profils inaccessibles.

**Independent Test**: Peut être testé avec des profils de test en seed : effectuer une recherche par métier, appliquer des filtres combinés, vérifier les résultats et la pagination.

**Acceptance Scenarios**:

1. **Given** un utilisateur sur l'annuaire, **When** il tape "ingénieur" dans la barre de recherche, **Then** les profils correspondant au titre ou compétences s'affichent en moins de 500ms
2. **Given** un utilisateur avec filtres actifs (secteur: IT, pays: France), **When** il ajoute un filtre compétence, **Then** les résultats se mettent à jour en combinant tous les filtres
3. **Given** une liste de résultats paginée, **When** l'utilisateur passe à la page suivante, **Then** les mêmes filtres sont conservés et les résultats suivants s'affichent
4. **Given** un toggle "profils vérifiés uniquement" activé, **When** la recherche est lancée, **Then** seuls les profils avec badge de vérification apparaissent
5. **Given** une recherche sans résultats, **When** aucun profil ne correspond aux critères, **Then** un message approprié s'affiche avec suggestion de modifier les filtres

---

### User Story 3 - Prise de contact entre membres (Priority: P3)

Un membre souhaite contacter un autre bassilois après avoir trouvé son profil. Il envoie un message via le formulaire de contact intégré et l'autre membre reçoit une notification par email.

**Why this priority**: La mise en relation est l'objectif fondamental de la plateforme. La recherche sans capacité de contact serait incomplète pour le MVP.

**Independent Test**: Peut être testé en envoyant un message depuis un profil vers un autre et en vérifiant la réception de l'email de notification.

**Acceptance Scenarios**:

1. **Given** un utilisateur connecté sur le profil d'un autre membre, **When** il clique sur "Contacter" et remplit sujet + message, **Then** le message est envoyé et stocké en base
2. **Given** un message envoyé, **When** le destinataire reçoit l'email de notification, **Then** l'email contient le sujet, le corps du message et l'email de l'expéditeur pour répondre directement
3. **Given** un expéditeur qui envoie un message, **When** l'envoi est confirmé, **Then** il reçoit un email de confirmation d'envoi
4. **Given** un utilisateur non connecté, **When** il tente d'accéder au formulaire de contact, **Then** il est redirigé vers la page de connexion

---

### User Story 4 - Publication et consultation d'articles de blog (Priority: P4)

Un membre ou admin publie un article sur la communauté bassiloise (actualités, histoires, opportunités). Les autres membres consultent et commentent les articles publiés.

**Why this priority**: Le blog enrichit la plateforme et encourage l'engagement communautaire au-delà du networking professionnel, mais n'est pas bloquant pour le MVP core.

**Independent Test**: Peut être testé en créant un article en brouillon, le publiant, et le consultant depuis un autre compte — avec commentaire soumis en attente de modération.

**Acceptance Scenarios**:

1. **Given** un utilisateur connecté sur l'éditeur d'article, **When** il rédige un contenu riche (texte gras, listes, images), **Then** le contenu est sauvegardé avec le formatage préservé
2. **Given** un article en statut "brouillon", **When** un visiteur tente d'y accéder via URL directe, **Then** il reçoit une page 404 ou est redirigé
3. **Given** un article publié, **When** un utilisateur connecté soumet un commentaire, **Then** le commentaire est enregistré en statut "en attente" et n'est pas visible publiquement avant modération
4. **Given** la liste des articles, **When** un visiteur filtre par catégorie, **Then** seuls les articles de cette catégorie s'affichent, triés par date décroissante

---

### User Story 5 - Modération et vérification des profils (Priority: P5)

Un administrateur de la plateforme revoit les nouveaux profils et articles, vérifie l'authenticité des membres, et maintient la qualité du contenu communautaire.

**Why this priority**: La confiance et la qualité sont essentielles à long terme mais peuvent être gérées manuellement en phase initiale avec peu d'utilisateurs.

**Independent Test**: Peut être testé en créant un profil de test, accédant au panel admin, approuvant/rejetant le profil et vérifiant que le badge apparaît (ou que la notification de rejet est envoyée).

**Acceptance Scenarios**:

1. **Given** un admin sur le panel de modération, **When** il consulte la liste des profils non-vérifiés, **Then** il voit nom, email, date d'inscription et peut approuver ou rejeter chaque profil
2. **Given** un admin qui approuve un profil, **When** l'action est confirmée, **Then** le badge de vérification apparaît sur le profil public et une notification est envoyée au membre
3. **Given** un admin qui rejette un profil, **When** il fournit une raison optionnelle, **Then** l'utilisateur reçoit un email avec la raison du rejet
4. **Given** un admin sur le dashboard, **When** il consulte les statistiques, **Then** il voit le nombre total d'utilisateurs, de posts publiés ce mois, et les inscriptions récentes

---

### Edge Cases

- Que se passe-t-il si un utilisateur soumet le formulaire de contact plusieurs fois rapidement (anti-spam) ?
- Comment le système gère-t-il un avatar corrompu ou trop volumineux lors de l'upload ?
- Que se passe-t-il si l'email de vérification expire avant que l'utilisateur ne clique dessus ?
- ~~Comment afficher les profils sans photo si l'avatar est obligatoire mais non encore fourni ?~~ → Résolu : avatar auto-généré depuis les initiales affiché par défaut
- Que se passe-t-il si un profil vérifié est ultérieurement suspendu ou supprimé par un admin ?
- Comment gérer les profils en double (même personne qui crée deux comptes avec emails différents) ?

---

## Clarifications

### Session 2026-04-07

- Q: Quel outillage de qualité de code sera configuré ? → A: Laravel Pint (formatage PSR-12) + Larastan (analyse statique niveau 5+) + vérification automatique en CI
- Q: Quelle stratégie d'observabilité en production ? → A: Sentry (tracking erreurs temps-réel) + logs structurés Laravel
- Q: Comportement si l'upload d'avatar échoue (fichier invalide/trop lourd) ? → A: Erreur inline sous le champ, autres champs conservés + avatar auto-généré depuis les initiales utilisé par défaut jusqu'à upload réel
- Q: Quelle cible de disponibilité pour le MVP ? → A: Best effort — monitoring Uptime Robot + alerte panne, pas d'SLA formel
- Q: Comportement si la livraison d'un email de contact échoue ? → A: 3 retries automatiques (backoff exponentiel) → si échec total : log Sentry + job en failed_jobs, utilisateur non notifié

---

## Requirements *(mandatory)*

### Functional Requirements

**Authentification**

- **FR-001**: Le système DOIT permettre l'inscription via email et mot de passe
- **FR-002**: Le système DOIT envoyer un email de vérification après inscription et restreindre l'accès complet jusqu'à confirmation
- **FR-003**: Les utilisateurs DOIVENT pouvoir réinitialiser leur mot de passe via un lien email
- **FR-004**: Le système DOIT maintenir les sessions utilisateurs de manière sécurisée
- **FR-005**: Le système DOIT permettre la déconnexion explicite

**Profils**

- **FR-006**: Les utilisateurs DOIVENT pouvoir créer et modifier leur profil avec : nom complet, bio (max 500 caractères), photo de profil (optionnelle — un avatar auto-généré depuis les initiales est affiché par défaut), localisation (pays/ville), années d'études (début/fin), titre professionnel, entreprise actuelle, secteur d'activité, compétences (tags multi-sélect), liens optionnels (LinkedIn/portfolio)
- **FR-006a**: Si l'upload d'avatar échoue (format invalide, taille > 2MB, image corrompue), le système DOIT afficher une erreur inline sous le champ avatar sans effacer les autres champs du formulaire. L'avatar auto-généré reste utilisé jusqu'au prochain upload réussi.
- **FR-007**: Le système DOIT afficher un badge de vérification sur les profils approuvés par un admin
- **FR-008**: La modification de profil DOIT être restreinte au propriétaire du profil
- **FR-009**: Chaque profil DOIT avoir une page publique accessible sans connexion

**Recherche**

- **FR-010**: Le système DOIT permettre la recherche fulltext dans les profils (nom, titre, entreprise)
- **FR-011**: Le système DOIT proposer des filtres combinables : métier/titre, secteur d'activité, localisation (pays/région), plage d'années d'études, compétences, profils vérifiés uniquement
- **FR-012**: Les résultats de recherche DOIVENT s'afficher en moins de 500ms pour un annuaire de 1000+ profils
- **FR-013**: Le système DOIT paginer les résultats et conserver les filtres actifs lors du changement de page
- **FR-014**: Les résultats DOIVENT être triables par pertinence ou date d'inscription

**Contact**

- **FR-015**: Les utilisateurs connectés DOIVENT pouvoir envoyer un message à un autre membre (sujet + corps du message)
- **FR-016**: Le système DOIT envoyer une notification email au destinataire contenant le message et l'email de l'expéditeur pour réponse directe
- **FR-017**: Le système DOIT envoyer une confirmation d'envoi à l'expéditeur
- **FR-018**: Tous les messages DOIVENT être stockés en base pour historique
- **FR-018a**: Les emails de notification DOIVENT être envoyés via une queue avec 3 tentatives automatiques (backoff exponentiel). En cas d'échec des 3 tentatives, le job est enregistré dans `failed_jobs` et l'erreur est capturée par Sentry. L'expéditeur n'est pas notifié de l'échec de livraison.

**Blog**

- **FR-019**: Les utilisateurs connectés DOIVENT pouvoir créer des articles avec un éditeur de contenu riche (texte formaté, listes, images, embeds vidéo)
- **FR-020**: Les articles DOIVENT supporter les statuts : brouillon, publié, archivé
- **FR-021**: Les articles en brouillon NE DOIVENT PAS être accessibles publiquement
- **FR-022**: Les articles publiés DOIVENT afficher l'auteur, la date de publication et le temps de lecture estimé
- **FR-023**: Les utilisateurs connectés DOIVENT pouvoir soumettre des commentaires sur les articles publiés
- **FR-024**: Les commentaires DOIVENT être soumis à modération avant publication

**Code Quality**

- **FR-030**: Le projet DOIT utiliser Laravel Pint pour le formatage automatique du code (standard PSR-12), configurable via `pint.json` à la racine
- **FR-031**: Le projet DOIT utiliser Larastan (PHPStan niveau 5 minimum) pour l'analyse statique, configurable via `phpstan.neon`
- **FR-032**: Les deux outils DOIVENT être exécutés automatiquement en CI avant tout merge — un échec bloque l'intégration

**Observabilité**

- **FR-033**: Le système DOIT intégrer Sentry pour le tracking des erreurs non gérées en production (exceptions, erreurs de queue, échecs d'envoi d'email)
- **FR-034**: Le système DOIT produire des logs structurés pour toutes les opérations critiques : authentification, envoi de messages, actions de modération

**Modération**

- **FR-025**: Les administrateurs DOIVENT avoir accès à un panel dédié pour gérer profils, articles et commentaires
- **FR-026**: Le système DOIT permettre aux admins d'approuver ou rejeter des profils (avec raison optionnelle en cas de rejet)
- **FR-027**: Le système DOIT notifier les membres par email du résultat de la vérification de leur profil
- **FR-028**: Le système DOIT enregistrer un journal des actions de modération (qui a fait quoi, quand)
- **FR-029**: Le panel admin DOIT afficher des statistiques de base : total utilisateurs, posts publiés ce mois, nouvelles inscriptions ce mois

### Key Entities

- **Utilisateur**: Compte d'accès avec email, mot de passe, statut de vérification email et rôle (user/admin/moderator)
- **Profil**: Fiche professionnelle publique d'un utilisateur avec informations personnelles, professionnelles, compétences et statut de vérification communautaire
- **Compétence (Skill)**: Tag de compétence professionnelle réutilisable, associé à plusieurs profils
- **Secteur**: Catégorie d'activité professionnelle (Agriculture, IT, Commerce, Santé, etc.)
- **Message de contact**: Communication d'un membre vers un autre, stockée avec sujet, corps, expéditeur et destinataire
- **Catégorie de blog (BlogCategory)**: Regroupement thématique des articles (ex: Actualités, Portraits, Opportunités). Chaque article peut appartenir à une catégorie.
- **Article de blog**: Contenu éditorial riche avec titre, contenu formaté, catégorie, tags, statut de publication et auteur
- **Commentaire**: Réponse textuelle à un article, soumise à modération avant publication
- **Log de modération**: Trace horodatée des actions administratives (vérification, modération)

---

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Un nouveau membre peut créer son compte, vérifier son email et compléter son profil en moins de 5 minutes
- **SC-002**: Les recherches dans l'annuaire retournent des résultats en moins de 500ms, même avec 1000+ profils enregistrés
- **SC-003**: 95% des messages de contact arrivent à destination dans les 2 minutes suivant l'envoi
- **SC-004**: Un administrateur peut traiter (approuver ou rejeter) un profil en moins de 2 minutes depuis le panel de modération
- **SC-005**: La plateforme est utilisable sur mobile et desktop sans dégradation fonctionnelle
- **SC-006**: Le temps de chargement initial des pages principales est inférieur à 2 secondes
- **SC-007**: Les commentaires soumis ne sont visibles publiquement qu'après approbation explicite d'un modérateur
- **SC-008**: Les erreurs non gérées en production sont capturées par Sentry dans les 30 secondes suivant leur occurrence
- **SC-009**: Les interruptions de service sont détectées par Uptime Robot dans les 5 minutes et notifiées à l'équipe — aucun SLA formel d'uptime n'est défini pour le MVP

---

## Assumptions

- Les utilisateurs ont un accès à internet stable et un navigateur web récent (pas d'application mobile native pour le MVP)
- La photo de profil est **optionnelle** — un avatar auto-généré depuis les initiales du nom complet est affiché par défaut. Si l'utilisateur upload une photo, elle doit faire minimum 200x200px et peser moins de 2MB.
- Un seul niveau de rôle admin est suffisant pour le MVP ; la distinction modérateur/admin sera introduite dans une version ultérieure
- Le système de messagerie en temps réel (inbox persistante) est hors scope pour le MVP — seul le formulaire de contact par email est inclus
- La recherche fulltext native est suffisante pour le MVP ; un moteur de recherche dédié est envisagé pour une version future si le volume le justifie
- L'internationalisation (i18n) est hors scope pour le MVP — la plateforme sera en français uniquement
- Les utilisateurs peuvent s'auto-inscrire sans invitation préalable
- Les profils non vérifiés restent visibles dans l'annuaire avec un indicateur "non vérifié" (la vérification par les admins est asynchrone)
- Le contenu du blog peut être créé par tout membre connecté, soumis à la même modération que les commentaires
- Les performances cibles sont mesurées avec un dataset de 1000+ profils en conditions normales d'utilisation
- Le formatage et l'analyse statique (Pint + Larastan) sont appliqués en CI — le code non conforme ne peut pas être mergé
- Sentry est configuré dès le déploiement initial en production — pas de monitoring pour l'environnement de développement local
- Aucun SLA d'uptime formel pour le MVP — Uptime Robot surveille la disponibilité et alerte en cas de panne (best effort)
- Les échecs de livraison email (après 3 retries) sont gérés silencieusement côté utilisateur — Sentry + `failed_jobs` assurent la visibilité opérationnelle
