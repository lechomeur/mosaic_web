<?php

namespace Controller;

use daos\participantDaos;
use entities\participant;
use Exception;

class participantController
{
    public function __construct() {}
    public function addParticipant()
    {
        // Vérifie si la requête est en POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Récupérer les données envoyées par la requête POST
            // Récupérer les données envoyées par la requête POST
            $data = json_decode(file_get_contents('php://input'), true);

            // Vérifier si la décodification s'est bien passée
            if (json_last_error() !== JSON_ERROR_NONE) {
                echo json_encode(['error' => 'Invalid JSON data']);
                exit;
            }

            // Extraire les données du tableau décodé
            $nom = $data['nom'] ?? '';
            $prenom = $data['prenom'] ?? '';
            $age = $data['age'] ?? 0;
            $telephone = $data['telephone'] ?? '';
            $montant = $data['montant'] ?? 0;
            $chequeEspece = $data['cheque_espece'] ?? '';
            $adherant = $data['adherant'] ?? '';
            $genre = $data['genre'] ?? '';
            $mail = $data['mail'] ?? '';

            // Vérification des champs obligatoires
            if (empty($nom) || empty($prenom)) {
                echo json_encode(['error' => 'Nom et prénom sont obligatoires.']);
                exit;
            }

            // Créer un nouvel objet Participant avec les données reçues
            $participant = new participant();
            $participant->setNom($nom);
            $participant->setPrenom($prenom);
            $participant->setAge($age);
            $participant->setTelephone($telephone);
            $participant->setMontant($montant);
            $participant->setChequeEspece($chequeEspece);
            $participant->setAdherant($adherant);
            $participant->setGenre($genre);
            $participant->setMail($mail);

            try {
                // Appeler la méthode addParticipant dans le DAO pour ajouter le participant
                $participantId = participantDaos::addParticipant($participant);

                // Réponse JSON avec l'ID du participant ajouté
                echo json_encode([
                    'success' => true,
                    'message' => 'Participant ajouté avec succès.',
                    'participantId' => $participantId
                ]);
            } catch (Exception $e) {
                // Gestion des erreurs en cas d'exception
                echo json_encode([
                    'success' => false,
                    'message' => 'Erreur système : ' . $e->getMessage()
                ]);
            }
        } else {
            // Si la requête n'est pas en POST, afficher une erreur
            echo json_encode([
                'success' => false,
                'message' => 'Méthode non autorisée. Utilisez POST.'
            ]);
        }
    }public function updateParticipant()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);
    
            // Vérification du décodage du JSON
            if ($data === null) {
                http_response_code(400);
                echo json_encode(['message' => 'Erreur de décodage JSON', 'success' => false]);
                return;
            }
    
            // Vérification si l'id est bien fourni
            if (!isset($data['id'])) {
                http_response_code(400);
                echo json_encode(['message' => 'L\'ID est requis', 'success' => false]);
                return;
            }
    
            // Récupérez l'id pour créer un objet participant avec les valeurs existantes
            $id = $data['id'];
    
            // Vous devez d'abord récupérer l'objet participant actuel depuis la base de données
            $participant = participantDaos::getById($id);
            if (!$participant) {
                http_response_code(404);
                echo json_encode(['message' => 'Participant non trouvé', 'success' => false]);
                return;
            }
    
            // Mettez à jour les champs existants uniquement si la valeur est présente dans les données reçues
            if (isset($data['nom'])) {
                $participant->setNom($data['nom']);
            }
            if (isset($data['prenom'])) {
                $participant->setPrenom($data['prenom']);
            }
            if (isset($data['age'])) {
                $participant->setAge($data['age']);
            }
            if (isset($data['telephone'])) {
                $participant->setTelephone($data['telephone']);
            }
            if (isset($data['montant'])) {
                $participant->setMontant($data['montant']);
            }
            if (isset($data['cheque_espece'])) {
                $participant->setChequeEspece($data['cheque_espece']);
            }
            if (isset($data['adherant'])) {
                $participant->setAdherant($data['adherant']);
            }
            if (isset($data['genre'])) {
                $participant->setGenre($data['genre']);
            }
            if (isset($data['mail'])) {
                $participant->setMail($data['mail']);
            }
    
            try {
                // Effectuer la mise à jour avec l'objet participant modifié
                $result = participantDaos::updateParticipant($participant);
                if ($result) {
                    http_response_code(200);
                    echo json_encode(['message' => 'Participant mis à jour avec succès', 'success' => true]);
                } else {
                    http_response_code(500);
                    echo json_encode(['message' => 'Erreur lors de la mise à jour du participant', 'success' => false]);
                }
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['message' => 'Une erreur est survenue', 'success' => false]);
            }
        } else {
            http_response_code(405);
            echo json_encode(['message' => 'Méthode non autorisée']);
        }
    }
    public function getParticipantsByPaymentStatus() {
        // Définir l'en-tête pour renvoyer du JSON
        header('Content-Type: application/json');
    
        // Récupérer les paramètres de l'URL via $_GET
        if (isset($_GET['evenementId']) && isset($_GET['hasPaid'])) {
            $evenementId = intval($_GET['evenementId']);  // Convertir en entier
            $hasPaid = filter_var($_GET['hasPaid'], FILTER_VALIDATE_BOOLEAN);  // Convertir en booléen
    
            try {
                // Appeler la méthode du DAO pour récupérer les participants
                $participants = participantDaos::getParticipantsByPaymentStatus($evenementId, $hasPaid);
    
                // Vérifier si des participants ont été trouvés
                if (!empty($participants)) {
                    // Convertir les participants en tableau associatif
                    $participantsArray = [];
                    foreach ($participants as $participant) {
                        $participantsArray[] = [
                            'id' => $participant->getId(),
                            'nom' => $participant->getNom(),
                            'prenom' => $participant->getPrenom(),
                            'age' => $participant->getAge(),
                            'telephone' => $participant->getTelephone(),
                            'montant' => $participant->getMontant(),
                            'chequeEspece' => $participant->getChequeEspece(),
                            'adherant' => $participant->getAdherant(),
                            'genre' => $participant->getGenre(),
                            'mail' => $participant->getMail()
                        ];
                    }
                    
                    // Renvoyer les résultats en JSON
                    echo json_encode([
                        'status' => 'success',
                        'data' => $participantsArray
                    ]);
                } else {
                    // Aucun participant trouvé
                    echo json_encode([
                        'status' => 'error',
                        'message' => 'Aucun participant trouvé pour cet événement avec le statut de paiement spécifié.'
                    ]);
                }
            } catch (Exception $e) {
                // Gérer les erreurs
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Une erreur est survenue : ' . $e->getMessage()
                ]);
            }
        } else {
            // Paramètres manquants
            echo json_encode([
                'status' => 'error',
                'message' => "Les paramètres 'evenementId' et 'hasPaid' sont requis dans l'URL."
            ]);
        }
    }
    
    
}
