<?php

session_start();
ini_set('display_errors', 'on');
ini_set('display_startup_errors', 'on');
error_reporting(E_ALL);
ini_set('html_errors', 0);

require_once __DIR__ . '/vendor/autoload.php';
require_once "daos/PdoBD.php";
// Dans index.php
define('ROOT_DIR', __DIR__); // __DIR__ donne le répertoire courant du fichier
spl_autoload_register(function ($className) {
    $classNameR = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $className);
    $file = __DIR__ . DIRECTORY_SEPARATOR . $classNameR . '.php';

    if (!file_exists($file)) {
        die("Fichier introuvable : " . $file);
    }
    include_once $file;
});
if (!isset($_SESSION['utilisateur'])) {
    // Si la requête est en POST, on passe à utilisateurController en action "connexion"
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $controller = new controller\utilisateurController("connexion");
        $controller->connexion();
    } else {
        $controller = new controller\utilisateurController("connexion");
    }
} else {
    if (!isset($_GET['controller'])) {
        $controller = "home";
    } else {
        $controller = $_GET['controller'];
    }
    switch ($controller) {
        case "home":
            $controller = new controller\homeController();
            break;
        case "adhesion":
            new controller\adherentController();
            break;
        case "association":
            new controller\associationController();
            break;
        case "evenement":
            new controller\evenementController();
            break;
        case "participantEvent":
            new controller\EventParticipantController();
            break;
        case "participant":
            new controller\participantController();
            break;
        default:
            echo "Controller $controller non géré actuellement";
            break;
    }

}
