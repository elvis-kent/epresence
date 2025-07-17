<?php
require_once('includes/config.php');
session_start();

// Vérifier que c’est un admin université connecté
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin_universite') {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    echo "Aucun professeur sélectionné.";
    exit();
}

$id = intval($_GET['id']);

// Récupérer les infos du professeur
$stmt = $conn->prepare("SELECT * FROM professeurs WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    echo "Professeur introuvable.";
    exit();
}

$prof = $result->fetch_assoc();

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nom = $_POST['nom'];
    $postnom = $_POST['postnom'];
    $prenom = $_POST['prenom'];
    $email = $_POST['email'];
    $identifiant = $_POST['identifiant'];

    $update = $conn->prepare("UPDATE professeurs SET nom=?, postnom=?, prenom=?, email=?, identifiant=? WHERE id=?");
    $update->bind_param("sssssi", $nom, $postnom, $prenom, $email, $identifiant, $id);
    if ($update->execute()) {
        header("Location: liste_professeurs.php?msg=modifié");
        exit();
    } else {
        echo "Erreur de mise à jour.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier Professeur</title>
    <style>
        body {
            font-family: Arial;
            background: #f0f0f0;
            padding: 20px;
        }
        .form-container {
            background: white;
            max-width: 600px;
            margin: auto;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px #aaa;
        }
        input {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
        }
        button {
            background: #007BFF;
            color: white;
            border: none;
            padding: 10px 20px;
        }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Modifier les informations du professeur</h2>
    <form method="POST">
        <label>Nom</label>
        <input type="text" name="nom" value="<?= htmlspecialchars($prof['nom']) ?>" required>

        <label>Post-nom</label>
        <input type="text" name="postnom" value="<?= htmlspecialchars($prof['postnom']) ?>" required>

        <label>Prénom</label>
        <input type="text" name="prenom" value="<?= htmlspecialchars($prof['prenom']) ?>" required>

        <label>Email</label>
        <input type="email" name="email" value="<?= htmlspecialchars($prof['email']) ?>" required>

        <label>Identifiant</label>
        <input type="text" name="identifiant" value="<?= htmlspecialchars($prof['identifiant']) ?>" required>

        <button type="submit">Mettre à jour</button>
    </form>
</div>

</body>
</html>
