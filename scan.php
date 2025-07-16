<?php
session_start();
require_once 'config.php';

// Vérification connexion prof
if (!isset($_SESSION['prof_id'], $_SESSION['cours_id'])) {
    header('Location: prof_login.php');
    exit;
}

$prof_id = $_SESSION['prof_id'];
$cours_id = $_SESSION['cours_id'];

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['qr_code'])) {
    $qr_code = trim($_POST['qr_code']);
    $response = ['success' => false, 'message' => ''];

    if ($qr_code === '') {
        $response['message'] = "Code QR vide.";
    } else {
        // Recherche étudiant via QR code
        $stmt = $conn->prepare("SELECT id, nom FROM etudiants WHERE qr_code = ?");
        $stmt->bind_param("s", $qr_code);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows === 1) {
            $etudiant = $result->fetch_assoc();

            // Vérifier si présence déjà enregistrée aujourd'hui
            $date_today = date('Y-m-d');
            $stmt_check = $conn->prepare("SELECT id FROM presences WHERE etudiant_id = ? AND cours_id = ? AND date_presence = ?");
            $stmt_check->bind_param("iis", $etudiant['id'], $cours_id, $date_today);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();

            if ($result_check && $result_check->num_rows > 0) {
                $response['message'] = "Présence déjà enregistrée pour " . htmlspecialchars($etudiant['nom']) . " aujourd'hui.";
            } else {
                // Insérer la présence
                $stmt_insert = $conn->prepare("INSERT INTO presences (etudiant_id, cours_id, date_presence, heure_presence) VALUES (?, ?, ?, ?)");
                $heure_now = date('H:i:s');
                $stmt_insert->bind_param("iiss", $etudiant['id'], $cours_id, $date_today, $heure_now);

                if ($stmt_insert->execute()) {
                    $response['success'] = true;
                    $response['message'] = "Présence enregistrée pour " . htmlspecialchars($etudiant['nom']) . ".";
                } else {
                    $response['message'] = "Erreur lors de l'enregistrement.";
                }
                $stmt_insert->close();
            }
            $stmt_check->close();
        } else {
            $response['message'] = "Étudiant non trouvé ou code QR invalide.";
        }
        $stmt->close();
    }

    // Répondre JSON (car AJAX)
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Scan de présence - ePresence</title>

<style>
    body {
        font-family: Arial, sans-serif;
        background: #eef2f7;
        padding: 20px;
        text-align: center;
    }
    #reader {
        width: 320px;
        margin: auto;
    }
    #result {
        margin-top: 20px;
        font-weight: bold;
        min-height: 40px;
    }
    button#resetBtn {
        margin-top: 20px;
        padding: 10px 20px;
        background-color: #007bff;
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }
    button#resetBtn:hover {
        background-color: #0056b3;
    }
</style>

<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

</head>
<body>

<h1>Scan de présence - ePresence</h1>

<div id="reader"></div>
<div id="result"></div>
<button id="resetBtn" style="display:none;">Scanner un autre étudiant</button>

<script>
    const resultContainer = document.getElementById('result');
    const resetBtn = document.getElementById('resetBtn');
    let html5QrcodeScanner;

    function startScanner() {
        html5QrcodeScanner = new Html5Qrcode("reader");
        html5QrcodeScanner.start(
            { facingMode: "environment" },
            {
                fps: 10,
                qrbox: 250
            },
            qrCodeMessage => {
                html5QrcodeScanner.stop();
                sendQrCode(qrCodeMessage);
            },
            errorMessage => {
                // console.log(`QR code scan error: ${errorMessage}`);
            }
        ).catch(err => {
            resultContainer.textContent = "Erreur accès caméra: " + err;
        });
    }

    function sendQrCode(qrCode) {
        resultContainer.textContent = "Traitement en cours...";
        fetch(window.location.href, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'qr_code=' + encodeURIComponent(qrCode)
        })
        .then(response => response.json())
        .then(data => {
            resultContainer.textContent = data.message;
            resetBtn.style.display = 'inline-block';
        })
        .catch(err => {
            resultContainer.textContent = "Erreur réseau.";
            resetBtn.style.display = 'inline-block';
        });
    }

    resetBtn.addEventListener('click', () => {
        resultContainer.textContent = '';
        resetBtn.style.display = 'none';
        startScanner();
    });

    // Démarrer le scan au chargement
    startScanner();
</script>

</body>
</html>
