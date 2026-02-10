# 🔎 Findmi

**Findmi** est une plateforme web développée en **PHP, HTML, CSS et JavaScript** permettant de publier, rechercher et gérer des annonces.  
Le projet vise à faciliter la mise en relation entre utilisateurs à travers un système simple, rapide et intuitif.

---

## 📋 Tables des matières

- [Fonctionnalités](#-fonctionnalités)
- [Architecture](#-architecture)
- [Technologies](#-technologies-utilisées)
- [Installation](#-installation-et-configuration)
- [Structure du projet](#-structure-du-projet)
- [Configuration avancée](#-configuration-avancée)
- [Pages et fonctionnalités](#-pages--fonctionnalités)
- [Système d'administration](#-système-dadministration)
- [Internationalisation (i18n)](#-internationalisation-i18n)
- [Troubleshooting](#-troubleshooting)
- [Contribution](#-contribution)
- [Auteur](#-auteur)

---

## 🚀 Fonctionnalités

### Utilisateurs
- ✅ Inscription et connexion sécurisées (password_hash/verify)
- ✅ Gestion complète du profil (nom, email, téléphone)
- ✅ Changement de mot de passe avec validation
- ✅ Historique d'activité et statistiques personnelles
- ✅ Dashboard utilisateur avec stats et raccourcis

### Annonces
- ✅ Publication d'annonces avec titre, description, images
- ✅ Modification et suppression d'annonces
- ✅ Statut d'annonce : "en attente", "active", "retrouvée", "fermée"
- ✅ Recherche avancée par catégorie, localisation, mots-clés
- ✅ Détails riches d'annonce avec images et contact
- ✅ Upload sécurisé de fichiers (images uniquement)

### Messagerie & Modération
- ✅ Système de messagerie entre utilisateurs (conversations)
- ✅ Messages avec validation par autorité/modérateur
- ✅ Tableaux d'historique (approuvés, rejetés, en attente)
- ✅ Workflow de modération avec transactions BD
- ✅ Marquage d'annonce comme "retrouvée" lors approbation
- ✅ Notifications intégrées

### Administration
- ✅ Tableau de bord admin avec statistiques globales
- ✅ Modération des messages avec visualisation par statut
- ✅ Gestion des permissions et des autorités
- ✅ Paramètres du site (nom, email, langue)
- ✅ Mode maintenance global avec page personnalisée
- ✅ Historique des actions de validation

### Site
- ✅ Page d'accueil avec hero section et recherche
- ✅ À propos et informations
- ✅ Contact avec envoi email (PHPMailer)
- ✅ Mentions légales et politique de confidentialité
- ✅ Footer avec liens importants
- ✅ Navigation responsive

### Sécurité & Multilingue
- ✅ Validation côté serveur de tous les formulaires
- ✅ CSRF tokens pour les actions sensibles
- ✅ Sessions PHP sécurisées avec timeout
- ✅ Protection des données personnelles
- ✅ Support multilingue (FR/EN)
- ✅ Système i18n avec traductions personnalisables

---

## 🏗️ Architecture

### Flux de données

```
┌──────────────┐
│  Utilisateur │
└──────┬───────┘
       │
       ▼
┌──────────────────────────────┐
│  Page HTTP (PHP)             │
│  (index.php, dashboard, etc) │
└──────┬───────────────────────┘
       │
       ▼
┌──────────────────────────────┐
│  Logique métier              │
│  (core/db_connect.php)       │
│  (include fichiers)          │
└──────┬───────────────────────┘
       │
       ▼
┌──────────────────────────────┐
│  PDO / MySQL                 │
│  (findmi_db via BD schema)   │
└──────────────────────────────┘
```

---

## 🛠️ Technologies utilisées

### Frontend
- **HTML5** — Structure sémantique
- **CSS3** — Flexbox, Grid, animations, gradients
- **JavaScript Vanilla** — Pas de framework (léger et rapide)
- **Font Awesome 6.4** — Icônes modernes
- **Google Fonts** — Typographie

### Backend
- **PHP 8.x** — Langage serveur (procédural)
- **MySQL 8.0** — Base de données relationnelle
- **PDO** — Abstraction BD avec requêtes préparées
- **PHPMailer** — Envoi d'emails SMTP ( a configurer dans la page contact: Votre_mot_de_passe &Votre_mail)

### Infrastructure
- **XAMPP / WAMP / Laragon** — Serveur local (Apache + MySQL)
- **Git & GitHub** — Versioning
- **Composer** — Gestion des dépendances

---

## ⚙️ Installation et configuration

### Prérequis
- PHP 8.0+, MySQL 5.7+, Apache
- Composer (optionnel)


### Configuration BD

`core/db_connect.php` :
```php
$host = 'localhost';
$dbname = 'findmi_db';
$user = 'root';
$pass = '';
```

---

## 📁 Structure du projet

```
findmi_site/
├── core/          # DB, i18n, configs
├── admin/         # Tableau de bord admin
├── lang/          # Traductions FR/EN
├── css/           # Styles
├── uploads/       # Images annonces
├── index.php      # Accueil
├── dashboard.php  # Mes annonces
├── profil.php     # Profil utilisateur
├── messagerie.php # Conversations
└── findmi_db.sql  # Dump BD
```

---

## 🔧 Configuration

### Paramètres site (depuis DB)

- `SITE_NAME` — Nom du site
- `SITE_EMAIL` — Email contact
- `DEFAULT_LANGUAGE` — Langue (fr/en)
- `MAINTENANCE_MODE` — Mode maintenance


### Connexion admin: /admin/connexion.php

- Admin 01: `Police Nationale` —--> mail: `police@gmail.com`
                                    mot de passe: `password123`

- Admin 02: `Mairie` —--> mail: `mairie@gmail.com`
                          mot de passe: `password123`


### Connexion user: /admin/connexion.php

- User 01: `Kevin` —--> mail: `kev@gmail.com`
                                    mot de passe: `password123`

- User 02: `Yoyo` —--> mail: `yoyo@gmail.com`
                          mot de passe: `password123`


### Mode Maintenance

Crée `.maintenance` ou update BD :
```sql
UPDATE settings SET setting_value = '1' WHERE setting_key = 'maintenance_mode';
```

---

## 📄 Pages principales

| Page | URL |
|------|-----|
| Accueil | `/` |
| Connexion | `/connexion.php` |
| Dashboard | `/dashboard.php` |
| Profil | `/profil.php` |
| Messagerie | `/messagerie.php` |
| Admin | `/admin/` |
| Modération | `/admin/moderation_messages.php` |

---

## 🐛 FAQ

**Q: "Undefined constant SITE_NAME"**
A: Ajoute `require_once 'core/db_connect.php';` en haut de page

**Q: Erreur 503 Maintenance**
A: Supprime `.maintenance` ou désactive en BD

**Q: Colonne manquante dans messages**
A: Exécute la migration dans `historique_messages.php`

---

## 🤝 Contribution

Fork → Branch → Commit → Pull Request

Domaines à améliorer :
- Tests unitaires
- API REST
- WebSockets messagerie
- Géolocalisation
- Analytics

---

## 👨‍💻 Auteur

**Kevin Savadogo** — Développeur Web
- 🔗 GitHub : [@Kevinsvdg01](https://github.com/Kevinsvdg01)
- 📍 Burkina Faso

---

## 📄 Licence

**MIT License** — Libre utilisation à fins éducatives et personnelles

**Merci d'utiliser Findmi ! 🙏**

## ⚙️ Installation et configuration

### 1️⃣ Cloner le projet
```bash
git clone https://github.com/Kevinsvdg01/Findmi.git
2️⃣ Déplacer le projet
Place le dossier dans :

htdocs (XAMPP)

ou www (WAMP)

3️⃣ Importer la base de données
Ouvre phpMyAdmin

Crée une base de données (ex: findmi_db)

Importe le fichier findmi_db.sql

4️⃣ Configurer la connexion à la base de données
Dans le dossier core/, vérifie les paramètres :

nom de la base

utilisateur MySQL

mot de passe

5️⃣ Lancer le projet
Dans ton navigateur :

http://localhost/findmi_site
🔐 Sécurité
Validation des formulaires côté serveur

Sessions PHP pour la gestion des connexions

Accès restreint aux pages sensibles

📌 Améliorations futures
🔔 Notifications en temps réel

📱 Version responsive avancée

🗺️ Géolocalisation des annonces

🛡️ Renforcement de la sécurité

📊 Statistiques et analytics

📄 Licence
Ce projet est open-source et peut être utilisé à des fins éducatives et personnelles.
