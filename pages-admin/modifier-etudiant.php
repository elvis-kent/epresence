<?php
session_start();
require_once 'config.php';

// Vérification du rôle
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin_departement') {
    header("Location: login.php");
    exit;
}

// Vérifier que l'id est présent en GET
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: liste_etudiants.php");
    exit;
}

$id = intval($_GET['id']);
$message = "";

// Récupérer les données actuelles de l'étudiant
$stmt = $conn->prepare("SELECT * FROM etudiants WHERE id = ? AND departement = ?");
$stmt->bind_param("is", $id, $_SESSION['departement']);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    // Étudiant non trouvé ou pas dans le département
    header("Location: liste_etudiants.php");
    exit;
}
$etudiant = $result->fetch_assoc();

// Traitement du formulaire POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = $_POST['nom'];
    $matricule = $_POST['matricule'];
    $promotion = $_POST['promotion'];

    // Mettre à jour la base
    $stmt = $conn->prepare("UPDATE etudiants SET nom = ?, matricule = ?, promotion = ? WHERE id = ? AND departement = ?");
    $stmt->bind_param("sssis", $nom, $matricule, $promotion, $id, $_SESSION['departement']);

    if ($stmt->execute()) {
        // Mettre à jour aussi le QR code
        include 'phpqrcode/qrlib.php';
        $qrCodeName = $matricule . '.png';
        $qrCodePath = 'phpqrcode/temp/' . $qrCodeName;
        QRcode::png("MATRICULE:$matricule; NOM:$nom; DEPARTEMENT:" . $_SESSION['departement'] . "; PROMO:$promotion", $qrCodePath);

        // Mettre à jour le nom du QR dans la BDD
        $stmt2 = $conn->prepare("UPDATE etudiants SET qr_code = ? WHERE id = ?");
        $stmt2->bind_param("si", $qrCodeName, $id);
        $stmt2->execute();

        // Log
        logAction($conn, $_SESSION['nom'], $_SESSION['role'], "Modification de l’étudiant $nom ($matricule)");

        $message = "✅ Étudiant modifié avec succès !";

        // Actualiser les données pour le formulaire
        $etudiant['nom'] = $nom;
        $etudiant['matricule'] = $matricule;
        $etudiant['promotion'] = $promotion;
        $etudiant['qr_code'] = $qrCodeName;
    } else {
        $message = "❌ Erreur lors de la modification : " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier Étudiant</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f2f2f2; padding: 40px; }
        form { background: white; padding: 20px; border-radius: 8px; max-width: 500px; margin: auto; box-shadow: 0 0 10px #ccc; }
        input[type="text"] {
            width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ccc; border-radius: 4px;
        }
        input[type="submit"] {
            background: #2c3e50; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;
        }
        input[type="submit"]:hover { background: #1a242f; }
        .message { text-align: center; margin-top: 15px; font-weight: bold; }
        .qr-preview { text-align: center; margin-bottom: 20px; }
    </style>
</head>
<body>

<h2 style="text-align:center;">✏️ Modifier Étudiant</h2>

<div class="qr-preview">
    <img src="phpqrcode/temp/<?= htmlspecialchars($etudiant['qr_code']) ?>" alt="QR Code" width="120">
</div>

<form method="POST">
    <label>Nom complet :</label>
    <input type="text" name="nom" required value="<?= htmlspecialchars($etudiant['nom']) ?>">

    <label>Matricule :</label>
    <input type="text" name="matricule" required value="<?= htmlspecialchars($etudiant['matricule']) ?>">

    <label>Promotion :</label>
    <input type="text" name="promotion" required value="<?= htmlspecialchars($etudiant['promotion']) ?>">

    <input type="submit" value="Modifier l'étudiant">
</form>

<?php if ($message): ?>
    <div class="message"><?= $message ?></div>
<?php endif; ?>

</body>
</html>
