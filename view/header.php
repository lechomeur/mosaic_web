<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <!-- Lien vers les icônes Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <!-- Lien vers le fichier CSS -->
  <link rel="stylesheet" href="style/menu.css">
  <link rel="stylesheet" href="style/header.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>
  <!-- Barre latérale (Sidebar) -->
  <div class="sidebar">
    <div class="logo-content">
      <div class="logo">
        <i class="fas fa-chess-board"></i>
        <div class="logo-name">Mosaic</div>
      </div>
      <i class="fas fa-bars" id="btn"></i>
    </div>
    <ul class="nav-list">
      <li>
        <a href="index.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' && !isset($_GET['controller']) ? 'active' : ''; ?>">
          <i class="fas fa-home"></i>
          <span class="links-name">Accueil</span>
        </a>
        <span class="tooltip">Accueil</span>
      </li>
      <li>
        <a href="index.php?controller=adhesion" class="<?php echo isset($_GET['controller']) && $_GET['controller'] == 'adhesion' ? 'active' : ''; ?>">
          <i class="fas fa-file-signature"></i>
          <span class="links-name">Adhésion</span>
        </a>
        <span class="tooltip">Adhésion</span>
      </li>
      <li>
        <a href="index.php?controller=evenement" class="<?php echo isset($_GET['controller']) && $_GET['controller'] == 'evenement' ? 'active' : ''; ?>">
          <i class="fas fa-calendar-alt"></i>
          <span class="links-name">Événements</span>
        </a>
        <span class="tooltip">Événements</span>
      </li>
      <li>
        <a href="index.php?controller=stagiaire" class="<?php echo isset($_GET['controller']) && $_GET['controller'] == 'stagiaire' ? 'active' : ''; ?>">
          <i class="fas fa-user-graduate"></i>
          <span class="links-name">Stagiaire</span>
        </a>
        <span class="tooltip">Stagiaire</span>
      </li>
      <li>
        <a href="index.php?controller=service-civique" class="<?php echo isset($_GET['controller']) && $_GET['controller'] == 'service-civique' ? 'active' : ''; ?>">
          <i class="fas fa-hands-helping"></i>
          <span class="links-name">Service Civique</span>
        </a>
        <span class="tooltip">Service Civique</span>
      </li>
      <li>
        <a href="javascript:history.back()">
          <i class="fas fa-arrow-left"></i>
          <span class="links-name">Retour</span>
        </a>
        <span class="tooltip">Retour</span>
      </li>
      <li>
        <a href="logout.php" class="logout">
          <i class="fas fa-sign-out-alt"></i>
          <span class="links-name">Déconnexion</span>
        </a>
        <span class="tooltip">Déconnexion</span>
      </li>
    </ul>
  </div>
  <!-- Boîte modale pour afficher le formulaire -->
  <div id="adhesionModal" class="modal">
    <div class="modal-content">
      <div id="modal-body">
      </div>
    </div>
  </div>
  <script src="script/header.js"></script>
  <script>
    function updateCharts() {
      const year = document.getElementById('year').value;
      const cotis = document.getElementById('cotis').value;
      const associationId = document.getElementById('titre-adhesions').getAttribute('data-association-id');

      fetch(`?controller=adhesion&action=getFilteredData&association_id=${associationId}&year=${year}&cotis=${cotis}`)
        .then(response => response.json())
        .then(data => {
          // Mettre à jour les graphiques avec les nouvelles données
          updateChart(ageChart, Object.keys(data.ageGroups), Object.values(data.ageGroups));
          updateChart(genderChart, Object.keys(data.genderCount), Object.values(data.genderCount));
          updateChart(totalCotisationChart, ['Cotisations totales'], [data.totalCotisation]);
          updateChart(paymentStatusChart, Object.keys(data.paymentStatus), Object.values(data.paymentStatus));
        })
        .catch(error => console.error('Erreur :', error));
    }
    $(document).ready(function() {
      // Quand on clique sur "Ajouter une adhésion"
      $("#addLink").click(function(e) {
        e.preventDefault(); // Empêche le lien de recharger la page
        console.log("addLink cliqué"); // Vérifie que l'événement se déclenche


        // Récupère l'ID de l'association (par exemple depuis un attribut data)
        var associationId = $(this).data("association-id");
        $.ajax({
          url: "?controller=adhesion&action=formAdd&association_id=" + associationId,
          type: "GET",
          success: function(response) {
            $("#modal-body").html(response); // Insère le formulaire dans la modale
            $("#adhesionModal").fadeIn(); // Affiche la modale
          },
          error: function() {
            alert("Erreur lors du chargement du formulaire !");
          }
        });
      });

      $(".close").click(function() {
        $("#adhesionModal").fadeOut();
      });

      // Fermer la modale en cliquant en dehors
      $(window).click(function(e) {
        if (e.target.id === "adhesionModal") {
          $("#adhesionModal").fadeOut();
        }
      });
    });

    $(document).ready(function() {
      $("#loginForm").off('submit').on('submit', function(e) {
        console.log("loginForm"); // Vérifiez combien de fois ce message apparaît

        e.preventDefault(); // Empêche le rechargement de la page
        let associationId = $("#associationId").val();

        let formData = $(this).serialize(); // Récupère les données du formulaire

        $.ajax({
          url: "index.php?controller=adhesion&action=add&association_id=" + associationId,
          type: "POST",
          data: formData,
          success: function(response) {
            // Fermer la modale si nécessaire

            // Mettre à jour la liste des adhésions sans recharger
            refreshAdhesionList();

            $("#loginForm").trigger("reset"); // Réinitialise le formulaire
            $("#messageContainer").html('<p class="success">L\'adhérent a été ajouté avec succès !</p>');
          },
          error: function() {
            alert("Erreur lors de l'ajout de l'adhérent !");
          }
        });
      });

      function showCustomToast() {
        let toast = document.getElementById("customToast");
        toast.classList.add("show-toast");

        setTimeout(() => {
          toast.classList.remove("show-toast");
        }, 4000); // Disparaît après 4 secondes
      }

      function refreshAdhesionList() {
        let associationId = $("#associationId").val();

        $.ajax({
          url: "",
          type: "GET",
          success: function(data) {
            console.log("Réponse du serveur :", data);
            $("#section-adhesion tbody").html($(data).find("tbody").html());
            // 🔥 Afficher le toast custom
            showCustomToast();
          },
          error: function() {
            alert("Erreur lors du chargement des adhésions !");
          }
        });
      }

    });
  </script>
</body>

</html>