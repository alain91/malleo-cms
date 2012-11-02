<?php
/*
|------------------------------------------------------------------------------------------------------------
| Software: Malleo ( CMS )
| Contact:  SP - http://www.malleo-cms.com
| Support: http://www.malleo-cms.com?module=forum
|  Documentation : Support: http://www.malleo-cms.com?module=wiki
|------------------------------------------------------------------------------------------------------------
|  Author: Stephane RAJALU
|  Copyright (c) 2008-2009, Stephane RAJALU All Rights Reserved
|------------------------------------------------------------------------------------------------------------
|  License: Distributed under the CECILL V2 License
|  This program is distributed in the hope that it will be useful - WITHOUT 
|  ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or 
|  FITNESS FOR A PARTICULAR PURPOSE. 
|
| Please read Licence_CeCILL_V2-en.txt
| SVP lisez Licence_CeCILL_V2-fr.txt
|------------------------------------------------------------------------------------------------------------
*/
if ( !defined('PROTECT') )
{
	die("Tentative de Hacking");
}
// Nettoyage de printemps
unset ($hote, $utilisateur, $password, $base,$img, $style_path, $style_name,$cache, 
		$c, $cf, $user,$users, $droits, $startime, $liste_plugins,$titre_page);
		
//Chargement du systeme d'installation
if (file_exists($root.'install/') && !ereg('install/',$_SERVER['PHP_SELF'])) header('location: '.$root.'install/index.php');
		
// Config
include_once($root.'config/config.php');
include_once($root.'config/constantes.php');
$style_name = 'BlueLight';

// Connexion a la base
require_once($root.'class/class_mysql.php');		
$c = new sql_db($hote, $utilisateur, $password, $base, false);
unset ($hote, $utilisateur, $password, $base);
if(!$c->db_connect_id)
{
	die("Impossible de se connecter à la base de données");
}
// On bloque tous les parametres de connexion
unset ($hote, $utilisateur, $password, $base);

// Lancement du timer
$mtime = microtime();
$mtime = explode(" ",$mtime);
$startime = $mtime[1] + $mtime[0];

require_once($root.'fonctions/fct_chaines.php');
require_once($root.'fonctions/fct_generiques.php');

// Chargement du Cache
require_once($root.'class/class_cache.php');
$cache= new cache();

// Config generale 
require_once($root.'class/class_config.php');
$cf = new config();
$cf->appel_config();

// On applique la configuration des modules au cache
$cache->initialiser_config_cache();

// Protection des variables
protection_variables();

//  Gestion de la session et mise en place des droits
require_once($root.'class/class_droits.php');
$droits = new droits();

require_once($root.'class/class_bots.php');
$bots = new bots();

require_once($root.'class/class_session.php');
$session = new session($cf->config);
$user = $session->new_session();

// On charge le template
include_once($root.'class/class_template.php');
//include_once($root.'class/class_template3.php');
$tpl = new Template($root);
$liste_plugins = $cache->appel_cache('listing_plugins');

//  SWITCHS
$tpl->assign_block_vars((($user['user_id'] < 2)? 'user_non_authentifie':'user_authentifie'),array());
switch ($user['level'])
{
	case 10:$tpl->assign_block_vars('switch_fondateur', array());
	case 9:			$level = 'switch_admin';break;
	case 8: case 7: case 6:	case 5:	case 4:	case 3:
	case 2:			$level = 'switch_membre';break;
	case 1: default:$level = 'switch_invite';break;
}
// Switchs des groupes
$tpl->assign_block_vars($level, array());
foreach($user['groupes'] as $switch_groupe){
	$tpl->assign_block_vars('switch_'.preg_replace('/[^a-z0-9\-_]/i','',$switch_groupe), array());
}
global $cache, $c, $cf, $user,$users, $droits, $style_name, $style_path, $startime, $liste_plugins;
?>
