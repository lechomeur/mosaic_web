<?php

namespace daos;

use entities\evenement;
use daos\PdoBD as DaosPdoBD;
use PdoBD;
use DateTime;
use Exception;
use PDOException;

require_once "PdoBD.php";

class evenementDaos
{
    public static function getAllEvenements()
    {
        $evenements = [];
        $db = DaosPdoBD::getInstance()->getMonPdo();
        try {
            $query = "SELECT * FROM evenement";
            $stmt = $db->prepare($query);
            $stmt->execute();
            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($result as $row) {
                $evenements[] = [
                    'id' => $row['Id'],
                    'titre' => $row['Titre'],
                    'date_evenement' => $row['Date_Evenement'],
                    'lieu' => $row['lieu']
                ];
            }
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération des événements : " . $e->getMessage());
            return false;
        }

        return $evenements;
    }
    public static function getDateEvenementById($id)
    {
        $db = DaosPdoBD::getInstance()->getMonPdo(); // Connexion à la base de données

        try {
            $query = "SELECT Date_Evenement FROM evenement WHERE Id = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$id]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($result) {
                return $result['Date_Evenement']; // Retourne la date si trouvée
            }
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération de la date de l'événement : " . $e->getMessage());
        }

        return null; // Retourne null si aucune date n'est trouvée ou en cas d'erreur
    }
    public static function deleteEvent($id)
    {
        $db = DaosPdoBD::getInstance()->getMonPdo();

        try {
            $query = "DELETE FROM evenement WHERE Id = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$id]);

            if ($stmt->rowCount() > 0) {
                return true; // Suppression réussie
            } else {
                return false; // Aucun événement supprimé (ID inexistant)
            }
        } catch (PDOException $e) {
            error_log("Erreur lors de la suppression de l'événement : " . $e->getMessage());
            return false;
        }
    }
    public static function addEvent($evenement)
    {
        $db = DaosPdoBD::getInstance()->getMonPdo();
        // Préparation de la requête d'insertion
        $query = "INSERT INTO evenement (Titre, Date_Evenement, lieu) VALUES (?, ?, ?)";
        $titre = $evenement->getTitre();
        $dateEvenement = $evenement->getDateEvenement()->format('Y-m-d');  // Formate la date en 'YYYY-MM-DD'
        $lieu = $evenement->getLieu();
        try {
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $titre, \PDO::PARAM_STR);
            $stmt->bindParam(2, $dateEvenement, \PDO::PARAM_STR);  // Formate la date en 'YYYY-MM-DD'
            $stmt->bindParam(3, $lieu, \PDO::PARAM_STR);

            // Exécution de la requête
            $stmt->execute();

            return true;
        } catch (PDOException $e) {
            error_log("Erreur lors de l'ajout de l'événement : " . $e->getMessage());
            return false;
        }
    }
    public static function updateEvenement($evenement)
    {
        // Récupère l'instance de la base de données
        $db = DaosPdoBD::getInstance()->getMonPdo();

        // Requête SQL pour mettre à jour un événement
        $updateQuery = "UPDATE evenement SET titre = ?, lieu = ?, date_evenement = ? WHERE id = ?";

        // Récupère les données de l'événement
        $titre = $evenement->getTitre();
        $lieu = $evenement->getLieu();
        $dateEvenement = $evenement->getDateEvenement()->format('Y-m-d');  // Formate la date en 'YYYY-MM-DD'
        $id = $evenement->getId();

        try {
            // Prépare la requête SQL
            $stmt = $db->prepare($updateQuery);

            // Lie les paramètres à la requête
            $stmt->bindParam(1, $titre, \PDO::PARAM_STR);
            $stmt->bindParam(2, $lieu, \PDO::PARAM_STR);
            $stmt->bindParam(3, $dateEvenement, \PDO::PARAM_STR);  // La date formatée en 'YYYY-MM-DD'
            $stmt->bindParam(4, $id, \PDO::PARAM_INT);

            // Exécute la requête de mise à jour
            $affectedRows = $stmt->execute();

            // Retourne true si une ligne a été affectée (mise à jour réussie)
            return $affectedRows > 0;
        } catch (PDOException $e) {
            // Si une erreur survient, on log l'erreur et on retourne false
            error_log("Erreur lors de la mise à jour de l'événement : " . $e->getMessage());
            return false;
        }
    }
    public static function getNParticipantByEvent(int $eventId): int {
        $query = "SELECT COUNT(*) AS count FROM participation WHERE id_evenement = ?";
        $participantCount = 0;
    
        try {
            $pdo =  DaosPdoBD::getInstance()->getMonPdo(); // Assurez-vous que Database::getInstance() retourne un objet PDO
            $stmt = $pdo->prepare($query);
            $stmt->execute([$eventId]);
            
            if ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $participantCount = (int) $row['count'];
            }
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération du nombre de participants : " . $e->getMessage());
        }
    
        return $participantCount;
    }
    public static function getMontantTotalByEvent($eventId) {
        $query = "SELECT COALESCE(SUM(p.Montant), 0) AS totalMontant 
                  FROM Participant p 
                  JOIN participation pa ON p.Id = pa.id_participant 
                  WHERE pa.id_evenement = ?";
        $totalMontant = 0;
        try {
            $db = DaosPdoBD::getInstance()->getMonPdo(); 
            $stmt = $db->prepare($query);
            $stmt->execute([$eventId]);

            if ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $totalMontant = $row["totalMontant"];
            }
        } catch (PDOException $e) {
            error_log("Erreur SQL : " . $e->getMessage());
        }

        return $totalMontant;
    }
    
}
