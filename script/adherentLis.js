// Activer le plugin datalabels
Chart.register(ChartDataLabels);

let ageChart, genderChart, totalCotisationChart, paymentStatusChart;

document.addEventListener("DOMContentLoaded", function () {
    // Récupérer les données depuis l'élément <script>
    const chartData = JSON.parse(document.getElementById('chart-data').textContent);

    // Utiliser les données dans vos graphiques
    const ageData = chartData.ageGroups;
    const genderData = chartData.genderCount;
    const totalCotisation = chartData.totalCotisation;
    const paymentStatusData = chartData.paymentStatus;

    // Filtrer les données pour ignorer les valeurs de 0
    const filteredAgeData = filterData(Object.keys(ageData), Object.values(ageData));
    const filteredGenderData = filterData(Object.keys(genderData), Object.values(genderData));
    const filteredPaymentStatusData = filterData(Object.keys(paymentStatusData), Object.values(paymentStatusData));

    // Initialiser les graphiques avec les données filtrées
    ageChart = createChart('ageChart', 'pie', filteredAgeData.labels, filteredAgeData.data);
    genderChart = createChart('genderChart', 'pie', filteredGenderData.labels, filteredGenderData.data);
    totalCotisationChart = createChart('totalCotisationChart', 'pie', ['Cotisations totales'], [totalCotisation]);
    paymentStatusChart = createChart('paymentStatusChart', 'pie', filteredPaymentStatusData.labels, filteredPaymentStatusData.data);

    // Écouter les changements de filtres
    document.getElementById('year').addEventListener('change', updateCharts);
    document.getElementById('cotis').addEventListener('change', updateCharts);
});

function updateCharts() {
    const year = document.getElementById('year').value;
    const cotis = document.getElementById('cotis').value;
    const associationId = document.getElementById('titre-adhesions').getAttribute('data-association-id');

    // Envoyer une requête AJAX pour récupérer les nouvelles données
    fetch(`?controller=adhesion&action=getAllAdhesion&association_id=${associationId}`)
        .then(response => response.json())
        .then(data => {
            // Filtrer les données pour ignorer les valeurs de 0
            const filteredAgeData = filterData(Object.keys(data.ageGroups), Object.values(data.ageGroups));
            const filteredGenderData = filterData(Object.keys(data.genderCount), Object.values(data.genderCount));
            const filteredPaymentStatusData = filterData(Object.keys(data.paymentStatus), Object.values(data.paymentStatus));

            // Mettre à jour les graphiques avec les nouvelles données
            updateChart(ageChart, filteredAgeData.labels, filteredAgeData.data);
            updateChart(genderChart, filteredGenderData.labels, filteredGenderData.data);
            updateChart(totalCotisationChart, ['Cotisations totales'], [data.totalCotisation]);
            updateChart(paymentStatusChart, filteredPaymentStatusData.labels, filteredPaymentStatusData.data);
        })
        .catch(error => console.error('Erreur :', error));
}

function filterData(labels, data) {
    const filteredLabels = [];
    const filteredData = [];

    labels.forEach((label, index) => {
        if (data[index] > 0) { // Ne garder que les valeurs > 0
            filteredLabels.push(label);
            filteredData.push(data[index]);
        }
    });

    return { labels: filteredLabels, data: filteredData };
}

function createChart(canvasId, type, labels, data) {
    return new Chart(document.getElementById(canvasId), {
        type: type,
        data: {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0'],
                label: 'Valeurs',
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                },
                datalabels: {
                    color: '#000',
                    font: {
                        weight: 'bold',
                        size: 14,
                    },
                    formatter: (value) => {
                        return value; // Afficher la valeur exacte
                    },
                },
            },
        
        },
    });
}

function updateChart(chart, labels, data) {
    chart.data.labels = labels; // Mettre à jour les labels
    chart.data.datasets[0].data = data; // Mettre à jour les données
    chart.update(); // Actualiser le graphique
}

let associationNom = document
  .getElementById("titre-adhesions")
  .getAttribute("data-association-nom");

