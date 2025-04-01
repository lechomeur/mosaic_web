<?php
namespace daos ; 
use entities\participant;
use daos\PdoBD;
use PDOException;
class participantDaos{
    public static function addParticipant(Participant $participant) {
        $query = "INSERT INTO participant (Nom, Prenom, Age, Telephone, Montant, Cheque_Espece, Adherant, Genre, Mail) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $participantId = -1;

        try {
            $conn = PdoBD::getInstance()->getMonPdo();
            $stmt = $conn->prepare($query);
            
            // Validation des données avant insertion (par exemple, vérifier que les champs nécessaires ne sont pas vides)
            if ($participant->getNom() == null || $participant->getPrenom() == null ) {
                throw new \InvalidArgumentException("Nom et prénom  sont obligatoires.");
            }

            $stmt->bindValue(1, $participant->getNom(), \PDO::PARAM_STR);
            $stmt->bindValue(2, $participant->getPrenom(), \PDO::PARAM_STR);
            $stmt->bindValue(3, $participant->getAge(), \PDO::PARAM_INT);
            $stmt->bindValue(4, $participant->getTelephone(), \PDO::PARAM_STR);
            $stmt->bindValue(5, $participant->getMontant(), \PDO::PARAM_INT);
            $stmt->bindValue(6, $participant->getChequeEspece(), \PDO::PARAM_STR);
            $stmt->bindValue(7, $participant->getAdherant(), \PDO::PARAM_STR);
            $stmt->bindValue(8, $participant->getGenre(), \PDO::PARAM_STR);
            $stmt->bindValue(9, $participant->getMail(), \PDO::PARAM_STR);

            $stmt->execute();

            // Récupérer l'ID du participant inséré
            $participantId = $conn->lastInsertId();

        } catch (PDOException $e) {
            // Log l'erreur pour un meilleur diagnostic
            error_log("Erreur lors de l'ajout du participant : " . $e->getMessage());
            throw $e; // Re-throw l'exception si nécessaire
        }

        return $participantId;
    }
    public static function getById($id) {
        $conn = PdoBD::getInstance()->getMonPdo();
        $query = "SELECT * FROM participant WHERE Id = ?";
        $stmt = $conn->prepare($query);
        $stmt->execute([$id]);
    
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
    
        if ($row) {
            return new Participant(
                $row['Id'],
                $row['Nom'],
                $row['Prenom'],
                $row['Age'],
                $row['Telephone'],
                $row['Montant'],
                $row['Cheque_Espece'],
                $row['Adherant'],
                $row['Genre'],
                $row['Mail']
            );
        } else {
            return null; // Si aucun participant trouvé
        }
    }
    public static function updateParticipant($participant) {
        $db = PdoBD::getInstance()->getMonPdo();
        try {
            $query = "UPDATE participant SET 
                      Nom = ?, Prenom = ?, Age = ?, Telephone = ?, Montant = ?, 
                      Cheque_Espece = ?, Adherant = ?, Genre = ?, Mail = ? 
                      WHERE Id = ?";
    
            $stmt = $db->prepare($query);
    
            // Créer des variables pour chaque paramètre et les lier avec bindParam
            $nom = $participant->getNom();
            $prenom = $participant->getPrenom();
            $age = $participant->getAge();
            $telephone = $participant->getTelephone();
            $montant = $participant->getMontant();
            $chequeEspece = $participant->getChequeEspece();
            $adherant = $participant->getAdherant();
            $genre = $participant->getGenre();
            $mail = $participant->getMail();
            $id = $participant->getId();
    
            // Lier les variables avec bindParam
            $stmt->bindParam(1, $nom);
            $stmt->bindParam(2, $prenom);
            $stmt->bindParam(3, $age, \PDO::PARAM_INT);
            $stmt->bindParam(4, $telephone);
            $stmt->bindParam(5, $montant, \PDO::PARAM_INT);
            $stmt->bindParam(6, $chequeEspece);
            $stmt->bindParam(7, $adherant);
            $stmt->bindParam(8, $genre);
            $stmt->bindParam(9, $mail);
            $stmt->bindParam(10, $id, \PDO::PARAM_INT);
    
            $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erreur lors de la mise à jour du participant : " . $e->getMessage());
            return false;
        }
    
        return true;
    }
    public static function getParticipantsByPaymentStatus($evenementId, $hasPaid) {
        $participants = [];

        // Connexion à la base de données
        $conn = PdoBD::getInstance()->getMonPdo();

        // Définition de la requête SQL selon le statut de paiement
        if ($hasPaid) {
            $query = "SELECT p.Id, p.Nom, p.Prenom, p.Age, p.Telephone, p.Montant, p.Cheque_Espece, p.Adherant, p.Genre, p.Mail "
                   . "FROM participant p "
                   . "JOIN participation pa ON p.Id = pa.id_participant "
                   . "WHERE pa.id_evenement = ? AND p.Montant IS NOT NULL AND p.Montant > 0";
        } else {
            $query = "SELECT p.Id, p.Nom, p.Prenom, p.Age, p.Telephone, p.Montant, p.Cheque_Espece, p.Adherant, p.Genre, p.Mail "
                   . "FROM participant p "
                   . "JOIN participation pa ON p.Id = pa.id_participant "
                   . "WHERE pa.id_evenement = ? AND (p.Montant IS NULL OR p.Montant <= 0)";
        }

        try {
            // Préparation et exécution de la requête
            $stmt = $conn->prepare($query);
            $stmt->bindParam(1, $evenementId,\PDO::PARAM_INT);
            $stmt->execute();

            // Récupération des résultats
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $participant = new Participant(
                    $row['Id'],
                    $row['Nom'],
                    $row['Prenom'],
                    $row['Age'],
                    $row['Telephone'],
                    $row['Montant'],
                    $row['Cheque_Espece'],
                    $row['Adherant'],
                    $row['Genre'],
                    $row['Mail']
                );
                $participants[] = $participant;
            }
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération des participants : " . $e->getMessage());
        }

        // Retourne la liste des participants
        return $participants;
    }
     
}
