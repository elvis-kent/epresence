<?php
session_start();
require 'config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'super_admin') {
    header('Location: login.php');
    exit;
}

$message = '';

// Récupération des universités
$universites = [];
$result = $conn->query("SELECT id, nom FROM universites");
while ($row = $result->fetch_assoc()) {
    $universites[] = $row;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nom = $_POST['nom'];
    $email = $_POST['email'];
    $password = password_hash($_POST['motdepasse'], PASSWORD_DEFAULT);
    $universite_id = $_POST['universite_id'];
    $role = 'admin_universite';

    // Vérifie si l'email existe déjà
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $message = "❌ Cet email est déjà utilisé.";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (nom, email, motdepasse, role, universite_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssi", $nom, $email, $password, $role, $universite_id);
        if ($stmt->execute()) {
            $message = "✅ Admin université ajouté avec succès.";
        } else {
            $message = "❌ Erreur : " . $stmt->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un admin d'université</title>
    <style>
        body { font-family: Arial; background: #f7f7f7; padding: 30px; }
        form { background: white; padding: 20px; border-radius: 10px; max-width: 500px; }
        label { display: block; margin-top: 15px; }
        input, select, button {
            width: 100%; padding: 10px; margin-top: 5px;
        }
        h2 { margin-bottom: 20px; color: #1a1a2e; }
        .message { margin-top: 20px; color: green; }
    </style>
</head>
<body>

<h2>👤 Ajouter un Admin Université</h2>

<?php if ($message): ?>
    <p class="message"><?= $message ?></p>
<?php endif; ?>

<form method="POST">
    <label>Nom complet</label>
    <input type="text" name="nom" required>

    <label>Email</label>
    <input type="email" name="email" required>

    <label>Mot de passe</label>
    <input type="password" name="motdepasse" required>

    <label>Université</label>
    <select name="universite_id" required>
        <option value="">-- Sélectionner --</option>
        <?php foreach ($universites as $u): ?>
            <option value="<?= $u['id'] ?>"><?= $u['nom'] ?></option>
        <?php endforeach; ?>
    </select>

    <button type="submit">Ajouter</button>
</form>

</body>
</html>
