<?php
session_start();
require_once('../includes/config.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin_faculte') {
    header('Location: ../login.php');
    exit;
}

$universite_id = $_SESSION['universite_id'];
$faculte_id = $_SESSION['faculte_id'];
$message = "";

// Récupération des départements de cette faculté
$departements = [];
$stmt = $conn->prepare("SELECT id, nom FROM departements WHERE faculte_id = ?");
$stmt->bind_param("i", $faculte_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $departements[] = $row;
}
$stmt->close();

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom']);
    $email = trim($_POST['email']);
    $motdepasse = password_hash($_POST['motdepasse'], PASSWORD_DEFAULT);
    $departement_id = intval($_POST['departement_id']);
    $role = 'admin_departement';

    // Vérifie si l'email est déjà utilisé
    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $message = "❌ Cet email est déjà utilisé.";
    } else {
        // Ajout de l'utilisateur
        $insert = $conn->prepare("INSERT INTO users (nom, email, motdepasse, role, universite_id) VALUES (?, ?, ?, ?, ?)");
        $insert->bind_param("ssssi", $nom, $email, $motdepasse, $role, $universite_id);
        
        if ($insert->execute()) {
            $user_id = $insert->insert_id;

            // Associer à un département
            $liaison = $conn->prepare("INSERT INTO admin_departement (user_id, departement_id) VALUES (?, ?)");
            $liaison->bind_param("ii", $user_id, $departement_id);
            $liaison->execute();

            $message = "✅ Admin de département ajouté avec succès.";
        } else {
            $message = "❌ Erreur lors de l'enregistrement : " . $insert->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un admin de département</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f0f2f5;
            padding: 40px;
        }
        form {
            background: white;
            padding: 25px;
            max-width: 500px;
            margin: auto;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            color: #007BFF;
        }
        label {
            margin-top: 15px;
            display: block;
            font-weight: bold;
        }
        input, select {
            padding: 10px;
            width: 100%;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button {
            margin-top: 20px;
            background: #007BFF;
            color: white;
            border: none;
            padding: 10px;
            width: 100%;
            border-radius: 4px;
            font-size: 16px;
        }
        .message {
            margin-top: 20px;
            text-align: center;
            font-weight: bold;
            color: green;
        }
    </style>
</head>
<body>

    <form method="POST">
        <h2>➕ Ajouter un Admin de Département</h2>

        <?php if (!empty($message)): ?>
            <div class="message"><?= $message ?></div>
        <?php endif; ?>

        <label>Nom complet</label>
        <input type="text" name="nom" required>

        <label>Email</label>
        <input type="email" name="email" required>

        <label>Mot de passe</label>
        <input type="password" name="motdepasse" required>

        <label>Département</label>
        <select name="departement_id" required>
            <option value="">-- Sélectionner --</option>
            <?php foreach ($departements as $d): ?>
                <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['nom']) ?></option>
            <?php endforeach; ?>
        </select>

        <button type="submit">Créer l’admin</button>
    </form>

</body>
</html>
