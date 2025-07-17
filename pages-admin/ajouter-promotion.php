<?php
require_once('includes/config.php');
session_start();

if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin_universite') {
    header("Location: login.php");
    exit();
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = $_POST['nom'];
    $postnom = $_POST['postnom'];
    $prenom = $_POST['prenom'];
    $email = $_POST['email'];
    $identifiant = $_POST['identifiant'];
    $mot_de_passe = password_hash($_POST['mot_de_passe'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO professeurs (nom, postnom, prenom, email, identifiant, mot_de_passe)
                            VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $nom, $postnom, $prenom, $email, $identifiant, $mot_de_passe);

    if ($stmt->execute()) {
        $message = "✅ Professeur ajouté avec succès !";
    } else {
        $message = "❌ Erreur : " . $stmt->error;
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un professeur</title>
    <style>
        body { font-family: Arial; background: #f0f0f0; padding: 30px; }
        .container {
            max-width: 600px; margin: auto; background: white;
            padding: 20px; border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 { text-align: center; }
        label { display: block; margin-top: 10px; }
        input {
            width: 100%; padding: 8px;
            margin-top: 5px; box-sizing: border-box;
        }
        button {
            margin-top: 20px;
            padding: 10px;
            width: 100%;
            background-color: #007BFF;
            color: white; font-weight: bold;
            border: none; border-radius: 5px;
        }
        .message {
            text-align: center;
            margin-top: 15px;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Ajouter un Professeur</h2>
    <form method="POST">
        <label>Nom :</label>
        <input type="text" name="nom" required>

        <label>Post-nom :</label>
        <input type="text" name="postnom" required>

        <label>Prénom :</label>
        <input type="text" name="prenom" required>

        <label>Email :</label>
        <input type="email" name="email" required>

        <label>Identifiant de connexion :</label>
        <input type="text" name="identifiant" required>

        <label>Mot de passe :</label>
        <input type="password" name="mot_de_passe" required>

        <button type="submit">Ajouter</button>
    </form>

    <?php if ($message): ?>
        <div class="message"><?= $message ?></div>
    <?php endif; ?>
</div>

</body>
</html>
