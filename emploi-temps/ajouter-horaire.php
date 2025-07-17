<?php
session_start();
require 'config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin_departement') {
    header("Location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $promotion_id = $_POST['promotion_id'];
    $jour = $_POST['jour'];
    $debut = $_POST['heure_debut'];
    $fin = $_POST['heure_fin'];
    $cours = $_POST['nom_cours'];
    $salle = $_POST['salle'];
    $prof = $_POST['professeur_id'];

    $stmt = $conn->prepare("INSERT INTO horaires (promotion_id, jour_semaine, heure_debut, heure_fin, nom_cours, salle, professeur_id)
                            VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssssi", $promotion_id, $jour, $debut, $fin, $cours, $salle, $prof);
    $stmt->execute();
    $message = "✅ Horaire ajouté avec succès !";
}

// Récupérer les promotions
$promos = $conn->query("SELECT id, nom FROM promotions");
$profs = $conn->query("SELECT id, nom FROM professeurs");
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un horaire</title>
    <style>
        body { font-family: Arial; padding: 30px; background: #f5f5f5; }
        form { background: white; padding: 20px; border-radius: 6px; width: 500px; margin: auto; box-shadow: 0 0 5px #ccc; }
        input, select { width: 100%; padding: 10px; margin-bottom: 15px; }
        button { padding: 10px 20px; background: #007BFF; color: white; border: none; cursor: pointer; }
        h2 { text-align: center; }
        .msg { color: green; text-align: center; }
    </style>
</head>
<body>

<h2>📅 Ajouter un horaire</h2>
<?php if (isset($message)) echo "<p class='msg'>$message</p>"; ?>

<form method="post">
    <label>Promotion</label>
    <select name="promotion_id" required>
        <option value="">-- Choisir --</option>
        <?php while ($row = $promos->fetch_assoc()): ?>
            <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['nom']) ?></option>
        <?php endwhile; ?>
    </select>

    <label>Jour de la semaine</label>
    <select name="jour" required>
        <option value="">-- Choisir --</option>
        <option>Lundi</option>
        <option>Mardi</option>
        <option>Mercredi</option>
        <option>Jeudi</option>
        <option>Vendredi</option>
        <option>Samedi</option>
    </select>

    <label>Heure début</label>
    <input type="time" name="heure_debut" required>

    <label>Heure fin</label>
    <input type="time" name="heure_fin" required>

    <label>Nom du cours</label>
    <input type="text" name="nom_cours" required>

    <label>Salle</label>
    <input type="text" name="salle" required>

    <label>Professeur</label>
    <select name="professeur_id" required>
        <option value="">-- Choisir --</option>
        <?php while ($row = $profs->fetch_assoc()): ?>
            <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['nom']) ?></option>
        <?php endwhile; ?>
    </select>

    <button type="submit">💾 Ajouter</button>
</form>

</body>
</html>
