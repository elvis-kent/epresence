<?php
require_once('includes/config.php');
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin_departement') {
    header('Location: login.php');
    exit();
}

$id_admin = $_SESSION['id'];

// Récupérer l'id du département lié à cet admin
$stmt = $conn->prepare("SELECT id_departement FROM admins_departement WHERE id = ?");
$stmt->bind_param("i", $id_admin);
$stmt->execute();
$stmt->bind_result($id_departement);
$stmt->fetch();
$stmt->close();

// Suppression de document
if (isset($_GET['delete'])) {
    $id_doc = intval($_GET['delete']);
    $stmt = $conn->prepare("SELECT fichier FROM bibliotheque WHERE id = ? AND id_departement = ?");
    $stmt->bind_param("ii", $id_doc, $id_departement);
    $stmt->execute();
    $stmt->bind_result($fichier);
    if ($stmt->fetch()) {
        unlink("uploads/bibliotheque/$fichier"); // Supprimer le fichier du serveur
        $stmt->close();

        $stmt = $conn->prepare("DELETE FROM bibliotheque WHERE id = ?");
        $stmt->bind_param("i", $id_doc);
        $stmt->execute();
        $message = "✅ Document supprimé avec succès.";
    } else {
        $message = "❌ Document introuvable ou non autorisé.";
    }
    $stmt->close();
}

// Recherche
$search = isset($_GET['search']) ? '%' . $_GET['search'] . '%' : '%';
$type = isset($_GET['type']) && $_GET['type'] !== 'tous' ? $_GET['type'] : '%';

$stmt = $conn->prepare("SELECT * FROM bibliotheque WHERE id_departement = ? AND titre LIKE ? AND type_document LIKE ? ORDER BY date_ajout DESC");
$stmt->bind_param("iss", $id_departement, $search, $type);
$stmt->execute();
$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gérer la bibliothèque</title>
    <style>
        body { font-family: Arial; background: #f5f5f5; padding: 20px; }
        .container { max-width: 1000px; margin: auto; background: white; padding: 20px; border-radius: 8px; }
        h2 { text-align: center; }
        .message { color: green; text-align: center; margin: 10px 0; }
        form { text-align: center; margin-bottom: 20px; }
        input[type="text"], select {
            padding: 10px;
            margin: 0 5px;
            width: 200px;
        }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; border: 1px solid #ccc; }
        th { background: #0066cc; color: white; }
        a.btn { padding: 5px 10px; text-decoration: none; color: white; border-radius: 4px; }
        .delete { background: red; }
        .download { background: green; }
    </style>
</head>
<body>

<div class="container">
    <h2>🛠 Gérer les documents de la bibliothèque</h2>

    <?php if (isset($message)) echo "<div class='message'>$message</div>"; ?>

    <form method="GET">
        <input type="text" name="search" placeholder="Rechercher un titre" value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
        <select name="type">
            <option value="tous">Tous types</option>
            <option value="PDF">PDF</option>
            <option value="Word">Word</option>
            <option value="Vidéo">Vidéo</option>
            <option value="Audio">Audio</option>
        </select>
        <button type="submit">🔍 Filtrer</button>
    </form>

    <table>
        <tr>
            <th>Titre</th>
            <th>Type</th>
            <th>Fichier</th>
            <th>Ajouté le</th>
            <th>Actions</th>
        </tr>
        <?php while ($doc = $result->fetch_assoc()) { ?>
            <tr>
                <td><?= htmlspecialchars($doc['titre']) ?></td>
                <td><?= htmlspecialchars($doc['type_document']) ?></td>
                <td><?= htmlspecialchars($doc['fichier']) ?></td>
                <td><?= date('d/m/Y', strtotime($doc['date_ajout'])) ?></td>
                <td>
                    <a class="btn download" href="uploads/bibliotheque/<?= urlencode($doc['fichier']) ?>" download>📥</a>
                    <a class="btn delete" href="?delete=<?= $doc['id'] ?>" onclick="return confirm('Confirmer la suppression ?')">🗑</a>
                </td>
            </tr>
        <?php } ?>
    </table>
</div>

</body>
</html>
