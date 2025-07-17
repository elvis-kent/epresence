<?php
require_once('includes/config.php');
session_start();

$erreur = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $motdepasse = trim($_POST['motdepasse']);

    $stmt = $conn->prepare("SELECT id, nom, role, motdepasse FROM users WHERE email = ? AND role = 'agent'");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 1) {
        $stmt->bind_result($id, $nom, $role, $hash);
        $stmt->fetch();

        if (password_verify($motdepasse, $hash)) {
            $_SESSION['id'] = $id;
            $_SESSION['nom'] = $nom;
            $_SESSION['role'] = $role;
            header("Location: dashboard_agent.php");
            exit();
        } else {
            $erreur = "Mot de passe incorrect.";
        }
    } else {
        $erreur = "Aucun agent trouvé avec cet email.";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion Agent</title>
    <style>
        body { font-family: Arial, background: #f0f0f0; display: flex; align-items: center; justify-content: center; height: 100vh; }
        .box { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 0 10px #ccc; width: 400px; }
        h2 { text-align: center; }
        input { width: 100%; padding: 10px; margin: 10px 0; }
        button { width: 100%; background: #0066cc; color: white; padding: 10px; border: none; cursor: pointer; }
        .erreur { color: red; text-align: center; }
    </style>
</head>
<body>

<div class="box">
    <h2>Connexion Agent</h2>

    <?php if ($erreur): ?>
        <p class="erreur"><?= $erreur ?></p>
    <?php endif; ?>

    <form method="POST">
        <input type="email" name="email" placeholder="Adresse email" required>
        <input type="password" name="motdepasse" placeholder="Mot de passe" required>
        <button type="submit">Se connecter</button>
    </form>
</div>

</body>
</html>
