<?php
require_once('includes/config.php');
session_start();

if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'agent') {
    header('Location: login_agent.php');
    exit();
}

$filtre_date = $_GET['date'] ?? "";
$recherche = $_GET['q'] ?? "";

$requete = "SELECT acces_service.date_acces, etudiants.nom, etudiants.postnom, etudiants.matricule
            FROM acces_service
            JOIN etudiants ON acces_service.id_etudiant = etudiants.id
            WHERE acces_service.id_agent = ?";

$types = "i";
$params = [$_SESSION['id']];

if (!empty($filtre_date)) {
    $requete .= " AND DATE(acces_service.date_acces) = ?";
    $types .= "s";
    $params[] = $filtre_date;
}

if (!empty($recherche)) {
    $requete .= " AND (etudiants.nom LIKE ? OR etudiants.postnom LIKE ? OR etudiants.matricule LIKE ?)";
    $types .= "sss";
    $like = "%$recherche%";
    array_push($params, $like, $like, $like);
}

$requete .= " ORDER BY acces_service.date_acces DESC";

$stmt = $conn->prepare($requete);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Historique des accès</title>
    <style>
        body { font-family: Arial; background: #eef2f5; padding: 20px; }
        .container { max-width: 900px; margin: auto; background: white; padding: 20px; border-radius: 8px; }
        h2 { text-align: center; }
        form { display: flex; justify-content: space-between; margin-bottom: 20px; gap: 10px; }
        input[type="date"], input[type="text"] { padding: 8px; width: 100%; }
        button { padding: 8px 15px; background: #0066cc; color: white; border: none; border-radius: 5px; cursor: pointer; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: center; }
        th { background: #0066cc; color: white; }
        a { text-decoration: none; color: #0066cc; display: inline-block; margin-top: 15px; }
    </style>
</head>
<body>

<div class="container">
    <h2>Historique des accès à votre service</h2>

    <form method="GET">
        <input type="date" name="date" value="<?= htmlspecialchars($filtre_date) ?>">
        <input type="text" name="q" placeholder="Recherche (nom, matricule...)" value="<?= htmlspecialchars($recherche) ?>">
        <button type="submit">🔍 Filtrer</button>
    </form>

    <table>
        <thead>
            <tr>
                <th>Date et heure</th>
                <th>Nom</th>
                <th>Post-nom</th>
                <th>Matricule</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['date_acces'] ?></td>
                    <td><?= htmlspecialchars($row['nom']) ?></td>
                    <td><?= htmlspecialchars($row['postnom']) ?></td>
                    <td><?= htmlspecialchars($row['matricule']) ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="4">Aucun accès trouvé.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>

    <a href="dashboard_agent.php">⬅️ Retour au tableau de bord</a>
</div>

</body>
</html>
