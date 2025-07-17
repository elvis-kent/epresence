<?php
session_start();
require_once 'config.php';

// Vérification : seul le super admin a accès
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'super_admin') {
    header("Location: login.php");
    exit;
}

$result = $conn->query("SELECT * FROM logs ORDER BY date DESC");
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Historique des actions</title>
    <style>
        body { font-family: Arial, background: #f9f9f9; padding: 30px; }
        h2 { text-align: center; margin-bottom: 20px; }
        table {
            width: 100%; border-collapse: collapse; background: white;
            box-shadow: 0 0 10px #ccc;
        }
        th, td { padding: 12px; border: 1px solid #ddd; text-align: left; }
        th { background: #2c3e50; color: white; }
        tr:nth-child(even) { background: #f2f2f2; }
    </style>
</head>
<body>

<h2>📜 Historique des actions (Logs)</h2>

<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Utilisateur</th>
            <th>Rôle</th>
            <th>Action</th>
            <th>Date</th>
        </tr>
    </thead>
    <tbody>
        <?php $i = 1; while($log = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $i++ ?></td>
                <td><?= htmlspecialchars($log['utilisateur']) ?></td>
                <td><?= htmlspecialchars($log['role']) ?></td>
                <td><?= htmlspecialchars($log['action']) ?></td>
                <td><?= htmlspecialchars($log['date']) ?></td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>

</body>
</html>
