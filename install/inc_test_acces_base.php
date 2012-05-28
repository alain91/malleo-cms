<?php
define('PROTECT',true);
$root = '../';
require_once($root.'class/class_mysql.php');

// Champs vides
if (empty($_POST['adresse_base']) || empty($_POST['nom_utilisateur']) || empty($_POST['nom_base'])){
	die('nok');
}
// Test connexion
$c = new sql_db($_POST['adresse_base'], $_POST['nom_utilisateur'], $_POST['mdp'], $_POST['nom_base'], false);
if(!$c->db_connect_id){
	die('nok');
}else{ 
	die('ok');
}

?>