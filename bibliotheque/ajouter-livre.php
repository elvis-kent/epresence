<?php
session_start();
require 'config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'agent_biblio') {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['fichier'])) {
    $titre = $_POST['titre'];
    $desc = $_POST['description'];
    $departement_id = $_SESSION['departement_id'];

    $file_name = basename($_FILES['fichier']['name']);
    $target_dir = "uploads/";
    $target_file = $target_dir . time() . "_" . $file_name;

    if (move_uploaded_file($_FILES["fichier"]["tmp_name"], $target_file)) {
        $stmt = $conn->prepare("INSERT INTO bibliotheque (titre, description, fichier, departement_id) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $titre, $desc, $target_file, $departement_id);
        $stmt->execute();
        $message = "✅ Livre ajouté avec succès.";
    } else {
        $message = "❌ Erreur lors de l’envoi du fichier.";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un livre</title>
    <style>
        body { font-family: Arial; background: #f9f9f9; padding: 20px; }
        form { background: white; padding: 20px; border-radius: 6px; width: 500px; margin: auto; box-shadow: 0 0 5px #ccc; }
        input, textarea { width: 100%; padding: 10px; margin-bottom: 15px; }
        button { padding: 10px 20px; background: #28a745; color: white; border: none; }
        h2 { text-align: center; }
        .msg { text-align: center; color: green; }
    </style>
</head>
<body>

<h2>📚 Ajouter un document</h2>
<?php if (isset($message)) echo "<p class='msg'>$message</p>"; ?>

<form method="post" enctype="multipart/form-data">
    <label>Titre du livre</label>
    <input type="text" name="titre" required>

    <label>Description</label>
    <textarea name="description" rows="3"></textarea>

    <label>Fichier (PDF)</label>
    <input type="file" name="fichier" accept="application/pdf" required>

    <button type="submit">💾 Ajouter</button>
</form>

</body>
</html>