document.getElementById("year").addEventListener("change", function () {
  let selectedYear = this.value;
  document.getElementById(
    "titre-adhesions"
  ).textContent = `Liste des adhésions - Année ${selectedYear} de ${associationNom}`;
});
$(document).ready(function () {
  // Quand l'utilisateur change une option dans les filtres ou tape dans le champ de recherche
  $("#form-filtre select, #form-filtre input").on("change input", function () {
    var formData = $("#form-filtre").serialize(); // Sérialiser les données du formulaire

    // Envoi AJAX de la requête
    $.ajax({
      url: "", // Envoie la requête à la même page
      type: "GET",
      data: formData, // Envoie les données du formulaire
      success: function (response) {
        // Récupère le nouveau contenu du tableau à partir de la réponse du serveur
        var newTableBody = $(response).find("#table-adhesions tbody").html();
        updateCharts() ;
        // Remplace le corps du tableau avec les nouvelles données
        $("#table-adhesions tbody").html(newTableBody);
      },
      error: function (xhr, status, error) {
        alert("Erreur lors de la mise à jour des adhésions.");
      },
    });
  });
});

function deleteAdhesion(event, element) {
  console.log("Fonction deleteAdhesion appelée"); // Vérifier que la fonction est appelée
  event.preventDefault();

  const adhesionId = element.getAttribute("data-adhesion-id");
  const associationId = element.getAttribute("data-association-id");

  if (!confirm("Êtes-vous sûr de vouloir supprimer cette adhésion ?")) {
    return;
  }

  $.ajax({
    url: `?controller=adhesion&action=delete&adhesion_id=${adhesionId}&association_id=${associationId}`,
    type: "GET",
    success: function (response) {
      console.log("Réponse du serveur :", response); // Afficher la réponse pour le débogage

      // Vérifier si la réponse est du JSON valide
      showCustomToast("Adhésion supprimée avec succès !");
      loadAdhesions(associationId);
      updateCharts() ;
    },
    error: function (xhr, status, error) {
      console.error("Erreur AJAX :", status, error);
      alert("Erreur lors de la suppression de l'adhésion !");
    },
  });
}
function loadAdhesions(associationId) {
  $.ajax({
    url: "",
    type: "GET",
    success: function (data) {
      console.log("Réponse du serveur :", data);
      $("#section-adhesion tbody").html($(data).find("tbody").html());
      // 🔥 Afficher le toast custom
    },
    error: function () {
      alert("Erreur lors du chargement des adhésions !");
    },
  });
}

