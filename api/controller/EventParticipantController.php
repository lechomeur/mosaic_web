<?php

namespace Controller;

use daos\eventParticipantDaos;
use Exception;



header('Content-Type: application/json; charset=utf-8');

class EventParticipantController
{
    public function __construct() {}
    public function getParticipantsByEvent()
    {
        $evenementId = filter_input(INPUT_GET, 'evenementId', FILTER_SANITIZE_SPECIAL_CHARS);
        // Récupérer les participants via le DAO
        $participants = eventParticipantDaos::getParticipantsByEvent($evenementId);

        if ($participants === false) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Erreur lors de la récupération des participants.'
            ]);
            return;
        }

        if (empty($participants)) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'Aucun participant trouvé.'
            ]);
            return;
        }

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'participants' => $participants
        ]);
    }
    public function checkAdherantStatus() {
        $nom = filter_input(INPUT_GET, 'nom', FILTER_SANITIZE_SPECIAL_CHARS);
        $prenom = filter_input(INPUT_GET, 'prenom', FILTER_SANITIZE_SPECIAL_CHARS);
        if (!$nom || !$prenom) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Nom et prénom sont requis.'
            ]);
            return;
        }
        $isAdherant = eventParticipantDaos::isAdherant($nom, $prenom);

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'isAdherant' => $isAdherant
        ]);
    }
    public function ajouterParticipantAEvent() {
        // Vérification si les données sont envoyées via POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Lire le contenu de la requête JSON
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Vérifier si les données sont présentes
            $participantId = $data['participantId'] ?? null;
            $eventId = $data['eventId'] ?? null;
    
            // Vérifier si les IDs sont valides
            if ($participantId && $eventId) {
                try {
                    // Appel de la méthode pour ajouter un participant à un événement
                    eventParticipantDaos::addParticipantToEvent($participantId, $eventId);
                    echo json_encode(["success" => true, "message" => "Le participant a été ajouté à l'événement avec succès."]);
                } catch (Exception $e) {
                    echo json_encode(["success" => false, "message" => "Erreur lors de l'ajout du participant à l'événement : " . $e->getMessage()]);
                }
            } else {
                echo json_encode(["success" => false, "message" => "Les IDs du participant et de l'événement sont requis."]);
            }
        }
    }
    public function removeParticipant() {
        if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
            // Récupérer les données envoyées en JSON (s'il y en a)
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);

            // Vérification du décodage du JSON
            if ($data === null) {
                http_response_code(400);
                echo json_encode(['message' => 'Erreur de décodage JSON', 'success' => false]);
                return;
            }

            // Vérification si les deux identifiants (participant et événement) sont fournis
            if (!isset($data['participantId']) || !isset($data['eventId'])) {
                http_response_code(400);
                echo json_encode(['message' => 'Les ID du participant et de l\'événement sont requis', 'success' => false]);
                return;
            }

            $participantId = $data['participantId'];
            $eventId = $data['eventId'];

            try {
                // Appel de la méthode DAO pour supprimer le participant de l'événement
                eventParticipantDaos::removeParticipantFromEvent($participantId, $eventId);

                http_response_code(200);
                echo json_encode(['message' => 'Participant supprimé de l\'événement avec succès', 'success' => true]);
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['message' => 'Erreur lors de la suppression du participant', 'success' => false]);
            }
        } else {
            // Si la méthode HTTP n'est pas DELETE, on retourne une erreur
            http_response_code(405);
            echo json_encode(['message' => 'Méthode non autorisée', 'success' => false]);
        }
    }
    public static function getNbParticipantsPayesByEvent() {
        $eventId = filter_input(INPUT_GET,'eventId',FILTER_SANITIZE_NUMBER_INT);
        if (!isset($eventId) || !is_numeric($eventId)) {
            echo json_encode(["error" => "ID de l'événement invalide"]);
            return;
        }

        $countParticipantsPayes = eventParticipantDaos::getNbParticipantsPayesByEvent($eventId);
        
        echo json_encode(["event_id" => $eventId, "participants_payes" => $countParticipantsPayes]);
    }
    public static function getNombreParticipantsNonPayesByEvent() {
        $eventId = filter_input(INPUT_GET,'eventId',FILTER_SANITIZE_NUMBER_INT);
        if (!isset($eventId) || !is_numeric($eventId)) {
            echo json_encode(["error" => "ID de l'événement invalide"]);
            return;
        }
        $countParticipantsNonPayes = eventParticipantDaos::getNombreParticipantsNonPayesByEvent($eventId);
        echo json_encode(["event_id" => $eventId, "participants_non_payes" => $countParticipantsNonPayes]);
    }
    public static function getNombreFemmesByEvent() {
        $eventId = filter_input(INPUT_GET,'eventId',FILTER_SANITIZE_NUMBER_INT);
        if (!isset($eventId) || !is_numeric($eventId)) {
            echo json_encode(["error" => "ID de l'événement invalide"]);
            return;
        }
        $countFemmes = eventParticipantDaos::getNbFemmesByEvent($eventId);
        echo json_encode(["event_id" => $eventId, "nombre_femmes" => $countFemmes]);
    }
    public static function getNbHommesByEvent() {
        $eventId = filter_input(INPUT_GET,'eventId',FILTER_SANITIZE_NUMBER_INT);
        if (!isset($eventId) || !is_numeric($eventId)) {
            echo json_encode(["error" => "ID de l'événement invalide"]);
            return;
        }

        $countHommes = eventParticipantDaos::getNbHommesByEvent($eventId);
        
        echo json_encode(["event_id" => $eventId, "nombre_hommes" => $countHommes]);
    }
    public function searchParticipants() {
        // Vérifier si les paramètres sont présents
        $eventId = isset($_GET['eventId']) ? intval($_GET['eventId']) : null;
        $searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';

        if ($eventId !== null) {
            // Appel de la fonction du modèle
            $participants = eventParticipantDaos::searchParticipantsByNomPrenom($eventId, $searchTerm);
            // Retourner les résultats en JSON
            header('Content-Type: application/json');
            echo json_encode($participants);
        } else {
            // Retourner une erreur si l'ID de l'événement est manquant
            header('Content-Type: application/json');
            echo json_encode(['error' => 'ID de l’événement requis']);
        }
    }
}

