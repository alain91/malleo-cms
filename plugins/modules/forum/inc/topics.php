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
// Parametres d'entree
if(!isset($_GET['id_topic']) && !isset($_GET['id_post']))
{
	error404();
	exit;
}
$id_post= (isset($_GET['id_post']))?	intval($_GET['id_post']):0;
$id_topic= (isset($_GET['id_topic']))? intval($_GET['id_topic']):0;
$start = (isset($_GET['start']))? intval($_GET['start']):0;


include_once($root.'fonctions/fct_profil.php');
$tpl->set_filenames(array('forum'=>$root.'plugins/modules/forum/html/topic.html'));

//
// RECHERCHE du topic et de la page où se trouve le post demandé
$sql = 'SELECT p.user_id, p.id_post, p.id_topic,
		t.id_topic, t.titre_topic, t.reponses_topic, t.lectures_topic, t.status_topic, t.type_topic, t.fin_annonce,
		f.titre_forum, f.id_forum, f.status_forum, 
		c.titre_cat, c.id_cat,
		tnl.id_topic AS topic_lu,		
		ts.id_topic AS topic_suivis, ts.prevenu, 
		tf.id_topic AS topic_favoris 		
		FROM '.TABLE_FORUM_POSTS.' as p 
		LEFT JOIN '.TABLE_FORUM_TOPICS.' as t 
			ON (p.id_topic=t.id_topic)
		LEFT JOIN '.TABLE_FORUM_TOPICS_NONLUS.' as tnl 
			ON (t.id_topic=tnl.id_topic AND tnl.user_id='.$user['user_id'].')
		LEFT JOIN '.TABLE_FORUM_TOPICS_SUIVIS.' as ts 
			ON (t.id_topic=ts.id_topic AND ts.user_id='.$user['user_id'].')
		LEFT JOIN '.TABLE_FORUM_TOPICS_FAVORIS.' as tf 
			ON (t.id_topic=tf.id_topic AND tf.user_id='.$user['user_id'].')
		LEFT JOIN '.TABLE_FORUM_FORUMS.' as f 
			ON (t.id_forum=f.id_forum)
		LEFT JOIN '.TABLE_FORUM_CATS.' as c 
			ON (f.id_cat=c.id_cat)			
		WHERE c.module=\''.$module.'\'
		AND p.id_topic = ';
if(isset($_GET['id_post']) && !isset($_GET['id_topic'])){
	$sql .=	'(SELECT id_topic FROM '.TABLE_FORUM_POSTS.' WHERE id_post='.$id_post.' LIMIT 1)';
}else{
	$sql .= $id_topic;
}
$sql .=	' ORDER BY p.date_post ASC';
if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,702,__FILE__,__LINE__,$sql);
if ($c->sql_numrows($resultat)==0){
	// topic/post inconnu
	error404();
	exit;
}
$i=0;
while($row = $c->sql_fetchrow($resultat))
{
	if ($i==0){
		// SECURITE
		if (!$droits->check($module,$row['id_forum'],'voir'))	error404(725);
		if (!$droits->check($module,$row['id_forum'],'lire'))	error404(724);
		//SAISIE
		if (($droits->check($module,$row['id_forum'],'ecrire') || $user['level']>9)		&& $row['status_forum']==1) $tpl->assign_block_vars('nouveau', array());
		if (($droits->check($module,$row['id_forum'],'repondre') || $user['level']>9)	&& $row['status_topic']==1) $tpl->assign_block_vars('repondre', array());
		// MODERATION
		if (($droits->check($module,$row['id_forum'],'moderer') || $user['level']>9)	&& $row['status_topic']==1) $tpl->assign_block_vars('verrouiller', array());
		if (($droits->check($module,$row['id_forum'],'moderer') || $user['level']>9)	&& $row['status_topic']==0) $tpl->assign_block_vars('deverrouiller', array());
		// SUPPRESSION
		$jeton = '';
		if (($droits->check($module,$row['id_forum'],'supprimer') && ($row['user_id'] == $user['user_id']))
			|| $droits->check($module,$row['id_forum'],'moderer') || $user['level']>9){
			// Creation du jeton de securite
			if (!session_id()) @session_start();
			$jeton = md5(uniqid(rand(), TRUE));
			$_SESSION['jeton'] = $jeton;
			$_SESSION['jeton_timestamp'] = $session->time;
			$tpl->assign_block_vars('supprimer', array());
		}
		if ($droits->check($module,$row['id_forum'],'moderer') || $user['level']>9)		$tpl->assign_block_vars('deplacer', array());
		if ($droits->check($module,$row['id_forum'],'moderer') || $user['level']>9)		$tpl->assign_block_vars('diviser', array());
		// Abonnements
		if ($user['level']>1){
			if ($row['topic_suivis'] == $row['id_topic']){
				// Suivis donc on propose de resilier
				$tpl->assign_block_vars('resilier', array());
			}else{
				// Pas suivis donc on propose de s'abonner
				$tpl->assign_block_vars('suivre', array());
			}
			if ($row['topic_favoris'] == $row['id_topic']){
				// déjà en favoris donc on propose de l'enlever
				$tpl->assign_block_vars('favoris_del', array());
			}else{
				// Pas en favoris on propose de l'ajouter
				$tpl->assign_block_vars('favoris_add', array());
			}
		}
		
		// On verifie que si le sujet est une annonce, que celle-ci ne soit plus obsolete
		if ($row['type_topic']==2 && $row['fin_annonce'] < $session->time){
			$f->update_topic_fin_annonce($row['id_topic']);
		}

		// on incremente de 1 pour comptabiliser le premier message
		$nbre_posts = ($row['reponses_topic']+1);
		$f->id_topic = $row['id_topic'];
		$f->id_forum = $row['id_forum'];
		
		// Titres
		$tpl->titre_navigateur = $row['titre_topic'].' :: '.$row['titre_cat'].' :: '.$row['titre_forum'];
		$tpl->titre_page = $f->formate_titre_sujet($row['titre_topic']);


		// Navlinks
		$session->make_navlinks(array(
			ucfirst($module)	=> formate_url('',true),
			$row['titre_cat']	=> formate_url('mode=cat&id_cat='.$row['id_cat'],true),
			$row['titre_forum']	=> formate_url('mode=forum&id_forum='.$f->id_forum,true),
			$row['titre_topic']	=> formate_url('mode=topic&id_topic='.$f->id_topic,true)
		));

		// On marque le topic comme lu
		if ($row['topic_lu'] == $row['id_topic']) $f->marquer_lu($f->id_topic);	

		// On marque ce topic comme lu dans les sujets suivis
		if ($row['prevenu'] == true) $f->marquer_suivis_lu($f->id_topic);
	}
	if ($row['id_post'] == $id_post)
	{
		$id_topic = $row['id_topic'];
		$start = (floor($i/$cf->config['forum_posts_par_topic'])*$cf->config['forum_posts_par_topic']);
		break;
	}
	$i++;
}

