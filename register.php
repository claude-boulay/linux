<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'];

    if (!$email || empty($password)) {
        die("Email ou mot de passe invalide.");
    }

    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    $stmt = $pdo->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
    $stmt->execute([$email, $hashedPassword]);

    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - Plateforme Étudiante</title>
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
            width: 100%;
            max-width: 400px;
            margin: 0 auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        form {
            display: flex;
            flex-direction: column;
        }

        label {
            font-size: 16px;
            margin-bottom: 8px;
            color: #34495e;
        }

        input[type="email"],
        input[type="password"] {
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }

        button {
            padding: 12px;
            background-color: #3498db;
            color: #fff;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }

        button:hover {
            background-color: #2980b9;
        }

        .footer {
            text-align: center;
            margin-top: 50px;
            font-size: 14px;
            color: #7f8c8d;
        }

        .error {
            color: #e74c3c;
            text-align: center;
            font-size: 14px;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>S'inscrire</h1>

        <form method="POST">
            <label for="email">Email :</label>
            <input type="email" name="email" required>

            <label for="password">Mot de passe :</label>
            <input type="password" name="password" required>

            <button type="submit">S'inscrire</button>
        </form>
    </div>

    <div class="footer">
        <p>&copy; 2025 Plateforme Étudiante. Tous droits réservés.</p>
    </div>
</body>
</html>
