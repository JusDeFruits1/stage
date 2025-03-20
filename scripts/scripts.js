$(document).on("click", ".delete-btn", function () {
  vacataireIdToDelete = $(this).closest("tr").data("id");
  console.log(vacataireIdToDelete);
});

$(document).on("click", ".confirm-delete", function () {
  if (vacataireIdToDelete) {
    $.ajax({
      url: "deleteVacataire.php",
      method: "POST",
      data: { id: vacataireIdToDelete },
      success: function (response) {
        $(`tr[data-id="${vacataireIdToDelete}"]`).remove();
        $("deleteModal").modal("hide");
        vacataireIdToDelete = null;
        alert("Vacataire supprimé avec succès");
      },
      error: function (xhr, status, error) {
        alert("Erreur lors de la suppression du vacataire");
        console.error(error);
      },
    });
  }
});

$(document).on("click", ".addVacataire", function () {
  $.ajax({
    url: "addVacataire.php",
    method: "POST",
    data: {
      nom: $("#nom").val(),
      prenom: $("#prenom").val(),
      email: $("#email").val(),
      telephone: $("#telephone").val(),
    },
  });
});

document.addEventListener("DOMContentLoaded", function () {
  const viewCoursesButtons = document.querySelectorAll(".view-courses-btn");
  viewCoursesButtons.forEach((button) => {
    button.addEventListener("click", function () {
      const vacataireId = this.getAttribute("data-id");
      loadCourseDetails(vacataireId);
    });
  });
  function loadCourseDetails(vacataireId) {
    const coursesDetails = document.getElementById("coursesDetails");
    coursesDetails.innerHTML =
      '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Chargement...</span></div>';
    fetch(`get_vacataire_courses.php?id=${vacataireId}`)
      .then((response) => response.json())
      .then((data) => {
        if (data.length === 0) {
          coursesDetails.innerHTML =
            '<p class="text-center">Ce vacataire n\'enseigne aucun cours.</p>';
          return;
        }

        let html =
          '<div class="table-responsive"><table class="table table-striped">';
        html +=
          "<thead><tr><th>Nom du cours</th><th>Description</th><th>Nombre d'heures</th></tr></thead>";
        html += "<tbody>";

        data.forEach((course) => {
          html += `<tr>
                      <td>${course.nom}</td>
                      <td>${course.description}</td>
                      <td>${course.nb_heures}</td>
                  </tr>`;
        });

        html += "</tbody></table></div>";
        coursesDetails.innerHTML = html;
      })
      .catch((error) => {
        coursesDetails.innerHTML =
          '<p class="text-danger">Une erreur est survenue lors du chargement des cours.</p>';
        console.error("Erreur:", error);
      });
  }
});
