<?php
require_once('../includes/config.php');
session_start();

if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin_universite') {
    header('Location: ../login.php');
    exit();
}

$message = "";

// Récupérer les facultés de l'université de l'admin connecté
$facultes = [];
$universite_id = $_SESSION['universite_id'] ?? 0;

$stmt = $conn->prepare("SELECT id, nom FROM facultes WHERE universite_id = ? ORDER BY nom ASC");
$stmt->bind_param("i", $universite_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $facultes[] = $row;
}
$stmt->close();

// Traitement du formulaire
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nom = trim($_POST['nom']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $faculte_id = intval($_POST['faculte_id']);

    if (empty($nom) || empty($email) || empty($password) || !$faculte_id) {
        $message = "❌ Tous les champs sont obligatoires.";
    } else {
        // Vérifie si l'email existe déjà
        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $message = "❌ Cet email est déjà utilisé.";
        } else {
            $check->close();
            $motdepasse_hash = password_hash($password, PASSWORD_DEFAULT);
            $role = "admin_faculte";

            $stmt = $conn->prepare("INSERT INTO users (nom, email, password, role, universite_id, faculte_id) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssii", $nom, $email, $motdepasse_hash, $role, $universite_id, $faculte_id);

            if ($stmt->execute()) {
                $user_id = $conn->insert_id;

                // Insérer dans admin_faculte
                $liaison = $conn->prepare("INSERT INTO admin_faculte (user_id, faculte_id) VALUES (?, ?)");
                $liaison->bind_param("ii", $user_id, $faculte_id);
                $liaison->execute();
                $liaison->close();

                $message = "✅ Admin de faculté ajouté avec succès.";
            } else {
                $message = "❌ Erreur lors de la création : " . $stmt->error;
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title>Ajouter un admin de faculté</title>
    <style>
        body { font-family: Arial; background: #f0f0f0; padding: 30px; }
        .box {
            max-width: 500px;
            margin: auto;
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 0 12px #ccc;
        }
        h2 { text-align: center; margin-bottom: 20px; }
        label { display: block; margin-top: 10px; }
        input, select {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        button {
            margin-top: 20px;
            background: #007BFF;
            color: white;
            border: none;
            padding: 12px;
            width: 100%;
            border-radius: 5px;
            font-weight: bold;
        }
        .message {
            margin-top: 15px;
            font-weight: bold;
            text-align: center;
        }
        .success { color: green; }
        .error { color: red; }
        a { display: block; text-align: center; margin-top: 20px; color: #007BFF; text-decoration: none; }
    </style>
</head>
<body>

<div class="box">
    <h2>Ajouter un Admin de Faculté</h2>

    <?php if ($message): ?>
        <div class="message <?= str_starts_with($message, '✅') ? 'success' : 'error' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <label for="nom">Nom complet :</label>
        <input type="text" name="nom" required>

        <label for="email">Email :</label>
        <input type="email" name="email" required>

        <label for="password">Mot de passe :</label>
        <input type="password" name="password" required>

        <label for="faculte_id">Faculté :</label>
        <select name="faculte_id" required>
            <option value="">-- Choisir une faculté --</option>
            <?php foreach ($facultes as $fac): ?>
                <option value="<?= $fac['id'] ?>"><?= htmlspecialchars($fac['nom']) ?></option>
            <?php endforeach; ?>
        </select>

        <button type="submit">Créer l'admin</button>
    </form>

    <a href="dashboard-admin-universite.php">⬅️ Retour au dashboard</a>
</div>

</body>
</html>
