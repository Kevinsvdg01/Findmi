# 📨 Système de Messagerie et Modération - Findmi

## Vue d'ensemble

Le système de messagerie de Findmi permet aux utilisateurs de communiquer de manière sécurisée lorsqu'un objet perdu/trouvé pourrait correspondre à une annonce. **Tous les messages sont modérés par une autorité compétente avant d'être visibles** pour garantir la sécurité des utilisateurs.

---

## 🔄 Flux de Communication

### 1. **Affichage de l'annonce** (`annonce_detail.php`)
- **Utilisateur NON connecté** : Voir le bouton "Connectez-vous pour contacter le déclarant"
- **Utilisateur connecté** : Voir le bouton "Contacter le déclarant" qui redirige vers la messagerie

### 2. **Page de messagerie** (`messagerie.php`)
- **Accès** : Uniquement pour les utilisateurs connectés
- **Paramètre** : `?id_annonce=X` (ID de l'annonce)
- **Fonctionnalités** :
  - Affichage du récapitulatif de l'annonce
  - Informations du déclarant (nom, email, téléphone)
  - Historique des messages (si conversation existante)
  - Formulaire pour envoyer des messages
  - **Badge de modération** : "En attente de validation" pour les messages non approuvés

### 3. **Système de modération** (`admin/moderation_messages.php`)
- **Accès** : Autorités connectées via le panneau admin
- **Fonctionnalités** :
  - **Onglet "En attente"** : Messages à vérifier (en attente d'approbation)
  - **Onglet "Approuvés"** : Historique des messages validés
  - **Actions** :
    - ✅ **Approuver** : Le message devient visible à l'autre utilisateur
    - ❌ **Rejeter** : Le message est supprimé (ne s'affichera jamais)
  - **Statistiques** : Nombre de messages en attente, approuvés, conversations totales

---

## 🗄️ Structure de la Base de Données

### Table : `conversations`
```sql
CREATE TABLE conversations (
  id_conversation INT PRIMARY KEY AUTO_INCREMENT,
  id_annonce INT,
  id_utilisateur_1 INT,
  id_utilisateur_2 INT,
  statut ENUM('en attente','en cours','resolue','fermee'),
  date_creation DATETIME,
  date_derniere_activite DATETIME
)
```

### Table : `messages`
```sql
CREATE TABLE messages (
  id_message INT PRIMARY KEY AUTO_INCREMENT,
  id_conversation INT,
  id_utilisateur INT,
  texte_message TEXT,
  date_envoi DATETIME,
  valide_par_admin TINYINT (0=en attente, 1=approuvé),
  date_validation DATETIME,
  id_validateur INT
)
```

---

## 🔒 Contrôles de Sécurité

✅ **Session requise** : Seuls les utilisateurs connectés peuvent accéder à la messagerie

✅ **Vérification d'annonce** : L'annonce doit être publiée et valide

✅ **Prévention d'auto-contact** : Le déclarant ne peut pas se contacter lui-même

✅ **Validation de messages** : Tous les messages doivent être approuvés par une autorité avant de être visibles

✅ **Protection contre les injections** : HTML échappe (`htmlspecialchars()`) et requêtes préparées

---

## 👥 Rôles et Permissions

| Rôle | Accès Messagerie | Envoyer Messages | Modérer |
|------|------------------|-----------------|---------|
| **Utilisateur** | ✅ | ✅* | ❌ |
| **Autorité/Admin** | ✅ (lecture) | ❌ | ✅ |

_*Les messages des utilisateurs doivent être approuvés avant d'être visibles_

---

## 📝 Étapes pour Configurer

### 1. Mettre à jour la base de données
```sql
-- Exécuter les requêtes du fichier ou réimporter :
mysql -u [user] -p [database] < findmi_db.sql
```

### 2. Ajouter la colonne `nom` (si elle n'existe pas)
```sql
ALTER TABLE utilisateurs ADD COLUMN nom VARCHAR(100) NOT NULL DEFAULT '';
```

### 3. Vérifier la table `autorites`
Assurez-vous que la table `autorites` existe avec au moins une entrée pour tester la modération.

### 4. Mettre à jour la page de notification (optionnel)
Pour notifier les utilisateurs des messages approuvés, ajoutez un système d'email :
```php
// Dans moderation_messages.php après approbation
// Envoi d'email au destinataire
```

---

## 🎯 Cas d'Usage

### Scénario 1 : Utilisateur trouve un document
1. L'utilisateur se connecte et clique "Contacter le déclarant"
2. Redirigé vers la messagerie pour l'annonce
3. Envoie un message : "J'ai peut-être trouvé votre X"
4. **Message en attente de modération** ⏳
5. L'autorité approuve le message après vérification
6. Le déclarant voit le message et peut répondre
7. Sa réponse est aussi modérée avant d'être visible

### Scénario 2 : Message suspects
1. L'autorité détecte un message suspect lors de la modération
2. Clique sur "❌ Rejeter"
3. Le message est supprimé de la base de données
4. Les utilisateurs ne voient jamais ce message

---

## 🚀 Fonctionnalités Futures (Optionnel)

- [ ] Notifications par email lors de nouveaux messages
- [ ] Read receipts (confirmer la lecture)
- [ ] Marquage de conversation comme "résolue"
- [ ] Blocage d'utilisateurs
- [ ] Signalement de messages inappropriés
- [ ] Chat en temps réel (WebSocket)
- [ ] Pièces jointes

---

## ⚙️ Configuration Personnalisée

### Modifier la validation automatique
Pour activer les messages automatiques au-delà de 50 messages :
```php
// Dans moderation_messages.php
if ($pdo->query("SELECT COUNT(*) FROM messages WHERE valide_par_admin = 1")->fetchColumn() > 50) {
    // Auto-validation possible
}
```

### Ajouter des filtres de mots clés
```php
$mots_interdits = ['mailto:', 'http://', '@gmail'];
foreach ($mots_interdits as $mot) {
    if (strpos(strtolower($message), $mot) !== false) {
        // Rejeter automatiquement
    }
}
```

---

## 📞 Support

Pour toute question concernant le système de messagerie, consultez le code commenté dans :
- `messagerie.php` - Interface utilisateur et logique de conversation
- `admin/moderation_messages.php` - Panneaux de modération
- `annonce_detail.php` - Affichage conditionnel du bouton

---

**Dernière mise à jour** : 9 février 2026
