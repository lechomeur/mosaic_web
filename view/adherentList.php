<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <!-- Lien vers les icônes Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Lien vers le fichier CSS -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <link rel="stylesheet" href="style/adhesion.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>
</head>
<?php
include __DIR__ . '/../view/header.php';
?>

<body>
    <section class="home-section" id="section-adhesion">
        <div class="home-content">
            <h2 id="titre-adhesions" data-association-id="<?= $association->getId() ?>" data-association-nom="<?= htmlspecialchars($association->getNom()) ?>">
                Liste des adhésions - Année <?= htmlspecialchars($year) ?> de <?= htmlspecialchars($association->getNom()) ?>
            </h2>
            <script type="application/json" id="chart-data">
                {
                    "ageGroups": <?= json_encode($ageGroups) ?>,
                    "genderCount": <?= json_encode($genderCount) ?>,
                    "totalCotisation": <?= json_encode($totalCotisation) ?>,
                    "paymentStatus": <?= json_encode($paymentStatus) ?>
                }
            </script>
           
            <form id="form-filtre" method="GET">
                <input type="hidden" name="controller" value="adhesion">
                <input type="hidden" name="action" value="getAllAdhesion">
                <!-- Filtre par année -->
                <label for="year">Filtrer par année :</label>
                <select name="year" id="year">
                    <?php

                    use Entities\adherent;

                    foreach ($annees as $an): ?>
                        <option value="<?= $an ?>" <?= ($an == $year) ? 'selected' : '' ?>><?= $an ?></option>
                    <?php endforeach; ?>
                </select>
                <!-- Filtre par statut de paiement -->
                <select name="cotis" id="cotis">
                    <option value="">Statut de paiement</option>
                    <option value="Payé" <?= ($cotis === 'Payé') ? 'selected' : '' ?>>Payé</option>
                    <option value="Impayé" <?= ($cotis === 'Impayé') ? 'selected' : '' ?>>Non payé</option>
                </select>
                <!-- Lien Ajouter -->
                <div class="actions-container">
                    <a href="#" id="addLink" data-association-id="<?= $association->getId() ?> " class="action-link" title="Ajouter une adhésion">
                        <img src="image/person-fill-add.svg" alt="Ajouter une adhésion">
                    </a>
                    <!-- Lien Exporter la liste -->
                    <a href="#" id="downloadLink" data-association-id="<?= $association->getId() ?>" class="action-link" title="Exporter la liste">
                        <img src="image/file-earmark-arrow-down.svg" alt="Exporter la liste">
                    </a>


                </div>
            </form>
            <form id="form-filtre" method="GET">
                <input type="hidden" name="controller" value="adhesion">
                <input type="hidden" name="action" value="searchAdhesions">
                <!--   < <div class="search-container"> 
                    <input type="text" id="search" name="search" placeholder="Rechercher" value="<?= htmlspecialchars($search ?? '') ?>">
            <i class="fas fa-search search-icon" id="searchButton"></i>   -->
        </div>
        </form>
        <div id="customToast" class="custom-toast">L'adhérent a été ajouté avec succès !</div>

        <table id="table-adhesions">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Genre</th>
                    <th>Age</th>
                    <th>Mail</th>
                    <th>Numéro</th>
                    <th>Date d'adhésion</th>
                    <th>Cotisation</th>
                    <th>Paiement</th>
                    <th>Statut</th>
                    <th>Attestation</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($adhesions)): ?>
                    <?php foreach ($adhesions as $adherent): ?>
                        <tr id="row-<?= $adherent->getId() ?>">
                            <!-- Affichage normal -->
                            <td class="editable" data-field="nom"><?= htmlspecialchars($adherent->getNom() ?? '') ?></td>
                            <td class="editable" data-field="prenom"><?= htmlspecialchars($adherent->getPrenom() ?? '') ?></td>
                            <td class="editable" data-field="genre"><?= htmlspecialchars($adherent->getGenre() ?? '') ?></td>
                            <td class="editable" data-field="age"><?= htmlspecialchars($adherent->getAge() ?? '') ?></td>
                            <td class="editable" data-field="mail" title="<?= htmlspecialchars($adherent->getMail() ?? '') ?>">
                                <?= htmlspecialchars($adherent->getMail() ?? '') ?>
                            </td>
                            <td class="editable" data-field="telephone"><?= htmlspecialchars($adherent->getTelephone() ?? '') ?></td>
                            <td class="editable" data-field="date_adhesion"><?= htmlspecialchars($adherent->getDateAdhesion() ?? '') ?></td>
                            <td class="editable" data-field="montant"><?= htmlspecialchars($adherent->getMontant() ?? '') ?></td>
                            <td class="editable" data-field="cheque_espece"><?= htmlspecialchars($adherent->getChequeEspece() ?? '') ?></td>
                            <td class="<?= $adherent->getMontant() > 0 ? 'paye' : 'non-paye' ?>">
                                <?= $adherent->getMontant() > 0 ? 'Payé' : 'Non payé' ?>
                            </td>
                            <td>
                                <a href="<?= !empty($adherent->getAttestation()) ? '?controller=adhesion&action=attestation&id=' . $adherent->getId() : '#' ?>"
                                    title="Voir l'attestation"
                                    class="attestation-link <?= empty($adherent->getAttestation()) ? 'disabled' : '' ?>">
                                    <i class="fas fa-eye"></i>
                                </a>

                                <!-- Bouton pour téléverser une nouvelle attestation -->
                                <a href="#" class="upload-link" title="Téléverser une attestation" data-adherent-id="<?= $adherent->getId() ?>">
                                    <i class="fas fa-upload"></i>
                                </a>

                                <!-- Modale pour le formulaire de téléversement -->
                                <div id="upload-modal-<?= $adherent->getId() ?>" class="upload-modal" style="display: none;">
                                    <div class="modal-contenu">
                                        <span class="close-modal">&times;</span>
                                        <form class="upload-form" enctype="multipart/form-data">
                                            <!-- Afficher le nom du fichier actuel -->
                                            <div id="current-file-<?= $adherent->getId() ?>" class="current-file">
                                                Fichier actuel : <?= htmlspecialchars($adherent->getAttestation() ?? 'Aucun fichier') ?>
                                            </div>
                                            <input type="file" name="attestation" class="attestation-file" accept=".pdf,.jpg,.png">
                                            <button type="submit" class="btn-save-upload">Enregistrer</button>
                                            <button type="button" class="btn-cancel-upload">Annuler</button>
                                        </form>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <!-- Bouton Modifier -->
                                <a href="#" class="edit-link" title="Modifier" data-adherent-id="<?= $adherent->getId() ?>">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <!-- Bouton Supprimer -->
                                <a href="#"
                                    class="delete-link"
                                    title="Supprimer"
                                    data-adhesion-id="<?= $adherent->getId() ?>"
                                    data-association-id="<?= $association->getId() ?>"
                                    onclick="deleteAdhesion(event, this);">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="12">Aucune adhésion trouvée pour cette année.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <div class="charts-container">
                <div class="chart-card">
                    <h3>Tranches d'âge</h3>
                    <canvas id="ageChart"></canvas>
                </div>
                <div class="chart-card">
                    <h3>Répartition par genre</h3>
                    <canvas id="genderChart"></canvas>
                </div>
                <div class="chart-card">
                    <h3>Cotisations totales</h3>
                    <canvas id="totalCotisationChart"></canvas>
                </div>
                <div class="chart-card">
                    <h3>Statut de paiement</h3>
                    <canvas id="paymentStatusChart"></canvas>
                </div>
            </div>
    </section>
    <script src="script/header.js"></script>
    <script src="script/adherentLis.js"></script>
  
</body>

</html>