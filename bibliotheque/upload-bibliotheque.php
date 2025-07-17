<?php
require_once('includes/config.php');
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin_departement') {
    header('Location: login.php');
    exit();
}

$id_admin = $_SESSION['id'];
// Récupération du département lié à cet admin
$stmt = $conn->prepare("SELECT id_departement FROM admins_departement WHERE id = ?");
$stmt->bind_param("i", $id_admin);
$stmt->execute();
$stmt->bind_result($id_departement);
$stmt->fetch();
$stmt->close();

$success = $error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $titre = $_POST['titre'];
    $description = $_POST['description'];
    $type_document = $_POST['type_document'];
    $visible_pour = $_POST['visible_pour'];
    
    // Gestion du fichier
    $upload_dir = "uploads/bibliotheque/";
    $fichier_name = basename($_FILES["fichier"]["name"]);
    $target_file = $upload_dir . $fichier_name;

    if (move_uploaded_file($_FILES["fichier"]["tmp_name"], $target_file)) {
        // Enregistrement en base
        $stmt = $conn->prepare("INSERT INTO bibliotheque (titre, description, fichier, type_document, id_departement, visible_pour) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssis", $titre, $description, $fichier_name, $type_document, $id_departement, $visible_pour);
        if ($stmt->execute()) {
            $success = "✅ Document ajouté avec succès.";
        } else {
            $error = "❌ Erreur lors de l'enregistrement.";
        }
        $stmt->close();
    } else {
        $error = "❌ Échec du téléchargement du fichier.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un document</title>
    <style>
        body { font-family: Arial; background: #f0f0f0; padding: 20px; }
        .form-container { max-width: 600px; margin: auto; background: white; padding: 20px; border-radius: 8px; }
        input, textarea, select { width: 100%; padding: 10px; margin: 10px 0; }
        button { padding: 10px 20px; background: #0066cc; color: white; border: none; border-radius: 5px; cursor: pointer; }
        .message { text-align: center; padding: 10px; }
        .success { color: green; }
        .error { color: red; }
    </style>
</head>
<body>

<div class="form-container">
    <h2>📤 Ajouter un document à la bibliothèque</h2>

    <?php if ($success) echo "<div class='message success'>$success</div>"; ?>
    <?php if ($error) echo "<div class='message error'>$error</div>"; ?>

    <form method="POST" enctype="multipart/form-data">
        <label for="titre">Titre du document :</label>
        <input type="text" name="titre" required>

        <label for="description">Description :</label>
        <textarea name="description" rows="4" required></textarea>

        <label for="fichier">Fichier :</label>
        <input type="file" name="fichier" accept=".pdf,.doc,.docx,.mp4,.mp3" required>

        <label for="type_document">Type de document :</label>
        <select name="type_document" required>
            <option value="PDF">PDF</option>
            <option value="Word">Word</option>
            <option value="Vidéo">Vidéo</option>
            <option value="Audio">Audio</option>
        </select>

        <label for="visible_pour">Visible pour :</label>
        <select name="visible_pour" required>
            <option value="etudiant">Étudiants uniquement</option>
            <option value="professeur">Professeurs uniquement</option>
            <option value="tous">Tous</option>
        </select>

        <button type="submit">📁 Ajouter</button>
    </form>
</div>

</body>
</html>
