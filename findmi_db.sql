-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : mar. 10 fév. 2026 à 17:29
-- Version du serveur : 9.1.0
-- Version de PHP : 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `findmi_db`
--

-- --------------------------------------------------------

--
-- Structure de la table `annonces`
--

DROP TABLE IF EXISTS `annonces`;
CREATE TABLE IF NOT EXISTS `annonces` (
  `id_annonce` int NOT NULL AUTO_INCREMENT,
  `titre` varchar(255) NOT NULL,
  `description` text,
  `type_annonce` enum('perdu','trouve') NOT NULL,
  `statut_annonce` enum('en_attente_validation','publiee','rejetee','retrouve','supprimee') NOT NULL DEFAULT 'en_attente_validation',
  `motif_rejet` text,
  `date_perte_trouve` date NOT NULL,
  `lieu_perte_trouve` varchar(255) NOT NULL,
  `nom_sur_document` varchar(150) DEFAULT NULL,
  `photo_url` varchar(255) DEFAULT NULL,
  `date_creation` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_validation` datetime DEFAULT NULL,
  `id_utilisateur` int NOT NULL,
  `id_categorie` int NOT NULL,
  `id_validateur` int DEFAULT NULL,
  PRIMARY KEY (`id_annonce`),
  KEY `id_utilisateur` (`id_utilisateur`),
  KEY `id_categorie` (`id_categorie`)
) ENGINE=MyISAM AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `annonces`
--

INSERT INTO `annonces` (`id_annonce`, `titre`, `description`, `type_annonce`, `statut_annonce`, `motif_rejet`, `date_perte_trouve`, `lieu_perte_trouve`, `nom_sur_document`, `photo_url`, `date_creation`, `date_validation`, `id_utilisateur`, `id_categorie`, `id_validateur`) VALUES
(21, 'Reçu de paiement', 'recudepaiement....', 'perdu', 'en_attente_validation', NULL, '2026-02-04', 'Koudougou vers la mosquée', 'Savadogo w', 'uploads/images/doc_4_1770743655.jpg', '2026-02-10 17:14:15', NULL, 4, 5, NULL),
(20, 'Carte National d\'Identite', 'carteI......', 'perdu', 'publiee', NULL, '2026-02-01', 'Bobo vers la grande mosquée', 'Kabre Jonas', 'uploads/images/doc_4_1770743613.jpg', '2026-02-10 17:13:33', '2026-02-10 17:16:28', 4, 1, 1),
(19, 'Permis de conduire', 'permis.....', 'perdu', 'rejetee', 'photo illisible', '2026-02-06', 'Marcher Nab Raaga a Samandin', 'Fofana T', 'uploads/images/doc_4_1770743562.jpg', '2026-02-10 17:12:42', '2026-02-10 17:17:25', 4, 4, 1),
(18, 'Reçu de paiement', 'reçu......', 'perdu', 'rejetee', 'informations sensibles', '2026-02-03', 'En face Hotel Pacific', 'Savadogo w', 'uploads/images/doc_3_1770743458.jpg', '2026-02-10 17:10:58', '2026-02-10 17:16:55', 3, 5, 1),
(17, 'Passeport', 'passeport......', 'perdu', 'publiee', NULL, '2026-02-06', 'Koudougou vers la mosquée', 'O Karim', 'uploads/images/doc_3_1770743398.jpg', '2026-02-10 17:09:58', '2026-02-10 17:23:45', 3, 3, 2),
(16, 'Carte grise', 'carte grise......', 'perdu', 'retrouve', NULL, '2026-02-17', 'samandin vers Hotel Eden Park', 'Savadogo w', 'uploads/images/doc_3_1770743349.jpg', '2026-02-10 17:09:09', '2026-02-10 17:24:02', 3, 2, 2);

-- --------------------------------------------------------

--
-- Structure de la table `autorites`
--

DROP TABLE IF EXISTS `autorites`;
CREATE TABLE IF NOT EXISTS `autorites` (
  `id_autorite` int NOT NULL AUTO_INCREMENT,
  `nom_autorite` varchar(255) NOT NULL,
  `email` varchar(155) NOT NULL,
  `mot_de_passe` varchar(190) NOT NULL,
  `role` varchar(50) NOT NULL,
  `est_actif` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_autorite`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `autorites`
--

INSERT INTO `autorites` (`id_autorite`, `nom_autorite`, `email`, `mot_de_passe`, `role`, `est_actif`) VALUES
(1, 'Police National', 'police@gmail.com', '$2y$12$M5PNZxnV0gFJWsjVje0Cye4tSG4KcknGFHcZpmDIrRx77Tix2agUe', 'Police N', 1),
(2, 'Mairie', 'mairie@gmail.com', '$2y$12$M5PNZxnV0gFJWsjVje0Cye4tSG4KcknGFHcZpmDIrRx77Tix2agUe', 'Mairie', 1);

-- --------------------------------------------------------

--
-- Structure de la table `categories`
--

DROP TABLE IF EXISTS `categories`;
CREATE TABLE IF NOT EXISTS `categories` (
  `id_categorie` int NOT NULL AUTO_INCREMENT,
  `nom_categorie` varchar(100) NOT NULL,
  PRIMARY KEY (`id_categorie`),
  UNIQUE KEY `nom_categorie` (`nom_categorie`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `categories`
--

INSERT INTO `categories` (`id_categorie`, `nom_categorie`) VALUES
(1, 'Carte Nationale d\'Identité'),
(2, 'Carte Grise'),
(3, 'Passeport'),
(4, 'Permis de Conduire'),
(5, 'Autres (reçu, carte bancaire...)');

-- --------------------------------------------------------

--
-- Structure de la table `conversations`
--

DROP TABLE IF EXISTS `conversations`;
CREATE TABLE IF NOT EXISTS `conversations` (
  `id_conversation` int NOT NULL AUTO_INCREMENT,
  `id_annonce` int NOT NULL,
  `id_utilisateur_1` int NOT NULL,
  `id_utilisateur_2` int NOT NULL,
  `statut` enum('en attente','en cours','resolue','fermee') NOT NULL DEFAULT 'en attente',
  `date_creation` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_derniere_activite` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_conversation`),
  KEY `id_annonce` (`id_annonce`),
  KEY `id_utilisateur_1` (`id_utilisateur_1`),
  KEY `id_utilisateur_2` (`id_utilisateur_2`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `conversations`
--

INSERT INTO `conversations` (`id_conversation`, `id_annonce`, `id_utilisateur_1`, `id_utilisateur_2`, `statut`, `date_creation`, `date_derniere_activite`) VALUES
(2, 16, 4, 3, 'resolue', '2026-02-10 17:19:41', '2026-02-10 17:24:02'),
(3, 20, 3, 4, 'en attente', '2026-02-10 17:21:55', '2026-02-10 17:21:55');

-- --------------------------------------------------------

--
-- Structure de la table `messages`
--

DROP TABLE IF EXISTS `messages`;
CREATE TABLE IF NOT EXISTS `messages` (
  `id_message` int NOT NULL AUTO_INCREMENT,
  `id_conversation` int NOT NULL,
  `id_utilisateur` int NOT NULL,
  `texte_message` text NOT NULL,
  `date_envoi` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `valide_par_admin` tinyint(1) NOT NULL DEFAULT '0',
  `date_validation` datetime DEFAULT NULL,
  `id_validateur` int DEFAULT NULL,
  `est_rejete` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id_message`),
  KEY `id_conversation` (`id_conversation`),
  KEY `id_utilisateur` (`id_utilisateur`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `messages`
--

INSERT INTO `messages` (`id_message`, `id_conversation`, `id_utilisateur`, `texte_message`, `date_envoi`, `valide_par_admin`, `date_validation`, `id_validateur`, `est_rejete`) VALUES
(2, 2, 4, 'Hello j\'ai retrouver votre document', '2026-02-10 17:20:03', 1, '2026-02-10 17:24:02', 2, 0),
(3, 3, 3, 'Salut vordnxjjjzx', '2026-02-10 17:22:10', 0, '2026-02-10 17:24:30', 2, 1);

-- --------------------------------------------------------

--
-- Structure de la table `settings`
--

DROP TABLE IF EXISTS `settings`;
CREATE TABLE IF NOT EXISTS `settings` (
  `setting_key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `setting_value` text COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `settings`
--

INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('allow_registration', '1'),
('default_language', 'fr'),
('items_per_page', '10'),
('maintenance_mode', '0'),
('notify_admin_new_message', '1'),
('site_email', 'contact@findmi.local'),
('site_name', 'Findmi');

-- --------------------------------------------------------

--
-- Structure de la table `utilisateurs`
--

DROP TABLE IF EXISTS `utilisateurs`;
CREATE TABLE IF NOT EXISTS `utilisateurs` (
  `id_utilisateur` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL DEFAULT '',
  `email` varchar(155) NOT NULL,
  `mot_de_passe` varchar(200) NOT NULL,
  `telephone` varchar(20) NOT NULL,
  `role` enum('utilisateur','admin') NOT NULL DEFAULT 'utilisateur',
  `date_inscription` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `est_actif` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_utilisateur`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `utilisateurs`
--

INSERT INTO `utilisateurs` (`id_utilisateur`, `nom`, `email`, `mot_de_passe`, `telephone`, `role`, `date_inscription`, `est_actif`) VALUES
(3, 'kevin', 'kev@gmail.com', '$2y$12$M5PNZxnV0gFJWsjVje0Cye4tSG4KcknGFHcZpmDIrRx77Tix2agUe', '65000000', 'utilisateur', '2026-02-09 15:48:24', 1),
(4, 'Yoyo', 'yoyo@gmail.com', '$2y$12$k6DGMOYEo4j85Y8i3RSDKOVZjq6fy/FtbOZHqSmqTPKsYQhOh3Hma', '65000001', 'utilisateur', '2026-02-09 17:09:12', 1);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
