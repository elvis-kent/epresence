<?php
require_once('includes/config.php');
session_start();

// On suppose que seuls admin_universite et admin_faculte peuvent ajouter un service
if (!isset($_SESSION['id']) || !in_array($_SESSION['role'], ['admin_universite', 'admin_faculte'])) {
    header('Location: login.php');
    exit();
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nom_service = trim($_POST['nom_service']);
    $description = trim($_POST['description']);

    if (empty($nom_service)) {
        $message = "❌ Veuillez saisir un nom pour le service.";
    } else {
        // Vérifier si le service existe déjà
        $stmt = $conn->prepare("SELECT id FROM services WHERE nom = ?");
        $stmt->bind_param("s", $nom_service);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $message = "❌ Ce service existe déjà.";
        } else {
            $stmt->close();

            $stmt = $conn->prepare("INSERT INTO services (nom, description) VALUES (?, ?)");
            $stmt->bind_param("ss", $nom_service, $description);

            if ($stmt->execute()) {
                $message = "✅ Service ajouté avec succès.";
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
    <title>Ajouter un service</title>
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
        input[type="text"], textarea {
            width: 100%; padding: 8px; margin-top: 5px; border-radius: 4px;
            border: 1px solid #ccc; box-sizing: border-box;
        }
        textarea {
            resize: vertical;
            min-height: 80px;
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
    <h2>Ajouter un service</h2>
    <form method="POST">
        <label for="nom_service">Nom du service :</label>
        <input type="text" id="nom_service" name="nom_service" required>

        <label for="description">Description (optionnelle) :</label>
        <textarea id="description" name="description"></textarea>

        <button type="submit">Ajouter</button>
    </form>

    <?php if ($message): ?>
        <div class="message"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <a href="dashboard_admin_universite.php">⬅️ Retour au dashboard</a>
</div>

</body>
</html>
