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
load_lang('time');
if (!isset($_GET['id_billet']) || ($_GET['id_billet']==''))
{
	message_die(E_WARNING,503,'','');
}
$id_billet = intval($_GET['id_billet']);

$tpl->set_filenames(array('blog' => $root.'plugins/modules/blog/html/billet.html'));
// Creation du jeton de securite
if (!session_id()) @session_start();
$jeton = md5(uniqid(rand(), TRUE));
$_SESSION['jeton'] = $jeton;
$_SESSION['jeton_timestamp'] = $session->time;
//
// AFFICHAGE du BILLET

$sql = 'SELECT b.id_billet, b.titre_billet, b.billet, b.auteur, b.date_redaction, b.date_parution, b.nbre_coms, b.id_cat, b.tags,  
		u.pseudo,u.avatar, u.rang, u.msg , 
		c.id_cat, c.image_cat, c.titre_cat 
		FROM '.TABLE_BLOG_BILLETS.' as b LEFT JOIN '.TABLE_USERS.' as u
		ON (b.auteur=u.user_id)
		LEFT JOIN '.TABLE_BLOG_CATS.' as c
			ON (b.id_cat=c.id_cat)
		WHERE id_billet='.$id_billet.'
		AND c.module=\''.$module.'\'
		LIMIT 1';
