<?php
require_once('includes/config.php');
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'professeur') {
    header('Location: login.php');
    exit();
}

$id_prof = $_SESSION['id'];

// Récupération des cours du prof
$cours = $conn->query("
    SELECT c.id, c.nom AS nom_cours, p.nom AS promotion, f.nom AS filiere
    FROM cours c
    JOIN promotions p ON c.id_promotion = p.id
    JOIN filieres f ON p.id_filiere = f.id
    WHERE c.id_professeur = $id_prof
");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['scan'])) {
    $qr_data = trim($_POST['qr_code']);
    $id_cours = intval($_POST['cours_id']);

    // On suppose que le matricule est dans le QR
    $matricule = extractMatricule($qr_data); // on crée cette fonction simple
    $stmt = $conn->prepare("SELECT id FROM etudiants WHERE matricule = ?");
    $stmt->bind_param("s", $matricule);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($id_etudiant);
        $stmt->fetch();

        // Vérifions si déjà scanné aujourd’hui
        $date = date('Y-m-d');
        $verif = $conn->prepare("SELECT id FROM presences WHERE id_etudiant=? AND id_cours=? AND DATE(date_pointage)=?");
        $verif->bind_param("iis", $id_etudiant, $id_cours, $date);
        $verif->execute();
        $verif->store_result();

        if ($verif->num_rows === 0) {
            $insert = $conn->prepare("INSERT INTO presences (id_etudiant, id_cours, date_pointage) VALUES (?, ?, NOW())");
            $insert->bind_param("ii", $id_etudiant, $id_cours);
            $insert->execute();
            $message = "Présence enregistrée pour $matricule.";
        } else {
            $message = "Cet étudiant a déjà été scanné aujourd’hui.";
        }

    } else {
        $message = "QR invalide ou étudiant inconnu.";
    }
}

// Fonction d’extraction
function extractMatricule($qr_data) {
    // Ici on suppose que le QR contient une ligne: Matricule: 2023001
    if (preg_match('/Matricule:\s*(\S+)/i', $qr_data, $match)) {
        return $match[1];
    }
    return '';
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Scan de présence</title>
    <style>
        body { font-family: Arial; background: #f0f0f0; padding: 20px; }
        .box { background: white; padding: 20px; border-radius: 8px; max-width: 600px; margin: auto; }
        input, select, button, textarea {
            width: 100%; padding: 10px; margin-top: 10px; border-radius: 5px;
            border: 1px solid #ccc;
        }
        .message { margin-top: 20px; padding: 10px; background: #e2ffe2; color: #2a5d2a; }
    </style>
</head>
<body>

<div class="box">
    <h2>Scan de présence</h2>

    <?php if (isset($message)) echo "<div class='message'>$message</div>"; ?>

    <form method="POST">
        <label>Choisir un cours</label>
        <select name="cours_id" required>
            <option value="">-- Choisir --</option>
            <?php while ($row = $cours->fetch_assoc()) { ?>
                <option value="<?= $row['id'] ?>">
                    <?= $row['nom_cours'] ?> (<?= $row['promotion'] ?> - <?= $row['filiere'] ?>)
                </option>
            <?php } ?>
        </select>

        <label>Coller ici le contenu du QR scanné :</label>
        <textarea name="qr_code" rows="4" placeholder="Ex: Nom: ... Matricule: 20231234" required></textarea>

        <button type="submit" name="scan">Scanner et enregistrer</button>
    </form>
</div>

</body>
</html>
