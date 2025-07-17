<?php
require_once('includes/config.php');
session_start();

if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'agent') {
    header('Location: login_agent.php');
    exit();
}

$nom_agent = $_SESSION['nom'] ?? "Agent";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Agent</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f1f3f6;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 800px;
            margin: 60px auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 15px #ccc;
        }
        h1 {
            text-align: center;
            color: #333;
        }
        .menu {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin-top: 30px;
        }
        .menu a {
            display: block;
            padding: 15px;
            background: #007BFF;
            color: white;
            text-align: center;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            transition: 0.2s;
        }
        .menu a:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Bienvenue, <?= htmlspecialchars($nom_agent) ?></h1>

    <div class="menu">
        <a href="scan_service.php">📷 Scanner un étudiant</a>
        <a href="historique_acces.php">📜 Historique des accès</a>
        <a href="logout.php">🚪 Déconnexion</a>
    </div>
</div>

</body>
</html>
