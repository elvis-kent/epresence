<?php
session_start();
require_once 'config.php';

// Vérification de l'admin de département
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin_departement') {
    header("Location: login.php");
    exit;
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cours_id = $_POST['cours_id'];
    $uploader_id = $_SESSION['user_id'];

    if (isset($_FILES['fichier'])) {
        $nom = basename($_FILES['fichier']['name']);
        $chemin = "uploads/bibliotheque/" . $nom;

        if (move_uploaded_file($_FILES['fichier']['tmp_name'], $chemin)) {
            $stmt = $conn->prepare("INSERT INTO bibliotheque (nom_fichier, chemin_fichier, cours_id, uploader_id) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssii", $nom, $chemin, $cours_id, $uploader_id);
            if ($stmt->execute()) {
                $message = "Fichier ajouté avec succès.";
            } else {
                $message = "Erreur lors de l'insertion.";
            }
        } else {
            $message = "Échec du téléchargement.";
        }
    }
}

// Récupération des cours à afficher dans le menu
$cours = $conn->query("SELECT id, nom FROM cours");
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un document</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f2f2f2; }
        label, input, select { display: block; margin: 10px 0; width: 100%; padding: 8px; }
        button { padding: 10px 20px; }
        .message { color: green; }
    </style>
</head>
<body>

<h2>Ajouter un document à la bibliothèque</h2>
<?php if ($message): ?>
    <p class="message"><?= $message ?></p>
<?php endif; ?>

<form method="post" enctype="multipart/form-data">
    <label for="cours_id">Cours :</label>
    <select name="cours_id" required>
        <?php while ($row = $cours->fetch_assoc()): ?>
            <option value="<?= $row['id'] ?>"><?= $row['nom'] ?></option>
        <?php endwhile; ?>
    </select>

    <label for="fichier">Fichier :</label>
    <input type="file" name="fichier" required />

    <button type="submit">Uploader</button>
</form>

</body>
</html>
