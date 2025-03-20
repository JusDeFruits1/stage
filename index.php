<?php
include_once('header.php');
include_once('functions.php');
include_once('database/connectDB.php');
include_once('database/extractDataFromDB.php');
$getVacataires = getDataFromDB($db, 'SELECT vacataire.id, vacataire.nom, vacataire.prenom, vacataire.tel, vacataire.email
, vacataire.profession, cours.nom as cours_enseignes, cours.description as cours_desc, enseigne.nb_heures as cours_heures FROM `vacataire` 
LEFT JOIN enseigne on vacataire.id = enseigne.id_vacataire 
LEFT JOIN cours on enseigne.id_cours = cours.id');

?>
<?php
include_once('database/connectDB.php');

// Traitement du formulaire d'édition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $id = $_POST['id'] ?? '';
    $nom = $_POST['nom'] ?? '';
    $prenom = $_POST['prenom'] ?? '';
    $profession = $_POST['profession'] ?? '';
    $tel = $_POST['tel'] ?? '';
    $email = $_POST['email'] ?? '';

    $errors = [];
    // Validation des champs du formulaire
    if (empty($nom)) {
        $errors['nom'] = 'Le nom est requis';
    } elseif (!preg_match("/^[a-zA-Z-' ]*$/", $nom)) {
        $errors['nom'] = 'Seuls les lettres et les espaces sont autorisés';
    }

    if (empty($prenom)) {
        $errors['prenom'] = 'Le prénom est requis';
    } elseif (!preg_match("/^[a-zA-Z-' ]*$/", $prenom)) {
        $errors['prenom'] = 'Seuls les lettres et les espaces sont autorisés';
    }

    if (empty($tel)) {
        $errors['tel'] = 'Le numéro de téléphone est requis';
    }

    if (empty($email)) {
        $errors['email'] = 'L\'adresse email est requise';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'L\'adresse email n\'est pas valide';
    }
    // Si aucune erreur, mise à jour du vacataire
    if (empty($errors)) {
        try {
            $db->beginTransaction();

            $stmt = $db->prepare("UPDATE vacataire SET nom = ?, prenom = ?, profession = ?, tel = ?, email = ? WHERE id = ?");
            $result = $stmt->execute([$nom, $prenom, $profession, $tel, $email, $id]);

            $db->commit();

            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'Vacataire mis à jour avec succès',
                'data' => [
                    'id' => $id,
                    'nom' => $nom,
                    'prenom' => $prenom,
                    'profession' => $profession,
                    'tel' => $tel,
                    'email' => $email
                ]
            ]);
            exit;

        } catch (PDOException $e) {
            $db->rollBack();
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour: ' . $e->getMessage(),
                'errors' => ['general' => 'Une erreur s\'est produite lors de la mise à jour']
            ]);
            exit;
        }
    } else {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Veuillez corriger les erreurs du formulaire',
            'errors' => $errors
        ]);
        exit;
    }
}
// Traitement de la récupération des données d'un vacataire
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'getVacataire') {
    $id = $_GET['id'] ?? '';

    if (!empty($id)) {
        try {
            $stmt = $db->prepare("SELECT * FROM vacataire WHERE id = ?");
            $stmt->execute([$id]);
            $vacataire = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($vacataire) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'data' => $vacataire
                ]);
            } else {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => 'Vacataire non trouvé'
                ]);
            }
        } catch (PDOException $e) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Erreur lors de la récupération: ' . $e->getMessage()
            ]);
        }
        exit;
    }
}
?>

