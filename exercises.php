<?php
session_start();
require 'db.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION['user_id'];
// Vérifier si le paramètre `course_id` est passé dans l'URL
if (!isset($_GET['course_id'])) {
    die("Cours non spécifié.");
}
$course_id = $_GET['course_id'];
// Récupérer les exercices associés au cours
$stmt = $pdo->prepare("SELECT e.id, e.number, e.description, e.expected_result
                       FROM exercises e
                       WHERE e.course_id = ?");
$stmt->execute([$course_id]);
$exercises = $stmt->fetchAll();
// Récupérer le nom du cours
$stmt = $pdo->prepare("SELECT name FROM courses WHERE id = ?");
$stmt->execute([$course_id]);
$course = $stmt->fetch();
// Préparation de la requête pour les soumissions
$submission_stmt = $pdo->prepare("SELECT score, submitted_at FROM submissions WHERE exercise_id = ? AND user_id = ? ORDER BY submitted_at DESC LIMIT 1");
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exercices - <?= htmlspecialchars($course['name']) ?></title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
<div class="container">
    <h1>Exercices pour le cours : <?= htmlspecialchars($course['name']) ?></h1>
    <?php if (count($exercises) > 0): ?>
        <ul>
            <?php foreach ($exercises as $exercise): 
                // Récupérer la dernière soumission pour cet exercice par cet utilisateur
                $submission_stmt->execute([$exercise['id'], $user_id]);
                $submission = $submission_stmt->fetch();
                $has_submission = ($submission !== false);
            ?>
                <li>
                    <strong>Exercice <?= htmlspecialchars($exercise['number']) ?>:</strong> <?= htmlspecialchars($exercise['description']) ?>
                    
                    <?php if ($has_submission): ?>
                        <div class="submission-info">
                            <span class="score">Dernière note: <?= htmlspecialchars($submission['score']) ?>/20</span>
                            <span class="date">Soumis le: <?= date('d/m/Y à H:i', strtotime($submission['submitted_at'])) ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <a href="submit_exercise.php?exercise_id=<?= $exercise['id'] ?>&course_id=<?= $course_id ?>" class="btn">
                        <?= $has_submission ? 'Réessayer l\'exercice' : 'Soumettre l\'exercice' ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>Aucun exercice trouvé pour ce cours.</p>
    <?php endif; ?>
    <a href="dashboard.php" class="btn">Retour au tableau de bord</a>
    <a href="logout.php" class="btn">Se déconnecter</a>
</div>
</body>
</html>
