<?php

namespace Controller;

use DateTime;
use entities\evenement;
use daos\evenementDaos;
use daos\participantDaos;
use Exception;

header('Content-Type: application/json; charset=utf-8');

class evenementController
{
    public function __construct() {}
    function getAllEvenements()
    {
        $evenements = EvenementDaos::getAllEvenements();

        if ($evenements === false) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Erreur lors de la récupération des événements.'
            ]);
            return;
        }
        if (empty($evenements)) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'Aucun événement trouvé.'
            ]);
            return;
        }
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'evenements' => $evenements
        ]);
    }
    public function getDateEvenementById()
    {
        if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Paramètre ID invalide ou manquant.'
            ]);
            return;
        }

        $id = intval($_GET['id']);
        $date = EvenementDaos::getDateEvenementById($id);

        if ($date) {
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'date_evenement' => $date
            ]);
        } else {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'Aucun événement trouvé pour cet ID.'
            ]);
        }
    }
    public function deleteEvent()
    {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        $id = isset($data['id']) ? (int)$data['id'] : 0;

        if ($id <= 0) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Paramètre ID invalide ou manquant.'
            ]);
            return;
        }
        $deleted = EvenementDaos::deleteEvent($id);

        if ($deleted) {
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => "Événement supprimé avec succès."
            ]);
        } else {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => "Erreur : Aucun événement supprimé, ID introuvable."
            ]);
        }
    }
    public function addEvent()
    {
        // Vérifie si la requête est en POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode([
                'success' => false,
                'message' => 'Méthode non autorisée. Utilisez POST.'
            ]);
            return;
        }

        // Récupère les données JSON envoyées en POST
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['titre'], $data['date_evenement'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Paramètres manquants : titre ou date_evenement '
            ]);
            return;
        }

        try {
            // Création d'un objet Evenement
            $evenement = new Evenement();
            $evenement->setTitre($data['titre']);
            $evenement->setDateEvenement(new DateTime($data['date_evenement']));
            $evenement->setLieu($data['lieu']);

            // Appel à la méthode pour ajouter l'événement
            $result = EvenementDaos::addEvent($evenement);

            if ($result) {
                http_response_code(201);
                echo json_encode([
                    'success' => true,
                    'message' => 'Événement ajouté avec succès.'
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'message' => 'Erreur lors de l\'ajout de l\'événement.'
                ]);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Erreur : ' . $e->getMessage()
            ]);
        }
    }
    public function updateEvent()
    {
        // Vérifie si la requête est en POST ou PUT
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'PUT') {
            http_response_code(405);
            echo json_encode([
                'success' => false,
                'message' => 'Méthode non autorisée. Utilisez POST ou PUT.'
            ]);
            return;
        }

        // Récupère les données JSON envoyées en POST ou PUT
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['id'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Paramètres manquants : id'
            ]);
            return;
        }

        try {
            // Création d'un objet Evenement
            $evenement = new Evenement();
            $evenement->setId($data['id']);
            $evenement->setTitre($data['titre']);
            $evenement->setDateEvenement(new DateTime($data['date_evenement']));
            $evenement->setLieu($data['lieu']);

            // Appel à la méthode pour mettre à jour l'événement
            $result = EvenementDaos::updateEvenement($evenement);

            if ($result) {
                http_response_code(200);
                echo json_encode([
                    'success' => true,
                    'message' => 'Événement mis à jour avec succès.'
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'message' => 'Erreur lors de la mise à jour de l\'événement.'
                ]);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Erreur : ' . $e->getMessage()
            ]);
        }
    }
    public static function getNParticipantByEvent()
    {
        $eventId = filter_input(INPUT_GET, 'eventId', FILTER_SANITIZE_NUMBER_INT);
        if (!isset($eventId) || !is_numeric($eventId)) {
            echo json_encode(["error" => "ID de l'événement invalide"]);
            return;
        }
        $count = evenementDaos::getNParticipantByEvent($eventId);
        echo json_encode(["event_id" => $eventId, "participant_count" => $count]);
    }
    public static function getMontantTotalByEvent()
    {
        $eventId = filter_input(INPUT_GET, 'eventId', FILTER_SANITIZE_NUMBER_INT);
        if (!isset($eventId) || !is_numeric($eventId)) {
            echo json_encode(["error" => "ID de l'événement invalide"]);
            return;
        }

        $totalMontant = evenementDaos::getMontantTotalByEvent($eventId);

        echo json_encode(["event_id" => $eventId, "total_montant" => $totalMontant]);
    }
}
