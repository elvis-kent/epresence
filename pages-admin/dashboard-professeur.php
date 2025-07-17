<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'professeur') {
    header('Location: login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Tableau de bord - Professeur</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #e9f0f5;
            margin: 0;
            padding: 0;
        }

        header {
            background-color: #003366;
            color: white;
            padding: 20px;
            text-align: center;
        }

        .welcome {
            margin: 0;
            font-size: 24px;
        }

        nav {
            background-color: #00509e;
            display: flex;
            justify-content: center;
            padding: 15px 0;
        }

        nav button {
            background-color: white;
            color: #00509e;
            border: none;
            padding: 12px 25px;
            margin: 0 10px;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.2s, color 0.2s;
        }

        nav button:hover {
            background-color: #003366;
            color: white;
        }

        iframe {
            width: 100%;
            height: 600px;
            border: none;
        }

        .container {
            padding: 20px;
        }
    </style>
</head>
<body>

    <header>
        <h1 class="welcome">Bienvenue Prof. <?php echo htmlspecialchars($_SESSION['nom']); ?></h1>
    </header>

    <nav>
        <button onclick="loadPage('scan_presence.php')">📷 Scanner les présences</button>
        <button onclick="loadPage('listepresence.php')">📋 Liste des présences</button>
        <button onclick="loadPage('logout.php')">🚪 Déconnexion</button>
    </nav>

    <div class="container">
        <iframe id="contentFrame" src="scan_presence.php"></iframe>
    </div>

    <script>
        function loadPage(page) {
            document.getElementById('contentFrame').src = page;
        }
    </script>

</body>
</html>
