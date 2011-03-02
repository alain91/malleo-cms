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

// SECURITE
if ($user['user_id']<2){
	error404(1200);
}

// Chargement OUTILS
require_once($root.'plugins/modules/messagerie/prerequis.php');
$mp->user_id = $user['user_id'];

// Squelette de page
$tpl->set_filenames(array('messagerie'=>$root.'plugins/modules/messagerie/html/mod_messagerie.html'));

$session->make_navlinks(array($module=> formate_url('',true)));
$tpl->options_page[] = array(
	'ICONE'		=> $img['messagerie_menu_options'],
	'LIBELLE'	=> $lang['L_OPTIONS'],
	'LIEN'		=> formate_url('mode=options',true)
);
			



// Box latérales
$mp->afficher_menu_lateral();
$mp->afficher_liste_contacts();
		
$action = $mode = null;
if(isset($_GET['mode'])||isset($_POST['mode'])) $mode=(isset($_POST['mode']))?$_POST['mode']:$_GET['mode'];
if(isset($_GET['action'])||isset($_POST['action'])) $action=(isset($_POST['action']))?$_POST['action']:$_GET['action'];
global $mode;

switch($mode){
	case 'marquerlu':
		if (isset($_POST['id_mp'])){
			$mp->clean($_POST);
			$mp->marquer_lu();
		}
		header('Location: '.formate_url('mode='.$mp->retour,true));
		break;
	case 'marquernonlu':
		if (isset($_POST['id_mp'])){
			$mp->clean($_POST);
			$mp->marquer_nonlu();
		}
		header('Location: '.formate_url('mode='.$mp->retour,true));
		break;
	case 'archiver':
		if (isset($_POST['id_mp'])){
			$mp->clean($_POST);
			$mp->archiver();
		}
		header('Location: '.formate_url('mode='.$mp->retour,true));
		break;
	case 'supprimer':
		if (isset($_POST['id_mp'])){
			$mp->clean($_POST);
			$mp->supprimer_mp();
		}elseif(isset($_GET['id_mp'])){
			$mp->clean($_GET);
			$mp->retour='inbox';
			$mp->supprimer_mp();
		}
		header('Location: '.formate_url('mode='.$mp->retour,true));
		break;
	case 'lecture':
		// Affichage de la boîte de réception
		$mp->clean($_GET);
		$mp->lecture_mp();
		break;
	case 'options':
		// ENREGISTREMENT des options
		if (isset($_POST['enregistrer'])){
			$mp->clean($_POST);
			$mp->update_element( '	messagerie_copie_mail='.$mp->messagerie_copie_mail.', 
									messagerie_accepter_mp='.$mp->messagerie_accepter_mp.', 
									messagerie_accepter_mail='.$mp->messagerie_accepter_mail.', 
									messagerie_absent_site='.$mp->messagerie_absent_site.', 
									messagerie_absent_site_msg=\''.$mp->messagerie_absent_site_msg.'\'');
			header('Location: '.formate_url('mode=options',true));
		}
		// TITRE Page + NavLinks
		$tpl->titre_navigateur = $tpl->titre_page = $lang['L_OPTIONS'];
		$session->make_navlinks(array($lang['L_OPTIONS']	=> formate_url('mode=options',true)));
		
		// Affichage des différentes options
		$mp->options = array(
			'messagerie_copie_mail'		=> array('booleen',$lang['L_COPIE_MAIL'],	array(1=>$lang['L_OUI'],0=>$lang['L_NON'])),
			'messagerie_accepter_mp'	=> array('booleen',$lang['L_ACCEPTER_MP'],	array(1=>$lang['L_TOUS'],0=>$lang['L_CONTACTS'])),
			'messagerie_accepter_mail'	=> array('booleen',$lang['L_ACCEPTER_MAIL'],array(1=>$lang['L_TOUS'],0=>$lang['L_CONTACTS'])),
			'messagerie_absent_site'	=> array('booleen',$lang['L_ABSENT_SITE'],	array(1=>$lang['L_OUI'],0=>$lang['L_NON'])),
			'messagerie_absent_site_msg'=> array('texte',$lang['L_ABSENT_SITE_MSG'],($user['messagerie_absent_site_msg']!=null)?$user['messagerie_absent_site_msg']:''),
		);
		if ($cf->config['wysiwyg_editor']!='') include_once($root.'fonctions/fct_'.$cf->config['wysiwyg_editor'].'.php');
		$mp->parametrer_mp();
		break;
	case 'contacts':
		// TITRE Page + NavLinks
		$tpl->titre_navigateur = $tpl->titre_page = $lang['L_CONTACTS'];
		$session->make_navlinks(array($lang['L_CONTACTS']	=> formate_url('mode=contacts',true)));
		
		switch ($action){
			case 'delete':
				$mp->contact_delete(intval($_GET['id']));
				header('Location: '.formate_url('mode=contacts',true));
				break;
			case 'ajouter':
				$pseudo = nettoyage_nom((isset($_POST['pseudo']))? $_POST['pseudo']:$_GET['pseudo']);
				$mp->contact_ajouter($pseudo);
				header('Location: '.formate_url('mode=contacts',true));
				break;
			default:
				// Affichage des contacts
				$mp->lister_contacts();
		}
		break;
	case 'savebox':
		// TITRE Page + NavLinks
		$tpl->titre_navigateur = $tpl->titre_page = $lang['L_SAVEBOX'];
		$session->make_navlinks(array($lang['L_SAVEBOX']	=> formate_url('mode=savebox',true)));
		
		// Affichage des archives
		$mp->lister_archives();
		break;
	case 'sentbox':
		// TITRE Page + NavLinks
		$tpl->titre_navigateur = $tpl->titre_page = $lang['L_SENTBOX'];
		$session->make_navlinks(array($lang['L_SENTBOX']	=> formate_url('mode=sentbox',true)));
		
		// Affichage de la boîte d'envoi
		$mp->lister_boite_envoyes();
		break;
	case 'outbox':
		// TITRE Page + NavLinks
		$tpl->titre_navigateur = $tpl->titre_page = $lang['L_OUTBOX'];
		$session->make_navlinks(array($lang['L_OUTBOX']	=> formate_url('mode=outbox',true)));
		
		// Affichage de la boîte d'envoi
		$mp->lister_boite_envoi();
		break;
	case 'newmail':
		// TITRE Page + NavLinks
		$tpl->titre_navigateur = $tpl->titre_page = $lang['L_NEWMAIL'];
		$session->make_navlinks(array($lang['L_NEWMAIL']	=> formate_url('mode=newmail',true)));
		
		if ($action=='nouveau' && (empty($_POST['a']) || empty($_POST['sujet']) || empty($_POST['message']))){
			erreur_saisie('erreur_saisie',$lang['L_REMPLISSEZ_CHAMPS'],array(
				'A'=>isset($_POST['a'])?stripslashes($_POST['a']):'',
				'SUJET'=>isset($_POST['sujet'])?stripslashes($_POST['sujet']):'',
				'MESSAGE'=>isset($_POST['message'])?stripslashes($_POST['message']):''));	
			$action='';
		}
		switch($action){
			case 'nouveau':
				$mp->clean($_POST);
				
				// Chargement de la configuration emails
				require_once($root.'class/class_mail.php');
				$email = new mail();
				$email->Subject = stripslashes($lang['L_MAIL_SUJET']);
				$email->message_explain = sprintf($lang['L_ENTETE_EMAIL'],$user['pseudo'],(isset($_SERVER['REMOTE_HOST']))?$_SERVER['REMOTE_HOST']:$session->ip);
				$email->titre_message = stripslashes($mp->sujet);
				$email->formate_html(html_entity_decode(stripslashes($post->bbcode2html($mp->message))));
				if ($mp->send_mail() == false){
					// Aucune adresse ne concorde, ou les destinataires ne souhaites pas recevoir de Mails
					affiche_message('message_envoye','L_MAIL_NON_ENVOYE',formate_url('mode=inbox',true));
				}else{
					// Si au moins 1 destinataire veut une alerte mail
					if (count($email->to)>0){
						$email->Send();
					}
					affiche_message('message_envoye','L_MAIL_ENVOYE',formate_url('mode=inbox',true));
				}				
				$tpl->assign_var_from_handle('ZONE_CENTRALE','message_envoye');
				break;
			default:
				// Affichage champs de saisie
				$WYSIWYG_METHODE='html';
				if ($cf->config['wysiwyg_editor']!='') include_once($root.'fonctions/fct_'.$cf->config['wysiwyg_editor'].'.php');
				$mp->afficher_saisie_mail();
		}
		break;
	case 'newmp':
		// TITRE Page + NavLinks
		$tpl->titre_navigateur = $tpl->titre_page = $lang['L_NEWMP'];
		$session->make_navlinks(array($lang['L_NEWMP']	=> formate_url('mode=newmp',true)));
		if ($action=='nouveau' && (empty($_POST['a']) || empty($_POST['sujet']) || empty($_POST['message']))){
			erreur_saisie('erreur_saisie',$lang['L_REMPLISSEZ_CHAMPS'],array(
				'MESSAGE'=>isset($_POST['message'])?stripslashes($_POST['message']):''));	
			$_GET = $_POST;
			$action='';
		}
		switch($action){
			case 'nouveau':
				$mp->clean($_POST);
				
				// Chargement de la configuration emails
				require_once($root.'class/class_mail.php');
				$email = new mail($cf->config);
				$email->Subject = stripslashes($lang['L_MP_SUJET']);
			
				
				if ($mp->send_mp() == false){
					// Aucune adresse ne concorde, ou les destinataires ne souhaites pas recevoir de MP
					affiche_message('message_envoye','L_MP_NON_ENVOYE',formate_url('mode=inbox',true));
				}else{
					// On enregistre le MP + Ajout des adresse emails dans la liste des destinataires
					$url = 'http://'.$cf->config['adresse_site'].$cf->config['path'].'index.php?module=messagerie&amp;mode=lecture&amp;id_mp='.$mp->id_mp;
					$email->message_explain = sprintf($lang['L_MP_BODY_HTML'],$url,$url);
					$email->titre_message = stripslashes($mp->sujet);
					$email->formate_html($post->bbcode2html(stripslashes($mp->message)));				
					// Si au moins 1 destinataire veut une alerte mail
					if (count($email->to)>0){
						$email->Send();
					}
					affiche_message('message_envoye','L_MP_ENVOYE',formate_url('mode=lecture&id_mp='.$mp->id_mp,true));
				}
				$tpl->assign_var_from_handle('ZONE_CENTRALE','message_envoye');
				break;
			default:
				// Affichage champs de saisie
				$mp->clean($_GET);
				if ($cf->config['wysiwyg_editor']!='') include_once($root.'fonctions/fct_'.$cf->config['wysiwyg_editor'].'.php');
				$mp->afficher_saisie_mp();
		}
		break;
	case 'inbox':
	default:
		// TITRE Page + NavLinks
		$tpl->titre_navigateur = $tpl->titre_page = $lang['L_INBOX'];
		$session->make_navlinks(array($lang['L_INBOX']	=> formate_url('mode=inbox',true)));
		
		// Affichage de la boîte de réception
		$mp->lister_boite_reception();
		break;
}

?>