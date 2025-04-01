<?php

namespace daos;

use entities\adherent;
use daos\PdoBD as DaosPdoBD;
use PdoBD;
use DateTime;
use Exception;
use PDOException;

require_once "PdoBD.php";
class adherentDaos
{
    public static function getDistinctAdhesionYears(): array
    {
        try {
            $sql = "SELECT DISTINCT YEAR(Date_Adhesion) AS annee FROM adhesion";
            $stmt = DaosPdoBD::getInstance()->getMonPdo()->prepare($sql);
            $stmt->execute();

            // Utilisation de fetchAll avec FETCH_CLASS
            $yearsObjects = $stmt->fetchAll(\PDO::FETCH_CLASS, adherent::class);

            // Extraction des années en tant que tableau
            $years = [];
            foreach ($yearsObjects as $adherent) {
                $years[] = $adherent->annee;
            }

            return $years;
        } catch (\Exception $e) {
            return ["error" => $e->getMessage()];
        }
    }
    public static function getAdherentsByAssociationByYearByCotis(int $associationId, ?int $year, ?string $cotis): array
    {
        try {
            $pdo = DaosPdoBD::getInstance()->getMonPdo();
            if ($pdo === null) {
                throw new Exception("Échec de la connexion à la base de données.");
            }

            $sql = "SELECT a.*, assoc.Nom AS AssociationNom FROM adhesion a 
                    JOIN association assoc ON a.Association_Id = assoc.Id WHERE 1=1";

            if ($associationId > 0) {
                $sql .= " AND a.Association_Id = :associationId";
            }
            if (!is_null($year)) {
                $sql .= " AND YEAR(a.Date_Adhesion) = :year";
            }
            if (!is_null($cotis)) {
                if (strcasecmp($cotis, "Payé") === 0) {
                    $sql .= " AND a.Montant IS NOT NULL";
                } elseif (strcasecmp($cotis, "Impayé") === 0) {
                    $sql .= " AND (a.Montant IS NULL OR a.Montant = 0)";
                }
            }

            $stmt = $pdo->prepare($sql);

            if ($associationId > 0) {
                $stmt->bindParam(":associationId", $associationId, \PDO::PARAM_INT);
            }
            if (!is_null($year)) {
                $stmt->bindParam(":year", $year, \PDO::PARAM_INT);
            }

            $stmt->execute();
            $adherents = [];

            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $adherent = new Adherent();
                $adherent->setId($row["Id"]); // Ajout du setter d'ID si nécessaire
                $adherent->setNom($row["Nom"]);
                $adherent->setPrenom($row["Prenom"]);
                $adherent->setAdresse($row["Adresse"]);
                $adherent->setMail($row["Mail"]);
                $adherent->setMontant($row["Montant"] ?? 0); // S'assure que Montant est un entier
                $adherent->setChequeEspece($row["Cheque_Espece"] ?? "");
                $adherent->setTelephone($row["Telephone"]);
                $adherent->setDateAdhesion($row["Date_Adhesion"]);
                $adherent->setAssociationNom($row["AssociationNom"]);
                $adherent->setAssociationId($row["Association_Id"]);

                $adherents[] = $adherent;
            }

            // Tri par date d'adhésion décroissante en utilisant le getter
            usort($adherents, fn($a, $b) => strtotime($b->getDateAdhesion()) - strtotime($a->getDateAdhesion()));

            return ["success" => true, "adherents" => array_map(fn($a) => $a->toArray(), $adherents)];
        } catch (Exception $e) {
            return ["success" => false, "message" => "Erreur lors de la récupération des adhérents", "error" => $e->getMessage()];
        }
    }
    public static function getNbAdhesionByAssociationByYearByCotis(int $associationId, ?int $year, ?string $cotisFilter): int
    {
        $count = 0;
        try {
            $pdo = DaosPdoBD::getInstance()->getMonPdo();
            if ($pdo === null) {
                throw new Exception("Échec de la connexion à la base de données.");
            }

            // Construction de la requête SQL
            $sql = "SELECT COUNT(*) FROM adhesion WHERE 1=1";

            if ($associationId > 0) {
                $sql .= " AND Association_Id = :associationId";
            }
            if ($year !== null) {
                $sql .= " AND YEAR(Date_Adhesion) = :year";
            }
            if ($cotisFilter !== null) {
                if (strcasecmp($cotisFilter, "Payé") === 0) {
                    $sql .= " AND Montant > 0"; // Payé = montant strictement positif
                } elseif (strcasecmp($cotisFilter, "Impayé") === 0) {
                    $sql .= " AND (Montant IS NULL OR Montant = 0)"; // Impayé = NULL ou 0
                }
            }

            // Debug : Vérifier la requête SQL générée
            error_log("Requête SQL exécutée : " . $sql);

            $stmt = $pdo->prepare($sql);
            if ($associationId > 0) {
                $stmt->bindParam(":associationId", $associationId, \PDO::PARAM_INT);
            }
            if ($year !== null) {
                $stmt->bindParam(":year", $year, \PDO::PARAM_INT);
            }

            $stmt->execute();
            $count = $stmt->fetchColumn();
        } catch (Exception $e) {
            error_log("Erreur lors de la récupération du nombre d'adhérents: " . $e->getMessage());
        }
        return $count;
    }
    public static function getNbCotpaye(int $associationId, ?int $year): int
    {
        $count = 0;
        try {
            $pdo = DaosPdoBD::getInstance()->getMonPdo();
            if ($pdo === null) {
                error_log("Échec de la connexion à la base de données.");
                return $count;
            }
            error_log("Connexion à la base de données établie.");

            // Construction de la requête SQL : On filtre uniquement les montants payés (≠ NULL et ≠ 0)
            $sql = "SELECT COUNT(*) FROM adhesion 
                    WHERE (Association_Id = :associationId OR :associationId = 0) 
                    AND Montant IS NOT NULL AND Montant > 0";

            if ($year !== null) {
                $sql .= " AND YEAR(Date_Adhesion) = :year";
            }

            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(":associationId", $associationId, \PDO::PARAM_INT);
            if ($year !== null) {
                $stmt->bindParam(":year", $year, \PDO::PARAM_INT);
            }
            $stmt->execute();
            $count = (int) $stmt->fetchColumn();
        } catch (Exception $e) {
            error_log("Erreur lors de la récupération du nombre d'adhésions payées : " . $e->getMessage());
        }
        return $count;
    }
    public static function getNbCotImpaye(int $associationId, ?int $year): int
    {
        $count = 0;
        try {
            $pdo = DaosPdoBD::getInstance()->getMonPdo();
            if ($pdo === null) {
                error_log("Échec de la connexion à la base de données.");
                return $count;
            }
            error_log("Connexion à la base de données établie.");

            // Construction de la requête SQL avec un tableau de paramètres
            $sql = "SELECT COUNT(*) FROM adhesion WHERE (Montant IS NULL OR Montant = 0)";
            $params = [];

            if ($associationId !== 0) {
                $sql .= " AND Association_Id = :associationId";
                $params[':associationId'] = $associationId;
            }
            if ($year !== null) {
                $sql .= " AND YEAR(Date_Adhesion) = :year";
                $params[':year'] = $year;
            }

            // Préparation et exécution de la requête
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $count = (int) $stmt->fetchColumn();
        } catch (Exception $e) {
            error_log("Erreur lors de la récupération du nombre d'adhésions non payées : " . $e->getMessage());
        }
        return $count;
    }
    public static function getCotisTot(int $associationId, ?int $year, ?string $cotisFilter): int
    {
        $totalCotisation = 0;
        try {
            $pdo = DaosPdoBD::getInstance()->getMonPdo();
            if ($pdo === null) {
                error_log("Échec de la connexion à la base de données.");
                return $totalCotisation;
            }
            error_log("Connexion à la base de données établie.");
            // Construction dynamique de la requête
            $sql = "SELECT SUM(Montant) FROM adhesion WHERE 1=1";
            $params = [];
            if ($associationId > 0) {
                $sql .= " AND Association_Id = :associationId";
                $params[':associationId'] = $associationId;
            }
            if ($year !== null) {
                $sql .= " AND YEAR(Date_Adhesion) = :year";
                $params[':year'] = $year;
            }
            if ($cotisFilter !== null) {
                if (strcasecmp($cotisFilter, "Payé") === 0) {
                    $sql .= " AND Montant IS NOT NULL AND Montant > 0";
                } elseif (strcasecmp($cotisFilter, "Impayé") === 0) {
                    $sql .= " AND (Montant IS NULL OR Montant = 0)";
                }
            }
            // Exécution de la requête
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetchColumn();

            if ($result !== false) {
                $totalCotisation = (int) $result;
            }
        } catch (Exception $e) {
            error_log("Erreur lors de la récupération du total des cotisations : " . $e->getMessage());
        }
        return $totalCotisation;
    }
    public static function addAdherant(array $adherant): bool
    {
        try {
            $pdo = DaosPdoBD::getInstance()->getMonPdo();
            if ($pdo === null) {
                error_log("Échec de la connexion à la base de données.");
                return false;
            }

            error_log("Connexion à la base de données établie.");

            // Vérification des champs obligatoires (nom et prenom uniquement)
            if (empty($adherant['nom']) || empty($adherant['prenom'])) {
                error_log("Les champs 'nom' et 'prenom' sont obligatoires.");
                return false;
            }

            // Gestion des valeurs optionnelles (NULL si non fournies ou vides)
            $adresse = !empty($adherant['adresse']) ? $adherant['adresse'] : null;
            $mail = !empty($adherant['mail']) ? $adherant['mail'] : null;
            $telephone = !empty($adherant['telephone']) ? $adherant['telephone'] : null;
            $montant = (!empty($adherant['montant']) && is_numeric($adherant['montant'])) ? (int) $adherant['montant'] : null;
            $cheque_espece = !empty($adherant['cheque_espece']) ? $adherant['cheque_espece'] : null;
            $association_id = !empty($adherant['association_id']) ? (int) $adherant['association_id'] : null;

            // Requête SQL sécurisée
            $sql = "INSERT INTO adhesion 
                    (Nom, Prenom, Adresse, Mail, Telephone, Montant, Cheque_Espece, Association_Id, Date_adhesion) 
                    VALUES (:nom, :prenom, :adresse, :mail, :telephone, :montant, :cheque_espece, :association_id, NOW())";

            $stmt = $pdo->prepare($sql);

            // Liaison des valeurs avec gestion des NULL
            $stmt->bindValue(':nom', $adherant['nom'], \PDO::PARAM_STR);
            $stmt->bindValue(':prenom', $adherant['prenom'], \PDO::PARAM_STR);
            $stmt->bindValue(':adresse', $adresse, is_null($adresse) ? \PDO::PARAM_NULL : \PDO::PARAM_STR);
            $stmt->bindValue(':mail', $mail, is_null($mail) ? \PDO::PARAM_NULL : \PDO::PARAM_STR);
            $stmt->bindValue(':telephone', $telephone, is_null($telephone) ? \PDO::PARAM_NULL : \PDO::PARAM_STR);
            $stmt->bindValue(':montant', $montant, is_null($montant) ? \PDO::PARAM_NULL : \PDO::PARAM_INT);
            $stmt->bindValue(':cheque_espece', $cheque_espece, is_null($cheque_espece) ? \PDO::PARAM_NULL : \PDO::PARAM_STR);
            $stmt->bindValue(':association_id', $association_id, is_null($association_id) ? \PDO::PARAM_NULL : \PDO::PARAM_INT);

            // Exécution et vérification
            if ($stmt->execute()) {
                error_log("Adhérent ajouté avec succès !");
                return true;
            } else {
                error_log("Échec de l'insertion de l'adhérent.");
                return false;
            }
        } catch (PDOException $e) {
            error_log("Erreur PDO : " . $e->getMessage());
            return false;
        } catch (Exception $e) {
            error_log("Erreur générale : " . $e->getMessage());
            return false;
        }
    }
    public static function deleteAdherant(int $id): bool
    {
        try {
            $pdo = DaosPdoBD::getInstance()->getMonPdo();
            if ($pdo === null) {
                error_log("Échec de la connexion à la base de données.");
                return false;
            }
            $sql = "DELETE FROM adhesion WHERE Id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $id, \PDO::PARAM_INT);

            if ($stmt->execute()) {
                error_log("Adhérent supprimé avec succès !");
                return true;
            } else {
                error_log("Échec de la suppression de l'adhérent.");
                return false;
            }
        } catch (PDOException $e) {
            error_log("Erreur PDO : " . $e->getMessage());
            return false;
        }
    }
    public static function updateAdherant(array $adherant, adherent $originalAdherant)
    {
        try {
            $pdo = DaosPdoBD::getInstance()->getMonPdo();
            if ($pdo === null) {
                error_log("Échec de la connexion à la base de données.");
                return false;
            }

            $updateQuery = "UPDATE adhesion SET ";
            $parameters = [];
            $updates = [];

            // Comparer chaque champ et ajouter ceux qui ont changé
            if (isset($adherant['nom']) && $adherant['nom'] !== $originalAdherant->getNom()) {
                $updates[] = "Nom = :nom";
                $parameters[':nom'] = $adherant['nom'];
            }
            if (isset($adherant['prenom']) && $adherant['prenom'] !== $originalAdherant->getPrenom()) {
                $updates[] = "Prenom = :prenom";
                $parameters[':prenom'] = $adherant['prenom'];
            }
            if (isset($adherant['adresse']) && $adherant['adresse'] !== $originalAdherant->getAdresse()) {
                $updates[] = "Adresse = :adresse";
                $parameters[':adresse'] = $adherant['adresse'];
            }
            if (isset($adherant['mail']) && $adherant['mail'] !== $originalAdherant->getMail()) {
                $updates[] = "Mail = :mail";
                $parameters[':mail'] = $adherant['mail'];
            }
            if (isset($adherant['montant']) && $adherant['montant'] !== $originalAdherant->getMontant()) {
                $updates[] = "Montant = :montant";
                $parameters[':montant'] = $adherant['montant'];
            }
            if (isset($adherant['cheque_espece']) && $adherant['cheque_espece'] !== $originalAdherant->getChequeEspece()) {
                $updates[] = "Cheque_Espece = :cheque_espece";
                $parameters[':cheque_espece'] = $adherant['cheque_espece'];
            }
            if (isset($adherant['telephone']) && $adherant['telephone'] !== $originalAdherant->getTelephone()) {
                $updates[] = "Telephone = :telephone";
                $parameters[':telephone'] = $adherant['telephone'];
            }
            if (isset($adherant['date_adhesion']) && $adherant['date_adhesion'] !== $originalAdherant->getDateAdhesion()) {
                $updates[] = "Date_adhesion = :date_adhesion";
                $parameters[':date_adhesion'] = $adherant['date_adhesion'];
            }
            if (isset($adherant['association_id']) && $adherant['association_id'] !== $originalAdherant->getAssociationId()) {
                $updates[] = "Association_Id = :association_id";
                $parameters[':association_id'] = $adherant['association_id'];
            }

            // Vérifier s'il y a des champs à mettre à jour
            if (empty($updates)) {
                error_log("Aucune modification détectée.");
                return false;
            }

            // Construire la requête SQL
            $updateQuery .= implode(", ", $updates) . " WHERE Id = :id";
            $parameters[':id'] = $adherant['id'];

            // Préparer et exécuter la requête
            $stmt = $pdo->prepare($updateQuery);
            return $stmt->execute($parameters);
        } catch (PDOException $e) {
            error_log("Erreur lors de la mise à jour de l'adhérent : " . $e->getMessage());
            return false;
        }
    }


    public static function getAdherentById(int $id)
    {
        $sql = "SELECT * FROM adhesion WHERE Id = :id";
        $stmt = DaosPdoBD::getInstance()->getMonPdo()->prepare($sql);
        $stmt->bindValue(":id", $id);
        $stmt->execute();
        $stmt->setFetchMode(\PDO::FETCH_CLASS, '\entities\adherent');
        $adherent = $stmt->fetch();
        return $adherent;;
    }
   public static function searchAdherantsByNomPrenom($associationId, $searchTerm) {
        try {
            $pdo = DaosPdoBD::getInstance()->getMonPdo();
            if ($pdo === null) {
                error_log("❌ Connexion à la base de données échouée !");
                return [];
            }
            $sql = "SELECT a.*, assoc.Nom AS AssociationNom 
                    FROM adhesion a 
                    JOIN association assoc ON a.Association_Id = assoc.Id 
                    WHERE 1=1";
            
            $params = [];
            if ($associationId > 0) {
                $sql .= " AND a.Association_Id = ?";
                $params[] = $associationId;
            }
            if (!empty($searchTerm)) {
                $sql .= " AND (a.Nom LIKE ? OR a.Prenom LIKE ?)";
                $params[] = "%$searchTerm%";
                $params[] = "%$searchTerm%";
            }   
            error_log("🔍 Requête SQL : " . $sql);  // Debug : Vérifie la requête générée
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
    
            if (empty($result)) {
                error_log("⚠️ Aucun adhérent trouvé pour associationId=$associationId et searchTerm=$searchTerm");
            } else {
                error_log("✅ Adhérents trouvés !");
            }
            return $result;
        } catch (PDOException $e) {
            error_log("❌ Erreur SQL : " . $e->getMessage());
            return [];
        }
    }
    
}
