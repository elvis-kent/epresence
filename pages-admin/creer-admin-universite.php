<?php
session_start();
require_once 'config.php';

// Vérifie si l'utilisateur est super_admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'super_admin') {
    header("Location: login.php");
    exit;
}

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'];
    $email = $_POST['email'];
    $universite = $_POST['universite'];
    $mot_de_passe = password_hash($_POST['mot_de_passe'], PASSWORD_DEFAULT);
    $role = 'admin_universite';

    // Vérifie si l’email est déjà utilisé
    $verif = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $verif->bind_param("s", $email);
    $verif->execute();
    $verif->store_result();

    if ($verif->num_rows > 0) {
        $message = "❌ Cet email est déjà utilisé.";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (nom, email, mot_de_passe, role, universite) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $nom, $email, $mot_de_passe, $role, $universite);
        if ($stmt->execute()) {
            $message = "✅ Admin de l’université créé avec succès !";
        } else {
            $message = "❌ Erreur lors de la création.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Créer un admin d'université</title>
    <style>
        body { font-family: Arial; background: #f0f0f0; padding: 40px; }
        form { background: white; padding: 20px; border-radius: 8px; width: 400px; margin: auto; box-shadow: 0 0 10px #ccc; }
        h2 { text-align: center; }
        input, select, button { width: 100%; padding: 10px; margin: 10px 0; }
        .success { color: green; }
        .error { color: red; }
    </style>
</head>
<body>

<h2>Créer un admin d'université</h2>

<form method="post">
    <?php if ($message): ?>
        <p class="<?= strpos($message, '✅') !== false ? 'success' : 'error' ?>"><?= $message ?></p>
    <?php endif; ?>

    <label>Nom complet</label>
    <input type="text" name="nom" required>

    <label>Email</label>
    <input type="email" name="email" required>

    <label>Mot de passe</label>
    <input type="password" name="mot_de_passe" required>

    <label>Nom de l’université</label>
    <input type="text" name="universite" placeholder="ex: Université Pédagogique Nationale" required>

    <button type="submit">Créer l’admin</button>
</form>

</body>
</html>
