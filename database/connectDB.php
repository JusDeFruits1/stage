<?php
$host = 'localhost';
$port = 3306;
$dbname = 'enseignement';
$user = 'root';

try {
    $db = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $user);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->exec("SET NAMES 'utf8'");
} catch (PDOException $e) {
    echo 'Erreur : ' . $e->getMessage();
    die();
}

?>