function showCustomToast(message) {
  const toast = document.getElementById("customToast");
  toast.textContent = message;
  toast.classList.add("show-toast");

  setTimeout(() => {
    toast.classList.remove("show-toast");
  }, 4000); // Disparaît après 4 secondes
}
$(document).ready(function () {
  // Activer l'édition
  $(document).on("click", ".edit-link", function (e) {
    e.preventDefault();
    var adherentId = $(this).data("adherent-id");
    var row = $("#row-" + adherentId);

    // Remplacer le texte par des champs de formulaire
    row.find(".editable").each(function () {
      var field = $(this).data("field");
      var value = $(this).text().trim(); // Valeur actuelle de la cellule

      if (field === "cheque_espece") {
        // Créer un <select> pour "cheque_espece"
        var options = [
          { value: "Chèque", text: "Chèque" },
          { value: "Espèce", text: "Espèce" },
        ];
        var select = '<select class="form-control" name="' + field + '">';
        options.forEach(function (option) {
          select +=
            '<option value="' +
            option.value +
            '" ' +
            (option.value === value ? "selected" : "") +
            ">" +
            option.text +
            "</option>";
        });
        select += "</select>";
        $(this).html(select);
      } else if (field === "genre") {
        // Créer un <select> pour "genre"
        var options = [
          { value: "Homme", text: "Homme" },
          { value: "Femme", text: "Femme" },
          { value: "Autre", text: "Autre" },
        ];
        var select = '<select class="form-control" name="' + field + '">';
        options.forEach(function (option) {
          select +=
            '<option value="' +
            option.value +
            '" ' +
            (option.value === value ? "selected" : "") +
            ">" +
            option.text +
            "</option>";
        });
        select += "</select>";
        $(this).html(select);
      } else {
        // Pour les autres champs, utiliser un <input>
        $(this).html(
          '<input type="text" class="form-control" name="' +
            field +
            '" value="' +
            value +
            '">'
        );
      }
    });

    // Remplacer le bouton "Modifier" par un bouton "Enregistrer"
    $(this).replaceWith(
      '<a href="#" class="save-link" title="Enregistrer" data-adherent-id="' +
        adherentId +
        '">' +
        '<i class="fas fa-save"></i>' +
        "</a>"
    );
  });

  // Enregistrer les modifications
  $(document).on("click", ".save-link", function (e) {
    e.preventDefault();
    var adherentId = $(this).data("adherent-id");
    var row = $("#row-" + adherentId);

    // Récupérer les valeurs des champs
    let data = {};
    row.find(".editable input, .editable select").each(function () {
      var field = $(this).attr("name");
      var value = $(this).val();
      data[field] = value;
    });

    console.log("Données envoyées :", data); // 🔥 Vérification avant envoi

    // Envoyer les données via AJAX
    $.ajax({
      url: "?controller=adhesion&action=updateAdherent&id=" + adherentId,
      type: "POST",
      data: data,
      success: function (response) {
        console.log("Réponse serveur :", response); // 🔥 Vérification de la réponse
        updateCharts() ;
        loadAdhesions();
        // Afficher un message de succès
        showCustomToast(response.message || "Mise à jour réussie !");
      },
      error: function (xhr, status, error) {
        console.error("Erreur AJAX :", status, error, xhr.responseText);
        alert("Erreur lors de la mise à jour des informations !");
      },
    });
  });

  // Fonction pour afficher un toast personnalisé
  function showCustomToast(message) {
    const toast = document.getElementById("customToast");
    toast.textContent = message;
    toast.classList.add("show-toast");

    setTimeout(() => {
      toast.classList.remove("show-toast");
    }, 4000); // Disparaît après 4 secondes
  }
});
$(document).ready(function () {
  // Afficher la modale de téléversement
  $(document).on("click", ".upload-link", function (e) {
    e.preventDefault();
    var adherentId = $(this).data("adherent-id");
    var modal = $("#upload-modal-" + adherentId);

    // Afficher la modale
    modal.show();
  });

  // Fermer la modale
  $(document).on("click", ".close-modal, .btn-cancel-upload", function () {
    var modal = $(this).closest(".upload-modal");
    modal.hide();
  });

  // Envoyer le fichier via AJAX
  $(document).on("submit", ".upload-form", function (e) {
    e.preventDefault();
    var form = $(this);
    var adherentId = form.closest(".upload-modal").attr("id").replace("upload-modal-", "");
    var formData = new FormData(form[0]); // Créer un objet FormData

    // Envoyer les données via AJAX
    $.ajax({
      url: "?controller=adhesion&action=uploadAttestation&id=" + adherentId,
      type: "POST",
      data: formData,
      processData: false, // Ne pas traiter les données
      contentType: false, // Ne pas définir le type de contenu
      success: function (response) {
        console.log("Réponse serveur :", response);
          // Afficher un message de succès
          updateCharts() ;
          showCustomToast(
            response.message || "Attestation téléversée avec succès !"
          );
          // Fermer la modale
         $("#upload-modal-" + adherentId).hide();
      },
      error: function (xhr, status, error) {
        console.error("Erreur AJAX :", status, error, xhr.responseText);
        alert("Erreur lors du téléversement de l'attestation !");
      },
    });
  });

  // Fonction pour afficher un toast personnalisé
  function showCustomToast(message) {
    const toast = document.getElementById("customToast");
    toast.textContent = message;
    toast.classList.add("show-toast");

    setTimeout(() => {
      toast.classList.remove("show-toast");
    }, 4000); // Disparaît après 4 secondes
  }
});
$(document).ready(function() {
  // Gestion du clic sur le lien "Exporter la liste"
  $("#downloadLink").click(function(e) {
      e.preventDefault();

      // Récupérer les valeurs du filtre
      let year = $("#year").val();    // Valeur sélectionnée pour l'année
      let cotis = $("#cotis").val();  // Valeur sélectionnée pour le statut de paiement
      let associationId = $(this).data("association-id"); // Récupérer l'ID de l'association depuis l'attribut data

      // Vérifier si associationId est valide
      if (!associationId) {
          alert("Erreur : ID de l'association introuvable.");
          return;
      }

      // Construction de l'URL d'export avec les paramètres
      let url = "?controller=adhesion&action=export&association_id=" + encodeURIComponent(associationId);
      
      if (year) {
          url += "&year=" + encodeURIComponent(year);
      }
      if (cotis) {
          url += "&cotis=" + encodeURIComponent(cotis);
      }

      // Rediriger le navigateur vers l'URL d'export
      window.location.href = url;
  });
});


