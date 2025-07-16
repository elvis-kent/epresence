<?php
session_start();
require_once 'config.php';

// Sécurité : s'assurer que l'utilisateur est un admin de département
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin_departement') {
    header('Location: login.php');
    exit;
}

// Traitement formulaire
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $promotion_id = $_POST['promotion_id'];
    $cours_id = $_POST['cours_id'];
    $jour = $_POST['jour_semaine'];
    $heure_debut = $_POST['heure_debut'];
    $heure_fin = $_POST['heure_fin'];
    $salle = $_POST['salle'];

    $stmt = $conn->prepare("INSERT INTO horaires (promotion_id, cours_id, jour_semaine, heure_debut, heure_fin, salle) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iissss", $promotion_id, $cours_id, $jour, $heure_debut, $heure_fin, $salle);

    if ($stmt->execute()) {
        $message = "Horaire ajouté avec succès.";
    } else {
        $message = "Erreur lors de l'ajout.";
    }
}

// Récupérer promotions et cours du département connecté
$promotions = $conn->query("SELECT id, nom FROM promotions");
$cours = $conn->query("SELECT id, nom FROM cours");
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Ajouter un horaire</title>
<style>
    body { font-family: Arial; padding: 20px; }
    input, select { padding: 8px; margin: 5px 0; width: 100%; }
    button { padding: 10px; }
    .success { color: green; }
</style>
</head>
<body>
<h2>Ajouter un horaire de cours</h2>
<?php if ($message): ?>
    <p class="success"><?= $message ?></p>
<?php endif; ?>
<form method="post">
    <label>Promotion :</label>
    <select name="promotion_id" required>
        <?php while($row = $promotions->fetch_assoc()): ?>
            <option value="<?= $row['id'] ?>"><?= $row['nom'] ?></option>
        <?php endwhile; ?>
    </select>

    <label>Cours :</label>
    <select name="cours_id" required>
        <?php while($row = $cours->fetch_assoc()): ?>
            <option value="<?= $row['id'] ?>"><?= $row['nom'] ?></option>
        <?php endwhile; ?>
    </select>

    <label>Jour :</label>
    <select name="jour_semaine" required>
        <option value="Lundi">Lundi</option>
        <option value="Mardi">Mardi</option>
        <option value="Mercredi">Mercredi</option>
        <option value="Jeudi">Jeudi</option>
        <option value="Vendredi">Vendredi</option>
        <option value="Samedi">Samedi</option>
    </select>

    <label>Heure de début :</label>
    <input type="time" name="heure_debut" required>

    <label>Heure de fin :</label>
    <input type="time" name="heure_fin" required>

    <label>Salle :</label>
    <input type="text" name="salle" required>

    <button type="submit">Ajouter</button>
</form>

</body>
</html>
