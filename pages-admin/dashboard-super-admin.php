<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'super_admin') {
    header('Location: login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Tableau de bord - Super Admin</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            background-color: #f2f4f7;
        }

        header {
            background-color: #1a1a2e;
            color: white;
            padding: 20px;
            text-align: center;
        }

        .menu {
            background-color: #16213e;
            display: flex;
            justify-content: center;
            padding: 15px;
            flex-wrap: wrap;
        }

        .menu button {
            margin: 10px;
            padding: 12px 20px;
            border: none;
            background-color: white;
            color: #1a1a2e;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            transition: 0.2s;
        }

        .menu button:hover {
            background-color: #1a1a2e;
            color: white;
        }

        iframe {
            width: 100%;
            height: 650px;
            border: none;
        }
    </style>
</head>
<body>

    <header>
        <h1>🧑‍💼 Tableau de bord du Super Admin</h1>
    </header>

    <div class="menu">
        <button onclick="loadPage('creer_universite.php')">🏛️ Créer Université</button>
        <button onclick="loadPage('ajouter_admin_universite.php')">👨‍💼 Ajouter Admin Université</button>
        <button onclick="loadPage('logs.php')">📜 Logs généraux</button>
        <button onclick="loadPage('cartes.php')">🎫 Générer/Imprimer les cartes</button>
        <button onclick="loadPage('logout.php')">🚪 Déconnexion</button>
    </div>

    <iframe id="mainFrame" src="creer_universite.php"></iframe>

    <script>
        function loadPage(page) {
            document.getElementById('mainFrame').src = page;
        }
    </script>

</body>
</html>