<div class="row mt-5">
    <div class="col-12">
        <h2 class="text-center text-dark">Liste des Vacataires</h2>
        <div class="table-responsive">
            <table class="table table-hover table-striped table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>Vacataire</th>
                        <th>Profession</th>
                        <th>Cours</th>
                        <th>Tél</th>
                        <th>Email</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($getVacataires as $vacataire): ?>
                        <tr data-id="<?php echo htmlspecialchars($vacataire["id"]) ?>">
                            <td class="vacataire-name">
                                <?php echo htmlspecialchars($vacataire["nom"]) . ' ' . htmlspecialchars($vacataire["prenom"]) ?>
                            </td>
                            <td class="vacataire-profession">
                                <?php echo htmlspecialchars($vacataire["profession"]) ?>
                            </td>
                            <td class="vacataire-cours">
                                <?php echo htmlspecialchars($vacataire["cours_enseignes"]) ?>
                            </td>
                            <td class="vacataire-tel">
                                <?php echo htmlspecialchars($vacataire["tel"]) ?>
                            </td>
                            <td class="vacataire-email">
                                <?php echo htmlspecialchars($vacataire["email"]) ?>
                            </td>
                            <td>
                                <button type="button" class="btn btn-primary view-courses-btn"
                                    data-id="<?php echo htmlspecialchars($vacataire["id"]) ?>" data-bs-toggle="modal"
                                    data-bs-target="#coursesModal">
                                    Voir cours
                                </button>
                                <button type="button" class="btn btn-warning edit-btn" data-bs-toggle="modal"
                                    data-bs-target="#editModal">
                                    Modifier
                                </button>
                                <button type="button" class="btn btn-danger delete-btn" data-bs-toggle="modal"
                                    data-bs-target="#deleteModal">
                                    Supprimer
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal pour la suppression -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h4 class="modal-title" id="deleteModalLabel">Supprimer un vacataire</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body">
                <p>Voulez-vous vraiment supprimer ce vacataire ?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger confirm-delete" data-bs-dismiss="modal">Supprimer</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal pour les cours -->
