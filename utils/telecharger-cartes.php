<?php
session_start();
require_once 'config.php';

// Vérification d'accès : uniquement super admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'super_admin') {
    header("Location: login.php");
    exit;
}

$etudiants = $conn->query("SELECT * FROM etudiants ORDER BY universite, nom");
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Cartes Étudiant - QR Codes</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f2f2f2; padding: 30px; }
        h2 { text-align: center; }
        .carte {
            width: 300px;
            border: 1px solid #ccc;
            background: white;
            padding: 15px;
            margin: 15px auto;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 0 8px #aaa;
        }
        .carte img.qr { width: 120px; margin: 10px 0; }
        .infos p { margin: 5px; font-size: 14px; }
        .actions button {
            padding: 8px 16px;
            background: #2c3e50;
            color: white;
            border: none;
            border-radius: 4px;
            margin-top: 10px;
            cursor: pointer;
        }
        .actions button:hover { background: #1a242f; }
    </style>
    <script>
        function imprimerCarte(id) {
            const contenu = document.getElementById(id).innerHTML;
            const fenetre = window.open('', '_blank');
            fenetre.document.write('<html><head><title>Carte Étudiant</title></head><body>' + contenu + '</body></html>');
            fenetre.document.close();
            fenetre.print();
        }
    </script>
</head>
<body>

<h2>📄 Téléchargement des Cartes Étudiant avec QR Code</h2>

<?php
$index = 0;
while ($etudiant = $etudiants->fetch_assoc()):
    $index++;
    $idCarte = "carte_" . $index;
    $qrCodePath = "phpqrcode/temp/" . $etudiant['qr_code'];
?>

<div class="carte" id="<?= $idCarte ?>">
    <h3><?= htmlspecialchars($etudiant['nom']) ?></h3>
    <div class="infos">
        <p><strong>Matricule :</strong> <?= htmlspecialchars($etudiant['matricule']) ?></p>
        <p><strong>Université :</strong> <?= htmlspecialchars($etudiant['universite']) ?></p>
        <p><strong>Département :</strong> <?= htmlspecialchars($etudiant['departement']) ?></p>
        <p><strong>Promotion :</strong> <?= htmlspecialchars($etudiant['promotion']) ?></p>
    </div>
    <img src="<?= $qrCodePath ?>" class="qr" alt="QR Code Étudiant">
</div>

<div class="actions" style="text-align:center;">
    <button onclick="imprimerCarte('<?= $idCarte ?>')">🖨️ Imprimer la carte</button>
</div>

<?php endwhile; ?>

</body>
</html>
