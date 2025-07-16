<?php
session_start();
require_once 'config.php'; // connexion à la BDD

// Si déjà connecté, rediriger vers dashboard
if (isset($_SESSION['prof_id'])) {
    header('Location: prof_dashboard.php');
    exit;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        $message = "Veuillez remplir tous les champs.";
    } else {
        // Recherche du prof par email
        $stmt = $conn->prepare("SELECT * FROM professeurs WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows === 1) {
            $prof = $result->fetch_assoc();

            // Pour l’instant on compare mot de passe en clair (à améliorer plus tard)
            if ($password === $prof['password']) {
                // Authentification réussie
                $_SESSION['prof_id'] = $prof['id'];
                $_SESSION['prof_nom'] = $prof['nom'];
                header('Location: prof_dashboard.php');
                exit;
            } else {
                $message = "Mot de passe incorrect.";
            }
        } else {
            $message = "Email non trouvé.";
        }
        $stmt->close();
    }
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Connexion Professeur - ePresence</title>
<style>
    body {
        font-family: Arial, sans-serif;
        background: #f0f2f5;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        margin: 0;
    }
    .login-container {
        background: white;
        padding: 30px 40px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        width: 320px;
    }
    h2 {
        text-align: center;
        margin-bottom: 25px;
        color: #333;
    }
    input[type="email"], input[type="password"] {
        width: 100%;
        padding: 10px 12px;
        margin: 10px 0 20px 0;
        border: 1px solid #ccc;
        border-radius: 5px;
        box-sizing: border-box;
        font-size: 14px;
    }
    button {
        width: 100%;
        background-color: #007bff;
        color: white;
        padding: 12px 0;
        border: none;
        border-radius: 5px;
        font-size: 16px;
        cursor: pointer;
    }
    button:hover {
        background-color: #0056b3;
    }
    .message {
        color: red;
        text-align: center;
        margin-bottom: 15px;
        font-size: 14px;
    }
    .footer {
        text-align: center;
        margin-top: 15px;
        font-size: 13px;
        color: #666;
    }
</style>
</head>
<body>

<div class="login-container">
    <h2>Connexion Professeur</h2>

    <?php if ($message): ?>
        <div class="message"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="post" action="">
        <input type="email" name="email" placeholder="Email" required autofocus />
        <input type="password" name="password" placeholder="Mot de passe" required />
        <button type="submit">Se connecter</button>
    </form>

    <div class="footer">
        ePresence &copy; 2025
    </div>
</div>

</body>
</html>
