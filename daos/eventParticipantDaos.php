<?php
namespace daos ; 
use entities\participant;
use daos\PdoBD;
use PDOException;
use Exception;
class eventParticipantDaos{
    public static function getParticipantsByEvent($evenementId) {
        $participants = [];

        // Vérifier que l'ID est bien un entier
        if (!is_numeric($evenementId) || $evenementId <= 0) {
            error_log("ID d'événement invalide : " . var_export($evenementId, true));
            return false;
        }

        // Récupération de l'instance PDO
        $db = PdoBD::getInstance()->getMonPdo();  

        // Requête SQL
        $query = "SELECT 
                    p.Id, 
                    p.Nom, 
                    p.Prenom, 
                    p.Age, 
                    p.Telephone, 
                    p.Montant, 
                    p.Cheque_Espece, 
                    p.Adherant, 
                    p.Genre, 
                    p.Mail 
                  FROM participant p 
                  JOIN participation pa ON p.Id = pa.id_participant 
                  WHERE pa.id_evenement = ?";

        try {
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $evenementId, \PDO::PARAM_INT);
            $stmt->execute();

            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $participant = new Participant(
                    $row['Id'],
                    $row['Nom'],
                    $row['Prenom'],
                    $row['Age'],
                    $row['Telephone'],
                    $row['Montant'],
                    $row['Cheque_Espece'] ?? '', // Convertit NULL en ''
                    $row['Adherant'] ?? '', 
                    $row['Genre'] ?? '', 
                    $row['Mail'] ?? ''
                );
                $participants[] = $participant;
            }
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération des participants : " . $e->getMessage());
            return false;
        }

        return $participants;
    }
    public static function isAdherant(string $nom, string $prenom): bool {
        $query = "SELECT 1 FROM adhesion 
                  WHERE (UPPER(Nom) = UPPER(?) AND UPPER(Prenom) = UPPER(?)) 
                     OR (UPPER(Nom) = UPPER(?) AND UPPER(Prenom) = UPPER(?)) 
                  AND Montant IS NOT NULL LIMIT 1";
    
        try {
            $db = PdoBD::getInstance()->getMonPdo();
            $stmt = $db->prepare($query);
            $stmt->execute([strtoupper($nom), strtoupper($prenom), strtoupper($prenom), strtoupper($nom)]);
    
            return $stmt->fetch() !== false;  
        } catch (PDOException $e) {
            error_log("Erreur dans isAdherant : " . $e->getMessage());
            return false;
        }
    }
    public static function addParticipantToEvent(int $participantId, int $eventId): void {
        $query = "INSERT INTO participation (id_participant, id_evenement) VALUES (:participantId, :eventId)";

        try {
            // Récupération de la connexion PDO à partir du singleton PdoBD
            $conn = PdoBD::getInstance()->getMonPdo();

            // Préparation de la requête
            $stmt = $conn->prepare($query);

            // Liaison des paramètres
            $stmt->bindParam(':participantId', $participantId, \PDO::PARAM_INT);
            $stmt->bindParam(':eventId', $eventId, \PDO::PARAM_INT);

            // Exécution de la requête
            $stmt->execute();

        } catch (PDOException $e) {
            // Affichage de l'erreur en cas d'échec
            echo "Erreur : " . $e->getMessage();
        }
    }
    public static function removeParticipantFromEvent(int $participantId, int $eventId): void
{
    // Connexion à la base de données
    $db = PdoBD::getInstance()->getMonPdo();

    // La requête SQL pour supprimer la participation
    $query = "DELETE FROM participation WHERE id_participant = :participantId AND id_evenement = :eventId";

    try {
        // Préparer la requête
        $stmt = $db->prepare($query);

        // Lier les paramètres
        $stmt->bindParam(':participantId', $participantId, \PDO::PARAM_INT);
        $stmt->bindParam(':eventId', $eventId, \PDO::PARAM_INT);

        // Exécuter la requête
        $stmt->execute();
    } catch (PDOException $e) {
        // Gérer les erreurs
        error_log("Erreur lors de la suppression du participant de l'événement : " . $e->getMessage());
        throw new Exception("Une erreur est survenue lors de la suppression de la participation.");
    }
}
public static function getNbParticipantsPayesByEvent($eventId) {
    $query = "SELECT COUNT(*) AS countParticipantsPayes 
              FROM Participant p 
              JOIN participation pa ON p.Id = pa.id_participant 
              WHERE pa.id_evenement = ? AND (p.Montant > 0 OR p.Montant IS NOT NULL)";

    $countParticipantsPayes = 0;

    try {
        $db =  PdoBD::getInstance()->getMonPdo();
        $stmt = $db->prepare($query);
        $stmt->execute([$eventId]);

        if ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $countParticipantsPayes = $row["countParticipantsPayes"];
        }
    } catch (PDOException $e) {
        error_log("Erreur SQL : " . $e->getMessage());
    }

    return $countParticipantsPayes;
}
public static function getNombreParticipantsNonPayesByEvent($eventId) {

    $query = "SELECT COUNT(*) AS countParticipantsNonPayes 
              FROM Participant p 
              JOIN participation pa ON p.Id = pa.id_participant 
              WHERE pa.id_evenement = ? AND (p.Montant IS NULL OR p.Montant <= 0)";

    $countParticipantsNonPayes = 0;

    try {
        $db =  PdoBD::getInstance()->getMonPdo();
        $stmt = $db->prepare($query);
        $stmt->execute([$eventId]);

        if ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $countParticipantsNonPayes = $row["countParticipantsNonPayes"];
        }
    } catch (PDOException $e) {
        error_log("Erreur SQL : " . $e->getMessage());
    }

    return $countParticipantsNonPayes;
}
public static function getNbFemmesByEvent($eventId) {
    $query = "SELECT COUNT(*) AS countFemmes 
              FROM Participant p 
              INNER JOIN Participation pa ON p.Id = pa.id_participant 
              WHERE UPPER(p.Genre) = 'FEMME' AND pa.id_evenement = ?";

    $countFemmes = 0;

    try {
        $db = PdoBD::getInstance()->getMonPdo();
        $stmt = $db->prepare($query);
        $stmt->execute([$eventId]);

        if ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $countFemmes = $row["countFemmes"];
        }
    } catch (PDOException $e) {
        error_log("Erreur SQL : " . $e->getMessage());
    }

    return $countFemmes;
}
public static function getNbHommesByEvent($eventId) {
    $query = "SELECT COUNT(*) AS countHommes 
              FROM Participant p 
              INNER JOIN Participation pa ON p.Id = pa.id_participant 
              WHERE UPPER(p.Genre) = 'HOMME' AND pa.id_evenement = ?";

    $countHommes = 0;
    try {
        $db = PdoBD::getInstance()->getMonPdo();
        $stmt = $db->prepare($query);
        $stmt->execute([$eventId]);

        if ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $countHommes = $row["countHommes"];
        }
    } catch (PDOException $e) {
        error_log("Erreur SQL : " . $e->getMessage());
    }

    return $countHommes;
}
public static function searchParticipantsByNomPrenom($eventId, $searchTerm) {
    $sql = "SELECT p.id AS Id, p.nom AS Nom, p.prenom AS Prenom, 
                   p.Age, p.Telephone, p.Montant, p.Cheque_Espece, 
                   p.Adherant, p.Genre, p.Mail 
            FROM Participant p 
            INNER JOIN Participation pa ON p.id = pa.id_participant 
            WHERE pa.id_evenement = ? 
            AND (LOWER(p.nom) LIKE LOWER(?) 
            OR LOWER(p.prenom) LIKE LOWER(?) 
            OR LOWER(CONCAT(p.prenom, ' ', p.nom)) LIKE LOWER(?) 
            OR LOWER(CONCAT(p.nom, ' ', p.prenom)) LIKE LOWER(?))";

    try {
        $pdo = PdoBD::getInstance()->getMonPdo(); // Connexion à la base via PDO
        $stmt = $pdo->prepare($sql);
        
        $searchTerm = "%$searchTerm%"; // Ajout des wildcards pour la recherche LIKE
        $stmt->execute([$eventId, $searchTerm, $searchTerm, $searchTerm, $searchTerm]);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC); // Retourne la liste des participants sous forme de tableau associatif
    } catch (PDOException $e) {
        return ['error' => $e->getMessage()];
    }
}

}