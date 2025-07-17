<?php
require_once('includes/config.php');
session_start();

if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin_faculte') {
    header("Location: login.php");
    exit();
}

$faculte_id = $_SESSION['faculte_id'];

// Gestion recherche
$search = "";
if (isset($_GET['search'])) {
    $search = trim($_GET['search']);
}

// Récupération des cours avec filtre recherche
$sql = "SELECT c.*, p.nom AS promotion_nom, pr.nom AS professeur_nom 
        FROM cours c
        JOIN promotions p ON c.promotion_id = p.id
        JOIN professeurs pr ON c.professeur_id = pr.id
        JOIN filieres f ON p.filiere_id = f.id
        JOIN departements d ON f.departement_id = d.id
        JOIN facultes fa ON d.faculte_id = fa.id
        WHERE fa.id = ? ";

$params = [$faculte_id];
$types = "i";

if ($search !== "") {
    $sql .= " AND c.nom LIKE ? ";
    $params[] = "%$search%";
    $types .= "s";
}

$sql .= " ORDER BY c.nom ASC";

$stmt = $conn->prepare($sql);

if (count($params) === 1) {
    $stmt->bind_param($types, $params[0]);
} elseif (count($params) === 2) {
    $stmt->bind_param($types, $params[0], $params[1]);
}

$stmt->execute();
$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Liste des cours</title>
<style>
    body { font-family: Arial, sans-serif; padding: 20px; background: #f9f9f9;}
    table { border-collapse: collapse; width: 100%; background: white; }
    th, td { padding: 12px; border: 1px solid #ccc; text-align: left; }
    th { background-color: #007BFF; color: white; cursor: pointer; }
    tr:hover { background-color: #f1f1f1; }
    input[type="text"] { padding: 7px; width: 250px; margin-bottom: 10px; }
    button { padding: 7px 15px; background-color: #007BFF; border: none; color: white; cursor: pointer; }
    a { text-decoration: none; color: #007BFF; }
    a:hover { text-decoration: underline; }
    .actions a { margin-right: 10px; }
</style>
</head>
<body>

<h2>Liste des cours</h2>

<form method="GET" action="liste_cours.php">
    <input type="text" name="search" placeholder="Rechercher un cours" value="<?= htmlspecialchars($search) ?>">
    <button type="submit">Rechercher</button>
    <a href="ajouter_cours.php" style="margin-left: 15px;">Ajouter un cours</a>
</form>

<table>
    <thead>
        <tr>
            <th>Nom du cours</th>
            <th>Professeur</th>
            <th>Promotion</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($result->num_rows === 0): ?>
            <tr><td colspan="4">Aucun cours trouvé.</td></tr>
        <?php else: ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['nom']) ?></td>
                    <td><?= htmlspecialchars($row['professeur_nom']) ?></td>
                    <td><?= htmlspecialchars($row['promotion_nom']) ?></td>
                    <td class="actions">
                        <a href="modifier_cours.php?id=<?= $row['id'] ?>">Modifier</a>
                        <a href="supprimer_cours.php?id=<?= $row['id'] ?>" onclick="return confirm('Supprimer ce cours ?');">Supprimer</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php endif; ?>
    </tbody>
</table>

<a href="dashboard_admin.php" style="display: inline-block; margin-top: 20px;">← Retour au tableau de bord</a>

</body>
</html>
