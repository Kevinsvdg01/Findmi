<?php
// core/db_connect.php

$host = 'localhost';        // Ou l'IP de votre serveur de BDD
$dbname = 'findmi_db';      // Le nom de la base de données que nous avons créée
$user = 'root';             // L'utilisateur de la BDD (par défaut 'root' sur XAMPP)
$pass = '';                 // Le mot de passe (par défaut vide sur XAMPP)

// Options de connexion PDO
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Gérer les erreurs comme des exceptions
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Récupérer les résultats en tableau associatif
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Utiliser de vraies requêtes préparées
];

try {
    // Création de l'instance PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass, $options);
} catch (\PDOException $e) {
    // En cas d'erreur de connexion, on arrête tout et on affiche un message
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}