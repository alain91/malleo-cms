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

if (!function_exists('formate_sexe')) include_once($root.'fonctions/fct_profil.php');

$tpl->set_filenames(array('forum'=>$root.'plugins/modules/forum/html/options.html'));
		

//
// ENREGISTREMENT des changements
if (isset($_POST['enregistrer'])){
	$sql = 'UPDATE '.TABLE_USERS.' SET 
				forum_vue='.intval($_POST['forum_vue']).',
				forum_email_reponse='.(intval($_POST['forum_email_reponse']==1)?'true':'false').'
			WHERE user_id='.$user['user_id'].' LIMIT 1';
	if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,703,__FILE__,__LINE__,$sql);
	$user = $cache->appel_cache('infos_user',true);
	affiche_message('forum','L_OPTIONS_ENREGISTREES',formate_url('',true));
}

// Titres
$tpl->titre_page = $tpl->titre_navigateur = $lang['L_OPTIONS'];

// Navlinks
$session->make_navlinks(array(
	ucfirst($module)			=> formate_url('',true)
));


$tpl->assign_vars(array(
	'S_VUE_COMPLETE'			=> ($user['forum_vue']==1)? ' selected="selected"':'',
	'S_VUE_CLASSIQUE'			=> ($user['forum_vue']==2)? ' selected="selected"':'',
	'S_REPONSE_OUI'				=> ($user['forum_email_reponse']==1)? ' checked="checked"':'',
	'S_REPONSE_NON'				=> ($user['forum_email_reponse']==0)? ' checked="checked"':''
));
?>