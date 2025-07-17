<?php
session_start();
require_once 'config.php';

// Vérification : seul le super admin a accès
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'super_admin') {
    header("Location: login.php");
    exit;
}

// Récupérer tous les admins d’université
$sql = "SELECT id, nom, email, universite FROM users WHERE role = 'admin_universite'";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des universités</title>
    <style>
        body { font-family: Arial, sans-serif; background: #eef; padding: 30px; }
        h2 { text-align: center; }
        table { width: 100%; background: white; border-collapse: collapse; margin-top: 20px; box-shadow: 0 0 10px #ccc; }
        th, td { padding: 12px; border: 1px solid #ccc; text-align: left; }
        th { background-color: #2c3e50; color: white; }
        .actions a {
            margin-right: 10px;
            text-decoration: none;
            color: #2980b9;
        }
        .actions a:hover { text-decoration: underline; }
    </style>
</head>
<body>

<h2>Universités enregistrées</h2>

<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Université</th>
            <th>Admin</th>
            <th>Email</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php $i = 1; while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $i++ ?></td>
                <td><?= htmlspecialchars($row['universite']) ?></td>
                <td><?= htmlspecialchars($row['nom']) ?></td>
                <td><?= htmlspecialchars($row['email']) ?></td>
                <td class="actions">
                    <a href="#">✏️ Modifier</a>
                    <a href="#">🗑️ Supprimer</a> <!-- À implémenter plus tard -->
                </td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>

</body>
</html>
