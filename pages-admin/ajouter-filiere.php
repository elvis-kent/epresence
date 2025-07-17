<?php
require_once('includes/config.php');
session_start();

// Seul l'admin de département peut accéder
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin_departement') {
    header('Location: login.php');
    exit();
}

$message = "";
$departement_id = $_SESSION['departement_id'];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nom_filiere = trim($_POST['nom_filiere']);

    if (empty($nom_filiere)) {
        $message = "❌ Veuillez indiquer un nom pour la filière.";
    } else {
        // Vérifier l'existence de la filière dans ce département
        $stmt = $conn->prepare("SELECT id FROM filieres WHERE nom = ? AND departement_id = ?");
        $stmt->bind_param("si", $nom_filiere, $departement_id);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $message = "❌ Cette filière existe déjà dans ce département.";
        } else {
            $stmt->close();

            $stmt = $conn->prepare("INSERT INTO filieres (nom, departement_id) VALUES (?, ?)");
            $stmt->bind_param("si", $nom_filiere, $departement_id);

            if ($stmt->execute()) {
                $message = "✅ Filière ajoutée avec succès.";
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
    <meta charset="UTF-8">
    <title>Ajouter une filière</title>
    <style>
        body { font-family: Arial; background: #f1f1f1; padding: 30px; }
        .box {
            max-width: 450px;
            margin: auto;
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 0 12px #ccc;
        }
        h2 { text-align: center; margin-bottom: 20px; }
        label { display: block; margin-top: 10px; }
        input[type="text"] {
            width: 100%; padding: 8px; margin-top: 5px; border-radius: 4px;
            border: 1px solid #ccc; box-sizing: border-box;
        }
        button {
            margin-top: 20px; width: 100%; padding: 10px;
            background-color: #28a745; color: white; border: none;
            border-radius: 5px; cursor: pointer; font-weight: bold;
        }
        button:hover { background-color: #218838; }
        .message {
            margin-top: 15px; text-align: center; font-weight: bold;
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
    <h2>Ajouter une filière</h2>
    <form method="POST">
        <label for="nom_filiere">Nom de la filière :</label>
        <input type="text" id="nom_filiere" name="nom_filiere" required>

        <button type="submit">Ajouter</button>
    </form>

    <?php if ($message): ?>
        <div class="message"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <a href="dashboard_admin_departement.php">⬅️ Retour au tableau de bord</a>
</div>

</body>
</html>
