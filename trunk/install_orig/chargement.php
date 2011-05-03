<?php
if ( !defined('PROTECT') )
{
	die("Tentative de Hacking");
}
require_once($root.'fonctions/fct_chaines.php');
require_once($root.'fonctions/fct_generiques.php');
include_once($root.'config/constantes.php');

// Protection des variables
protection_variables();

$user['langue'] = 'fr';

// On charge le template
include_once($root.'class/class_template.php');
$tpl = new Template($root);

// Configuration du theme
$style_path = 'styles/' ;
$style_name = 'BlueLight';
include_once($root.$style_path.$style_name.'/cfg.php');

$lang=$erreur=array();
include_once($root.'lang/'.$user['langue'].'/lang_install.php');

?>