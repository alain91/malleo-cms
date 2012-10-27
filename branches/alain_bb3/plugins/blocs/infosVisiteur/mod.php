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
load_lang_bloc('infosVisiteur');

$tpl->set_filenames(array(
	'infosVisiteur' =>  $root. 'plugins/blocs/infosVisiteur/html/bloc_info_visiteur.html')
);

// on appele notre boite à outils... qui pourra être réutilisée
include_once($root . 'plugins/blocs/infosVisiteur/fct_info_visiteur.php');

$ip			= info_visiteur_get_ip(); //récupération de l'IP
$host		= gethostbyaddr($ip); // hôte
$browser	=(isset($_SERVER['HTTP_USER_AGENT'])) ? info_visiteur_get_browser($_SERVER['HTTP_USER_AGENT']):''; // Navigateur
$langue		= (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) ?info_visiteur_get_langue($_SERVER['HTTP_ACCEPT_LANGUAGE']):''; // langue
$system		= (isset($_SERVER['HTTP_USER_AGENT'])) ?info_visiteur_get_os($_SERVER['HTTP_USER_AGENT']):''; //systeme d'exploitation
$origine	= (isset($_SERVER['HTTP_REFERER'])) ?$_SERVER['HTTP_REFERER']:$lang['L_ORIGINE_INCONNUE']; // page d'où le visiteur vient

$tpl->assign_vars(array(
	'IP'				=> $ip,
	'HOST'				=> (strlen($host)>20)?substr($host,0,20).'...':$host,
	'BROWSER' 			=> $browser,
	'OS'				=> $system,
	'LANGUE'			=> $langue,
	'L_ORIGINE'			=> sprintf($lang['L_ORIGINE'],$origine)
));

?>