<?php
require_once "config.php"; // Connexion à la base
require_once "phpqrcode/qrlib.php"; // Librairie QR Code

// Création du dossier qr/ si non existant
$qrPath = "qr/";
if (!file_exists($qrPath)) {
    mkdir($qrPath, 0777, true);
}

// Récupération des données
$nom = $_POST['nom'];
$matricule = $_POST['matricule'];
$faculte = $_POST['faculte'];
$departement = $_POST['departement'];
$filiere = $_POST['filiere'];
$promotion = $_POST['promotion'];

// Traitement photo
$photo_nom = $_FILES['photo']['name'];
$photo_tmp = $_FILES['photo']['tmp_name'];
$photo_path = "uploads/" . uniqid() . "_" . basename($photo_nom);
if (!file_exists("uploads")) {
    mkdir("uploads", 0777, true);
}
move_uploaded_file($photo_tmp, $photo_path);

// Génération QR Code
$qrText = "Nom:$nom; Matricule:$matricule; Faculté:$faculte; Département:$departement; Filière:$filiere; Promotion:$promotion";
$qrFileName = $qrPath . uniqid() . ".png";
QRcode::png($qrText, $qrFileName, QR_ECLEVEL_L, 4);

// Insertion BDD
$sql = "INSERT INTO etudiants (nom, matricule, faculte, departement, filiere, promotion, photo, qr_code) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->execute([$nom, $matricule, $faculte, $departement, $filiere, $promotion, $photo_path, $qrFileName]);

// Récupérer l’ID inséré
$id = $conn->lastInsertId();

// Redirection vers la carte
header("Location: carte.php?id=" . $id);
exit();
?>
