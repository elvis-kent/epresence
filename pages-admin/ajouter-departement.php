<?php
require_once('includes/config.php');
session_start();

// Seuls les admins de faculté peuvent accéder ici
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin_faculte') {
    header('Location: login.php');
    exit();
}

$message = "";

// Récupérer la faculté de l'admin connecté (pour lier le département à cette faculté)
$faculte_id = $_SESSION['faculte_id'];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nom_dept = trim($_POST['nom_departement']);

    if (empty($nom_dept)) {
        $message = "❌ Veuillez saisir un nom pour le département.";
    } else {
        // Vérifier si le département existe déjà dans cette faculté
        $stmt = $conn->prepare("SELECT id FROM departements WHERE nom = ? AND faculte_id = ?");
        $stmt->bind_param("si", $nom_dept, $faculte_id);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $message = "❌ Ce département existe déjà dans votre faculté.";
        } else {
            $stmt->close();

            $stmt = $conn->prepare("INSERT INTO departements (nom, faculte_id) VALUES (?, ?)");
            $stmt->bind_param("si", $nom_dept, $faculte_id);

            if ($stmt->execute()) {
                $message = "✅ Département ajouté avec succès.";
            } else {
                $message = "❌ Erreur lors de l'ajout.";
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title>Ajouter un département</title>
    <style>
        body { font-family: Arial; background: #f9f9f9; padding: 30px; }
        .box {
            max-width: 450px;
            margin: auto;
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 0 12px #ddd;
        }
        h2 { text-align: center; margin-bottom: 20px; }
        label { display: block; margin-top: 10px; }
        input[type="text"] {
            width: 100%; padding: 8px; margin-top: 5px; border-radius: 4px;
            border: 1px solid #ccc; box-sizing: border-box;
        }
        button {
            margin-top: 20px; width: 100%; padding: 10px;
            background-color: #007BFF; color: white; border: none; border-radius: 5px;
            cursor: pointer; font-weight: bold;
        }
        button:hover { background-color: #0056b3; }
        .message {
            margin-top: 15px;
            text-align: center;
            font-weight: bold;
        }
        a {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #007BFF;
            text-decoration: none;
        }
    </style>
</head>
<body>

<div class="box">
    <h2>Ajouter un département</h2>
    <form method="POST">
        <label for="nom_departement">Nom du département :</label>
        <input type="text" id="nom_departement" name="nom_departement" required>

        <button type="submit">Ajouter</button>
    </form>

    <?php if ($message): ?>
        <div class="message"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <a href="dashboard_admin_faculte.php">⬅️ Retour au dashboard</a>
</div>

</body>
</html>
