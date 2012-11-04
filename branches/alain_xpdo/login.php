<?php
/*
|------------------------------------------------------------------------------------------------------------
| Software: Malleo ( CMS )
| Contact:  SP - http://www.malleo-cms.com
| Support: http://www.malleo-cms.com?module=forum
|  Documentation : Support: http://www.malleo-cms.com?module=wiki
|------------------------------------------------------------------------------------------------------------
|  Author: Alain GANDON
|  Copyright (c) 2012, Alain GANDON All Rights Reserved
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
define('PROTECT',true);
global $root,$droits,$session,$lang,$erreur;
define('DUREE_BANNISSEMENT',3600); // 1h en secondes

$root = './';
require_once($root.'/chargement.php');
$style_name=load_style();
$lang=$erreur=array();
load_lang('defaut');
load_lang('login');

$tpl->set_filenames(array(
	  'body' =>  $root . 'html/login.html'
));

function hacking_detection($time)
{
	global $droits,$session,$lang;
	$_SESSION['essais_login'] = 0;
	$droits->ban_ip($session->ip,$time,sprintf($lang['L_LOGIN_HACK'],$_POST['login']));
	header('location: ./login.php');
	exit;
}

// Mdp perdu
if(isset($_POST['mail_mdp'])){
	// Chargement de la librairie Captcha
	$cryptinstall = $root.'/librairies/crypt/cryptographp.fct.php';
	include_once($cryptinstall);
    $_POST['login'] = trim($_POST['login']);
    $_POST['code'] = trim($_POST['code']);

	// Tentative de hacking?
	$essais = (isset($_SESSION['essais_login']))?intval($_SESSION['essais_login']):0;

	if (empty($_POST['login']) || !chk_crypt($_POST['code'])){
		// on invite le user a recommencer
		$essais++;
		if ($essais > 5) hacking_detection($session->time+DUREE_BANNISSEMENT);
		$_SESSION['essais_login'] = $essais;
		header('location: ./login.php?renvoyer');
		exit;
	}
	//On recherche le user
	$pseudo	= nettoyage_nom($_POST['login']);
	$sql = 'SELECT user_id,email,pseudo FROM '.TABLE_USERS.'
			WHERE pseudo=\''.str_replace("\'","''",$pseudo).'\' LIMIT 1';
	if (!$resultat = $c->sql_query($sql)) message_die(E_ERROR,24,__FILE__,__LINE__,$sql);
	if ($c->sql_numrows($resultat) == 0){
		// on invite le user a recommencer
		$essais++;
		if ($essais > 5) hacking_detection($session->time+DUREE_BANNISSEMENT);
		$_SESSION['essais_login'] = $essais;
		header('location: ./login.php?renvoyer');
		exit;
	}
	unset($_SESSION['essais_login']);
	$row = $c->sql_fetchrow($resultat);
	$pseudo = $row['pseudo'];
	$mail = $row['email'];
	//On change le MDP
	require($root.'fonctions/fct_maths.php');
	$mdp = generate_key(6);
	$sql = 'UPDATE '.TABLE_USERS.' SET pass=\''.md5($mdp).'\' WHERE user_id='.$row['user_id'].' LIMIT 1';
	if (!$c->sql_query($sql)) message_die(E_ERROR,24,__FILE__,__LINE__,$sql);

	// On envoie le mail
	load_lang('emails');
	require($root.'class/class_mail.php');
	$email = new mail();
	$email->Subject = stripslashes($lang['L_MAIL_MAILPERDU_SUJET']);
	$email->message_explain = stripslashes(sprintf($lang['L_MAIL_MAILPERDU_BODY_HTML'],'http://'.$cf->config['adresse_site'].$cf->config['path'].'login.php'));
	$email->titre_message = stripslashes($lang['L_MAIL_MAILPERDU_SUJET']);
	$email->formate_html(sprintf($lang['L_MAIL_MAILPERDU_MESSAGE'],$pseudo, $mdp));
	$email->AddAddress($mail,$pseudo);
	$email->Send();
	$tpl->assign_block_vars('mail_envoye', array());

}elseif (isset($_GET['renvoyer'])){
	// Chargement de la librairie Captcha
	$cryptinstall = $root.'/librairies/crypt/cryptographp.fct.php';
	include_once($cryptinstall);

	// Tentative de hacking?
	$essais = (isset($_SESSION['essais_login']))?intval($_SESSION['essais_login']):0;

	$tpl->assign_block_vars('formulaire_renvoi', array());
	$tpl->assign_vars(array(
		'CAPTCHA'			=>	dsp_crypt(0,1),
		'ALERTE'			=>	sprintf($lang['L_ERREUR_CAPTCHA_CODE'],$essais)
	));
	if ($essais > 0) $tpl->assign_block_vars('formulaire_renvoi.essais', array());

// Une requete a ete postee
}elseif (isset($_POST['authentifier'])){
    $_POST['login']=trim($_POST['login']);
    $_POST['pass']=trim($_POST['pass']);
	$pseudo	= nettoyage_nom($_POST['login']);
	$mdp = $_POST['pass'];
	$droits->charge_bannis();
	if (array_key_exists(0,$droits->liste_bannis)){
		if(array_key_exists(stripslashes($pseudo),$droits->liste_bannis[0])){
			message_die(E_WARNING,61,'','');
		}
	}
	// On verifie que le user / mdp existe
	$sql = 'SELECT user_id, email FROM '.TABLE_USERS.'
			WHERE pseudo=\''.str_replace("\'","''",$pseudo).'\'
			AND pass=\''.md5($mdp).'\'
			AND actif=1 LIMIT 1';
	if (!$resultat = $c->sql_query($sql)) message_die(E_ERROR,24,__FILE__,__LINE__,$sql);
	if ($c->sql_numrows($resultat) > 0){
		$row = $c->sql_fetchrow($resultat);
		$droits->charge_bannis();
		if (array_key_exists(1,$droits->liste_bannis)){
			foreach ($droits->liste_bannis[1] as $pattern=>$val){
				if (ereg($pattern,$row['email'])){
					message_die(E_WARNING,62,'','');
				}
			}
		}
		// On indique au user qu'il est connecte
		$tpl->assign_block_vars('connecte', array());
		$cookie = (isset($_POST['cookie']))?true:false;
		$session->login($row['user_id'],$cookie);

		// on redirige le user vers la page où il était si il l'a spécifié
		if (isset($_GET['redirect']) || isset($_POST['redirect'])){
			$redirect = (isset($_GET['redirect']))?$_GET['redirect']:$_POST['redirect'];
			header('location: '.$redirect);
			exit;
		}
	}else{
		// Tentative de hacking?
		if (!session_id()) session_start();
		$essais = (isset($_SESSION['essais_login']))?intval($_SESSION['essais_login']):0;
		$essais++;
		if ($essais >= 3) hacking_detection($session->time+DUREE_BANNISSEMENT);
		$_SESSION['essais_login'] = $essais;

		// on demande à la personne de se réidentifier
		$tpl->assign_block_vars('champs_saisie', array());
		// on incrémente un compteur de tentatives
		// 3 on le bloque pendant 10 minutes
		$tpl->assign_block_vars('champs_saisie.alerte', array());
	}
}elseif (isset($_GET['logout'])){
		// DESTRUCTION
		$session->logout();
		$tpl->assign_block_vars('deconnecte', array());	// message
}elseif($user['user_connecte'] == 1){
	// Vous êtes déjà connecté
	$tpl->assign_block_vars('connecte_ok', array());
}else{
	// AFFICHAGE des champs de saisie par défaut
	$tpl->assign_block_vars('champs_saisie', array());
}

$tpl->assign_vars(array(
	'LOGIN_TITRE'		=> $lang['TITRE_LOGIN'],
	'LOGIN_DESCRIPTION'	=> sprintf($lang['LOGIN_DESCRIPTION'],'<a href="'.formate_url('register.php').'">','</a>','<a href="'.formate_url('login.php?renvoyer').'">','</a>'),
));


include_once($root.'page_haut.php');
$tpl->pparse('body');
include_once($root.'page_bas.php');
$tpl->afficher_page();
?>