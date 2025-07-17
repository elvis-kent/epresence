<?php
session_start();
require 'config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'super_admin') {
    header('Location: login.php');
    exit;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nom = $_POST['nom'];
    $slogan = $_POST['slogan'];
    $domaine = $_POST['domaine'];

    // Gestion du logo
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
        $logo_name = uniqid() . '_' . $_FILES['logo']['name'];
        move_uploaded_file($_FILES['logo']['tmp_name'], 'uploads/' . $logo_name);
    } else {
        $logo_name = '';
    }

    $stmt = $conn->prepare("INSERT INTO universites (nom, logo, slogan, domaine) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $nom, $logo_name, $slogan, $domaine);

    if ($stmt->execute()) {
        $message = "Université ajoutée avec succès.";
    } else {
        $message = "Erreur : " . $stmt->error;
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter une Université</title>
    <style>
        body { font-family: Arial; padding: 30px; background: #f4f4f4; }
        h2 { color: #1a1a2e; }
        form { background: white; padding: 20px; border-radius: 8px; width: 100%; max-width: 600px; }
        label { display: block; margin-top: 15px; }
        input[type="text"], input[type="file"] {
            width: 100%; padding: 10px; margin-top: 5px;
        }
        button {
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #1a1a2e;
            color: white;
            border: none;
            cursor: pointer;
        }
        .message { margin-top: 20px; color: green; }
    </style>
</head>
<body>

    <h2>🏛️ Ajouter une Université</h2>

    <?php if ($message): ?>
        <p class="message"><?= $message ?></p>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <label>Nom de l'université</label>
        <input type="text" name="nom" required>

        <label>Slogan</label>
        <input type="text" name="slogan">

        <label>Domaine (ex : upn.ac.cd)</label>
        <input type="text" name="domaine">

        <label>Logo</label>
        <input type="file" name="logo">

        <button type="submit">Ajouter</button>
    </form>

</body>
</html>
