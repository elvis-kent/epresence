<?php
session_start();
require 'config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin_departement') {
    header('Location: login.php');
    exit;
}

$universite_id = $_SESSION['universite_id'];
$message = '';

// Récupérer les filières et promotions du département
$departement_id = $_SESSION['departement_id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'];
    $email = $_POST['email'];
    $password = password_hash($_POST['motdepasse'], PASSWORD_DEFAULT);
    $role = 'professeur';

    // Vérifie si l’email existe déjà
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $message = "❌ Cet email est déjà utilisé.";
    } else {
        // Insérer dans users
        $stmt = $conn->prepare("INSERT INTO users (nom, email, motdepasse, role, universite_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssi", $nom, $email, $password, $role, $universite_id);
        if ($stmt->execute()) {
            $user_id = $stmt->insert_id;

            // Insérer dans professeurs
            $stmt2 = $conn->prepare("INSERT INTO professeurs (user_id, nom, email, departement_id) VALUES (?, ?, ?, ?)");
            $stmt2->bind_param("issi", $user_id, $nom, $email, $departement_id);
            $stmt2->execute();

            $message = "✅ Professeur ajouté avec succès.";
        } else {
            $message = "❌ Erreur : " . $stmt->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un professeur</title>
    <style>
        body { font-family: Arial; background: #f4f4f4; padding: 30px; }
        form { background: white; padding: 20px; border-radius: 8px; max-width: 500px; }
        h2 { color: #1a1a2e; }
        label { display: block; margin-top: 15px; }
        input, select, button {
            width: 100%; padding: 10px; margin-top: 5px;
        }
        .message { margin-top: 20px; font-weight: bold; color: green; }
    </style>
</head>
<body>

    <h2>👨‍🏫 Ajouter un Professeur</h2>

    <?php if ($message): ?>
        <p class="message"><?= $message ?></p>
    <?php endif; ?>

    <form method="POST">
        <label>Nom complet</label>
        <input type="text" name="nom" required>

        <label>Email</label>
        <input type="email" name="email" required>

        <label>Mot de passe</label>
        <input type="password" name="motdepasse" required>

        <button type="submit">Ajouter</button>
    </form>

</body>
</html>
