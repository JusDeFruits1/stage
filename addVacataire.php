<?php
include_once('header.php');
include_once('functions.php');
include_once('database/connectDB.php');
?>

<style>
    <?php include 'css/addVacataire.css'; ?>
</style>

<?php
// Initialisation des variables d'erreur et du message d'erreur
$nameErr = $prenomErr = $telErr = $emailErr = $cours_nomErr = $nb_heuresErr = "";
$errorMessage = "";

// Vérification si la méthode de la requête est POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Vérification si tous les champs requis sont définis
    if (isset($_POST['nom']) && isset($_POST['prenom']) && isset($_POST['tel']) && isset($_POST['email']) && isset($_POST['cours_nom']) && isset($_POST['nb_heures'])) {
        // Récupération et nettoyage des données du formulaire
        $nom = trim($_POST['nom']);
        $prenom = trim($_POST['prenom']);
        $tel = $_POST['tel'];
        $email = $_POST['email'];
        $metier = $_POST['metier'];
        $checkInfo = true;

        $cours_nom = $_POST['cours_nom'];
        $nb_heures = $_POST['nb_heures'];
        $description = $_POST['description'];

        // Validation du champ nom
        if (empty($_POST['nom'])) {
            $nameErr = "Le nom est requis";
            $checkInfo = false;
        } else {
            $nom = test_input($nom);
            if (!preg_match("/^[a-zA-ZÀ-ÿ-' ]*$/u", $nom)) {
                $nameErr = "Seuls les lettres et les espaces sont autorisés";
                $checkInfo = false;
            }
        }

        // Validation du champ prénom
        if (empty($_POST['prenom'])) {
            $prenomErr = "Le prénom est requis";
            $checkInfo = false;
        } else {
            $prenom = test_input($prenom);
            if (!preg_match("/^[a-zA-ZÀ-ÿ-' ]*$/u", $prenom)) {
                $prenomErr = "Seuls les lettres et les espaces sont autorisés";
                $checkInfo = false;
            }
        }

        // Validation du champ téléphone
        if (empty($tel)) {
            $telErr = "Le numéro de téléphone est requis";
            $checkInfo = false;
        } else {
            $tel = test_input($tel);
            if (!preg_match("/^0[1-9]([-. ]?[0-9]{2}){4}$/", $tel)) {
                $telErr = "Entrez un numéro de téléphone valide";
                $checkInfo = false;
            }
        }

        // Validation du champ email
        if (empty($email)) {
            $emailErr = "L'email est requis";
            $checkInfo = false;
        } else {
            $email = test_input($email);
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $emailErr = "Format d'email invalide";
                $checkInfo = false;
            }
        }

        // Validation du champ métier
        if (empty($metier)) {
            $metier = "Non spécifié";
        }

        // Validation du champ nom du cours
        if (empty($cours_nom)) {
            $cours_nomErr = "Le nom du cours est requis";
            $checkInfo = false;
        } else {
            $cours_nom = test_input($cours_nom);
            if (!preg_match("/^[a-zA-ZÀ-ÿ-' ]*$/u", $cours_nom)) {
                $cours_nomErr = "Seuls les lettres, les accents et les espaces sont autorisés";
                $checkInfo = false;
            }
        }

        // Validation du champ nombre d'heures
        if (empty($nb_heures)) {
            $nb_heuresErr = "Le nombre d'heures est requis";
            $checkInfo = false;
        } else {
            $nb_heures = test_input($nb_heures);
            if (!preg_match("/^[0-9]*$/", $nb_heures)) {
                $nb_heuresErr = "Entrez un nombre valide";
                $checkInfo = false;
            }
        }

        // Validation du champ description
        if (empty($description)) {
            $description = "Non spécifié";
        } else {
            $description = test_input($description);
        }

        // Vérification des doublons dans la base de données
        $checkDuplicate = $db->prepare("SELECT COUNT(*) as count FROM vacataire 
                               INNER JOIN enseigne ON vacataire.id = enseigne.id_vacataire 
                               INNER JOIN cours ON enseigne.id_cours = cours.id 
                               WHERE LOWER(vacataire.nom) = LOWER(:nom) 
                               AND LOWER(vacataire.prenom) = LOWER(:prenom) 
                               AND LOWER(cours.nom) = LOWER(:cours_nom)");
        $checkDuplicate->execute([
            'nom' => trim($nom),
            'prenom' => trim($prenom),
            'cours_nom' => trim($cours_nom)
        ]);
        $result = $checkDuplicate->fetch(PDO::FETCH_ASSOC);

        // Si un doublon est trouvé, afficher un message d'erreur
        if ($result['count'] > 0) {
            $errorMessage = "Ce vacataire enseigne déjà ce cours";
            $checkInfo = false;
        }

        // Si toutes les validations sont réussies, insérer les données dans la base de données
        if ($checkInfo) {
            $insertVacataire = $db->prepare("INSERT INTO vacataire(nom, prenom, tel, email, profession) VALUES(:nom, :prenom, :tel, :email, :profession)");
            $insertVacataire->execute(array(
                'nom' => $nom,
                'prenom' => $prenom,
                'tel' => $tel,
                'email' => $email,
                'profession' => $metier
            ));
            $vacataireId = $db->lastInsertId();

            // Vérification si le cours existe déjà
            $checkCours = $db->prepare("SELECT id FROM cours WHERE nom = :nom");
            $checkCours->execute(['nom' => $cours_nom]);
            $existingCours = $checkCours->fetch();

            if ($existingCours) {
                $coursId = $existingCours['id'];
                $_SESSION["courseInfo"] = "Un cours avec ce nom existe déjà. Utilisation du cours existant.";
                $_SESSION["showCourseInfo"] = true;
            } else {
                $insertCours = $db->prepare("INSERT INTO cours(nom, description) VALUES(:nom, :description)");
                $insertCours->execute(array(
                    'nom' => $cours_nom,
                    'description' => $description
                ));
                $coursId = $db->lastInsertId();
            }

            // Insertion des données dans la table enseigne
            $insertEnseigne = $db->prepare("INSERT INTO enseigne(id_vacataire, id_cours, nb_heures) VALUES(:id_vacataire, :id_cours, :nb_heures)");
            $insertEnseigne->execute(array(
                'id_vacataire' => $vacataireId,
                'id_cours' => $coursId,
                'nb_heures' => $nb_heures
            ));
            $_SESSION["addVacataire"] = "Vacataire ajouté avec succès !";
            header('Location: addVacataire.php');
            exit();
        }
    }
}
?>

