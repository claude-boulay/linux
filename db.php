<?php
// Charger les variables d'environnement depuis le fichier .env
function loadEnv($path) {
    if (!file_exists($path)) {
        throw new Exception("Le fichier .env n'existe pas");
    }
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Ignorer les commentaires
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        
        // Définir la variable d'environnement
        putenv("$name=$value");
        $_ENV[$name] = $value;
    }
}

// Charger les variables d'environnement
try {
    loadEnv(__DIR__ . '/.env');
} catch (Exception $e) {
    die("Erreur de chargement du fichier .env : " . $e->getMessage());
}

// Fonction pour tenter de se connecter à la base de données
function connectToDatabase($host, $dbname, $user, $pass, $driver) {
    try {
        $pdo = new PDO("$driver:host=$host;dbname=$dbname", $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_TIMEOUT => 3, // Timeout en secondes pour la connexion
        ]);
        return $pdo;
    } catch (PDOException $e) {
        // Retourner false pour indiquer l'échec de connexion
        return false;
    }
}

// Récupérer les informations de connexion depuis les variables d'environnement
$driver = getenv('DB_DRIVER');
$dbname = getenv('DB_NAME');
$user = getenv('DB_USER');
$pass = getenv('DB_PASS');

// Récupérer les hôtes principal et secondaire
$primaryHost = getenv('DB_PRIMARY_HOST');
$replicaHost = getenv('DB_REPLICA_HOST');

// Tentative de connexion à la base principale
$pdo = connectToDatabase($primaryHost, $dbname, $user, $pass, $driver);

// Si la connexion à la base principale échoue, essayer la réplique
if (!$pdo) {
    // Journaliser la tentative de basculement
    error_log("Tentative de basculement sur la base de données répliquée");
    
    $pdo = connectToDatabase($replicaHost, $dbname, $user, $pass, $driver);
    
    // Si la connexion à la réplique réussit, journaliser le succès
    if ($pdo) {
        error_log("Basculement réussi vers la base de données répliquée");
    }
}

// Si aucune connexion n'a pu être établie, afficher une erreur
if (!$pdo) {
    die("Erreur critique : Impossible de se connecter aux bases de données principale et répliquée.");
}
?>
