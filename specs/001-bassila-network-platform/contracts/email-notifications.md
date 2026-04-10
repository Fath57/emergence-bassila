# Email Notification Contracts

**Branch**: `001-bassila-network-platform` | **Date**: 2026-04-07

Ces contrats définissent les emails envoyés par la plateforme, leur déclencheur, leur destinataire et leur contenu minimum requis.

---

## ENV-001 — Vérification d'email à l'inscription

**Déclencheur**: Utilisateur crée un compte (POST /register)
**Destinataire**: Nouvel utilisateur (email saisi à l'inscription)
**Classe Mailable**: `App\Mail\EmailVerification` (standard Laravel)
**Mode d'envoi**: Synchrone (immédiat)

**Contenu requis**:
- Lien de vérification unique (valide 60 minutes)
- Nom de la plateforme
- Instruction claire pour cliquer sur le lien

**Condition de succès**: L'utilisateur peut cliquer sur le lien et activer son compte.

---

## ENV-002 — Confirmation d'envoi de message (expéditeur)

**Déclencheur**: Utilisateur envoie un message de contact (POST /contact/{profile})
**Destinataire**: Expéditeur du message
**Classe Mailable**: `App\Mail\ContactMessageSent`
**Mode d'envoi**: Asynchrone (queue)

**Contenu requis**:
- Confirmation que le message a bien été envoyé
- Rappel du sujet et du destinataire (nom du profil contacté)
- Date et heure d'envoi

**Condition de succès**: L'expéditeur sait que son message a été transmis.

---

## ENV-003 — Notification de message reçu (destinataire)

**Déclencheur**: Message de contact envoyé à un membre
**Destinataire**: Membre contacté (propriétaire du profil)
**Classe Mailable**: `App\Mail\ContactMessageReceived`
**Mode d'envoi**: Asynchrone (queue)

**Contenu requis**:
- Sujet du message
- Corps complet du message
- Nom et email de l'expéditeur (pour permettre une réponse directe par email)
- Lien vers le profil de l'expéditeur

**Condition de succès**: Le destinataire peut répondre directement à l'expéditeur par email sans passer par la plateforme.

---

## ENV-004 — Approbation de profil (profil vérifié)

**Déclencheur**: Admin approuve un profil depuis le panel de modération
**Destinataire**: Propriétaire du profil
**Classe Mailable**: `App\Mail\ProfileVerificationApproved`
**Mode d'envoi**: Asynchrone (queue)

**Contenu requis**:
- Message de félicitations : profil vérifié
- Explication du badge de vérification et de sa signification
- Lien vers leur profil public

**Condition de succès**: Le membre sait que son profil est maintenant vérifié et affiché avec le badge.

---

## ENV-005 — Rejet de profil (profil non approuvé)

**Déclencheur**: Admin rejette un profil depuis le panel de modération
**Destinataire**: Propriétaire du profil
**Classe Mailable**: `App\Mail\ProfileVerificationRejected`
**Mode d'envoi**: Asynchrone (queue)

**Contenu requis**:
- Information que le profil n'a pas été approuvé
- Raison du rejet (si fournie par l'admin, sinon message générique)
- Invitation à corriger le profil et à repostuler

**Condition de succès**: Le membre comprend pourquoi son profil a été rejeté et peut prendre des mesures correctives.

---

## Règles communes à tous les emails

1. **Expéditeur**: `noreply@bassila-network.com` (ou domaine configuré)
2. **Langue**: Français uniquement (MVP)
3. **Format**: HTML + plain text fallback
4. **Queue**: Tous les emails sauf ENV-001 doivent passer par une queue pour éviter de bloquer les requêtes HTTP
5. **Retry**: 3 tentatives en cas d'échec de livraison
6. **Logging**: Tous les envois doivent être loggés (succès et échecs)
