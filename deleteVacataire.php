<?php
include_once('database/connectDB.php');
session_start();

// Définir le type de contenu de la réponse comme JSON
header('Content-Type: application/json');

// Vérifier si l'ID du vacataire est fourni et non vide
if(!isset($_POST['id']) || empty($_POST['id'])){
    echo json_encode(['success' => false, 'message' => 'ID du vacataire non fourni']);
    exit();
}

// Convertir l'ID en entier
$id = (int)$_POST['id'];

try {
    // Démarrer une transaction
    $db->beginTransaction();

    // Supprimer les enregistrements associés dans la table 'enseigne'
    $deleteEnseigne = $db->prepare('DELETE FROM enseigne WHERE id_vacataire = :id');
    $deleteEnseigne->bindParam(':id', $id, PDO::PARAM_INT);
    $deleteEnseigne->execute();

    // Supprimer le vacataire dans la table 'vacataire'
    $deleteVacataire = $db->prepare('DELETE FROM vacataire WHERE id = :id');
    $deleteVacataire->bindParam(':id', $id, PDO::PARAM_INT);
    $deleteVacataire->execute();

    // Vérifier si un vacataire a été supprimé
    if($deleteVacataire->rowCount() > 0){
        // Valider la transaction
        $db->commit();
        echo json_encode(['success' => true, 'message' => 'Vacataire supprimé avec succès']);
    } else {
        // Annuler la transaction si aucun vacataire n'a été trouvé
        $db->rollBack();
        echo json_encode(['success' => false, 'message' => 'Aucun vacataire trouvé avec cet ID']);
    }
} catch(PDOException $e) {
    // Annuler la transaction en cas d'erreur
    $db->rollBack();
    // Enregistrer l'erreur dans le journal des erreurs
    error_log('Erreur lors de la suppression du vacataire : ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erreur lors de la suppression du vacataire']);
}

?>