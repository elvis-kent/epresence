<?php
// Fonction pour enregistrer une action dans le fichier log
function logAction($user_id, $action_description) {
    // Définir le chemin du fichier log
    $logfile = '../logs/actions.log';  // Si 'logs' est à la racine du projet

    // Obtenir l'heure et la date actuelles
    $timestamp = date('Y-m-d H:i:s');

    // Créer un message de log avec l'heure, l'ID utilisateur et la description de l'action
    $log_message = "$timestamp - User ID: $user_id - Action: $action_description\n";

    // Ajouter le message au fichier log
    file_put_contents($logfile, $log_message, FILE_APPEND);
}
?>
