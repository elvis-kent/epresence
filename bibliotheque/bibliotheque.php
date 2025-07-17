<?php
require_once('includes/config.php');
session_start();

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['etudiant', 'professeur'])) {
    header('Location: login.php');
    exit();
}

// Identifier le département de l’utilisateur connecté
$id_user = $_SESSION['id'];
$role = $_SESSION['role'];

if ($role == 'etudiant') {
    $stmt = $conn->prepare("SELECT id_departement FROM etudiants WHERE id = ?");
} else {
    $stmt = $conn->prepare("SELECT id_departement FROM professeurs WHERE id = ?");
}
$stmt->bind_param("i", $id_user);
$stmt->execute();
$stmt->bind_result($id_departement);
$stmt->fetch();
$stmt->close();

// Rechercher par mot-clé ou type
$search = isset($_GET['search']) ? '%' . $_GET['search'] . '%' : '%';
$type = isset($_GET['type']) && $_GET['type'] !== 'tous' ? $_GET['type'] : '%';

$stmt = $conn->prepare("SELECT * FROM bibliotheque WHERE id_departement = ? AND titre LIKE ? AND type_document LIKE ? AND (visible_pour = ? OR visible_pour = 'tous') ORDER BY date_ajout DESC");
$stmt->bind_param("isss", $id_departement, $search, $type, $role);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Bibliothèque</title>
    <style>
        body { font-family: Arial; background: #f9f9f9; padding: 20px; }
        .container { max-width: 1000px; margin: auto; background: white; padding: 20px; border-radius: 8px; }
        h2 { text-align: center; }
        form { text-align: center; margin-bottom: 20px; }
        input, select { padding: 10px; margin: 5px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 10px; border: 1px solid #ccc; text-align: left; }
        th { background: #0066cc; color: white; }
        a.download { background: #28a745; color: white; padding: 6px 10px; border-radius: 4px; text-decoration: none; }
    </style>
</head>
<body>

<div class="container">
    <h2>📚 Bibliothèque virtuelle</h2>

    <form method="GET">
        <input type="text" name="search" placeholder="Rechercher un document" value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
        <select name="type">
            <option value="tous">Tous les types</option>
            <option value="PDF">PDF</option>
            <option value="Word">Word</option>
            <option value="Vidéo">Vidéo</option>
            <option value="Audio">Audio</option>
        </select>
        <button type="submit">🔍 Rechercher</button>
    </form>

    <table>
        <tr>
            <th>Titre</th>
            <th>Type</th>
            <th>Date d’ajout</th>
            <th>Action</th>
        </tr>
        <?php while ($doc = $result->fetch_assoc()) { ?>
            <tr>
                <td><?= htmlspecialchars($doc['titre']) ?></td>
                <td><?= htmlspecialchars($doc['type_document']) ?></td>
                <td><?= date('d/m/Y', strtotime($doc['date_ajout'])) ?></td>
                <td>
                    <a class="download" href="uploads/bibliotheque/<?= urlencode($doc['fichier']) ?>" download>📥 Télécharger</a>
                </td>
            </tr>
        <?php } ?>
    </table>
</div>

</body>
</html>
