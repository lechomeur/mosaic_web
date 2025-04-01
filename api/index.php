<?php
session_start();
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
ini_set('html_errors', 0);

header('Content-Type: application/json; charset=utf-8');

// Inclusion des dépendances et classes
require_once __DIR__ . '/vendor/autoload.php';
require_once "daos/PdoBD.php";

// Charger automatiquement les classes
spl_autoload_register(function ($className) {
    $classNameR = str_replace("\\", DIRECTORY_SEPARATOR, $className);
    $file = __DIR__ . '/' . $classNameR . '.php';

    if (!file_exists($file)) {
        die(json_encode(["success" => false, "message" => "Fichier introuvable : " . $file]));
    }

    include_once $file;
});
// Vérification de l'action
$action = filter_input(INPUT_GET, "action");

if (isset($_SESSION['utilisateur']) && $_SESSION['utilisateur']) {
    // L'utilisateur est connecté
    switch ($action) {
        case 'deconnexion':
            $controller = new Controller\utilisateurController();
            $controller->deconnexion();
            break;
        case 'getAnneeAdhesion':
            $controller = new Controller\adherentController();
            $controller->getDistinctAdhesionYears();
            break;
        case 'getAdhesionParAsso_Annee_Cot':
            $controller = new Controller\adherentController();
            $controller->getAdherentsByAssociationByYearByCot();
            break;
        case 'getNbAdhesionParAsso_Annee_Cot':
            $controller = new Controller\adherentController();
            $controller->getNbAdhesionByAssociationByYearByCotis();
        case 'getNbCotPaye':
            $controller = new Controller\adherentController();
            $controller->getNbCotPaye();
        case 'getNbCotImpaye':
            $controller = new Controller\adherentController();
            $controller->getNbCotImpaye();
        case 'getCotTotal':
            $controller = new Controller\adherentController();
            $controller->getCotTotal();
        case 'addAdherent':
            $controller = new Controller\adherentController();
            $controller->AddAdherant();
        case 'delAdherent':
            $controller = new Controller\adherentController();
            $controller->delAdherent();
        case 'updateAdherent':
            $controller = new Controller\adherentController();
            $controller->updateAdherent();
        case 'searchAdherants':
            $controller = new Controller\adherentController();
            $controller->searchAdherants();
        case 'getAllEvenements':
            $controller = new Controller\evenementController();
            $controller->getAllEvenements();
        case 'getDateEvenementById':
            $controller = new Controller\evenementController();
            $controller->getDateEvenementById();
        case 'deletEvent':
            $controller = new Controller\evenementController();
            $controller->deleteEvent();
        case 'addEvent':
            $controller = new Controller\evenementController();
            $controller->addEvent();
        case 'updateEvenement':
            $controller = new Controller\evenementController();
            $controller->updateEvent();
        case 'getAllAsso':
            $controller = new Controller\associationController();
            $controller->getAllAssociations();
        case 'getParticipantsByEvent':
            $controller = new Controller\EventParticipantController();
            $controller->getParticipantsByEvent();
        case 'checkAdherant':
            $controller = new Controller\EventParticipantController();
            $controller->checkAdherantStatus();
        case 'addParticipant':
            $controller = new Controller\participantController();
            $controller->addParticipant();
        case 'addParticipantToEvent':
            $controller = new Controller\EventParticipantController();
            $controller->ajouterParticipantAEvent();
        case 'updateParticipant':
            $controller = new Controller\participantController();
            $controller->updateParticipant();
        case 'removeParticipantE':
            $controller = new Controller\EventParticipantController();
            $controller->removeParticipant();
        case 'getParticipantsByPaymentStatus':
            $controller = new Controller\participantController();
            $controller->getParticipantsByPaymentStatus();
        case 'getNParticipantByEvent':
            $controller = new Controller\evenementController();
            $controller->getNParticipantByEvent();
        case 'getMontantTotalByEvent':
            $controller = new Controller\evenementController();
            $controller->getMontantTotalByEvent();
        case 'getNbParticipantsPayesByEvent':
            $controller = new Controller\EventParticipantController();
            $controller->getNbParticipantsPayesByEvent();
        case 'getNParticipantsNotPaByEvent':
            $controller = new Controller\EventParticipantController();
            $controller->getNombreParticipantsNonPayesByEvent();
        case 'getNbFemmesByEvent':
            $controller = new Controller\EventParticipantController();
            $controller->getNombreFemmesByEvent();
        case 'getNbHommeByEvent':
            $controller = new Controller\EventParticipantController();
            $controller->getNbHommesByEvent();
        case 'searchParticipants':
            $controller = new Controller\EventParticipantController();
            $controller->searchParticipants();
        default:
            echo json_encode(["success" => false, "message" => "Action inconnue"]);
            break;
    }
} else {

    if ($action === 'connexion') {
        $controller = new Controller\utilisateurController();
        $controller->connexion();  // Appel de la méthode de connexion
    } else {
        echo json_encode(["success" => false, "message" => "Action non reconnue"]);
    }
}
