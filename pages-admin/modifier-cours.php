<?php
require_once('includes/config.php');
session_start();

if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin_faculte') {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID de cours invalide.");
}

$cours_id = intval($_GET['id']);

// Récupérer le cours à modifier, vérifier qu'il appartient bien à cette faculté
$query = "
SELECT c.*, h.jour, h.heure_debut, h.heure_fin 
FROM cours c 
LEFT JOIN horaires h ON c.id = h.cours_id
JOIN promotions pr ON c.promotion_id = pr.id
JOIN filieres f ON pr.filiere_id = f.id
JOIN departements d ON f.departement_id = d.id
JOIN facultes fa ON d.faculte_id = fa.id
WHERE c.id = ? AND fa.id = ?
LIMIT 1";

$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $cours_id, $_SESSION['faculte_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Cours non trouvé ou accès interdit.");
}

$cours = $result->fetch_assoc();

// Récupérer toutes les promotions et professeurs pour les select
$promotions = $conn->query("SELECT p.id, p.nom, f.nom AS filiere FROM promotions p
JOIN filieres f ON p.filiere_id = f.id
JOIN departements d ON f.departement_id = d.id
JOIN facultes fa ON d.faculte_id = fa.id
WHERE fa.id = " . intval($_SESSION['faculte_id']));

$professeurs = $conn->query("SELECT * FROM professeurs WHERE faculte_id = " . intval($_SESSION['faculte_id']));

// Traitement du formulaire
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nom = $_POST['nom'];
    $professeur_id = $_POST['professeur_id'];
    $promotion_id = $_POST['promotion_id'];
    $jour = $_POST['jour'];
    $heure_debut = $_POST['heure_debut'];
    $heure_fin = $_POST['heure_fin'];

    // Mise à jour cours
    $stmt_update = $conn->prepare("UPDATE cours SET nom=?, professeur_id=?, promotion_id=? WHERE id=?");
    $stmt_update->bind_param("siii", $nom, $professeur_id, $promotion_id, $cours_id);
    $stmt_update->execute();

    // Mise à jour horaires
    // Vérifier si horaire existe déjà
    $check_horaire = $conn->prepare("SELECT id FROM horaires WHERE cours_id=?");
    $check_horaire->bind_param("i", $cours_id);
    $check_horaire->execute();
    $res = $check_horaire->get_result();

    if ($res->num_rows > 0) {
        // Update
        $horaire_update = $conn->prepare("UPDATE horaires SET jour=?, heure_debut=?, heure_fin=? WHERE cours_id=?");
        $horaire_update->bind_param("sssi", $jour, $heure_debut, $heure_fin, $cours_id);
        $horaire_update->execute();
    } else {
        // Insert
        $horaire_insert = $conn->prepare("INSERT INTO horaires (cours_id, jour, heure_debut, heure_fin) VALUES (?, ?, ?, ?)");
        $horaire_insert->bind_param("isss", $cours_id, $jour, $heure_debut, $heure_fin);
        $horaire_insert->execute();
    }

    header("Location: liste_cours.php?msg=modifié");
    exit();
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier le cours</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #eef2f3;
            padding: 20px;
        }
        .form-container {
            background: white;
            padding: 30px;
            max-width: 600px;
            margin: auto;
            border-radius: 10px;
            box-shadow: 0 0 10px #ccc;
        }
        label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
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
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 5px;
        }
        a.back-link {
            display: inline-block;
            margin-top: 20px;
            text-decoration: none;
            color: #555;
        }
        a.back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Modifier le cours</h2>
    <form method="POST">
        <label>Nom du cours</label>
        <input type="text" name="nom" value="<?= htmlspecialchars($cours['nom']) ?>" required>

        <label>Professeur</label>
        <select name="professeur_id" required>
            <option value="">-- Choisir un professeur --</option>
            <?php while ($p = $professeurs->fetch_assoc()): ?>
                <option value="<?= $p['id'] ?>" <?= $p['id'] == $cours['professeur_id'] ? "selected" : "" ?>>
                    <?= htmlspecialchars($p['nom'] . " " . $p['postnom'] . " " . $p['prenom']) ?>
                </option>
            <?php endwhile; ?>
        </select>

        <label>Promotion</label>
        <select name="promotion_id" required>
            <option value="">-- Choisir une promotion --</option>
            <?php while ($pr = $promotions->fetch_assoc()): ?>
                <option value="<?= $pr['id'] ?>" <?= $pr['id'] == $cours['promotion_id'] ? "selected" : "" ?>>
                    <?= htmlspecialchars($pr['nom'] . " (" . $pr['filiere'] . ")") ?>
                </option>
            <?php endwhile; ?>
        </select>

        <label>Jour</label>
        <select name="jour" required>
            <?php 
            $jours = ["Lundi","Mardi","Mercredi","Jeudi","Vendredi","Samedi"];
            foreach ($jours as $jour) {
                $selected = ($jour == $cours['jour']) ? "selected" : "";
                echo "<option value='$jour' $selected>$jour</option>";
            }
            ?>
        </select>

        <label>Heure de début</label>
        <input type="time" name="heure_debut" value="<?= htmlspecialchars($cours['heure_debut']) ?>" required>

        <label>Heure de fin</label>
        <input type="time" name="heure_fin" value="<?= htmlspecialchars($cours['heure_fin']) ?>" required>

        <button type="submit">Modifier</button>
    </form>

    <a href="liste_cours.php" class="back-link">&larr; Retour à la liste des cours</a>
</div>

</body>
</html>
