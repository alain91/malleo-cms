<?php
if ( !defined('PROTECT') )
{
	die("Tentative de Hacking");
}
global $root;
// LISTING TABLES
$prefixe = 'a_';
define('TABLE_CONFIG',			$prefixe.'config');
define('TABLE_DROITS_FONCTIONS',$prefixe.'droits_fonctions');
define('TABLE_DROITS_REGLES',	$prefixe.'droits_regles');
define('TABLE_GROUPES',			$prefixe.'groupes');
define('TABLE_GROUPES_INDEX',	$prefixe.'groupes_index');
define('TABLE_MODULES',			$prefixe.'modules');
define('TABLE_MODELES',			$prefixe.'modeles');
define('TABLE_MODELISATION',	$prefixe.'modelisation_tables');
define('TABLE_PLUGINS',			$prefixe.'plugins');
define('TABLE_RANGS',			$prefixe.'rangs');
define('TABLE_SESSIONS',		$prefixe.'sessions');
define('TABLE_SESSIONS_SUIVIES',$prefixe.'sessions_suivies');
define('TABLE_SMILEYS',			$prefixe.'smileys');
define('TABLE_USERS',			$prefixe.'users');
define('TABLE_BANNIS',			$prefixe.'users_bannis');
define('TABLE_ROBOTS',			$prefixe.'robots');

// On force certains parametres de configuration
ini_set ("magic_quotes_runtime", 0);
// Pas d'id de session apparant dans l'URL
ini_set('session.use_trans_sid',0);

// fichier du reglement
define('PATH_REGLEMENT',$root.'data/reglement.html');
// fichier contenant les champs du profile publies
define('PATH_LISTE_CHAMPS_PROFILE',$root.'data/liste_champs_profile.cache');
$chps_o = array('pseudo','avatar');
// Chemin des smileys
define('PATH_SMILEYS','data/smileys/');
// duree de validite du jeton
define('VALIDITE_JETON',300);
// Compression Gzip
$gzip = (array_key_exists('HTTP_ACCEPT_ENCODING',$_SERVER) && strstr($_SERVER['HTTP_ACCEPT_ENCODING'],'gzip'))? true:false;
// Chemin des themes
$style_path = 'styles/' ;

// MODE Dev
// Commenter les lignes ci dessous quand votre site sera installe completement (en ajoutant // devant la ligne)
error_reporting(E_ALL);
$gzip = false;
// FIN MODE Dev


// MODE PROD
// Decommenter les lignes ci dessous quand votre site sera installe completement (en supprimant // devant la ligne)
//error_reporting(0);
// FIN MODE PROD

define('ACTIVE_GZIP',$gzip);
?>