// Incrémentation du compteur de lectures
$f->incremente_lecture_topic();
		
		
load_lang('utilisateurs');
// récupération de la liste des champs à afficher
// SINON on affiche seulement les champs obligatoires
if (file_exists(PATH_LISTE_CHAMPS_PROFILE))
{
	$chps_o = unserialize(file_get_contents(PATH_LISTE_CHAMPS_PROFILE));
}

require($root.'fonctions/fct_formulaires.php');
// Récupération des champs configurés dans la modélisation
require_once($root.'class/class_modelisation.php');
$md = new Modelisation();
$md->page = 'Utilisateurs'; // Nom du champs page dans la table de modélisation
// Nous ne sommes pas dans la configuration d'une liste de champs comma la config
// donc on déporte toutes les sorties de fonctions 
$md->deporter = true;
$md->generer_saisie('DEPORTER'); // Lancement du generateur et Récupération des champs configurés sous la forme champs1, champs2, ...

// Classement alphabetique
asort($chps_o);

//
// LISTING des posts

$sql = 'SELECT '.implode(',',$chps_o).',u.user_id,u.msg,u.rang,u.signature, p.text_post, p.id_post, p.date_post 
		FROM '.TABLE_FORUM_POSTS.' as p
		LEFT JOIN '.TABLE_USERS.' as u
			ON (p.user_id=u.user_id)
		WHERE p.id_topic='.$f->id_topic.' 
		ORDER BY date_post ASC
		LIMIT '.$start.','.$cf->config['forum_posts_par_topic'];
