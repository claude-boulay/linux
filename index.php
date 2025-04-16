<?php
session_start();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil - Plateforme Étudiante</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7f6;
            margin: 0;
            padding: 0;
            color: #333;
        }

        h1 {
            text-align: center;
            color: #2c3e50;
            margin-top: 50px;
        }

        .container {
            width: 80%;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        p {
            font-size: 18px;
            line-height: 1.5;
        }

        a {
            color: #3498db;
            text-decoration: none;
            font-weight: bold;
        }

        a:hover {
            text-decoration: underline;
        }

        .footer {
            text-align: center;
            margin-top: 50px;
            font-size: 14px;
            color: #7f8c8d;
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #3498db;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 10px;
            font-weight: bold;
        }

        .btn:hover {
            background-color: #2980b9;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Bienvenue sur la plateforme</h1>

        <?php if (isset($_SESSION['user_id'])) : ?>
            <p>Vous êtes connecté.</p>
            <p><a class="btn" href="dashboard.php">Accéder à votre espace</a></p>
            <p><a href="logout.php">Se déconnecter</a></p>
        <?php else : ?>
            <p><a href="login.php">Se connecter</a> | <a href="register.php">S'inscrire</a></p>
        <?php endif; ?>
    </div>

    <div class="footer">
        <p>&copy; 2025 Plateforme Étudiante. Tous droits réservés.</p>
    </div>
</body>
</html>
