<?php

namespace Controller;

use daos\adherentDaos;
use DateTime;
use entities\evenement;
use daos\evenementDaos;
use daos\participantDaos;
use Exception;


class evenementController
{

    private $adherentDao;
    private $associationController;
    public function __construct()
    {

        $request = filter_input(INPUT_SERVER, 'REQUEST_METHOD');
        $this->adherentDao = new adherentDaos();
        $this->associationController = new associationController();
        $this->handleRequest($request);
    }


    private function handleRequest($request)
    {
        // Récupérer l'action à partir de la requête
        $action = filter_input(INPUT_GET, 'action');
        switch ($action) {
            case 'getAllEvenementByAsso':
                $this->listEvenement();
                break;
            default:
                $this->choixAssociation();
                break;
        }
    }

    function listEvenement()
    {
        $associationId = filter_input(INPUT_GET, "association_id", FILTER_SANITIZE_NUMBER_INT);
        $evenements = EvenementDaos::getAllEvenementsByAsso($associationId);
        include __DIR__ . '/../view/EventList.php';

      
    }
    function choixAssociation()
    {
        $associationId = 0;
        $associations = $this->associationController->getAllAssociations($associationId);
        include __DIR__ . '/../view/choixAssoEvent.php';
    }
}