<div class="col-6 offset-3 mt-5">
    <h2 class="text-center">Ici vous pourrez ajouter un vacataire</h2>
    <span class="error"><strong>* champ obligatoire</strong></span>
    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
        <?php if (!empty($errorMessage)): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $errorMessage; ?>
                <?php unset($errorMessage); ?>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION["courseInfo"]) && isset($_SESSION["showCourseInfo"])): ?>
            <div class="alert alert-info" role="alert">
                <?php echo $_SESSION["courseInfo"]; ?>
                <?php unset($_SESSION["courseInfo"]);
                unset($_SESSION["showCourseInfo"]);
                ?>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION["addVacataire"])): ?>
            <div class="alert alert-success" role="alert">
                <?php echo $_SESSION["addVacataire"]; ?>
                <?php unset($_SESSION["addVacataire"]); ?>
            </div>
        <?php endif; ?>

        <div class="mb-3">
            <h5><em>Informations personnelles</em></h5>
            <label for="nom" class="form-label">Nom <span class="error">*</span></label>
            <input type="text" class="form-control" name="nom" id="nom">
            <span class="error"><?php echo $nameErr; ?></span>
            <br>

            <label for="prenom" class="form-label">Prénom <span class="error">*</span> </label>
            <input type="text" class="form-control" id="prenom" name="prenom">
            <span class="error"><?php echo $prenomErr ?></span>
            <br>

            <label for="tel" class="form-label">Tél <span class="error">*</span></label>
            <input type="tel" class="form-control" id="tel" name="tel">
            <span class="error"><?php echo $telErr ?></span>
            <br>

            <label for="email" class="form-label">Email <span class="error">*</span></label>
            <input type="email" class="form-control" id="email" name="email">
            <span class="error"><?php echo $emailErr ?></span>
            <br>

            <label for="metier" class="form-label">Profession</label>
            <input type="text" class="form-control" id="metier" name="metier">
        </div>
        <div class="mb-3">
            <h5><em>Matière enseigné</em></h5>

            <label for="cours_nom" class="form_label">Nom du cours <span class="error">*</span></label>
            <input type="text" class="form-control" id="cours_nom" name="cours_nom">
            <span class="error"><?php echo $cours_nomErr?></span>
            <br>

            <label for="nb_heures" class="form_label">Nombre d'heures <span class="error">*</span></label>
            <input type="number" class="form-control" id="nb_heures" name="nb_heures">
            <span class="error"><?php echo $nb_heuresErr?></span>

            <label for="description" class="form_label">Description</label>
            <input type="text" class="form-control" id="description" name="description">
        </div>
        <button type="submit" class="btn btn-primary addVacataire">Ajouter</button>
    </form>
</div>
</body>

</html>