<?php
require_once('includes/config.php');
session_start();

// Vérifie si l'utilisateur est admin faculté
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin_faculte') {
    header('Location: login.php');
    exit();
}

// Faculté liée à l’admin
$id_faculte = $_SESSION['id_faculte'] ?? null;

// Ajouter un département
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajouter_departement'])) {
    $nom = htmlspecialchars($_POST['nom']);
    $description = htmlspecialchars($_POST['description']);

    $stmt = $conn->prepare("INSERT INTO departements (nom, description, id_faculte) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $nom, $description, $id_faculte);
    $stmt->execute();
    $stmt->close();

    $message = "Département ajouté avec succès.";
}

// Supprimer un département
if (isset($_GET['supprimer'])) {
    $id = intval($_GET['supprimer']);
    $conn->query("DELETE FROM departements WHERE id = $id");
    $message = "Département supprimé.";
}

// Récupérer les départements liés à cette faculté
$departements = $conn->query("SELECT * FROM departements WHERE id_faculte = $id_faculte ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Créer un département</title>
    <style>
        body { font-family: Arial; background: #f4f4f4; padding: 20px; }
        h2 { color: #333; }
        form, table { background: white; padding: 20px; border-radius: 5px; margin-top: 20px; }
        input, textarea, button {
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

    <h2>Créer un nouveau département</h2>

    <?php if (isset($message)) echo "<div class='message'>$message</div>"; ?>

    <form method="POST">
        <label>Nom du département</label>
        <input type="text" name="nom" required>

        <label>Description</label>
        <textarea name="description" rows="3"></textarea>

        <button type="submit" name="ajouter_departement">Ajouter</button>
    </form>

    <h3>Liste des départements</h3>
    <table>
        <tr>
            <th>Nom</th>
            <th>Description</th>
            <th>Action</th>
        </tr>
        <?php while ($row = $departements->fetch_assoc()) { ?>
            <tr>
                <td><?= htmlspecialchars($row['nom']) ?></td>
                <td><?= htmlspecialchars($row['description']) ?></td>
                <td><a href="?supprimer=<?= $row['id'] ?>" onclick="return confirm('Supprimer ce département ?')">Supprimer</a></td>
            </tr>
        <?php } ?>
    </table>

</body>
</html>
