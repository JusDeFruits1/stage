<?php 
include_once('database/extractDataFromDB.php');
include_once('database/connectDB.php');

header('Content-Type: application/json');
if(!isset($_GET['id']) || empty($_GET['id'])) {
    header('Content-Type: application/json');
    echo json_encode([]);
    exit;
}

$vacataireId = intval($_GET['id']);

$courses = getDataFromDB($db, 'SELECT 
    c.id, c.nom, c.description, e.nb_heures 
FROM 
    cours c
JOIN 
    enseigne e ON c.id = e.id_cours
WHERE 
    e.id_vacataire = ' . $vacataireId);

header('Content-Type: application/json');
echo json_encode($courses);
?>