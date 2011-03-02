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
if (!defined('PROTECT'))
{
	die("Tentative de Hacking");
}
//
// Chargement des prerequis du module Forum

require_once($root.'plugins/modules/forum/prerequis.php');

//
// Pas le droit de voir ? on ejecte !
if (!$droits->check($module,0,'voir'))
{
	error404(725);
	exit;
}

//
// Outils de posting
include_once($root.'class/class_posting.php');
$post=new posting();

// Options raccourcis
if ($user['level']>1){
	$tpl->options_page = array(
		0=>array(
		'ICONE'		=> $img['forum_raccourcis_nouvelles_reponses'],
		'LIBELLE'	=> $lang['L_MSG_NOUVEAU'],
		'LIEN'		=> formate_url('mode=nouveau',true)),
		
		1=>array(
		'ICONE'		=> $img['forum_raccourcis_marquer_comme_lus'],
		'LIBELLE'	=> $lang['L_MSG_REPONSE_LU'],
		'LIEN'		=> formate_url('mode=marquer_lu',true)),
		
		2=>array(
		'ICONE'		=> $img['forum_raccourcis_mes_sujets'],
		'LIBELLE'	=> $lang['L_MSG_MES_MSG'],
		'LIEN'		=> formate_url('mode=mes_messages',true)),
		
		3=>array(
		'ICONE'		=> $img['forum_raccourcis_sans_reponses'],
		'LIBELLE'	=> $lang['L_MSG_SANS_REPONSE'],
		'LIEN'		=> formate_url('mode=sans_reponse',true)),
		
		4=>array(
		'ICONE'		=> $img['forum_raccourcis_mes_favoris'],
		'LIBELLE'	=> $lang['L_MSG_FAVORIS'],
		'LIEN'		=> formate_url('mode=favoris',true)),
		
 		5=>array(
		'ICONE'		=> $img['forum_raccourcis_options'],
		'LIBELLE'	=> $lang['L_OPTIONS'],
		'LIEN'		=> formate_url('mode=options',true))
	);
}

//
// Différents modes de fonctionnement

$mode = null;
if(isset($_GET['mode'])||isset($_POST['mode'])){
	$mode=(isset($_POST['mode']))?$_POST['mode']:$_GET['mode'];
}
switch($mode)
{
	// -------------------------------------------------
	//  ACTIVITE BASIQUE
	// -------------------------------------------------
	case 'Enregistrer':
	case 'EnregistrerEditerPost':
	case 'NouveauTopic':
	case 'NouveauPost':
	case 'EditerTopic':
	case 'EditerPost':
	case 'SupprimerTopic':
	case 'SupprimerPost':	
	case 'SuivreTopic':	
	case 'ResilierTopic':	
	case 'AjouterFavoris':	
	case 'SupprimerFavoris':	
	case 'marquer_lu':	include($root.'plugins/modules/forum/inc/saisie.php'); 	break;
	// -------------------------------------------------
	//  FILTRES
	// -------------------------------------------------		
	case 'favoris':	
	case 'nouveau':	
	case 'mes_messages':	
	case 'sans_reponse':include($root.'plugins/modules/forum/inc/raccourcis.php'); 	break;
	// -------------------------------------------------
	//  MODERATION PURE
	// -------------------------------------------------
	case 'VerrouillerForum':
	case 'DeVerrouillerForum':	
	case 'VerrouillerTopic':
	case 'DeVerrouillerTopic':
	case 'DeplacerTopic':
	case 'DiviserTopic':
	case 'FusionnerTopics':	include($root.'plugins/modules/forum/inc/moderation.php'); 	break;
	// -------------------------------------------------
	//  AFFICHAGE
	// -------------------------------------------------
	case 'options':	include($root.'plugins/modules/forum/inc/options.php');	break;
	case 'topic':	include($root.'plugins/modules/forum/inc/topics.php');	break;
	case 'forum':	include($root.'plugins/modules/forum/inc/forums.php');	break;
	case 'categories':default:include($root.'plugins/modules/forum/inc/categories.php'); break;
}
?>