<div class="modal fade" id="coursesModal" tabindex="-1" aria-labelledby="coursesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h4 class="modal-title" id="coursesModalLabel">Cours enseignés</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body" id="coursesDetails">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Chargement...</span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>
<!-- Modal pour l'édition -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h4 class="modal-title" id="editModalLabel">Modifier un vacataire</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body">
                <div id="editFormErrors" class="alert alert-danger" style="display: none;"></div>
                <div id="editFormSuccess" class="alert alert-success" style="display: none;"></div>

                <form id="editVacataireForm" method="post">
                    <input type="hidden" id="editId" name="id">
                    <input type="hidden" name="action" value="edit">

                    <div class="mb-3">
                        <label for="editNom" class="form-label">Nom</label>
                        <input type="text" class="form-control" id="editNom" name="nom">
                        <div class="invalid-feedback" id="nomError"></div>
                    </div>

                    <div class="mb-3">
                        <label for="editPrenom" class="form-label">Prénom</label>
                        <input type="text" class="form-control" id="editPrenom" name="prenom">
                        <div class="invalid-feedback" id="prenomError"></div>
                    </div>

                    <div class="mb-3">
                        <label for="editProfession" class="form-label">Profession</label>
                        <input type="text" class="form-control" id="editProfession" name="profession">
                        <div class="invalid-feedback" id="professionError"></div>
                    </div>

                    <div class="mb-3">
                        <label for="editTel" class="form-label">Téléphone</label>
                        <input type="tel" class="form-control" id="editTel" name="tel">
                        <div class="invalid-feedback" id="telError"></div>
                    </div>

                    <div class="mb-3">
                        <label for="editEmail" class="form-label">Email</label>
                        <input type="email" class="form-control" id="editEmail" name="email">
                        <div class="invalid-feedback" id="emailError"></div>
                    </div>

                    <div class="mb-3">
                        <label for="editCours" class="form-label">Cours enseignés</label>
                        <input type="text" class="form-control" id="editCours" name="cours" disabled>                        
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="saveVacataire">Enregistrer</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Gestion des boutons "Voir cours"
        const viewCoursesButtons = document.querySelectorAll('.view-courses-btn');
        viewCoursesButtons.forEach(button => {
            button.addEventListener('click', function () {
                const vacataireId = this.getAttribute('data-id');
                loadCourseDetails(vacataireId);
            });
        });

        // Fonction pour charger les détails des cours
        function loadCourseDetails(vacataireId) {
            const coursesDetails = document.getElementById('coursesDetails');
            coursesDetails.innerHTML = '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Chargement...</span></div>';

            // Requête AJAX pour obtenir les détails des cours
            fetch(`get_vacataire_courses.php?id=${vacataireId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.length === 0) {
                        coursesDetails.innerHTML = '<p class="text-center">Ce vacataire n\'enseigne aucun cours.</p>';
                        return;
                    }

                    let html = '<div class="table-responsive"><table class="table table-striped">';
                    html += '<thead><tr><th>Nom du cours</th><th>Description</th><th>Nombre d\'heures</th></tr></thead>';
                    html += '<tbody>';

                    data.forEach(course => {
                        html += `<tr>
                        <td>${course.nom}</td>
                        <td>${course.description}</td>
                        <td>${course.nb_heures}</td>
                    </tr>`;
                    });

                    html += '</tbody></table></div>';
                    coursesDetails.innerHTML = html;
                })
                .catch(error => {
                    coursesDetails.innerHTML = '<p class="text-danger">Une erreur est survenue lors du chargement des cours.</p>';
                    console.error('Erreur:', error);
                });
        }

        // Gestion des boutons "Modifier"
        const editButtons = document.querySelectorAll('.edit-btn');
        editButtons.forEach(button => {
            button.addEventListener('click', function () {
                const row = this.closest('tr');
                const vacataireId = row.getAttribute('data-id');
                const fullName = row.querySelector('.vacataire-name').textContent.trim();

                // Séparation du nom et du prénom
                const nameParts = fullName.split(' ');
                let prenom = nameParts.pop();
                let nom = nameParts.join(' ');

                const profession = row.querySelector('.vacataire-profession').textContent.trim();
                const tel = row.querySelector('.vacataire-tel').textContent.trim();
                const email = row.querySelector('.vacataire-email').textContent.trim();

                let cours = row.querySelector('.vacataire-cours').textContent.trim();
                if (cours.includes('Aucun cours assigné')) {
                    cours = '';
                }
                resetEditForm();

                // Préremplir le formulaire avec les données du vacataire
                document.getElementById('editId').value = vacataireId;
                document.getElementById('editNom').value = nom;
                document.getElementById('editPrenom').value = prenom;
                document.getElementById('editProfession').value = profession;
                document.getElementById('editTel').value = tel;
                document.getElementById('editEmail').value = email;
                document.getElementById('editCours').value = cours;
            });
        });

        // Gestion du bouton "Enregistrer"
        document.getElementById('saveVacataire').addEventListener('click', function () {
            const form = document.getElementById('editVacataireForm');
            const formData = new FormData(form);

            resetEditForm();

            fetch('update_vacataire.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(response => {
                    if (response.success) {
                        showEditSuccess(response.message);

                        updateTableRow(response.data);
                        setTimeout(() => {
                            const modal = bootstrap.Modal.getInstance(document.getElementById('editModal'));
                            modal.hide();
                        }, 1000);
                    } else {

                        if (response.errors) {
                            Object.keys(response.errors).forEach(field => {
                                const input = document.getElementById(`edit${field.charAt(0).toUpperCase() + field.slice(1)}`);
                                const errorDiv = document.getElementById(`${field}Error`);

                                if (input && errorDiv) {
                                    input.classList.add('is-invalid');
                                    errorDiv.textContent = response.errors[field];
                                    errorDiv.style.display = 'block';
                                }
                            });
                        }
                        showEditError(response.message);
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    showEditError('Une erreur est survenue lors de la soumission du formulaire');
                });
        });

        // Fonction pour mettre à jour la ligne du tableau
        function updateTableRow(data) {
            const row = document.querySelector(`tr[data-id="${data.id}"]`);

            if (row) {
                row.querySelector('.vacataire-name').textContent = `${data.nom} ${data.prenom}`;
                row.querySelector('.vacataire-profession').textContent = data.profession;
                row.querySelector('.vacataire-tel').textContent = data.tel;
                row.querySelector('.vacataire-email').textContent = data.email;
            }
        }

        // Fonction pour réinitialiser le formulaire d'édition
        function resetEditForm() {
            const form = document.getElementById('editVacataireForm');
            const errorAlert = document.getElementById('editFormErrors');
            const successAlert = document.getElementById('editFormSuccess');

            errorAlert.style.display = 'none';
            errorAlert.innerHTML = '';
            successAlert.style.display = 'none';
            successAlert.innerHTML = '';

            form.querySelectorAll('.is-invalid').forEach(input => {
                input.classList.remove('is-invalid');
            });

            form.querySelectorAll('.invalid-feedback').forEach(div => {
                div.textContent = '';
                div.style.display = 'none';
            });
        }
         // Fonction pour afficher un message d'erreur
        function showEditError(message) {
            const errorAlert = document.getElementById('editFormErrors');
            errorAlert.innerHTML = message;
            errorAlert.style.display = 'block';
        }
        // Fonction pour afficher un message de succès
        function showEditSuccess(message) {
            const successAlert = document.getElementById('editFormSuccess');
            successAlert.innerHTML = message;
            successAlert.style.display = 'block';
        }
    });
</script>

</body>

</html>