<?php
include_once('connectDB.php');
function getDataFromDB($db, $query)
{
    $stmt = $db->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll();
}

?>