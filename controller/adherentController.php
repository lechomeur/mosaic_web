<?php

namespace controller;

use daos\adherentDaos;
use Entities\adherent;
use Exception;
use controller\associationController;
use daos\associationDaos;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
class adherentController
{
    private $adherentDao;
    private $associationController;
    public function __construct()
    {
        $request = filter_input(INPUT_SERVER, 'REQUEST_METHOD');
        $this->adherentDao = new adherentDaos();
        $this->associationController = new associationController();
        $this->handleRequest($request);
        //  $this->handleRequest();
    }
    private function handleRequest($request)
    {
        // Récupérer l'action à partir de la requête
        $action = filter_input(INPUT_GET, 'action');
        switch ($action) {
            case 'getAllAdhesion':
                $this->listAdhesions();
                break;
            case 'searchAdhesions':
                $this->searchAdhesions();
                break;
            case 'formAdd':
                $this->addAdhesionVue();
                break;
            case 'add':
                $this->addAdhesion();
                break;
            case 'updateAdherent':
                $this->updateAdherent();
                break;
            case 'delete':
                $this->deleteAdhesion();
                break;
            case 'uploadAttestation':
                $this->uploadAttestation();
                break;
           /* case 'export':
                $this->export();
                break; */
            case 'attestation':
                $this->attestation();
                break;
            default:
                $this->choixAssociation();
                break;
        }
    }
    private function choixAssociation()
    {
        $associationId = 0;
        $associations = $this->associationController->getAllAssociations($associationId);
        include __DIR__ . '/../view/choixAssoAdhesion.php';
    }
    private function listAdhesions()
    {
        $_SESSION['message'] = "L'adhérent a été ajouté avec succès !";
        $_SESSION['message_type'] = "success";
        // Récupérer l'ID de l'association et vérifier sa validité
        $associationId = filter_input(INPUT_GET, "association_id", FILTER_SANITIZE_NUMBER_INT);
        if ($associationId === null || $associationId === false || $associationId <= 0) {
            die("Erreur : association_id invalide ou manquant.");
        }
        $latestYear = adherentDaos::getLatestYear($associationId);
        // Récupérer l'année depuis le GET et vérifier sa validité
        $year = filter_input(INPUT_GET, "year", FILTER_SANITIZE_NUMBER_INT);
        $year = intval($year); // Convertir explicitement en entier

        if ($year === 0 || $year < 1900 || $year > 2100) {
            $year = $latestYear ?: date('Y'); // Si l'année est invalide, prendre l'année actuelle
        }
        // Récupérer les années distinctes pour l'association
        $annees = adherentDaos::getDistinctAdhesionYears($associationId);
        // Récupérer le statut de cotisation et vérifier sa validité
        $cotis = filter_input(INPUT_GET, "cotis", FILTER_SANITIZE_SPECIAL_CHARS);
        if ($cotis !== "Payé" && $cotis !== "Impayé") {
            $cotis = null; // Valeur par défaut si le statut est invalide
        }
        // Récupérer les informations de l'association
        $association = associationDaos::getAssociationById($associationId);
        // Appeler la méthode avec les paramètres corrects
        $result = adherentDaos::getAdherentsByAssociationByYearByCotis($associationId, $year, $cotis);
        $adhesions = $result;
        // Calculer les données pour les graphiques
        $ageGroups = ['0-18' => 0, '19-35' => 0, '36-50' => 0, '51+' => 0];
        $genderCount = ['Homme' => 0, 'Femme' => 0, 'Autre' => 0];
        $totalCotisation = 0;
        $paymentStatus = ['Payé' => 0, 'Impayé' => 0];

        foreach ($adhesions as $adherent) {
            // Calculer les tranches d'âge
            $age = $adherent->getAge();
            if ($age <= 18) $ageGroups['0-18']++;
            elseif ($age <= 35) $ageGroups['19-35']++;
            elseif ($age <= 50) $ageGroups['36-50']++;
            else $ageGroups['51+']++;

            // Calculer la répartition par genre
            $gender = $adherent->getGenre();
            if (isset($genderCount[$gender])) $genderCount[$gender]++;

            // Calculer la cotisation totale
            $totalCotisation += $adherent->getMontant();

            // Calculer le statut de paiement
            $status = ($adherent->getMontant() > 0) ? 'Payé' : 'Impayé';
            $paymentStatus[$status]++;
        }

        // Passer les données à la vue
        $data = [
            'ageGroups' => $ageGroups,
            'genderCount' => $genderCount,
            'totalCotisation' => $totalCotisation,
            'paymentStatus' => $paymentStatus,
            'adhesions' => $adhesions,
            'association' => $association,
            'annees' => $annees,
            'year' => $year,
            'cotis' => $cotis,
        ];

        // Inclure la vue
        extract($data); // Convertit les clés du tableau en variables
        // Gérer la requête AJAX
        if (isset($_GET['ajax']) && $_GET['ajax'] == 'true') {
            ob_start(); // Commencer la capture de la sortie
            include __DIR__ . '/../view/adherentList.php'; // Inclure la vue
            $html = ob_get_clean(); // Récupérer le contenu HTML généré

            // Retourner uniquement le contenu du tableau
            echo $html;
            exit; // Ne pas continuer l'exécution, juste envoyer le contenu du tableau
        }


        // Inclure la vue normale
        include __DIR__ . '/../view/adherentList.php';
    }
    private function searchAdhesions()
    {
        // Récupérer l'ID de l'association et vérifier sa validité
        $associationId = filter_input(INPUT_GET, "association_id", FILTER_SANITIZE_NUMBER_INT);
        if ($associationId === null || $associationId === false || $associationId <= 0) {
            die("Erreur : association_id invalide ou manquant.");
        }

        // Récupérer les paramètres de recherche
        $year = filter_input(INPUT_GET, "year", FILTER_SANITIZE_NUMBER_INT);
        $cotis = filter_input(INPUT_GET, "cotis", FILTER_SANITIZE_SPECIAL_CHARS);
        $searchTerm = filter_input(INPUT_GET, "search", FILTER_SANITIZE_SPECIAL_CHARS);

        // Appeler la méthode de recherche avec les paramètres
        $adhesions = adherentDaos::searchAdherantsByNomPrenom($associationId, $year, $searchTerm, $cotis);

        // Gérer la requête AJAX
        if (isset($_GET['ajax']) && $_GET['ajax'] == 'true') {
            ob_start(); // Commencer la capture de la sortie
            include __DIR__ . '/../view/adherentList.php'; // Inclure la vue du tableau filtré
            $html = ob_get_clean(); // Récupérer le contenu HTML généré
            echo $html; // Retourner uniquement le contenu du tableau
            exit; // Ne pas continuer l'exécution, juste envoyer le contenu du tableau
        }

        // Si ce n'est pas une requête AJAX, inclure la vue complète (tableau + autres données)
        include __DIR__ . '/../view/adherentList.php';
    }
    private function addAdhesionVue()
    {
        $associationId = filter_input(INPUT_GET, "association_id", FILTER_SANITIZE_NUMBER_INT);
        // var_dump($associationId);
        include __DIR__ . '/../view/addAdhesion.php';
    }
    private function addAdhesion()
    {
        $associationId = filter_input(INPUT_GET, "association_id", FILTER_SANITIZE_NUMBER_INT);
        $nom = filter_input(INPUT_POST, 'nom', FILTER_SANITIZE_SPECIAL_CHARS);
        $prenom = filter_input(INPUT_POST, 'prenom', FILTER_SANITIZE_SPECIAL_CHARS);
        $genre = filter_input(INPUT_POST, 'genre', FILTER_SANITIZE_SPECIAL_CHARS);
        $age = filter_input(INPUT_POST, 'age', FILTER_SANITIZE_NUMBER_INT);
        $mail = filter_input(INPUT_POST, 'mail', FILTER_SANITIZE_SPECIAL_CHARS);
        $numero = filter_input(INPUT_POST, 'numero', FILTER_SANITIZE_SPECIAL_CHARS);
        $cotisation = filter_input(INPUT_POST, 'cotisation', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        $moyenPaiement = filter_input(INPUT_POST, 'moyenPaiement', FILTER_SANITIZE_SPECIAL_CHARS);
        $attestationPath = null;

        if (empty($nom) || empty($prenom)) {
            echo "Le nom et le prénom sont obligatoires.";
            exit;  // Arrêter le traitement si ces champs ne sont pas remplis
        }

        // On laisse les autres champs comme optionnels
        if (empty($genre)) {
            $genre = null;  // On peut aussi les laisser à null si non rempli
        }

        if (empty($age)) {
            $age = null;
        }

        if (empty($mail)) {
            $mail = null;
        }

        if (empty($numero)) {
            $numero = null;
        }

        if (empty($cotisation)) {
            $cotisation = null;
        }

        if (empty($moyenPaiement)) {
            $moyenPaiement = null;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Vérifier si un fichier a été téléchargé
            if (isset($_FILES['attestation']) && $_FILES['attestation']['error'] === UPLOAD_ERR_OK) {

                // Afficher les détails du fichier pour le debug

                // Chemin du répertoire où les fichiers seront stockés
                $uploadDir = realpath(__DIR__ . '/../uploads') . '/';
                $uploadDirWeb = 'uploads/'; // Chemin relatif pour la base de données

                // Vérifier si le dossier d'upload existe, sinon le créer
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                // Récupérer les informations du fichier
                $fileName = basename($_FILES['attestation']['name']); // Nom original du fichier
                $fileTmpPath = $_FILES['attestation']['tmp_name'];   // Chemin temporaire du fichier
                $fileSize = $_FILES['attestation']['size'];          // Taille du fichier
                $fileType = $_FILES['attestation']['type'];          // Type MIME du fichier

                // Autoriser uniquement les fichiers PDF, JPG et PNG
                $allowedTypes = ['application/pdf', 'image/jpeg', 'image/png'];
                $allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png'];

                // Récupérer l'extension du fichier
                $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                if (!in_array($fileType, $allowedTypes) || !in_array($fileExtension, $allowedExtensions)) {
                    echo "❌ Erreur : Seuls les fichiers PDF, JPG et PNG sont autorisés.";
                    exit;
                }

                // Valider la taille du fichier (exemple : maximum 5 Mo)
                $maxFileSize = 5 * 1024 * 1024; // 5 Mo
                if ($fileSize > $maxFileSize) {
                    echo "❌ Erreur : Le fichier est trop volumineux. Taille maximale autorisée : 5 Mo.";
                    exit;
                }

                // Générer un nom de fichier unique pour éviter les conflits
                $uniqueFileName = uniqid() . '.' . $fileExtension;
                $uploadFilePath = $uploadDir . $uniqueFileName;
                $uploadFilePathWeb = $uploadDirWeb . $uniqueFileName; // Pour la base de données

                // Déplacer le fichier téléchargé vers le dossier d'upload
                if (move_uploaded_file($fileTmpPath, $uploadFilePath)) {
                    echo "✅ Fichier téléchargé avec succès : " . $uploadFilePath;
                    $attestationPath = $uploadFilePathWeb; // Stocker le chemin dans la base
                } else {
                    echo "❌ Erreur lors du déplacement du fichier.";
                }
            } else {
                echo "❌ Erreur de téléchargement : " . $_FILES['attestation']['error'];
            }
        }



        $adherentAdded = adherentDaos::addAdherant(
            $nom,
            $prenom,
            $genre,
            $age,
            $mail,
            $numero,
            $cotisation,
            $moyenPaiement,
            $associationId,
            $attestationPath
        );
        include __DIR__ . '/../view/header.php';
        
        header('Content-Type: application/json'); // Précise que la réponse est en JSON

        if ($adherentAdded) {
            echo json_encode(["status" => "success", "message" => "L'adhérent a été ajouté avec succès !"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Erreur lors de l'ajout de l'adhérent."]);
        }
    }
    private function updateAdherent()
    {
        // Récupérer les données du formulaire
        $id = isset($_GET['id']) ? intval($_GET['id']) : null;
        $nom = isset($_POST['nom']) ? htmlspecialchars($_POST['nom']) : '';
        $prenom = isset($_POST['prenom']) ? htmlspecialchars($_POST['prenom']) : '';
        $genre = isset($_POST['genre']) ? htmlspecialchars($_POST['genre']) : '';
        $age = isset($_POST['age']) ? intval($_POST['age']) : null;
        $mail = isset($_POST['mail']) ? htmlspecialchars($_POST['mail']) : '';
        $telephone = isset($_POST['telephone']) ? htmlspecialchars($_POST['telephone']) : '';
        $montant = isset($_POST['montant']) ? floatval($_POST['montant']) : 0;
        $chequeEspece = isset($_POST['cheque_espece']) ? htmlspecialchars($_POST['cheque_espece']) : '';

        // Vérifier que l'ID est valide
        if (!$id) {
            echo json_encode(["status" => "error", "message" => "ID invalide."]);
            return;
        }

        try {
            // Appeler la méthode du DAO pour mettre à jour l'adhérent
            $success = adherentDaos::updateAdherant(
                $id,
                $nom,
                $prenom,
                $genre,
                $age,
                $mail,
                $telephone,
                $montant,
                $chequeEspece
            );

            if ($success) {
                header('Content-Type: application/json'); // Indique que la réponse est du JSON
                echo json_encode(["status" => "success", "message" => "L'adhérent a été mis à jour avec succès."]);
            } else {
                header('Content-Type: application/json'); // Indique que la réponse est du JSON
                echo json_encode(["status" => "error", "message" => "Erreur lors de la mise à jour de l'adhérent."]);
            }
        } catch (Exception $e) {
            header('Content-Type: application/json'); // Indique que la réponse est du JSON
            echo json_encode(["status" => "error", "message" => "Erreur lors de la modification de l'adhésion : " . $e->getMessage()]);
        }
    }
    private function deleteAdhesion()
    {
        ob_start(); // Empêche les erreurs de headers

        // Récupérer et convertir les paramètres en entiers
        $adhesionId = intval(filter_input(INPUT_GET, 'adhesion_id', FILTER_SANITIZE_NUMBER_INT));
        $associationId = intval(filter_input(INPUT_GET, 'association_id', FILTER_SANITIZE_NUMBER_INT));

        // Vérifier que les IDs sont valides
        if ($adhesionId <= 0 || $associationId <= 0) {
            ob_end_clean(); // Nettoie toute sortie avant de renvoyer du JSON
            header('Content-Type: application/json');
            echo json_encode(["success" => false, "message" => "Données invalides"]);
            exit;
        }

        try {
            adherentDaos::deleteAdherant($adhesionId, $associationId);

            ob_end_clean(); // Nettoie tout contenu HTML envoyé avant
            header('Content-Type: application/json');
            echo json_encode(["success" => true]);
            exit;
        } catch (Exception $e) {
            ob_end_clean();
            header('Content-Type: application/json');
            echo json_encode(["success" => false, "message" => "Erreur : " . $e->getMessage()]);
            exit;
        }
    }
    public function uploadAttestation()
    {
        $adhesionId = isset($_GET['id']) ? intval($_GET['id']) : null;
        if (isset($_FILES['attestation']) && $_FILES['attestation']['error'] === UPLOAD_ERR_OK) {
            // Chemin du répertoire où les fichiers seront stockés
            $uploadDir = realpath(__DIR__ . '/../uploads') . '/';
            $uploadDirWeb = 'uploads/'; // Chemin relatif pour la base de données
            // Vérifier si le dossier d'upload existe, sinon le créer
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            // Récupérer les informations du fichier
            $fileName = basename($_FILES['attestation']['name']); // Nom original du fichier
            $fileTmpPath = $_FILES['attestation']['tmp_name'];   // Chemin temporaire du fichier
            $fileSize = $_FILES['attestation']['size'];          // Taille du fichier
            $fileType = $_FILES['attestation']['type'];          // Type MIME du fichier
            // Autoriser uniquement les fichiers PDF, JPG et PNG
            $allowedTypes = ['application/pdf', 'image/jpeg', 'image/png'];
            $allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png'];
            // Récupérer l'extension du fichier
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            if (!in_array($fileType, $allowedTypes) || !in_array($fileExtension, $allowedExtensions)) {
                echo "❌ Erreur : Seuls les fichiers PDF, JPG et PNG sont autorisés.";
                exit;
            }
            // Valider la taille du fichier (exemple : maximum 5 Mo)
            $maxFileSize = 5 * 1024 * 1024; // 5 Mo
            if ($fileSize > $maxFileSize) {
                echo "❌ Erreur : Le fichier est trop volumineux. Taille maximale autorisée : 5 Mo.";
                exit;
            }

            // Générer un nom de fichier unique pour éviter les conflits
            $uniqueFileName = uniqid() . '.' . $fileExtension;
            $uploadFilePath = $uploadDir . $uniqueFileName;
            $uploadFilePathWeb = $uploadDirWeb . $uniqueFileName; // Pour la base de données

            // Déplacer le fichier téléchargé vers le dossier d'upload
            if (move_uploaded_file($fileTmpPath, $uploadFilePath)) {
                echo "✅ Fichier téléchargé avec succès : " . $uploadFilePath;
                $attestationPath = $uploadFilePathWeb; // Stocker le chemin dans la base
            } else {
                echo "❌ Erreur lors du déplacement du fichier.";
            }
        } else {
            echo "❌ Erreur de téléchargement : " . $_FILES['attestation']['error'];
        }
        $uploadFile = adherentDaos::updateAttestation($adhesionId, $attestationPath);
        if ($uploadFile) {
            echo json_encode(["status" => "success", "message" => "Attestation téléversée avec succès."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Erreur lors du téléversement du fichier."]);
        }
    }
    public function attestation()
    {
        // Pas d'espace ou de sortie avant ce point

        $adhesionId = isset($_GET['id']) ? intval($_GET['id']) : null;

        // Validation de l'ID
        if (!$adhesionId) {
            header("HTTP/1.0 400 Bad Request");
            echo "ID invalide.";
            exit;
        }

        // Récupérer le chemin relatif du fichier depuis la base de données
        $relativeFilePath = adherentDaos::getAttestationFilePath($adhesionId);

        // Construire le chemin absolu
        $absoluteFilePath = ROOT_DIR . DIRECTORY_SEPARATOR . $relativeFilePath;

        // Vérifier que le fichier existe
        if ($absoluteFilePath && file_exists($absoluteFilePath)) {
            // Déterminer le type MIME du fichier
            $mimeType = mime_content_type($absoluteFilePath);

            // Envoyer les en-têtes HTTP
            header("Content-Type: " . $mimeType);
            header("Content-Length: " . filesize($absoluteFilePath));
            header("Content-Disposition: attachment; filename=" . basename($absoluteFilePath)); // Téléchargement automatique

            // Lire et envoyer le fichier
            readfile($absoluteFilePath);
            exit;
        } else {
            // Si le fichier n'existe pas, afficher une erreur 404
            header("HTTP/1.0 404 Not Found");
            echo "Fichier non trouvé.";
            var_dump($absoluteFilePath);
            exit;
        }
    }
    private function searchAdhesion()
    {
        $searchTerm = filter_input(INPUT_POST, 'search_term');
        //    $adhesions = $this->adherentDao->searchAdhesions($searchTerm);

        // Afficher les résultats de la recherche
        include __DIR__ . '/../view/adherentList.php';
    }

    // Télécharger la liste des adhésions (par exemple, Excel ou PDF)
  /*  private function export()
    {
        $associationId = filter_input(INPUT_GET, 'association_id', FILTER_SANITIZE_NUMBER_INT);
        $year = filter_input(INPUT_GET, 'year', FILTER_SANITIZE_NUMBER_INT);
        $cotis = filter_input(INPUT_GET, 'cotis', FILTER_SANITIZE_SPECIAL_CHARS);

        // Vérification si l'association ID est bien un entier
        if (!$associationId) {
            die("Erreur : ID d'association invalide.");
        }

        // Récupérer la liste des adhésions en fonction des filtres
        $adhesions = adherentDaos::getAdherentsByAssociationByYearByCotis($associationId, $year, $cotis);

        // Créer un nouveau fichier Excel
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Définir les en-têtes des colonnes
        $headers = ['Nom', 'Prénom', 'Genre', 'Âge', 'Mail', 'Téléphone', 'Date d\'adhésion', 'Montant', 'Paiement'];
        $sheet->fromArray([$headers], NULL, 'A1');

        // Appliquer un style aux en-têtes (gras + fond gris)
        $styleArray = [
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'DDDDDD'],
            ],
        ];
        $sheet->getStyle('A1:I1')->applyFromArray($styleArray);

        // Insérer les données des adhérents
        $row = 2;
        foreach ($adhesions as $adherent) {
            $sheet->setCellValue('A' . $row, $adherent->getNom());
            $sheet->setCellValue('B' . $row, $adherent->getPrenom());
            $sheet->setCellValue('C' . $row, $adherent->getGenre());
            $sheet->setCellValue('D' . $row, $adherent->getAge());
            $sheet->setCellValue('E' . $row, $adherent->getMail());
            $sheet->setCellValue('F' . $row, $adherent->getTelephone());
            $sheet->setCellValue('G' . $row, $adherent->getDateAdhesion());
            $sheet->setCellValue('H' . $row, $adherent->getMontant());
            $sheet->setCellValue('I' . $row, $adherent->getChequeEspece()); // Mode de paiement
            $row++;
        }

        // Ajuster la largeur des colonnes
        foreach (range('A', 'I') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        // Générer le fichier Excel et l'envoyer en téléchargement
        $filename = "adhesions.xlsx";
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
    // Fonction pour générer un fichier Excel (à adapter selon ton besoin)
    private function generateExcel($adhesions)
    {
        // Utiliser une bibliothèque comme PhpSpreadsheet pour générer un fichier Excel
        // ou utiliser une bibliothèque pour PDF comme TCPDF pour générer un PDF
    }
        */
}
