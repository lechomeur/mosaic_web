<?php
namespace Controller;
use daos\associationDaos;
class associationController{
    public function __construct(){
        
    }
    public function getAllAssociations($associationId = 0) {
        // Appel de la méthode dans le DAO
        $associations = associationDaos::getAllAssoc($associationId);

        // Vérifier si des associations ont été récupérées
        if (empty($associations)) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'Aucune association trouvée.'
            ]);
            return;
        }
            return $associations;
        
    }
}
?>