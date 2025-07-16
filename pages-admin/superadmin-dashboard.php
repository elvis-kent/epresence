<?php
session_start();

// Vérifier si l'utilisateur est connecté et est superadmin
if (!isset($_SESSION['utilisateur']) || $_SESSION['role'] !== 'superadmin') {
    header('Location: login.html');
    exit();
}

$nom = $_SESSION['utilisateur']['nom'];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title>Dashboard Super Admin - ePresence</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #005792;
            color: white;
            margin: 0; padding: 0;
            display: flex; justify-content: center; align-items: center;
            height: 100vh;
            flex-direction: column;
        }
        h1 {
            margin-bottom: 20px;
        }
        a.logout {
            color: #ffc107;
            text-decoration: none;
            font-weight: bold;
            margin-top: 30px;
        }
        a.logout:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <h1>Bienvenue, Super Admin <?= htmlspecialchars($nom) ?> !</h1>
    <p>Ceci est ta page d'administration principale.</p>

    <a href="logout.php" class="logout">Se déconnecter</a>
</body>
</html>
