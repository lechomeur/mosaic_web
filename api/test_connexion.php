<?php
require_once "daos/PdoBD.php";

$db = daos\PdoBD::getMonPdo();

if ($db) {
    echo json_encode(["success" => true, "message" => "Connexion réussie !"]);
} else {
    echo json_encode(["success" => false, "message" => "Échec de la connexion"]);
}
?>
