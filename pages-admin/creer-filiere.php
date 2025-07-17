<?php
require_once('includes/config.php');
session_start();

// Vérifie si c'est bien un admin de département
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin_departement') {
    header('Location: login.php');
    exit();
}

// Récupère l'id du département lié à l'admin
$id_departement = $_SESSION['id_departement'] ?? null;

// Ajouter une filière
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajouter_filiere'])) {
    $nom = htmlspecialchars($_POST['nom']);
    $description = htmlspecialchars($_POST['description']);

    $stmt = $conn->prepare("INSERT INTO filieres (nom, description, id_departement) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $nom, $description, $id_departement);
    $stmt->execute();
    $stmt->close();

    $message = "Filière ajoutée avec succès.";
}

// Supprimer une filière
if (isset($_GET['supprimer'])) {
    $id = intval($_GET['supprimer']);
    $conn->query("DELETE FROM filieres WHERE id = $id");
    $message = "Filière supprimée.";
}

// Récupérer les filières du département
$filieres = $conn->query("SELECT * FROM filieres WHERE id_departement = $id_departement ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Créer une filière</title>
    <style>
        body { font-family: Arial; background: #f9f9f9; padding: 20px; }
        h2 { color: #444; }
        form, table { background: #fff; padding: 20px; border-radius: 5px; margin-top: 20px; }
        input, textarea, button {
            padding: 10px;
            margin-top: 10px;
            width: 100%;
            border-radius: 4px;
            border: 1px solid #ccc;
        }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
        th { background: #28a745; color: white; }
        .message { background: #d4edda; padding: 10px; margin-top: 10px; color: #155724; }
    </style>
</head>
<body>

    <h2>Créer une nouvelle filière</h2>

    <?php if (isset($message)) echo "<div class='message'>$message</div>"; ?>

    <form method="POST">
        <label>Nom de la filière</label>
        <input type="text" name="nom" required>

        <label>Description</label>
        <textarea name="description" rows="3"></textarea>

        <button type="submit" name="ajouter_filiere">Ajouter</button>
    </form>

    <h3>Liste des filières existantes</h3>
    <table>
        <tr>
            <th>Nom</th>
            <th>Description</th>
            <th>Action</th>
        </tr>
        <?php while ($row = $filieres->fetch_assoc()) { ?>
            <tr>
                <td><?= htmlspecialchars($row['nom']) ?></td>
                <td><?= htmlspecialchars($row['description']) ?></td>
                <td><a href="?supprimer=<?= $row['id'] ?>" onclick="return confirm('Supprimer cette filière ?')">Supprimer</a></td>
            </tr>
        <?php } ?>
    </table>

</body>
</html>
