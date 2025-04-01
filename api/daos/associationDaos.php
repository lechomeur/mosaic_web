<?php

namespace daos;

use entities\association;
use daos\PdoBD as DaosPdoBD;
use PdoBD;
use DateTime;
use Exception;
use PDOException;

require_once "PdoBD.php";

class associationDaos{
    public static function getAllAssoc($associationId = 0) {
        $associations = [];
    
        // Connexion à la base de données
        $db = DaosPdoBD::getInstance()->getMonPdo();
        $sql = "SELECT * FROM association";
    
        // Si un ID d'association est passé, on ajoute la condition WHERE
        if ($associationId > 0) {
            $sql .= " WHERE Id = ?";
        }
    
        try {
            // Préparation et exécution de la requête
            $stmt = $db->prepare($sql);
    
            // Si un ID est passé, on le lie à la requête
            if ($associationId > 0) {
                $stmt->bindParam(1, $associationId, \PDO::PARAM_INT);
            }
    
            $stmt->execute();
    
            // Récupération des résultats
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $id = $row['Id'];
                $nom = $row['Nom'];
    
                // Création de l'objet Association et ajout dans le tableau
                $association = new Association($id, $nom);
                $associations[] = $association;
            }
        } catch (\PDOException $e) {
            error_log("Erreur lors de la récupération des associations : " . $e->getMessage());
        }
    
        return $associations;
    }
}