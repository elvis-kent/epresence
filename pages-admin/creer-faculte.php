<?php
require_once('includes/config.php');
session_start();

// Vérifie si l'utilisateur est super admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'superadmin') {
    header('Location: login.php');
    exit();
}

// Ajouter une faculté
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ajouter_faculte'])) {
    $nom = htmlspecialchars($_POST['nom']);
    $description = htmlspecialchars($_POST['description']);

    $stmt = $conn->prepare("INSERT INTO facultes (nom, description) VALUES (?, ?)");
    $stmt->bind_param("ss", $nom, $description);
    $stmt->execute();
    $stmt->close();

    $message = "Faculté ajoutée avec succès.";
}

// Supprimer une faculté
if (isset($_GET['supprimer'])) {
    $id = intval($_GET['supprimer']);
    $conn->query("DELETE FROM facultes WHERE id = $id");
    $message = "Faculté supprimée.";
}

// Récupérer les facultés
$facultes = $conn->query("SELECT * FROM facultes ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Créer une faculté</title>
    <style>
        body { font-family: Arial; background: #f2f2f2; padding: 20px; }
        h2 { color: #333; }
        form, table { background: white; padding: 15px; border-radius: 5px; margin-top: 20px; }
        input, textarea, button {
            padding: 8px;
            margin-top: 10px;
            width: 100%;
            border-radius: 4px;
            border: 1px solid #ccc;
        }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
        th { background: #007BFF; color: white; }
        a { color: red; text-decoration: none; }
        .message { background: #d4edda; color: #155724; padding: 10px; margin-top: 10px; border-radius: 4px; }
    </style>
</head>
<body>

    <h2>Créer une nouvelle faculté</h2>

    <?php if (isset($message)) echo "<div class='message'>$message</div>"; ?>

    <form method="POST">
        <label>Nom de la faculté</label>
        <input type="text" name="nom" required>

        <label>Description</label>
        <textarea name="description" rows="3"></textarea>

        <button type="submit" name="ajouter_faculte">Ajouter</button>
    </form>

    <h3>Liste des facultés existantes</h3>
    <table>
        <tr>
            <th>Nom</th>
            <th>Description</th>
            <th>Action</th>
        </tr>
        <?php while ($row = $facultes->fetch_assoc()) { ?>
            <tr>
                <td><?= htmlspecialchars($row['nom']) ?></td>
                <td><?= htmlspecialchars($row['description']) ?></td>
                <td><a href="?supprimer=<?= $row['id'] ?>" onclick="return confirm('Supprimer cette faculté ?')">Supprimer</a></td>
            </tr>
        <?php } ?>
    </table>

</body>
</html>