if (!$resultat = $c->sql_query($sql)) message_die(E_ERROR,502,__FILE__,__LINE__,$sql); 
if ($c->sql_numrows($resultat) == 0)
{
	error404();
	exit;
}else{
	$row = $c->sql_fetchrow($resultat);
	// Formatage de la Date
	$Dp = explode(':',date('j:n:Y:H:i',$row['date_parution']));
	if (!function_exists('formate_sexe')) include_once($root.'fonctions/fct_profil.php');
	
	$tpl->assign_vars(array(
		'TITRE_BILLET'		=> $row['titre_billet'],
		'DATE_PARUTION'		=> sprintf($lang['L_DATE'],$Dp[0],$Dp[1],$Dp[2],$Dp[3],$Dp[4]),
		'JOUR'				=> $Dp[0],
		'MOIS'				=> $lang['mois_court'][$Dp[1]],
		'ANNEE'				=> $Dp[2],
		'CATEGORIE'			=> $row['titre_cat'],
		'ID_BILLET'			=> $row['id_billet'],
		'BILLET'			=> $post->bbcode2html($row['billet']),
		'AUTEUR'			=> formate_pseudo($row['auteur'],$row['pseudo']),
		'AVATAR'			=> $row['avatar'],
		'RANG'				=> ($row['auteur']>1)?formate_rang($row['rang'],$row['msg']):'',
		'NBRE_COMS'			=> $row['nbre_coms'],
		'EDITER'			=> formate_url('mode=saisie&id_cat='.$row['id_cat'].'&id_billet='.$row['id_billet'].'&action=editer',true),
		'SUPPRIMER'			=> formate_url('mode=saisie&id_cat='.$row['id_cat'].'&id_billet='.$row['id_billet'].'&action=supprimer&jeton='.$jeton,true),
		'S_CATEGORIE'		=> formate_url('mode=liste&categorie='.$row['id_cat'],true),
		'I_EDITER'			=> $img['editer'],
		'I_EFFACER'			=> $img['effacer']
	));

	// Formatage des tags
	if ($droits->check($module,0,'tags') && strlen($row['tags'])>0)
	{
		$tpl->assign_block_vars('tags_ok', array());
		$tags=explode(' ',$row['tags']);
		foreach($tags as $key=>$val)
		{
			$tpl->assign_block_vars('tags_ok.liste_tags', array(
				'URL_TAGS'	=> formate_url('mode=liste&tag='.$val,true),
				'TAGS'		=> $val
			));
		}
	}

	//
	// On incrémente le compteur de lecture
	$blog->id_billet=$row['id_billet'];
	$blog->incremente_lectures();

	// on est fondateur ou l'auteur de ce billet
	if ($droits->check($module,0,'editer') || $row['auteur'] == $user['user_id']) $tpl->assign_block_vars('editer', array());
	if ($droits->check($module,0,'supprimer') || $row['auteur'] == $user['user_id']) $tpl->assign_block_vars('supprimer', array());
	//
	// Titre de page 
	$tpl->titre_navigateur = $row['titre_billet'].' :: '.$module;
	$tpl->meta_description = $post->bbcode2html($row['billet']);
	$tpl->titre_page = '<img src="data/icones_cat_blog/'.$row['image_cat'].'" alt="'.$row['titre_billet'].'" />&nbsp; '. $row['titre_billet'];

	// Navlinks
	$session->make_navlinks(ucfirst($module),formate_url('',true));
	$session->make_navlinks($row['titre_cat'],formate_url('mode=liste&categorie='.$row['id_cat'],true));
	$session->make_navlinks($row['titre_billet'],formate_url('mode=billet&id_billet='.$row['id_billet'],true));
	
	//
	// SAISIE des commentaires autorise ?
	if ($droits->check($module,0,'commenter') && $cf->config['blog_ok_coms'] == 1){ 
		$cryptinstall = $root.'librairies/crypt/cryptographp.fct.php';
		require_once($cryptinstall);
		$tpl->assign_block_vars('commentaires_ok', array('CRYPT_CODE' => dsp_crypt()));
		if ($user['user_id'] < 2 ) $tpl->assign_block_vars('commentaires_ok.user_non_authentifie', array());
	}
	
	//
	// AFFICHAGE des Commentaires
	$sql = 'SELECT c.id_com, c.user_id, c.pseudo, c.email, c.site, c.msg, c.date, u.pseudo AS PseudoUser, u.avatar,
			u.rang, u.msg as nbre_msg
			FROM '.TABLE_BLOG_COMS.' AS c LEFT JOIN '.TABLE_USERS.' AS u
			ON 	(c.user_id=u.user_id) 
			WHERE id_billet='.$id_billet.'
			ORDER BY date ASC';
	if (!$resultat = $c->sql_query($sql)) message_die(E_ERROR,504,__FILE__,__LINE__,$sql); 
	if ($c->sql_numrows($resultat) < 1 )
	{
		$tpl->assign_block_vars('no_liste_coms', array());
	}else{
		$coms = array();
		while ($row = $c->sql_fetchrow($resultat))
		{ 
			$coms[] = $row;
		}
		// Sens de l'affichage? plus récent en haut ou en bas?
		if ($cf->config['blog_sens'] == 'DESC') $coms = array_reverse($coms);
		
		for($i=0;$i<sizeof($coms);$i++)
		{
			$auteur = ($coms[$i]['user_id'] > 1)? formate_pseudo($coms[$i]['user_id'],$coms[$i]['PseudoUser']):(($coms[$i]['site'] != null)?'<a href="'.$coms[$i]['site'].'" target="_blank" class="pseudo">'.$coms[$i]['pseudo'].'</a>':$coms[$i]['pseudo']);
			$D = explode(':',date('j:n:Y:H:i',$coms[$i]['date']));
			$D = sprintf($lang['L_DATE'],$D[0],$D[1],$D[2],$D[3],$D[4]);
			$site = (!preg_match("/^http:\/\//i",$coms[$i]['site']))?'http://'.$coms[$i]['site']:$coms[$i]['site'];
			$tpl->assign_block_vars('liste_coms', array(
				'DATE'		=> $D,
				'AUTEUR'	=> $auteur,
				'AVATAR'	=> $coms[$i]['avatar'],
				'RANG'		=> ($coms[$i]['user_id']>1)?formate_rang($coms[$i]['rang'],$coms[$i]['nbre_msg']):'',
				'MSG'		=> $post->bbcode2html($coms[$i]['msg']),
				'SITE'		=> $site,
				'DELETE'	=> formate_url('mode=saisie&action=supprimer_coms&id_billet='.$id_billet.'&id_com='.$coms[$i]['id_com'].'&jeton='.$jeton,true)
			));
			// on est admin ou l'auteur de ce billet
			if ($droits->check($module,0,'supprimer') || ($coms[$i]['user_id']>1 && $coms[$i]['user_id'] == $user['user_id']))$tpl->assign_block_vars('liste_coms.supprimer', array());
			if ($coms[$i]['site'] != '') $tpl->assign_block_vars('liste_coms.site', array());
			
			// Meta description
			$tpl->meta_description .= $post->bbcode2html($coms[$i]['msg']);
		}
	}
	
	// Editeur WYSIWYG
	$WYSIWYG_PROFILE = 'simple';
	if ($cf->config['wysiwyg_editor']!='') include_once($root.'fonctions/fct_'.$cf->config['wysiwyg_editor'].'.php');
	
	$tpl->assign_vars(array(
		'FORM_TARGET'		=> formate_url('mode=saisie',true)
	));
}
?>
