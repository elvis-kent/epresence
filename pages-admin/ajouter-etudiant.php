<?php
require_once('../includes/config.php');
require_once('../phpqrcode/qrlib.php');
require_once('../utils/log-action.php');
session_start();

// Sécurité session
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin_departement') {
    header('Location: ../login.php');
    exit();
}

$id_departement = $_SESSION['id_departement'] ?? null;
if (!$id_departement) {
    die("Identifiant de département non défini.");
}

// Récupérer filières et promotions
$filieres = $conn->query("SELECT * FROM filieres WHERE id_departement = $id_departement");

$promotions = $conn->query("
    SELECT p.*, f.nom AS nom_filiere
    FROM promotions p
    JOIN filieres f ON p.id_filiere = f.id
    WHERE f.id_departement = $id_departement
");

// Ajouter étudiant
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajouter'])) {
    $nom = htmlspecialchars($_POST['nom']);
    $postnom = htmlspecialchars($_POST['postnom']);
    $prenom = htmlspecialchars($_POST['prenom']);
    $matricule = htmlspecialchars($_POST['matricule']);
    $promotion_id = intval($_POST['promotion_id']);
    $photo = '';
    $qr_code = '';

    $contenu = "Nom: $nom\nPostnom: $postnom\nPrénom: $prenom\nMatricule: $matricule";
    $chemin_qr = '../qrcodes/' . $matricule . '.png';
    if (!file_exists('../qrcodes')) {
        mkdir('../qrcodes');
    }
    QRcode::png($contenu, $chemin_qr, QR_ECLEVEL_L, 4);
    $qr_code_data = base64_encode(file_get_contents($chemin_qr));

    $stmt = $conn->prepare("INSERT INTO etudiants (nom, postnom, prenom, matricule, photo, qr_code, id_promotion) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssi", $nom, $postnom, $prenom, $matricule, $photo, $qr_code_data, $promotion_id);
    $stmt->execute();
    $stmt->close();

    // Log de l’action
    logAction($conn, $_SESSION['id'], "Ajout de l'étudiant $nom $postnom ($matricule)");

    $message = "Étudiant ajouté avec succès !";
}

// Suppression d'étudiant
if (isset($_GET['supprimer'])) {
    $id = intval($_GET['supprimer']);
    $result = $conn->query("DELETE FROM etudiants WHERE id = $id");
    if ($result) {
        logAction($conn, $_SESSION['id'], "Suppression de l'étudiant ID $id");
        $message = "Étudiant supprimé.";
    }
}

// Liste étudiants
$etudiants = $conn->query("
    SELECT e.*, p.nom AS promotion, f.nom AS filiere
    FROM etudiants e
    JOIN promotions p ON e.id_promotion = p.id
    JOIN filieres f ON p.id_filiere = f.id
    WHERE f.id_departement = $id_departement
    ORDER BY e.id DESC
");
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un étudiant</title>
    <style>
        body { font-family: Arial; background: #f4f4f4; padding: 20px; }
        h2 { color: #444; }
        form, table { background: white; padding: 20px; border-radius: 5px; margin-top: 20px; }
        input, select, button {
            padding: 10px;
            margin-top: 10px;
            width: 100%;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        table { width: 100%; border-collapse: collapse; margin-top: 30px; }
        th, td { border: 1px solid #ccc; padding: 10px; }
        th { background: #343a40; color: white; }
        .message { background: #d4edda; color: #155724; padding: 10px; margin-top: 10px; }
        img.qr { width: 60px; }
    </style>
</head>
<body>

<h2>Ajouter un étudiant</h2>

<?php if (isset($message)) echo "<div class='message'>$message</div>"; ?>

<form method="POST">
    <label>Nom</label>
    <input type="text" name="nom" required>

    <label>Postnom</label>
    <input type="text" name="postnom" required>

    <label>Prénom</label>
    <input type="text" name="prenom" required>

    <label>Matricule</label>
    <input type="text" name="matricule" required>

    <label>Promotion</label>
    <select name="promotion_id" required>
        <option value="">-- Choisir la promotion --</option>
        <?php while ($row = $promotions->fetch_assoc()) { ?>
            <option value="<?= $row['id'] ?>">
                <?= htmlspecialchars($row['nom']) ?> - <?= htmlspecialchars($row['annee']) ?> (<?= htmlspecialchars($row['nom_filiere']) ?>)
            </option>
        <?php } ?>
    </select>

    <button type="submit" name="ajouter">Ajouter l’étudiant</button>
</form>

<h3>Liste des étudiants ajoutés</h3>
<table>
    <tr>
        <th>Nom complet</th>
        <th>Matricule</th>
        <th>Filière</th>
        <th>Promotion</th>
        <th>QR Code</th>
        <th>Action</th>
    </tr>
    <?php while ($row = $etudiants->fetch_assoc()) { ?>
        <tr>
            <td><?= htmlspecialchars($row['nom'] . ' ' . $row['postnom'] . ' ' . $row['prenom']) ?></td>
            <td><?= htmlspecialchars($row['matricule']) ?></td>
            <td><?= htmlspecialchars($row['filiere']) ?></td>
            <td><?= htmlspecialchars($row['promotion']) ?></td>
            <td><img src="data:image/png;base64,<?= $row['qr_code'] ?>" class="qr" /></td>
            <td><a href="?supprimer=<?= $row['id'] ?>" onclick="return confirm('Supprimer cet étudiant ?')">Supprimer</a></td>
        </tr>
    <?php } ?>
</table>

</body>
</html>
