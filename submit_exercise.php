<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Vérifier si l'exercice et le fichier sont présents
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file']) && isset($_POST['exercise_id']) && isset($_POST['course_id']) && isset($_POST['language'])) {
    $exercise_id = $_POST['exercise_id'];
    $course_id = $_POST['course_id'];
    $language = $_POST['language'];  // Le langage choisi (Python ou C)
    
    // Vérifier l'extension du fichier (Python ou C)
    $file = $_FILES['file'];
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (($language == 'python' && $file_extension != 'py') || ($language == 'c' && $file_extension != 'c')) {
        die("Le fichier ne correspond pas au langage sélectionné. Veuillez télécharger un fichier ".($language == 'python' ? '.py' : '.c')." pour ce langage.");
    }

    // Déplacer le fichier temporaire vers un emplacement permanent
    $upload_dir = 'uploads/';
    $file_path = $upload_dir . basename($file['name']);
    
    if (!move_uploaded_file($file['tmp_name'], $file_path)) {
        die("Erreur de téléchargement du fichier.");
    }

    // Récupérer le résultat attendu depuis la base de données
    $stmt = $pdo->prepare("SELECT expected_result FROM exercises WHERE id = ?");
    try{
       $stmt->execute([$exercise_id]);
        $exercise = $stmt->fetch(); 
    }catch(\Throwable$e){
        var_dump($exercise_id);
        var_dump($e);
    }
    

    if (!$exercise) {
        die("Exercice non trouvé.");
    }

    $expected_result = $exercise['expected_result'];
    $absolute_file_path = "/var/www/code-evaluation/uploads/" . basename($file['name']);
    if (!file_exists($absolute_file_path)) {
        die("Erreur : le fichier n'existe pas.");
    }
      // Récupérer le nom du fichier sans son chemin
    $filename = basename($file['name']);
    // Transférer le fichier sur la machine distante via SCP
    $scp_command = "/usr/bin/scp -i /var/www/.ssh/id_rsa $absolute_file_path claude@192.168.247.144:/home/claude/uploads/$filename";
    $output_scp = [];
    $return_var = 0;
    
    exec($scp_command, $output_scp, $return_var);
    
    if($return_var!=0){
        die("Erreur de transfert de fichier");
    }



  

    // Construire la commande SSH correctement
    $ssh_command = "ssh -o StrictHostKeyChecking=no claude@192.168.247.144 '/home/claude/execute_and_grade.sh $user_id $course_id $exercise_id /home/claude/uploads/$filename \"$expected_result\" $language'";
    
    // Exécuter la commande SSH
    $output = shell_exec($ssh_command);

  
    // Analyser la sortie du script
    if (strpos($output, 'Erreur') !== false) {
        die("Erreur lors de l'exécution du script distant.");
    }
    
    // Enregistrer le résultat dans la base de données
    $stmt = $pdo->prepare("INSERT INTO submissions (user_id, course_id, exercise_id, filename, score, submitted_at)
                           VALUES (?, ?, ?, ?, ?, NOW())");
    try{
          $stmt->execute([$user_id, $course_id, $exercise_id, $file_path, $output]);
    }catch(\Throwable $f){
        var_dump($f); die();
    }
  

    echo "Exercice soumis avec succès. Votre score est : " . htmlspecialchars($output);
} else {
    echo "Veuillez sélectionner un fichier et un langage à soumettre.";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Soumettre un exercice</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            padding: 20px;
            background-color: #f8f9fa;
        }
        h1 {
            color: #343a40;
        }
        label {
            font-size: 1.2em;
            margin-top: 10px;
        }
        input[type="file"] {
            margin: 10px 0;
        }
        select, button {
            padding: 10px 20px;
            margin-top: 10px;
        }
        button {
            background-color: #28a745;
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
    <h1>Soumettre un exercice</h1>
    <form action="submit_exercise.php" method="POST" enctype="multipart/form-data">
        <label for="file">Choisissez un fichier :</label>
        <input type="file" name="file" required>
        
        <label for="language">Choisissez le langage :</label>
        <select name="language" required>
            <option value="python">Python (.py)</option>
            <option value="c">C (.c)</option>
        </select>

        <input type="hidden" name="exercise_id" value="<?= htmlspecialchars($_GET['exercise_id']) ?>">
        <input type="hidden" name="course_id" value="<?= htmlspecialchars($_GET['course_id']) ?>">
        
        <button type="submit">Soumettre l'exercice</button><br>
        <a href="dashboard.php" class="btn">Retour au tableau de bord</a>
    </form>
</body>
</html>
