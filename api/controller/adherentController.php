<?php

namespace Controller;

use daos\utilisateurDaos;
use daos\adherentDaos;
use Entities\adherent;
use Exception;

header('Content-Type: application/json; charset=utf-8');

class adherentController
{
    function getDistinctAdhesionYears()
    {
        $years = adherentDaos::getDistinctAdhesionYears();

        if (isset($years['error'])) {
            echo json_encode([
                "success" => false,
                "message" => "Erreur lors de la récupération des années d'adhésion",
                "error" => $years['error']
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        } else {
            // Transformer chaque année en un objet { "année": XXXX }
            $formattedYears = array_map(fn($year) => ["année" => $year], $years);
            echo json_encode([
                "years" => $formattedYears
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        }
    }
    public function getAdherentsByAssociationByYearByCot()
    {
        $associationId = isset($_GET['associationId']) ? intval($_GET['associationId']) : 0;
        $year = isset($_GET['year']) ? intval($_GET['year']) : null;
        $cotis = isset($_GET['cotis']) ? $_GET['cotis'] : null;

        $result = AdherentDaos::getAdherentsByAssociationByYearByCotis($associationId, $year, $cotis);

        echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
    public function getNbAdhesionByAssociationByYearByCotis()
    {
        // Récupération des paramètres de la requête (GET ou POST)
        $associationId = isset($_GET['associationId']) ? (int) $_GET['associationId'] : 0;
        $year = isset($_GET['year']) ? (int) $_GET['year'] : null;
        $cotisFilter = isset($_GET['cotisFilter']) ? $_GET['cotisFilter'] : null;
        try {
            // Appel de la fonction du DAO
            $count = adherentDaos::getNbAdhesionByAssociationByYearByCotis($associationId, $year, $cotisFilter);
            echo json_encode([
                "success" => true,
                "count" => $count
            ]);
        } catch (Exception $e) {
            // Gestion des erreurs
            echo json_encode([
                "success" => false,
                "message" => "Erreur lors de la récupération du nombre d'adhérents.",
                "error" => $e->getMessage()
            ]);
        }
    }
    public function getNbCotPaye()
    {
        $associationId = isset($_GET['associationId']) ? (int) $_GET['associationId'] : 0;
        $year = isset($_GET['year']) ? (int) $_GET['year'] : null;
        try {
            // Appel de la fonction du DAO
            $NbCotPaye = adherentDaos::getNbCotpaye($associationId, $year);
            echo json_encode([
                "success" => true,
                "NbCotisationPaye" => $NbCotPaye
            ]);
        } catch (Exception $e) {
            // Gestion des erreurs
            echo json_encode([
                "success" => false,
                "message" => "Erreur lors de la récupération du nombre de cotisation paye.",
                "error" => $e->getMessage()
            ]);
        }
    }
    public function getNbCotImpaye()
    {
        $associationId = isset($_GET['associationId']) ? (int) $_GET['associationId'] : 0;
        $year = isset($_GET['year']) ? (int) $_GET['year'] : null;
        try {
            $NbCotPaye = adherentDaos::getNbCotImpaye($associationId, $year);
            echo json_encode([
                "success" => true,
                "NbCotisationImpaye" => $NbCotPaye
            ]);
        } catch (Exception $e) {
            // Gestion des erreurs
            echo json_encode([
                "success" => false,
                "message" => "Erreur lors de la récupération du nombre de cotisation paye.",
                "error" => $e->getMessage()
            ]);
        }
    }
    public function getCotTotal()
    {
        $cotis = isset($_GET['cotis']) ? $_GET['cotis'] : null;
        $associationId = isset($_GET['associationId']) ? (int) $_GET['associationId'] : 0;
        $year = isset($_GET['year']) ? (int) $_GET['year'] : null;
        try {
            $CotTotal = adherentDaos::getCotisTot($associationId, $year, $cotis);
            echo json_encode([
                "success" => true,
                "cotisation totale" => $CotTotal
            ]);
        } catch (Exception $e) {
            // Gestion des erreurs
            echo json_encode([
                "success" => false,
                "message" => "Erreur lors de la récupération du nombre de cotisation paye.",
                "error" => $e->getMessage()
            ]);
        }
    }
    public function AddAdherant()
    {
        // Vérifier que la requête est bien en POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Lire les données envoyées en JSON
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);
            try {
                if (adherentDaos::addAdherant($data)) {
                    http_response_code(201); // Code 201 = Créé avec succès
                    echo json_encode([
                        'message' => 'Adhérent ajouté avec succès',
                        'success' => true
                    ]);
                } else {
                    http_response_code(500); // Code 500 = Erreur serveur
                    echo json_encode([
                        'message' => 'Erreur lors de l\'ajout de l\'adhérent',
                        'success' => false
                    ]);
                }
            } catch (Exception $e) {
                http_response_code(500); // Code 500 = Erreur serveur
                echo json_encode([
                    'message' => 'Erreur lors de l\'ajout de l\'adhérent',
                    'error' => $e->getMessage()
                ]);
            }
        } else {
            http_response_code(405); // Code 405 = Méthode non autorisée
            echo json_encode(['message' => 'Méthode non autorisée']);
        }
    }
    public function delAdherent()
    {
        // Vérifier que la requête est bien en POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);
            // Vérifier si l'ID est présent et valide
            $id = isset($data['id']) ? (int)$data['id'] : 0;
            if ($id <= 0) {
                http_response_code(400); // 400 = Requête invalide
                echo json_encode(['message' => 'ID invalide', 'success' => false]);
                return;
            }
            try {
                if (adherentDaos::deleteAdherant($id)) {
                    http_response_code(201); // Code 201 = Créé avec succès
                    echo json_encode([
                        'message' => 'Adhérent supprimé avec succès',
                        'success' => true
                    ]);
                } else {
                    http_response_code(500); // Code 500 = Erreur serveur
                    echo json_encode([
                        'message' => 'Erreur lors de la suppression de l\'adhérent',
                        'success' => false
                    ]);
                }
            } catch (Exception $e) {
                http_response_code(500); // Code 500 = Erreur serveur
                echo json_encode([
                    'message' => 'Erreur lors de la suppression de l\'adhérent',
                    'error' => $e->getMessage()
                ]);
            }
        } else {
            http_response_code(405); // Code 405 = Méthode non autorisée
            echo json_encode(['message' => 'Méthode non autorisée']);
        }
    }
    public function updateAdherent()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);

            if (!isset($data['id'])) {
                http_response_code(400);
                echo json_encode(['message' => 'ID requis', 'success' => false]);
                return;
            }

            $id = (int) $data['id'];
            $originalAdherant = adherentDaos::getAdherentById($id); // Récupère les données actuelles

            if (!$originalAdherant) {
                http_response_code(404);
                echo json_encode(['message' => 'Adhérent introuvable', 'success' => false]);
                return;
            }

            $success = adherentDaos::updateAdherant($data, $originalAdherant);

            if ($success) {
                http_response_code(200);
                echo json_encode(['message' => 'Adhérent mis à jour avec succès', 'success' => true]);
            } else {
                http_response_code(500);
                echo json_encode(['message' => 'Erreur lors de la mise à jour', 'success' => false]);
            }
        } else {
            http_response_code(405);
            echo json_encode(['message' => 'Méthode non autorisée']);
        }
    }

    // Fonction pour rechercher des adhérents par nom/prénom et ID de l'association
    function searchAdherants() {
        $associationId = isset($_GET['associationId']) ? intval($_GET['associationId']) : 0;
        $searchTerm = isset($_GET['searchTerm']) ? $_GET['searchTerm'] : '';
    
        error_log("📢 Recherche de adherents avec associationId=$associationId et searchTerm=$searchTerm");
    
        $result = adherentDaos::searchAdherantsByNomPrenom($associationId, $searchTerm);
    
        if (empty($result)) {
            echo json_encode(["success" => false, "message" => "Aucun adhérent trouvé."]);
        } else {
            echo json_encode(["success" => true, "data" => $result]);
        }
    }
    
    
}
