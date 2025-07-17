<?php
require_once('includes/config.php');
session_start();

if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin_universite') {
    header('Location: login.php');
    exit();
}

$message = "";

// Récupérer tous les services disponibles
$services = [];
$result = $conn->query("SELECT id, nom_service FROM services");
while ($row = $result->fetch_assoc()) {
    $services[] = $row;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nom = trim($_POST["nom"]);
    $email = trim($_POST["email"]);
    $password = password_hash(trim($_POST["password"]), PASSWORD_DEFAULT);
    $id_service = intval($_POST["id_service"]);

    // Vérifier si l’email existe déjà
    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $message = "❌ Cet email est déjà utilisé.";
    } else {
        // Insérer dans la table users
        $stmt = $conn->prepare("INSERT INTO users (nom, email, password, role, id_service) VALUES (?, ?, ?, 'agent', ?)");
        $stmt->bind_param("sssi", $nom, $email, $password, $id_service);
        if ($stmt->execute()) {
            $message = "✅ Agent créé avec succès.";
        } else {
            $message = "❌ Erreur lors de la création.";
        }
        $stmt->close();
    }
    $check->close();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un agent</title>
    <style>
        body { font-family: Arial; background: #f4f6f8; padding: 30px; }
        .box { max-width: 600px; margin: auto; background: white; padding: 25px; border-radius: 10px; box-shadow: 0 0 10px #ccc; }
        h2 { text-align: center; margin-bottom: 20px; }
        label { display: block; margin-top: 10px; }
        input, select { width: 100%; padding: 8px; margin-top: 5px; border: 1px solid #ccc; border-radius: 4px; }
        button { margin-top: 20px; padding: 10px; width: 100%; background: #007BFF; color: white; border: none; border-radius: 5px; cursor: pointer; }
        .msg { text-align: center; margin-top: 15px; font-weight: bold; }
        a { display: block; margin-top: 20px; text-align: center; color: #007BFF; }
    </style>
</head>
<body>

<div class="box">
    <h2>Créer un nouvel agent</h2>
    <form method="POST">
        <label>Nom complet :</label>
        <input type="text" name="nom" required>

        <label>Email :</label>
        <input type="email" name="email" required>

        <label>Mot de passe :</label>
        <input type="text" name="password" required>

        <label>Service assigné :</label>
        <select name="id_service" required>
            <option value="">-- Sélectionner un service --</option>
            <?php foreach ($services as $s): ?>
                <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['nom_service']) ?></option>
            <?php endforeach; ?>
        </select>

        <button type="submit">Ajouter l'agent</button>
    </form>
    <?php if ($message): ?>
        <div class="msg"><?= $message ?></div>
    <?php endif; ?>
    <a href="dashboard_admin.php">⬅️ Retour au tableau de bord</a>
</div>

</body>
</html>
