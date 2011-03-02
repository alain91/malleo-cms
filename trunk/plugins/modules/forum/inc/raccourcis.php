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
// PAGINATION
include($root.'fonctions/fct_affichage.php');
$nbre_enregistrements = 0;
$class='row2';
$start = (isset($_GET['start']) && $_GET['start']>0)? intval($_GET['start']):0;

$tpl->set_filenames(array('forum'=>$root.'plugins/modules/forum/html/resultats.html'));
$sql = '';		
switch($mode)
{
	case 'favoris':
		$sql = 'SELECT t.id_topic, t.titre_topic, t.id_forum, t.status_topic, t.date_topic, 
						t.post_depart, t.post_fin, t.reponses_topic, t.lectures_topic,
						f.titre_forum,
						pd.date_post AS date_post_auteur, pd.id_post AS id_post_auteur,
						pf.date_post AS date_post_reponse, pf.id_post AS id_post_reponse,
						ud.user_id, ud.pseudo,
						n.id_topic  AS msg_lu,
						ts.id_topic  AS topic_abonne 
				FROM '.TABLE_FORUM_TOPICS_FAVORIS.' AS fav
				LEFT JOIN '.TABLE_FORUM_TOPICS.' AS t 
					ON (fav.id_topic=t.id_topic)
					LEFT JOIN '.TABLE_FORUM_FORUMS.' AS f 
						ON (t.id_forum=f.id_forum)
					LEFT JOIN '.TABLE_FORUM_CATS.' AS c 
						ON (f.id_cat=c.id_cat)
					LEFT JOIN '.TABLE_FORUM_POSTS.' as pd
						ON (t.post_depart=pd.id_post)
					LEFT JOIN '.TABLE_USERS.' as ud
						ON (pd.user_id=ud.user_id)
					LEFT JOIN '.TABLE_FORUM_POSTS.' as pf
						ON (t.post_fin=pf.id_post)
					LEFT JOIN '.TABLE_FORUM_TOPICS_NONLUS.' as n
						ON (t.id_topic=n.id_topic AND n.user_id='.$user['user_id'].')
					LEFT JOIN '.TABLE_FORUM_TOPICS_SUIVIS.' as ts
						ON (t.id_topic=ts.id_topic AND ts.user_id='.$user['user_id'].')
				WHERE fav.user_id='.$user['user_id'].'
				AND c.module="'.$module.'" 
				ORDER BY date_topic DESC
				LIMIT 100';
		$tpl->titre_page = $tpl->titre_navigateur = $lang['L_MSG_FAVORIS'];
		// Navlinks
		$session->make_navlinks(array(
			ucfirst($module)		=> formate_url('',true),
			$lang['L_MSG_FAVORIS']	=> formate_url('mode=favoris',true)
		));
		break;
	case 'nouveau':
		$sql = 'SELECT t.id_topic, t.titre_topic, t.id_forum, t.status_topic, t.date_topic, 
						t.post_depart, t.post_fin, t.reponses_topic, t.lectures_topic,
						f.titre_forum,
						pd.date_post AS date_post_auteur, pd.id_post AS id_post_auteur,
						pf.date_post AS date_post_reponse, pf.id_post AS id_post_reponse,
						ud.user_id, ud.pseudo,
						n.id_topic  AS msg_lu,
						ts.id_topic  AS topic_abonne 
				FROM '.TABLE_FORUM_TOPICS_NONLUS.' AS n
				LEFT JOIN '.TABLE_FORUM_TOPICS.' AS t 
					ON (n.id_topic=t.id_topic)
					LEFT JOIN '.TABLE_FORUM_FORUMS.' AS f 
						ON (t.id_forum=f.id_forum)
					LEFT JOIN '.TABLE_FORUM_CATS.' AS c 
						ON (f.id_cat=c.id_cat)
					LEFT JOIN '.TABLE_FORUM_POSTS.' as pd
						ON (t.post_depart=pd.id_post)
					LEFT JOIN '.TABLE_USERS.' as ud
							ON (pd.user_id=ud.user_id)
					LEFT JOIN '.TABLE_FORUM_POSTS.' as pf
						ON (t.post_fin=pf.id_post)
					LEFT JOIN '.TABLE_FORUM_TOPICS_SUIVIS.' as ts
						ON (t.id_topic=ts.id_topic AND ts.user_id='.$user['user_id'].')
				WHERE n.user_id='.$user['user_id'].'
				AND c.module="'.$module.'" 
				ORDER BY date_topic DESC
				LIMIT 100';
		$tpl->titre_page = $tpl->titre_navigateur = $lang['L_MSG_NOUVEAU'];
		// Navlinks
		$session->make_navlinks(array(
			ucfirst($module)		=> formate_url('',true),
			$lang['L_MSG_NOUVEAU']	=> formate_url('mode=nouveau',true)
		));
		break;
	case 'mes_messages':
		$sql = 'SELECT DISTINCT t.id_topic, t.titre_topic, t.id_forum, t.status_topic, t.date_topic, 
						t.post_depart, t.post_fin, t.reponses_topic, t.lectures_topic,
						f.titre_forum,
						pd.date_post AS date_post_auteur, pd.id_post AS id_post_auteur,
						pf.date_post AS date_post_reponse, pf.id_post AS id_post_reponse,
						ud.user_id, ud.pseudo,
						n.id_topic  AS msg_lu,
						ts.id_topic  AS topic_abonne 
				FROM '.TABLE_FORUM_POSTS.' as p
					LEFT JOIN '.TABLE_FORUM_TOPICS.' AS t 
						ON (p.id_topic=t.id_topic)
					LEFT JOIN '.TABLE_FORUM_FORUMS.' AS f 
						ON (t.id_forum=f.id_forum)
					LEFT JOIN '.TABLE_FORUM_CATS.' AS c 
						ON (f.id_cat=c.id_cat)
					LEFT JOIN '.TABLE_FORUM_POSTS.' as pd
						ON (t.post_depart=pd.id_post)
						LEFT JOIN '.TABLE_USERS.' as ud
							ON (pd.user_id=ud.user_id)
					LEFT JOIN '.TABLE_FORUM_POSTS.' as pf
						ON (t.post_fin=pf.id_post)
					LEFT JOIN '.TABLE_FORUM_TOPICS_NONLUS.' as n
						ON (t.id_topic=n.id_topic AND n.user_id='.$user['user_id'].')
					LEFT JOIN '.TABLE_FORUM_TOPICS_SUIVIS.' as ts
						ON (t.id_topic=ts.id_topic AND ts.user_id='.$user['user_id'].')
				WHERE p.user_id='.$user['user_id'].'
				AND  c.module="'.$module.'" 
				ORDER BY date_topic DESC
				LIMIT 100';
		$tpl->titre_page = $tpl->titre_navigateur = $lang['L_MSG_MES_MSG'];
		// Navlinks
		$session->make_navlinks(array(
			ucfirst($module)		=> formate_url('',true),
			$lang['L_MSG_MES_MSG']	=> formate_url('mode=mes_messages',true)
		));
		break;
	case 'sans_reponse':
		$sql = 'SELECT DISTINCT t.id_topic, t.titre_topic, t.id_forum, t.status_topic, t.date_topic, 
						t.post_depart, t.post_fin, t.reponses_topic, t.lectures_topic,
						f.titre_forum,
						pd.date_post AS date_post_auteur, pd.id_post AS id_post_auteur,
						pf.date_post AS date_post_reponse, pf.id_post AS id_post_reponse,
						ud.user_id, ud.pseudo,
						n.id_topic  AS msg_lu,
						ts.id_topic  AS topic_abonne 
				FROM '.TABLE_FORUM_TOPICS.' AS t 
					LEFT JOIN '.TABLE_FORUM_FORUMS.' AS f 
						ON (t.id_forum=f.id_forum)
					LEFT JOIN '.TABLE_FORUM_CATS.' AS c 
						ON (f.id_cat=c.id_cat)
					LEFT JOIN '.TABLE_FORUM_POSTS.' as pd
						ON (t.post_depart=pd.id_post)
						LEFT JOIN '.TABLE_USERS.' as ud
							ON (pd.user_id=ud.user_id)
					LEFT JOIN '.TABLE_FORUM_POSTS.' as pf
						ON (t.post_fin=pf.id_post)
					LEFT JOIN '.TABLE_FORUM_TOPICS_NONLUS.' as n
						ON (t.id_topic=n.id_topic AND n.user_id='.$user['user_id'].')
					LEFT JOIN '.TABLE_FORUM_TOPICS_SUIVIS.' as ts
						ON (t.id_topic=ts.id_topic AND ts.user_id='.$user['user_id'].')
				WHERE t.post_fin is null
				AND  c.module="'.$module.'" 
				ORDER BY date_topic DESC
				LIMIT 100';
		$tpl->titre_page = $tpl->titre_navigateur = $lang['L_MSG_SANS_REPONSE'];
		// Navlinks
		$session->make_navlinks(array(
			ucfirst($module)			=> formate_url('',true),
			$lang['L_MSG_SANS_REPONSE']	=> formate_url('mode=sans_reponse',true)
		));
		break;
	default : error404(); break;
}
if ($sql != ''){
	if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,704,__FILE__,__LINE__,$sql); 
	if ($c->sql_numrows($resultat)==0)
	{
		$tpl->assign_block_vars('aucun_resultat', array());
	}else{
		$r = array();
		while ($row = $c->sql_fetchrow($resultat)){
			// Securite
			if (($droits->check($module,$row['id_forum'],'voir')
				&& $droits->check($module,$row['id_forum'],'lire')) || $user['level']==10)	$r[] = $row;
			
		}
		$nbre_enregistrements = count($r);
		if ($nbre_enregistrements ==0)
		{
			$tpl->assign_block_vars('aucun_resultat', array());
		}else{
			for($i=$start;$i<($start+$cf->config['forum_topics_par_forum']);$i++){
				
				if(array_key_exists($i,$r)){
				
					$class = ($class=='row1')? 'row2':'row1';
					$tpl->assign_block_vars('liste_resultats', array(
						'CLASS'			=> $class,
						'LIEN_TOPIC'	=> formate_url('mode=topic&id_topic='.$r[$i]['id_topic'].'&id_post='.$r[$i]['id_post_auteur'],true),
						'LIEN_FORUM'	=> formate_url('mode=forum&id_forum='.$r[$i]['id_forum'],true),
						'AUTEUR'		=> formate_pseudo($r[$i]['user_id'],$r[$i]['pseudo']),
						'DATE'			=> formate_date($r[$i]['date_topic'],'d m Y H i','FORMAT_DATE',$user['fuseau']),
						'TOPIC'			=> $f->formate_titre_sujet($r[$i]['titre_topic']),
						'FORUM'			=> $r[$i]['titre_forum'],
						'NBRE_REPONSES'	=> $r[$i]['reponses_topic'],
						'NBRE_LECTURES'	=> $r[$i]['lectures_topic'],
						'TOPIC_ABONNE'	=> ($r[$i]['topic_abonne']!=$r[$i]['id_topic'])? $img['forum_sujet_non_abonne']:$img['forum_sujet_abonne'],
						'TOPIC_ABONNE_LIBELLE'=> ($r[$i]['topic_abonne']!=$r[$i]['id_topic'])? $lang['L_TOPIC_NON_ABONNE']:$lang['L_TOPIC_ABONNE'],
						'S_PREMIER'		=> formate_url('mode=topic&id_topic='.$r[$i]['id_topic'].'&id_post='.$r[$i]['id_post_auteur'].'#'.$r[$i]['id_post_auteur'],true),
						'S_DERNIER'		=> formate_url('mode=topic&id_topic='.$r[$i]['id_topic'].'&id_post='.$r[$i]['id_post_reponse'].'#'.$r[$i]['id_post_reponse'],true),
						'PAGINATION'	=> create_pagination(0, 'mode=topic&id_topic='.$r[$i]['id_topic'].'&start=', ($r[$i]['reponses_topic']+1) , $cf->config['forum_posts_par_topic'],$lang['L_TOPIC'],true)
					));
					if ($r[$i]['id_post_reponse']!=null)$tpl->assign_block_vars('liste_resultats.dernier', array());
					$icone = ($r[$i]['msg_lu'] != $r[$i]['id_topic'])? 'liste_resultats.icon_lu':'liste_resultats.icon_new';
					$tpl->assign_block_vars($icone, array());
				}
			}
		}
	}
}

$tpl->assign_vars(array(
	'L_NOUVEAU_SUJET'	=> $lang['L_TOPIC_JAMAIS_LU'],
	'L_SUJET_LU'		=> $lang['L_TOPIC_DEJA_LU'],
	'I_ICON_NEW'		=> $img['forum_sujet_non_lu'],
	'I_ICON_LU'			=> $img['forum_sujet_lu'],
	'PAGINATION'		=> create_pagination($start, 'mode='.$mode.'&start=', $nbre_enregistrements , $cf->config['forum_topics_par_forum'],$lang['L_TOPIC'])
));
?>