<?php
require_once('includes/config.php');
session_start();

// Vérifie que c'est un admin université connecté
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin_universite') {
    header("Location: login.php");
    exit();
}

$search = "";
$sql = "SELECT * FROM professeurs";

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = htmlspecialchars($_GET['search']);
    $sql .= " WHERE nom LIKE '%$search%' OR postnom LIKE '%$search%' OR email LIKE '%$search%'";
}

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des professeurs</title>
    <style>
        body {
            font-family: Arial;
            background: #f7f7f7;
            padding: 30px;
        }
        .container {
            background: white;
            max-width: 900px;
            margin: auto;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 { text-align: center; }

        form {
            margin-bottom: 20px;
            text-align: center;
        }

        input[type="text"] {
            padding: 8px;
            width: 300px;
        }

        button {
            padding: 8px 15px;
            background-color: #007BFF;
            color: white;
            border: none;
            margin-left: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }

        th {
            background-color: #007BFF;
            color: white;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Liste des Professeurs</h2>

    <form method="GET" action="">
        <input type="text" name="search" placeholder="Rechercher par nom, postnom ou email" value="<?= $search ?>">
        <button type="submit">Rechercher</button>
    </form>

    <table>
        <tr>
            <th>ID</th>
            <th>Nom</th>
            <th>Post-nom</th>
            <th>Prénom</th>
            <th>Email</th>
            <th>Identifiant</th>
            <th>Date création</th>
        </tr>

        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= $row['nom'] ?></td>
                    <td><?= $row['postnom'] ?></td>
                    <td><?= $row['prenom'] ?></td>
                    <td><?= $row['email'] ?></td>
                    <td><?= $row['identifiant'] ?></td>
                    <td><?= $row['created_at'] ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="7">Aucun professeur trouvé.</td></tr>
        <?php endif; ?>
    </table>
</div>

</body>
</html>
