<?php
require_once('includes/config.php');
session_start();

if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin_universite') {
    header('Location: login.php');
    exit();
}

$nom = $_SESSION['nom'] ?? "Admin Université";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin Université</title>
    <style>
        body {
            font-family: Arial;
            background: #f1f2f6;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 900px;
            margin: 50px auto;
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px #ccc;
        }
        h1 {
            text-align: center;
            color: #333;
        }
        .menu {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-top: 30px;
            justify-content: center;
        }
        .menu a {
            flex: 1 1 40%;
            padding: 20px;
            text-align: center;
            background: #007BFF;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            transition: 0.2s;
        }
        .menu a:hover {
            background: #0056b3;
        }
        .logout {
            text-align: center;
            margin-top: 30px;
        }
        .logout a {
            color: #e74c3c;
            font-weight: bold;
            text-decoration: none;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Bienvenue, <?= htmlspecialchars($nom) ?></h1>

    <div class="menu">
        <a href="ajouter_faculte.php">🏢 Ajouter une faculté</a>
        <a href="ajouter_admin_faculte.php">👤 Ajouter un admin de faculté</a>
        <a href="ajouter_service.php">➕ Ajouter un service</a>
        <a href="ajouter_agent.php">👷‍♂️ Créer un agent</a>
        <a href="liste_logs.php">🕓 Voir l'historique (logs)</a>
        <a href="profil_admin_univ.php">🔐 Gérer mon profil</a>
    </div>

    <div class="logout">
        <a href="logout.php">🚪 Se déconnecter</a>
    </div>
</div>

</body>
</html>
