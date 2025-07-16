<?php
session_start();
if (!isset($_SESSION['utilisateur']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.html');
    exit();
}
$nom = $_SESSION['utilisateur']['nom'];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <title>Dashboard Admin - ePresence</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #00695c;
      color: white;
      display: flex; flex-direction: column;
      justify-content: center; align-items: center;
      height: 100vh;
    }
    h1 { margin-bottom: 20px; }
    a { color: yellow; margin-top: 20px; }
  </style>
</head>
<body>
  <h1>Bienvenue Admin <?= htmlspecialchars($nom) ?> !</h1>
  <p>Panneau de contrôle de votre université.</p>
  <a href="logout.php">Se déconnecter</a>
</body>
</html>