$i=$start;
if (!$resultat = $c->sql_query($sql)) message_die(E_ERROR,702,__FILE__,__LINE__,$sql);
while($row = $c->sql_fetchrow($resultat))
{
	// Meta Description
	$tpl->meta_description .= $post->bbcode2html($row['text_post']);
	// Url Canonique
	if (empty($tpl->url_canonique)) $tpl->url_canonique = formate_url('mode=topic&id_topic='.$f->id_topic.'&id_post='.$row['id_post'],true);
	// Infos du post
	$tpl->assign_block_vars('liste_topics', array(
		'AUTEUR'		=> formate_pseudo($row['user_id'],$row['pseudo']),
		'AVATAR'		=> $row['avatar'],
		'RANG'			=> formate_rang($row['rang'],$row['msg']),
		'DATE'			=> formate_date($row['date_post'],'d m Y H i','FORMAT_DATE',$user['fuseau']),
		'TEXTE'			=> $post->bbcode2html($row['text_post']),
		'SIGNATURE'		=> $post->bbcode2html($row['signature']),
		'ID_POST'		=> $row['id_post'],
		'S_POST'		=> formate_url('mode=topic&id_topic='.$f->id_topic.'&id_post='.$row['id_post'].'#'.$row['id_post'],true),
		'S_EDITER'		=> formate_url('mode=EditerPost&id_post='.$row['id_post'],true),
		'S_EFFACER'		=> formate_url('mode=SupprimerPost&id_post='.$row['id_post'].'&jeton='.$jeton,true)
	));
	
	// Infos du user
	foreach ($chps_o as $key=>$val)
	{
		if (!in_array($val,array('pseudo','avatar')))
		{
			$md->valeur_actuelle = $row[$val];
			$rep = $md->formate_affichage($md->liste_champs[$val]['nom_champs'],$md->liste_champs[$val]['type_saisie'],$md->liste_champs[$val]['param']);
			// Certains champs beneficient d'un affichage spécifique
			$rep = formate_info_user($val,$rep);
			if (!empty($rep)){
				$tpl->assign_block_vars('liste_topics.infos_user', array(
					'LANG'	=> $lang[$md->liste_champs[$val]['lang']],
					'INFO'	=> $rep
				));
			}
		}
	}
	if (($droits->check($module,$f->post['id_forum'],'editer') && ($user['user_id'] == $row['user_id'])) || $droits->check($module,$f->post['id_forum'],'moderer') || $user['level'] > 9) $tpl->assign_block_vars('liste_topics.editer_post', array());
	if ((($droits->check($module,$f->post['id_forum'],'supprimer') && ($user['user_id'] == $row['user_id'])) || $droits->check($module,$f->post['id_forum'],'moderer') || $user['level'] > 9) && $i>0 ) $tpl->assign_block_vars('liste_topics.effacer_post', array());
	$i++;
}

// WYSIWYG
if ($cf->config['wysiwyg_editor']!='') include_once($root.'fonctions/fct_'.$cf->config['wysiwyg_editor'].'.php');


// PAGINATION (preparation)
include($root.'fonctions/fct_affichage.php');

$tpl->assign_vars(array(
	'S_NOUVEAU'		=> formate_url('mode=NouveauTopic&id_forum='.$f->id_forum,true),
	'S_REPONDRE'	=> formate_url('mode=NouveauPost&id_topic='.$f->id_topic,true),		
	'S_VERROUILLER'	=> formate_url('mode=VerrouillerTopic&id_topic='.$f->id_topic,true),		
	'S_DEVERROUILLER'=> formate_url('mode=DeVerrouillerTopic&id_topic='.$f->id_topic,true),		
	'S_SUPPRIMER'	=> formate_url('mode=SupprimerTopic&id_topic='.$f->id_topic.'&jeton='.$jeton,true),
	'S_DEPLACER'	=> formate_url('mode=DeplacerTopic&id_topic='.$f->id_topic,true),
	'S_DIVISER'		=> formate_url('mode=DiviserTopic&id_topic='.$f->id_topic,true),
	'S_SUIVRE'		=> formate_url('mode=SuivreTopic&id_topic='.$f->id_topic,true),	
	'S_RESILIER'	=> formate_url('mode=ResilierTopic&id_topic='.$f->id_topic,true),	
	'S_FAVORIS_ADD'	=> formate_url('mode=AjouterFavoris&id_topic='.$f->id_topic,true),	
	'S_FAVORIS_DEL'	=> formate_url('mode=SupprimerFavoris&id_topic='.$f->id_topic,true),	
	
	'I_NOUVEAU'		=> $img['nouveau'],
	'I_REPONDRE'	=> $img['repondre'],
	'I_REPONSE_RAPIDE'=> $img['reponse_rapide'],
	'I_VERROUILLER'	=> $img['verrouiller'],
	'I_DEVERROUILLER'=> $img['deverrouiller'],
	'I_SUPPRIMER'	=> $img['supprimer'],
	'I_DEPLACER'	=> $img['deplacer'],
	'I_DIVISER'		=> $img['diviser'],
	'I_EFFACER'		=> $img['effacer'],
	'I_EDITER'		=> $img['editer'],
	'I_DETAILS'		=> $img['forum_sujet_non_abonne'],
	'I_SUIVRE'		=> $img['suivre'],
	'I_RESILIER'	=> $img['resilier'],
	'I_FAVORIS_ADD'	=> $img['forum_sujet_favoris_add'],
	'I_FAVORIS_DEL'	=> $img['forum_sujet_favoris_del'],
	
	'MODULE'		=> $module,	
	'ID_TOPIC'		=> $id_topic,	
	'PAGINATION'	=> create_pagination($start, 'mode=topic&id_topic='.$f->id_topic.'&start=', $nbre_posts, $cf->config['forum_posts_par_topic'],$lang['L_POST'])
));
?>
