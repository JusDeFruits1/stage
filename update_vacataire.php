<?php
include_once('database/connectDB.php');

// Définir le type de contenu de la réponse comme JSON
header('Content-Type: application/json');

// Vérifier si la méthode de la requête est POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer les données du formulaire
    $id = $_POST['id'] ?? '';
    $nom = $_POST['nom'] ?? '';
    $prenom = $_POST['prenom'] ?? '';
    $profession = $_POST['profession'] ?? '';
    $tel = $_POST['tel'] ?? '';
    $email = $_POST['email'] ?? '';

    // Initialiser un tableau pour les erreurs
    $errors = [];

    // Validation des champs
    if (empty($nom)) {
        $errors['nom'] = 'Le nom est requis';
    } elseif (!preg_match("/^[a-zA-ZÀ-ÿ-' ]*$/u", $nom)) {
        $errors['nom'] = 'Seuls les lettres, les accents et les espaces sont autorisés';
    }

    if (empty($prenom)) {
        $errors['prenom'] = 'Le prénom est requis';
    } elseif (!preg_match("/^[a-zA-ZÀ-ÿ-' ]*$/u", $prenom)) {
        $errors['prenom'] = 'Seuls les lettres, les accents et les espaces sont autorisés';
    }

    if (empty($tel)) {
        $errors['tel'] = 'Le numéro de téléphone est requis';
    } elseif (!preg_match("/^0[1-9]([-. ]?[0-9]{2}){4}$/", $tel)) {
        $errors['tel'] = 'Entrez le numéro de téléphone au format valide';
    }

    if (empty($email)) {
        $errors['email'] = 'L\'adresse email est requise';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'L\'adresse email n\'est pas valide';
    }

    // Si aucune erreur, mise à jour du vacataire
    if (empty($errors)) {
        try {
            // Démarrer une transaction
            $db->beginTransaction();

            // Préparer la requête de mise à jour
            $stmt = $db->prepare("UPDATE vacataire SET nom = ?, prenom = ?, profession = ?, tel = ?, email = ? WHERE id = ?");
            $result = $stmt->execute([$nom, $prenom, $profession, $tel, $email, $id]);

            // Valider la transaction
            $db->commit();

            // Retourner une réponse JSON de succès
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
        } catch (PDOException $e) {
            // Annuler la transaction en cas d'erreur
            $db->rollBack();
            // Retourner une réponse JSON d'erreur
            echo json_encode([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour: ' . $e->getMessage(),
                'errors' => ['general' => 'Une erreur s\'est produite lors de la mise à jour']
            ]);
        }
    } else {
        // Retourner une réponse JSON avec les erreurs de validation
        echo json_encode([
            'success' => false,
            'message' => 'Veuillez corriger les erreurs du formulaire',
            'errors' => $errors
        ]);
    }
    exit;
}

// Retourner une réponse JSON si la méthode n'est pas autorisée
echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
?>