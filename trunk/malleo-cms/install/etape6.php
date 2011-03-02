<?php
if (!defined('PROTECT'))
{
	die("Tentative de Hacking");
}
// Initialisation de la connexion SQL
include_once($root.'config/config.php');
require_once($root.'class/class_mysql.php');		
$c = new sql_db($hote, $utilisateur, $password, $base, false);
if(!$c->db_connect_id)
{
	die("Impossible de se connecter à la base de données");
}
$sql = 'SELECT pseudo FROM '.TABLE_USERS.' WHERE level>8';
if (!$resultat = $c->sql_query($sql)) die('impossible de rechercher des fondateurs');
	
if (isset($_POST['creer_compte'])){
	$pseudo =	nettoyage_nom($_POST['pseudo']);
	$mail =		nettoyage_mail($_POST['email']);
	$pass =		nettoyage_pass($_POST['pass1']);
	require($root.'fonctions/fct_maths.php');
	$clef = generate_key(30);
	$sql = 'INSERT INTO '.TABLE_USERS.' 
			(pseudo, email, pass,  date_register, actif, level, clef, msg ) 
			VALUES  
			(\''.$pseudo.'\',\''.$mail.'\',\''.md5($pass).'\','.time().',1,10,\''.$clef.'\',1)';
	if (!$res = $c->sql_query($sql)) die('impossible de creer le compte');
	
	$tpl->assign_block_vars('compte_cree', array());
	$tpl->assign_vars(array(
		'LOGIN'	 => stripslashes($pseudo),
		'EMAIL'	 => $mail,
		'PASS'	 => $pass,
		'DISABLED'=> ''
	));
}elseif($c->sql_numrows($resultat)>0){
	header('location: index.php?etape=7');
}else{
	$tpl->assign_block_vars('saisie', array());
	$tpl->assign_vars(array(
		'DISABLED'=> ' disabled'
	));
}

$tpl->assign_vars(array(
	'L_EXPLAIN_FONDATEUR'		=> $lang['L_EXPLAIN_FONDATEUR'],
	'L_SAISISSEZ_PARAMETRES'	=> $lang['L_SAISISSEZ_PARAMETRES'],
	'L_LIBELLE_ETAPE7'			=> $lang['L_LIBELLE_ETAPE7'],
	'L_CREER_COMPTE'			=> $lang['L_CREER_COMPTE'],
	'L_COMPTE_CREE'				=> $lang['L_COMPTE_CREE'],
	'L_LEGEND_PSEUDO'			=> $lang['L_LEGEND_PSEUDO'],
	'L_LOGIN'					=> $lang['L_LOGIN'],
	'L_MAIL'					=> $lang['L_MAIL'],
	'L_MDP'						=> $lang['L_MDP'],
	'IMAGE_OK'					=> $img['valide'],
	'IMAGE_NOK'					=> $img['invalide']
));
?>