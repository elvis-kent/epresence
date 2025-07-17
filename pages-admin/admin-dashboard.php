<?php
session_start();

// Vérification de la session
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin_universite') {
    header('Location: ../login.php');
    exit();
}

$nom = $_SESSION['nom'] ?? 'Administrateur';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <title>Dashboard Admin Université - ePresence</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #00695c;
      color: white;
      margin: 0;
      padding: 40px;
      text-align: center;
    }
    h1 { margin-bottom: 10px; }
    .menu {
      display: flex;
      flex-direction: column;
      gap: 15px;
      margin-top: 40px;
      max-width: 400px;
      margin-left: auto;
      margin-right: auto;
    }
    a {
      display: block;
      padding: 12px;
      background-color: #004d40;
      color: #fff;
      text-decoration: none;
      border-radius: 8px;
      transition: background-color 0.3s;
    }
    a:hover {
      background-color: #00332c;
    }
  </style>
</head>
<body>

  <h1>Bienvenue, <?= htmlspecialchars($nom) ?> 👋</h1>
  <p>Panneau de gestion de l’université</p>

  <div class="menu">
    <a href="creer-faculte.php">➕ Créer une faculté</a>
    <a href="creer-departement.php">➕ Créer un département</a>
    <a href="creer-filiere.php">➕ Créer une filière</a>
    <a href="creer-promotion.php">➕ Créer une promotion</a>
    <a href="liste-etudiant.php">👨‍🎓 Liste des étudiants</a>
    <a href="liste-professeurs.php">👨‍🏫 Liste des professeurs</a>
    <a href="liste-cours.php">📘 Liste des cours</a>
    <a href="liste-presence.php">📋 Liste des présences</a>
    <a href="../logout.php">🚪 Se déconnecter</a>
  </div>

</body>
</html>
