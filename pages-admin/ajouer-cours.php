<?php
require_once('../includes/config.php');
session_start();

// Seul l'admin de faculté peut accéder
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin_faculte') {
    header("Location: ../login.php");
    exit();
}

$faculte_id = intval($_SESSION['faculte_id']);
$message = "";

// Récupération des promotions de la faculté
$promotions = $conn->query("
    SELECT p.id, p.nom, f.nom AS filiere
    FROM promotions p
    JOIN filieres f ON p.id_filiere = f.id
    JOIN departements d ON f.id_departement = d.id
    JOIN facultes fa ON d.id_faculte = fa.id
    WHERE fa.id = $faculte_id
");

// Récupération des professeurs de la faculté
$professeurs = $conn->query("SELECT id, nom, postnom, prenom FROM professeurs WHERE faculte_id = $faculte_id");

// Traitement de l'ajout
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nom = trim($_POST['nom']);
    $professeur_id = intval($_POST['professeur_id']);
    $promotion_id = intval($_POST['promotion_id']);
    $jour = $_POST['jour'];
    $heure_debut = $_POST['heure_debut'];
    $heure_fin = $_POST['heure_fin'];

    // Insertion du cours
    $stmt = $conn->prepare("INSERT INTO cours (nom_cours, professeur_id, promotion_id, statut) VALUES (?, ?, ?, 'non introduit')");
    $stmt->bind_param("sii", $nom, $professeur_id, $promotion_id);

    if ($stmt->execute()) {
        $cours_id = $conn->insert_id;

        // Insertion de l’horaire
        $horaire = $conn->prepare("INSERT INTO horaires (cours_id, jour, heure_debut, heure_fin) VALUES (?, ?, ?, ?)");
        $horaire->bind_param("isss", $cours_id, $jour, $heure_debut, $heure_fin);
        $horaire->execute();

        header("Location: liste-cours.php?msg=ajoute");
        exit();
    } else {
        $message = "❌ Erreur : " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un cours</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #eef2f3;
            padding: 30px;
        }
        .form-container {
            background: white;
            padding: 30px;
            max-width: 650px;
            margin: auto;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 { text-align: center; color: #007BFF; }
        label {
            font-weight: bold;
            margin-top: 15px;
            display: block;
        }
        input, select {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
        }
        button {
            margin-top: 20px;
            background: #007BFF;
            color: white;
            border: none;
            padding: 10px;
            width: 100%;
            font-size: 16px;
            border-radius: 5px;
        }
        .message {
            color: red;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Ajouter un nouveau cours</h2>

    <?php if (!empty($message)): ?>
        <p class="message"><?= $message ?></p>
    <?php endif; ?>

    <form method="POST">
        <label>Nom du cours</label>
        <input type="text" name="nom" required>

        <label>Professeur</label>
        <select name="professeur_id" required>
            <option value="">-- Choisir un professeur --</option>
            <?php while ($p = $professeurs->fetch_assoc()): ?>
                <option value="<?= $p['id'] ?>">
                    <?= htmlspecialchars($p['nom'] . ' ' . $p['postnom'] . ' ' . $p['prenom']) ?>
                </option>
            <?php endwhile; ?>
        </select>

        <label>Promotion</label>
        <select name="promotion_id" required>
            <option value="">-- Choisir une promotion --</option>
            <?php while ($pr = $promotions->fetch_assoc()): ?>
                <option value="<?= $pr['id'] ?>">
                    <?= htmlspecialchars($pr['nom']) ?> (<?= htmlspecialchars($pr['filiere']) ?>)
                </option>
            <?php endwhile; ?>
        </select>

        <label>Jour</label>
        <select name="jour" required>
            <option value="Lundi">Lundi</option>
            <option value="Mardi">Mardi</option>
            <option value="Mercredi">Mercredi</option>
            <option value="Jeudi">Jeudi</option>
            <option value="Vendredi">Vendredi</option>
            <option value="Samedi">Samedi</option>
        </select>

        <label>Heure de début</label>
        <input type="time" name="heure_debut" required>

        <label>Heure de fin</label>
        <input type="time" name="heure_fin" required>

        <button type="submit">Ajouter le cours</button>
    </form>
</div>

</body>
</html>
