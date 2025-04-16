<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Récupérer tous les cours auxquels l'utilisateur est inscrit
$stmt = $pdo->prepare("SELECT DISTINCT c.id, c.name
                       FROM courses c
                       ");
$stmt->execute();
$courses = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>

<div class="container">
    <h1>Tableau de bord</h1>
  

    <?php if (count($courses) > 0): ?>
        <h2>Cours disponibles</h2>
        <ul>
            <?php foreach ($courses as $course): ?>
                <li>
                    <a href="exercises.php?course_id=<?= htmlspecialchars($course['id']) ?>"><?= htmlspecialchars($course['name']) ?></a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>Aucun cours trouvé.</p>
    <?php endif; ?>

    <a href="logout.php" class="btn">Se déconnecter</a>
</div>

</body>
</html>
