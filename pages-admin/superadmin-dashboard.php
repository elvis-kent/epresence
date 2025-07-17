<?php
session_start();
require_once 'config.php';

// Protection : seul super admin peut accéder
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'super_admin') {
    header("Location: login.php");
    exit;
}

// Compter le nombre d’universités et admins
$universites = $conn->query("SELECT COUNT(*) AS total FROM users WHERE role = 'admin_universite'")->fetch_assoc()['total'];
$etudiants = $conn->query("SELECT COUNT(*) AS total FROM etudiants")->fetch_assoc()['total'];
$professeurs = $conn->query("SELECT COUNT(*) AS total FROM professeurs")->fetch_assoc()['total'];
$logs = $conn->query("SELECT COUNT(*) AS total FROM logs")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Super Admin</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; padding: 30px; }
        h1 { text-align: center; }
        .container { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 20px; margin-top: 30px; }
        .card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px #ccc;
            text-align: center;
        }
        .card h2 { margin: 0; font-size: 40px; color: #2c3e50; }
        .card p { margin: 10px 0 0; font-size: 16px; color: #555; }
        .actions { margin-top: 40px; text-align: center; }
        .actions a {
            margin: 10px;
            padding: 10px 20px;
            background: #2c3e50;
            color: white;
            border-radius: 5px;
            text-decoration: none;
        }
        .actions a:hover { background: #34495e; }
    </style>
</head>
<body>

<h1>Dashboard Super Admin</h1>

<div class="container">
    <div class="card">
        <h2><?= $universites ?></h2>
        <p>Universités enregistrées</p>
    </div>
    <div class="card">
        <h2><?= $etudiants ?></h2>
        <p>Étudiants enregistrés</p>
    </div>
    <div class="card">
        <h2><?= $professeurs ?></h2>
        <p>Professeurs</p>
    </div>
    <div class="card">
        <h2><?= $logs ?></h2>
        <p>Actions enregistrées</p>
    </div>
</div>

<div class="actions">
    <a href="creer_admin_universite.php">➕ Créer un admin d'université</a>
    <a href="liste_universites.php">🏫 Voir les universités</a>
    <a href="logs.php">📜 Voir les logs</a>
    <a href="telecharger_cartes.php">📄 Télécharger les cartes QR</a>
</div>

</body>
</html>
