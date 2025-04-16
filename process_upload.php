<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    die("Accès refusé.");
}

$user_id = $_SESSION['user_id'];
$course_id = $_POST['course_id'];
$exercise_number = $_POST['exercise_number'];
$language = $_POST['language'];

if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    die("Erreur d'upload du fichier.");
}

// Vérification de l'extension
$allowed_exts = ['py' => 'Python', 'c' => 'C'];
$ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
if (!isset($allowed_exts[$ext]) || $allowed_exts[$ext] !== $language) {
    die("Erreur : Langage et extension non cohérents.");
}

$filename = uniqid() . ".$ext";
move_uploaded_file($_FILES['file']['tmp_name'], "uploads/$filename");

// Récupérer l'exercice
$stmt = $pdo->prepare("SELECT id FROM exercises WHERE course_id = ? AND number = ?");
$stmt->execute([$course_id, $exercise_number]);
$exercise = $stmt->fetch();
if (!$exercise) {
    die("Exercice introuvable.");
}
$exercise_id = $exercise['id'];

// Lancer l'évaluation
$command = escapeshellcmd("bash execute_and_grade.sh $user_id $course_id $exercise_id $filename");
shell_exec($command);

header("Location: dashboard.php");
exit();
?>
