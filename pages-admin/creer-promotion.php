<?php
require_once('includes/config.php');
session_start();

// Vérifie si l'utilisateur est bien un admin_departement
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin_departement') {
    header('Location: login.php');
    exit();
}

$id_departement = $_SESSION['id_departement'] ?? null;

// Récupère les filières de ce département
$filieres = $conn->query("SELECT id, nom FROM filieres WHERE id_departement = $id_departement");

// Ajouter une promotion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajouter_promotion'])) {
    $id_filiere = intval($_POST['id_filiere']);
    $nom = htmlspecialchars($_POST['nom']);
    $annee = htmlspecialchars($_POST['annee']);

    $stmt = $conn->prepare("INSERT INTO promotions (nom, annee, id_filiere) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $nom, $annee, $id_filiere);
    $stmt->execute();
    $stmt->close();

    $message = "Promotion ajoutée avec succès.";
}

// Supprimer une promotion
if (isset($_GET['supprimer'])) {
    $id = intval($_GET['supprimer']);
    $conn->query("DELETE FROM promotions WHERE id = $id");
    $message = "Promotion supprimée.";
}

// Récupérer les promotions existantes
$promotions = $conn->query("
    SELECT promotions.id, promotions.nom, promotions.annee, filieres.nom AS filiere
    FROM promotions
    JOIN filieres ON promotions.id_filiere = filieres.id
    WHERE filieres.id_departement = $id_departement
    ORDER BY promotions.id DESC
");
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Créer une promotion</title>
    <style>
        body { font-family: Arial; background: #f0f0f0; padding: 20px; }
        h2 { color: #333; }
        form, table { background: white; padding: 20px; border-radius: 5px; margin-top: 20px; }
        input, select, button {
            padding: 10px;
            margin-top: 10px;
            width: 100%;
            border-radius: 4px;
            border: 1px solid #ccc;
        }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
        th { background: #007BFF; color: white; }
        .message { background: #d4edda; padding: 10px; margin-top: 10px; color: #155724; }
    </style>
</head>
<body>

    <h2>Créer une nouvelle promotion</h2>

    <?php if (isset($message)) echo "<div class='message'>$message</div>"; ?>

    <form method="POST">
        <label>Filière</label>
        <select name="id_filiere" required>
            <option value="">-- Choisir une filière --</option>
            <?php while ($row = $filieres->fetch_assoc()) { ?>
                <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['nom']) ?></option>
            <?php } ?>
        </select>

        <label>Nom de la promotion (ex : L1, M1...)</label>
        <input type="text" name="nom" required>

        <label>Année académique (ex : 2024-2025)</label>
        <input type="text" name="annee" required>

        <button type="submit" name="ajouter_promotion">Ajouter</button>
    </form>

    <h3>Promotions existantes</h3>
    <table>
        <tr>
            <th>Nom</th>
            <th>Année</th>
            <th>Filière</th>
            <th>Action</th>
        </tr>
        <?php while ($row = $promotions->fetch_assoc()) { ?>
            <tr>
                <td><?= htmlspecialchars($row['nom']) ?></td>
                <td><?= htmlspecialchars($row['annee']) ?></td>
                <td><?= htmlspecialchars($row['filiere']) ?></td>
                <td><a href="?supprimer=<?= $row['id'] ?>" onclick="return confirm('Supprimer cette promotion ?')">Supprimer</a></td>
            </tr>
        <?php } ?>
    </table>

</body>
</html>
