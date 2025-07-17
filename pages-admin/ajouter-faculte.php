<?php
require_once('includes/config.php');
require_once('phpqrcode/qrlib.php');
session_start();

if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin_departement') {
    header("Location: login.php");
    exit();
}

$message = "";
$departement_id = $_SESSION['departement_id'];

// Récupérer toutes les filières du département
$filiere_query = $conn->prepare("SELECT id, nom FROM filieres WHERE departement_id = ?");
$filiere_query->bind_param("i", $departement_id);
$filiere_query->execute();
$filiere_result = $filiere_query->get_result();
$filieres = $filiere_result->fetch_all(MYSQLI_ASSOC);
$filiere_query->close();

$promotions = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['filiere_id'])) {
    $stmt = $conn->prepare("SELECT id, nom FROM promotions WHERE filiere_id = ?");
    $stmt->bind_param("i", $_POST['filiere_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $promotions = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['submit'])) {
    $nom = $_POST['nom'];
    $postnom = $_POST['postnom'];
    $prenom = $_POST['prenom'];
    $matricule = $_POST['matricule'];
    $email = $_POST['email'];
    $promotion_id = $_POST['promotion_id'];

    // Générez le contenu QR
    $qr_content = "$matricule|$nom|$postnom|$prenom";
    $qr_filename = "qrcodes/" . uniqid() . ".png";
    QRcode::png($qr_content, $qr_filename, QR_ECLEVEL_H, 4);

    // Enregistrez la photo
    $photo = "";
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = "uploads/";
        $photo_name = basename($_FILES['photo']['name']);
        $target_file = $upload_dir . time() . "_" . $photo_name;
        if (move_uploaded_file($_FILES['photo']['tmp_name'], $target_file)) {
            $photo = $target_file;
        }
    }

    $stmt = $conn->prepare("INSERT INTO etudiants (nom, postnom, prenom, matricule, email, photo, qr_code, promotion_id)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $qr_data = base64_encode(file_get_contents($qr_filename));
    $stmt->bind_param("sssssssi", $nom, $postnom, $prenom, $matricule, $email, $photo, $qr_data, $promotion_id);
    
    if ($stmt->execute()) {
        $message = "✅ Étudiant ajouté avec succès !";
    } else {
        $message = "❌ Erreur : " . $stmt->error;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un étudiant</title>
    <style>
        body { font-family: Arial; background: #f4f4f4; padding: 30px; }
        .box {
            max-width: 600px; margin: auto; background: white; padding: 20px;
            border-radius: 8px; box-shadow: 0 0 10px #ccc;
        }
        h2 { text-align: center; margin-bottom: 20px; }
        label { display: block; margin-top: 10px; }
        input, select {
            width: 100%; padding: 8px; border-radius: 4px;
            border: 1px solid #ccc; box-sizing: border-box;
        }
        button {
            width: 100%; margin-top: 20px; padding: 10px;
            background: #28a745; color: white; border: none;
            font-weight: bold; border-radius: 5px;
        }
        .message { text-align: center; font-weight: bold; margin-top: 15px; }
    </style>
</head>
<body>

<div class="box">
    <h2>Ajouter un étudiant</h2>
    <form method="POST" enctype="multipart/form-data">
        <label>Nom :</label>
        <input type="text" name="nom" required>
        
        <label>Post-nom :</label>
        <input type="text" name="postnom" required>
        
        <label>Prénom :</label>
        <input type="text" name="prenom" required>

        <label>Matricule :</label>
        <input type="text" name="matricule" required>

        <label>Email :</label>
        <input type="email" name="email">

        <label>Photo :</label>
        <input type="file" name="photo">

        <label>Filière :</label>
        <select name="filiere_id" onchange="this.form.submit()">
            <option value="">-- Sélectionner --</option>
            <?php foreach ($filieres as $f): ?>
                <option value="<?= $f['id'] ?>" <?= isset($_POST['filiere_id']) && $_POST['filiere_id'] == $f['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($f['nom']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <?php if (!empty($promotions)): ?>
            <label>Promotion :</label>
            <select name="promotion_id" required>
                <option value="">-- Choisir --</option>
                <?php foreach ($promotions as $p): ?>
                    <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nom']) ?></option>
                <?php endforeach; ?>
            </select>
        <?php endif; ?>

        <input type="hidden" name="submit" value="1">
        <button type="submit">Ajouter</button>
    </form>

    <?php if ($message): ?>
        <div class="message"><?= $message ?></div>
    <?php endif; ?>
</div>

</body>
</html>
