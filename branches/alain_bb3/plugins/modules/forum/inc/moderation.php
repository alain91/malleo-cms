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

switch($mode)
{
	case 'VerrouillerTopic':
		if (isset($_GET['id_topic']))
		{
			$f->clean($_GET);
			$f->Get_Topic();
			if (!$droits->check($module,$f->topic['id_forum'],'moderer') && $user['level']<10) error404(723);
			$f->verrouiller_topic();
		}
		break;
	case 'DeVerrouillerTopic':
		if (isset($_GET['id_topic']))
		{
			$f->clean($_GET);
			$f->Get_Topic();
			if (!$droits->check($module,$f->topic['id_forum'],'moderer') && $user['level']<10) error404(723);
			$f->deverrouiller_topic();
		}
		break;
	case 'VerrouillerForum':
		if (isset($_GET['id_forum']))
		{
			$f->clean($_GET);
			$f->Get_Forum();
			if (!$droits->check($module,$f->forum['id_forum'],'verrouiller') && $user['level']<10) error404(723);
			$f->verrouiller_forum();
		}
		break;
	case 'DeVerrouillerForum':
		if (isset($_GET['id_forum']))
		{
			$f->clean($_GET);
			$f->Get_Forum();
			if (!$droits->check($module,$f->forum['id_forum'],'verrouiller') && $user['level']<10) error404(723);
			$f->deverrouiller_forum();
		}
		break;
	case 'DeplacerTopic':
		if (isset($_POST['id_forum']) && isset($_POST['id_topic']))
		{
			$f->clean($_POST);
			$f->Get_Topic();
			if (!$droits->check($module,$f->topic['id_forum'],'moderer') && $user['level']<10) error404(723);
			$f->id_topic = intval($_POST['id_topic']);
			$f->id_forum = intval($_POST['id_forum']); // ID du forum de destination et non actuel
			$f->deplacer_topic();
		}elseif( isset($_GET['id_topic'])){
			$tpl->set_filenames(array('forum'=>$root.'plugins/modules/forum/html/deplacer.html'));
			$f->clean($_GET);
			$f->Get_Topic();
			if (!$droits->check($module,$f->topic['id_forum'],'moderer') && $user['level']<10) error404(723);
			
			// Metas
			$tpl->titre_navigateur = $lang['L_DEPLACER_TOPIC']. ' '.$f->topic['titre_topic'] ;
			$tpl->titre_page = $f->formate_titre_sujet($f->topic['titre_topic']);
			
			// Navlinks
			$session->make_navlinks(array(
				ucfirst($module)			=> formate_url('',true),
				$f->topic['titre_forum']	=> formate_url('mode=forum&id_forum='.$f->topic['id_forum'],true),
				$f->topic['titre_topic']	=> formate_url('mode=topic&id_topic='.$f->id_topic,true)
			));
			
			$tpl->assign_vars(array(
				'TITRE_TOPIC'		=> $f->topic['titre_topic'],
				'ID_TOPIC'			=> $f->id_topic,
				'LISTE_FORUMS'		=> $f->affiche_select_liste_forums(),
			));
		}
		break;
	case 'DiviserTopic':
		$selected_forum = false;
		if (isset($_POST['enregistrer']) && (!isset($_POST['post']) || !is_array($_POST['post']) || sizeof($_POST['post'])==0 || $_POST['titre']=='' 
					|| intval($_POST['id_forum'])==0)){
			erreur_saisie('erreur_saisie',$lang['L_TOUT_REMPLIR'],array(
				'TITRE'=>isset($_POST['titre'])?stripslashes($_POST['titre']):''));					
			$selected_forum = 	(intval($_POST['id_forum'])>0)?intval($_POST['id_forum']):false;
			
		}elseif (isset($_POST['enregistrer'])){
			// Verif des droits
			$f->clean($_POST);
			$f->Get_Topic();
			$ancien_id_topic = $f->id_topic;
			if (!$droits->check($module,$f->topic['id_forum'],'moderer') && $user['level']<10) error404(723);
			
			// Division
			// On trouve les ID des posts en fonction de la methode selectionnee
			$liste_topics = '';
			if (isset($_POST['methode']) && $_POST['methode']=='A_Partir'){
				// Recherche du premier message coche
				$liste_topics = implode(',',$f->recherche_liste_posts($_POST['post'][0]));			
			}else{
				// Liste des message coches
				$liste_topics = implode(',',$_POST['post']);
			}
			// Nouveau topic
			// +  Update nombres sujets dans le forum selectionne
			$f->creer_topic();
			$nouveau_id_topic =  $f->id_topic;
					
			// Update des id_topic des posts choisis					
			$f->update_id_topic_from_id_post($liste_topics);
			
			//Update du nombre de message dans les sujets choisis
			$f->id_topic = $ancien_id_topic;
			$f->maj_topic();
			$f->id_topic = $nouveau_id_topic;
			$f->maj_topic();
			
			// Maj nombre sujet dans le forum de depart
			// si le forum a change
			if ($f->topic['id_forum']!=intval($_POST['id_forum'])){
				$f->maj_forums($f->topic['id_forum']);
			}
			
			// Ajout du nouveau sujet, aux topics non lus
			$f->marquer_non_lu($f->id_topic);
			
			// retour 
			affiche_message('forum','L_TOPIC_DIVISE',formate_url('mode=topic&id_topic='.$f->id_topic,true));
			break;
		}
		if(isset($_GET['id_topic'])){
			$tpl->set_filenames(array('forum'=>$root.'plugins/modules/forum/html/diviser.html'));
			
			// Infos sur le topic
			$f->clean($_GET);
			$f->Get_Topic();
			if (!$droits->check($module,$f->topic['id_forum'],'moderer') && $user['level']<10) error404(723);
			
			// Navlinks
			$session->make_navlinks(array(
				ucfirst($module)			=> formate_url('',true),
				$f->topic['titre_forum']	=> formate_url('mode=forum&id_forum='.$f->topic['id_forum'],true),
				$f->topic['titre_topic']	=> formate_url('mode=topic&id_topic='.$f->id_topic,true)
			));
			
			// Metas
			$tpl->titre_navigateur = $lang['L_DIVISER_TOPIC']. ' '.$f->topic['titre_topic'] ;
			$tpl->titre_page = $f->formate_titre_sujet($f->topic['titre_topic']);
			
			// Listing des messages
			$sql = 'SELECT u.user_id, u.pseudo, p.text_post, p.id_post, p.date_post 
				FROM '.TABLE_FORUM_POSTS.' as p
				LEFT JOIN '.TABLE_USERS.' as u
					ON (p.user_id=u.user_id)
				WHERE p.id_topic='.$f->id_topic.' 
				ORDER BY date_post ASC';
			if (!$resultat = $c->sql_query($sql)) message_die(E_ERROR,702,__FILE__,__LINE__,$sql);
			$class='row2';
			$i=0;
			while($row = $c->sql_fetchrow($resultat))
			{
				$class=($class=='row2')?'row1':'row2';
				$msg = utf8_encode(html_to_str($post->bbcode2html($row['text_post'])));
				$msg = strip_tags($msg);
				$msg = str_to_html((strlen($msg)>200)? substr($msg,0,200).'...'.$lang['L_MSG_TRONQUE']:$msg);
				$tpl->assign_block_vars('liste_messages', array(
					'PSEUDO'	=> formate_pseudo($row['user_id'],$row['pseudo']),
					'DATE'		=> formate_date($row['date_post'],'d m Y H i','FORMAT_DATE',$user['fuseau']),
					'MESSAGE'	=> $msg,
					'ID_POST'	=> $row['id_post'],
					'CLASS'		=> $class,
					'DISABLED'	=> ($i==0)?' disabled':'',
				));
				$i++;
			}
		
			$tpl->assign_vars(array(
				'TITRE_TOPIC'		=> $f->topic['titre_topic'],
				'ID_TOPIC'			=> $f->id_topic,
				'LISTE_FORUMS'		=> $f->affiche_select_liste_forums($selected_forum),
			));
		}
		break;
	case 'FusionnerTopics':
		$selected_forum = false;
		if (isset($_POST['enregistrer']) && (!isset($_POST['topic']) || !is_array($_POST['topic']))){
			erreur_saisie('erreur_saisie',$lang['L_TOUT_REMPLIR']);
		}elseif (isset($_POST['enregistrer']) && sizeof($_POST['topic'])<=1 ){
			erreur_saisie('erreur_saisie',$lang['L_SAISIR_2_SUJETS']);		
		}elseif (isset($_POST['enregistrer'])){
			// Verif des droits
			$f->clean($_POST);
			$f->Get_Forum();
			if (!$droits->check($module,$f->forum['id_forum'],'moderer') && $user['level']<10) error404(723);
			
			// On trouve les ID des sujets
			// sort()  afin que le plus ancien topic sorte en premier (NB: id_topic le plus petit)
			sort($_POST['topic']);
			$f->id_topic = $_POST['topic'][0];
			
			// On supprime le Sujet referent de la liste des sujets a traiter (ca evite de le supprimer dans le traitement un peu plus loin)
			$_POST['topic'] = array_slice ($_POST['topic'], 1);
			$liste_topics = '';
			$liste_topics = implode(',',$_POST['topic']);
						
			// Update des id_topic des posts choisis					
			$f->update_id_topic_from_id_topic($liste_topics);
			
			//Update du nombre de message dans le sujet
			$f->maj_topic();
			
			// Suppression des topics inutiles
			$sql = 'DELETE FROM '.TABLE_FORUM_TOPICS.' WHERE id_topic IN ('.$liste_topics.')';
			if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,714,__FILE__,__LINE__,$sql);
			$sql = 'DELETE FROM '.TABLE_FORUM_TOPICS_NONLUS.' WHERE  id_topic IN ('.$liste_topics.')';
			if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,714,__FILE__,__LINE__,$sql);
			
			// Maj nombre sujet dans le forum de depart
			$f->maj_forums($f->forum['id_forum']);
			
			// retour 
			affiche_message('forum','L_TOPICS_FUSIONNES',formate_url('mode=forum&id_forum='.$f->forum['id_forum'],true));
			break;
		}
		if(isset($_GET['id_forum'])){
			$tpl->set_filenames(array('forum'=>$root.'plugins/modules/forum/html/fusionner.html'));
			
			// Infos sur le topic
			$f->clean($_GET);
			$f->Get_Forum();
			if (!$droits->check($module,$f->forum['id_forum'],'moderer') && $user['level']<10) error404(723);
			
			// Navlinks
			$session->make_navlinks(array(
				ucfirst($module)			=> formate_url('',true),
				$f->forum['titre_forum']	=> formate_url('mode=forum&id_forum='.$f->forum['id_forum'],true)
			));
			
			// Metas
			$tpl->titre_navigateur = $lang['L_FUSIONNER_TOPICS']. ' '.$f->forum['titre_forum'] ;
			$tpl->titre_page = $f->forum['titre_forum'];
			
			// Listing des sujets		
			$sql = 'SELECT t.id_topic, t.date_topic, t.titre_topic, t.date_topic, u.user_id , u.pseudo
					FROM '.TABLE_FORUM_TOPICS.' as t 
					LEFT JOIN '.TABLE_FORUM_POSTS.' as p
						ON (t.post_depart=p.id_post)
					LEFT JOIN '.TABLE_USERS.' as u
						ON (p.user_id=u.user_id)
					WHERE t.id_forum='.$f->forum['id_forum'].'
					ORDER BY date_topic DESC';
			if (!$resultat = $c->sql_query($sql)) message_die(E_ERROR,702,__FILE__,__LINE__,$sql);
			$class='row2';
			$i=0;
			while($row = $c->sql_fetchrow($resultat))
			{
				$class=($class=='row2')?'row1':'row2';
				$titre = utf8_encode(html_to_str($post->bbcode2html($row['titre_topic'])));
				$titre = str_to_html((strlen($titre)>200)? substr($titre,0,200).'...'.$lang['L_MSG_TRONQUE']:$titre);
				$tpl->assign_block_vars('liste_messages', array(
					'PSEUDO'	=> formate_pseudo($row['user_id'],$row['pseudo']),
					'DATE'		=> formate_date($row['date_topic'],'d m Y H i','FORMAT_DATE',$user['fuseau']),
					'SUJET'		=> $f->formate_titre_sujet($titre),
					'URL_SUJET'	=> formate_url('mode=topic&id_topic='.$row['id_topic'],true),
					'ID_TOPIC'	=> $row['id_topic'],
					'CLASS'		=> $class
				));
				$i++;
			}
			$tpl->assign_vars(array(
				'ID_FORUM'			=> $f->forum['id_forum'],
			));
		}
		break;
}
?>