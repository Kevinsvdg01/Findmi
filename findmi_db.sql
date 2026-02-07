-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : mer. 16 juil. 2025 à 15:38
-- Version du serveur : 8.2.0
-- Version de PHP : 8.3.0

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
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `annonces`
--

INSERT INTO `annonces` (`id_annonce`, `titre`, `description`, `type_annonce`, `statut_annonce`, `motif_rejet`, `date_perte_trouve`, `lieu_perte_trouve`, `nom_sur_document`, `photo_url`, `date_creation`, `date_validation`, `id_utilisateur`, `id_categorie`, `id_validateur`) VALUES
(1, 'Passport perdu a samandin', '', 'perdu', 'en_attente_validation', NULL, '2025-07-14', 'samandin vers Hotel Eden Park', 'Savadogo ibrahim w', 'uploads/images/doc_1_1752517349.png', '2025-07-14 18:22:29', NULL, 1, 2, NULL),
(2, 'Passport perdu a samandin', '', 'perdu', 'en_attente_validation', NULL, '2025-07-14', 'samandin vers Hotel Eden Park', 'Savadogo ibrahim w', 'uploads/images/doc_1_1752517790.png', '2025-07-14 18:29:50', NULL, 1, 2, NULL);

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `utilisateurs`
--

DROP TABLE IF EXISTS `utilisateurs`;
CREATE TABLE IF NOT EXISTS `utilisateurs` (
  `id_utilisateur` int NOT NULL AUTO_INCREMENT,
  `email` varchar(155) NOT NULL,
  `mot_de_passe` varchar(200) NOT NULL,
  `telephone` varchar(20) NOT NULL,
  `date_inscription` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `est_actif` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_utilisateur`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `utilisateurs`
--

INSERT INTO `utilisateurs` (`id_utilisateur`, `email`, `mot_de_passe`, `telephone`, `date_inscription`, `est_actif`) VALUES
(1, 'yoyo@gmail.com', '$2y$10$hq4nh/x1UrRShgHnJaRlDuPYrviRVW3bhmH0v.UfL200VyHJihRIe', '6533', '2025-07-14 17:41:52', 1);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
