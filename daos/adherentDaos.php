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

    public static function getDistinctAdhesionYears($idAssociation): array
    {
        try {
            // Modifie la requête pour filtrer par l'id de l'association
            $sql = "SELECT DISTINCT YEAR(Date_Adhesion) AS annee FROM adhesion WHERE association_id = :association_id";
            $stmt = DaosPdoBD::getInstance()->getMonPdo()->prepare($sql);
            $stmt->bindParam(':association_id', $idAssociation, \PDO::PARAM_INT);
            $stmt->execute();

            // Récupération des résultats sous forme de tableau d'années
            $years = [];
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $years[] = $row['annee']; // On ajoute l'année à notre tableau
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

            // Construction de la requête SQL
            $sql = "SELECT a.*, assoc.Nom AS AssociationNom 
                    FROM adhesion a 
                    JOIN association assoc ON a.Association_Id = assoc.Id 
                    WHERE 1=1";

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
            $sql .= " ORDER BY a.Date_Adhesion DESC";

            // Afficher la requête SQL pour le débogage
            //    echo "Requête SQL : " . $sql . "<br>";
            // Préparation et exécution de la requête
            $stmt = $pdo->prepare($sql);

            if ($associationId > 0) {
                $stmt->bindParam(":associationId", $associationId, \PDO::PARAM_INT);
            }
            if (!is_null($year)) {
                $stmt->bindParam(":year", $year, \PDO::PARAM_INT);
            }

            $stmt->execute();

            // Récupérer les résultats sous forme d'objets Adherent
            $adherents = $stmt->fetchAll(\PDO::FETCH_CLASS, Adherent::class);

            /*  // Afficher les données retournées pour le débogage
              echo "<pre>";
              var_dump($adherents); // Afficher les objets Adherent retournés
              echo "</pre>"; */

            // Vérifier si des résultats ont été trouvés
            if ($adherents === false || empty($adherents)) {
                return [];
            }

            // Tri par date d'adhésion décroissante (en utilisant une fonction de comparaison personnalisée)
            usort($adherents, function ($a, $b) {
                return strtotime($b->getDateAdhesion()) - strtotime($a->getDateAdhesion());
            });

            // Retourner uniquement les objets Adherent (pas de tableau supplémentaire)
            return $adherents;
        } catch (Exception $e) {
            // En cas d'erreur, retourner uniquement l'erreur sous forme d'objet
            return [
                "success" => false,
                "message" => "Erreur lors de la récupération des adhérents",
                "error" => $e->getMessage()
            ];
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

    public static function addAdherant(
        string $nom,
        string $prenom,
        string $genre,
        int $age,
        ?string $mail,
        ?string $telephone,
        ?float $cotisation,
        ?string $moyenPaiement,
        int $association_id,
        ?string $attestationPath
    ): bool {
        try {
            $pdo = DaosPdoBD::getInstance()->getMonPdo();
            if ($pdo === null) {
                error_log("Échec de la connexion à la base de données.");
                return false;
            }
            if (empty($nom) || empty($prenom)) {
                error_log("Les champs 'nom' et 'prenom' sont obligatoires.");
                return false;
            }
            $sql = "INSERT INTO adhesion 
                    (Nom, Prenom, Genre, Age, Mail, Telephone, Montant, Cheque_Espece, Association_Id, Attestation, Date_adhesion) 
                    VALUES (:nom, :prenom, :genre, :age, :mail, :telephone, :montant, :cheque_espece, :association_id, :attestation, NOW())";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':nom', $nom, \PDO::PARAM_STR);
            $stmt->bindValue(':prenom', $prenom, \PDO::PARAM_STR);
            $stmt->bindValue(':genre', $genre, \PDO::PARAM_STR);
            $stmt->bindValue(':age', $age, \PDO::PARAM_INT);
            $stmt->bindValue(':mail', $mail, is_null($mail) ? \PDO::PARAM_NULL : \PDO::PARAM_STR);
            $stmt->bindValue(':telephone', $telephone, is_null($telephone) ? \PDO::PARAM_NULL : \PDO::PARAM_STR);
            $stmt->bindValue(':montant', $cotisation, is_null($cotisation) ? \PDO::PARAM_NULL : \PDO::PARAM_INT);
            $stmt->bindValue(':cheque_espece', $moyenPaiement, is_null($moyenPaiement) ? \PDO::PARAM_NULL : \PDO::PARAM_STR);
            $stmt->bindValue(':association_id', $association_id, \PDO::PARAM_INT);
            $stmt->bindValue(':attestation', $attestationPath, is_null($attestationPath) ? \PDO::PARAM_NULL : \PDO::PARAM_STR);
            /*       var_dump([
              'nom' => $nom,
              'prenom' => $prenom,
              'genre' => $genre,
              'age' => $age,
              'mail' => $mail,
              'telephone' => $telephone,
              'montant' => $cotisation,
              'cheque_espece' => $moyenPaiement,
              'association_id' => $association_id,
              'attestation' => $attestationPath
              ]); */
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

    public static function deleteAdherant(int $adhesionId, $associationId): bool
    {
        try {
            $pdo = DaosPdoBD::getInstance()->getMonPdo();
            if ($pdo === null) {
                error_log("Échec de la connexion à la base de données.");
                return false;
            }
            $sql = "DELETE FROM adhesion WHERE Id = :adhesion_id and Association_Id = :association_id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':adhesion_id', $adhesionId, \PDO::PARAM_INT);
            $stmt->bindValue(':association_id', $associationId, \PDO::PARAM_INT);

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

    public static function updateAdherant(
        int $id,
        string $nom,
        string $prenom,
        string $genre,
        int $age,
        ?string $mail,
        ?string $telephone,
        ?float $montant,
        ?string $chequeEspece
    ): bool {
        try {
            $pdo = DaosPdoBD::getInstance()->getMonPdo();
            if ($pdo === null) {
                error_log("Échec de la connexion à la base de données.");
                return false;
            }
    
            // Requête SQL pour mettre à jour l'adhérent
            $sql = "
                UPDATE adhesion
                SET Nom = :nom,
                    Prenom = :prenom,
                    genre = :genre,
                    Age = :age,
                    Mail = :mail,
                    Telephone = :telephone,
                    Montant = :montant,
                    Cheque_Espece = :cheque_espece
                WHERE Id = :id
            ";
    
            // Préparation de la requête
            $stmt = $pdo->prepare($sql);
    
            // Liaison des paramètres
            $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
            $stmt->bindValue(':nom', $nom, \PDO::PARAM_STR);
            $stmt->bindValue(':prenom', $prenom, \PDO::PARAM_STR);
            $stmt->bindValue(':genre', $genre, \PDO::PARAM_STR);
            $stmt->bindValue(':age', $age, \PDO::PARAM_INT);
            $stmt->bindValue(':mail', $mail, \PDO::PARAM_STR);
            $stmt->bindValue(':telephone', $telephone, \PDO::PARAM_STR);
            $stmt->bindValue(':montant', $montant, \PDO::PARAM_STR);
            $stmt->bindValue(':cheque_espece', $chequeEspece, \PDO::PARAM_STR);
    
            // Exécution de la requête
            return $stmt->execute();
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
    public static function updateAttestation($id, $filePath) {
        $sql = "UPDATE adhesion SET Attestation = :filePath WHERE Id = :id";
        $stmt = DaosPdoBD::getInstance()->getMonPdo()->prepare($sql);
        $stmt->bindValue(":id", $id);
        $stmt->bindValue(":filePath", $filePath);
        return $stmt->execute();
    }
    public static function getAttestationFilePath($adhesionId) {
        // Récupérer le chemin du fichier depuis la base de données
        $sql = "SELECT Attestation FROM adhesion WHERE Id = :id";
        $stmt = DaosPdoBD::getInstance()->getMonPdo()->prepare($sql);
        $stmt->bindValue(":id", $adhesionId, \PDO::PARAM_INT);
        $stmt->execute();
        
        // Retourner le chemin du fichier (ou false si non trouvé)
        return $stmt->fetchColumn();
    }
    
    public static function searchAdherantsByNomPrenom($associationId, $anne, $searchTerm)
    {
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

            // Ajouter la condition sur l'association, si elle est spécifiée
            if ($associationId > 0) {
                $sql .= " AND a.Association_Id = ?";
                $params[] = $associationId;
            }

            // Ajouter la condition sur l'année, si elle est spécifiée
            if ($anne > 0) {
                $sql .= " AND YEAR(a.Date_Adhesion) = ?";
                $params[] = $anne;
            }

            // Ajouter la condition de recherche sur le nom ou le prénom
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
                error_log("⚠️ Aucun adhérent trouvé pour associationId=$associationId, année=$anne et searchTerm=$searchTerm");
            } else {
                error_log("✅ Adhérents trouvés !");
            }

            return $result;
        } catch (PDOException $e) {
            error_log("❌ Erreur SQL : " . $e->getMessage());
            return [];
        }
    }

    public static function getLatestYear($associationId)
    {
        $pdo = DaosPdoBD::getInstance()->getMonPdo();
        // Modifier la requête pour extraire uniquement l'année
        $query = "SELECT YEAR(MAX(Date_Adhesion)) AS latest_year FROM adhesion WHERE association_id = :association_id";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':association_id', $associationId, \PDO::PARAM_INT);
        $stmt->execute();
        $latestYear = $stmt->fetchColumn();

        // Retourne l'année actuelle si aucun résultat n'est trouvé
        return $latestYear ?: date('Y');
    }
}
