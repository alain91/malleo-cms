<?php
/*
|------------------------------------------------------------------------------------------------------------
| Software: Malleo ( CMS )
| Contact:  alain91 - http://www.malleo-cms.com
| Support: http://www.malleo-cms.com?module=forum
| Documentation : Support : 
|------------------------------------------------------------------------------------------------------------
|  Author: Alain GANDON
|  Copyright (c) 2011, Alain GANDON All Rights Reserved
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

defined('PROTECT_ADMIN') OR die("Tentative de Hacking");

global $root,$cf,$base_formate_url;

load_lang('config');
load_lang_mod('citations');

// TRAITEMENT
if (isset($_POST['action']) || isset($_GET['action']))
{
	$action = (isset($_POST['action']))? $_POST['action']:$_GET['action'] ;
	switch ($action)
	{
		case 'enregistrer':
			$cf->appel_config('MODIFIER', $_POST);
			header('location: '.$base_formate_url);
			break;
	}
	$cf->config('LECTURE');
}

include($root.'fonctions/fct_formulaires.php');
include($root.'class/class_modelisation.php');
$md = new Modelisation();

// Nom des boucles switch dans le .html
$md->nom_switch = 'liste_choix_config_citations';
// Nom du champs page dans la table de mod�lisation
$md->page = 'Citations';
// Nom de la fonction permettant d'avoir la valeur par d�faut (optionnel)
$md->valeur_actuelle = 'valeur_variable_config';
// Lancement du generateur
$md->generer_saisie();

$tpl->set_filenames(array(
	  'body_admin' => dirname(__FILE__).'/html/admin_config.html'
));
?>