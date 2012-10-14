<?php
define('PROTECT',true);
$root = '../';
// Initialisation de la connexion SQL
include_once($root.'config/config.php');
include_once($root.'config/constantes.php');
require_once($root.'class/class_mysql.php');		
$c = new sql_db($hote, $utilisateur, $password, $base, false);
if(!$c->db_connect_id)
{
	die("Impossible de se connecter à la base de données");
}
require_once($root.'fonctions/fct_chaines.php');
require_once($root.'fonctions/fct_generiques.php');
load_lang('register');

//
// Test d'existence du pseudo
if (isset($_POST['pseudo'])){
	if ($_POST['pseudo']=='')$lang['L_REMPLIR_TOUT'];
	$pseudo_nettoye = nettoyage_nom($_POST['pseudo']);
	//if ($pseudo_nettoye != $_POST['pseudo']) die($lang['PSEUDO_NOK'].' : '.$pseudo_nettoye);
	$sql = 'SELECT user_id FROM '.TABLE_USERS.' WHERE pseudo=\''.addslashes($pseudo_nettoye).'\' LIMIT 1';
	if (!$resultat = $c->sql_query($sql)) message_die(E_ERROR,15,__FILE__,__LINE__,$sql);
	die((($c->sql_numrows($resultat) > 0)?$lang['PSEUDO_NOK']:'OK'));
//
// Test de coherence de l'adresse email
}elseif(isset($_POST['email'])){
	if ($_POST['email']=='')$lang['L_REMPLIR_TOUT'];
	die(((preg_match("/^[-+.\w]{1,64}@[-.\w]{1,64}\.[-.\w]{2,6}$/i", $_POST['email']))? 'OK':$lang['MAIL_NOK']));
//
// Test de coherence des mots de passe saisis
}elseif(isset($_POST['pass2'])){
	if ($_POST['pass2']=='' || $_POST['pass1']=='')$lang['L_REMPLIR_TOUT'];
	die(($_POST['pass1'] == $_POST['pass2'])?'OK':$lang['PASS2_NOK']);
//
// Test de complexite du mdp saisi
// Compexite du mdp  /5 ( au moins 6 caracteres, des chiffres, lettres , des maj et min, et caracteres exotiques)
}elseif(isset($_POST['pass1'])){
	if ($_POST['pass1']=='')$lang['L_REMPLIR_TOUT'];
	$n = 0;
	if (strlen($_POST['pass1'])>5)$n++;
	if (preg_match("/[A-Z]/",$_POST['pass1'])) $n++;
	if (preg_match("/[a-z]/",$_POST['pass1'])) $n++;
	if (preg_match("/[0-9]/",$_POST['pass1'])) $n++;
	if (preg_match("/[^a-zA-Z0-9]/",$_POST['pass1'])) $n++;	
	if ($n<=3){
		die($lang['PASS1_NOK']);
	}else{
		die( 'OK' );
	}	
}
?>
