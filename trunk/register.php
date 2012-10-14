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
define('PROTECT',true);
$root = './';
require_once($root.'/chargement.php');
$style_name=load_style();
$lang=$erreur=array();
load_lang('defaut');
load_lang('register');

// Chargement de la librairie Captcha
$cryptinstall = $root.'librairies/crypt/cryptographp.fct.php';
include_once($cryptinstall);

if (isset($_POST['etape']) || isset($_GET['etape']))
{
	$etape = (isset($_POST['etape']))?intval($_POST['etape']):intval($_GET['etape']);
}else{
	$etape = 0;
}
$alerte = '';

//
// TRAITEMENT de la SAISIE
switch($etape)
{
	case '3':
		$email = nettoyage_mail($_GET['email']);
		$clef = preg_replace("/[^A-Za-z0-9]/i",'',$_GET['clef']);
		$sql = 'SELECT user_id FROM '.TABLE_USERS.' 
				WHERE email=\''.$email.'\' AND clef=\''.$clef.'\'LIMIT 1';
		if (!$resultat = $c->sql_query($sql)) message_die(E_ERROR,29,__FILE__,__LINE__,$sql);
		if ($c->sql_numrows($resultat) > 0)
		{
			$row = $c->sql_fetchrow($resultat);
			$etape = 4;
			$sql = 'UPDATE '.TABLE_USERS.' SET actif=1,level=2 WHERE user_id='.$row['user_id'];
			if (!$resultat = $c->sql_query($sql)) message_die(E_ERROR,29,__FILE__,__LINE__,$sql);
		}else{
			$etape = 3;
			$alerte = $lang['L_ALERTE_MAIL_CLEF_NOK'];
		}
		break;
	case '2':
		// Nettoyage des saisies
		$pseudo =	nettoyage_nom($_POST['pseudo']);
		$mail =		nettoyage_mail($_POST['email']);
		$pass =		nettoyage_pass($_POST['pass1']);
		$droits->charge_bannis();
		if (array_key_exists(0,$droits->liste_bannis)){
			if(array_key_exists(stripslashes($pseudo),$droits->liste_bannis[0])){
				message_die(E_WARNING,61,'','');
			}
		}
		$sql = 'SELECT user_id FROM '.TABLE_USERS.' WHERE pseudo = \''.$pseudo.'\' OR email=\''.$mail.'\' LIMIT 1';
		if (!$resultat = $c->sql_query($sql)) message_die(E_ERROR,29,__FILE__,__LINE__,$sql);
		
		if (empty($_POST['pseudo']) || empty($_POST['email']) 
			|| empty($_POST['pass1']) || empty($_POST['pass2']) || empty($_POST['code'])
			|| empty($_POST['question'])){
			// au moins 1 champ obligatoire est resté vide
			$tpl->assign_vars(array(
				'PSEUDO_SAISI'	=> $pseudo,
				'MAIL'		=> $mail
			));
			$alerte = $lang['L_REMPLIR_TOUT'];
			$etape = 2;
		}elseif(!chk_crypt($_POST['code'])){
			// Le code captcha saisi n'est pas bon
			$tpl->assign_vars(array(
				'PSEUDO_SAISI'	=> $pseudo,
				'MAIL'		=> $mail
			));
			$alerte = $lang['L_CAPTCHA_INVALIDE'];
			$etape = 2;
		}elseif($_POST['question'] != $cf->config['register_question_reponse']){
			// Le code captcha saisi n'est pas bon
			$tpl->assign_vars(array(
				'PSEUDO_SAISI'	=> $pseudo,
				'MAIL'		=> $mail
			));
			$alerte = $lang['L_REPONSE_QUESTION_INCORRECTE'];
			$etape = 2;
		}elseif($_POST['pass1'] != $_POST['pass2']){
			// les 2 mdps sont-ils bien identiques ? 
			$tpl->assign_vars(array(
				'PSEUDO_SAISI'	=> $pseudo,
				'MAIL'		=> $mail
			));
			$alerte = $lang['L_PSEUDOS_DIFFERENTS'];
			$etape = 2;
		}elseif ($c->sql_numrows($resultat) > 0)
		{
			// Le pseudo et l'adresse mail  n'existent pas déjà ? 
			$alerte = $lang['L_PSEUDOS_MAIL_EXISTENT_DEJA'];
			$etape = 2;
				// Si au moins 1 champs n'a pas été remplit on alerte le user
		}else{
			// On ajoute le compte en mode inactif
			require_once($root.'fonctions/fct_maths.php');
			$clef = generate_key(30);
			
			// On envoit le mail de vérification
			load_lang('emails');
			require_once($root.'class/class_mail.php');
			$email = new mail();
			
			$verifier = 'http://'.$cf->config['adresse_site'].$cf->config['path'].'register.php?etape=3&email='.$mail.'&clef='.$clef;
			
			$email->Subject = sprintf($lang['L_MAIL_REGISTER_SUJET'],$cf->config['nom_site']);
			$email->titre_message = $lang['L_MAIL_REGISTER_SUJET_MESSAGE'];
			$email->message_explain = sprintf($lang['L_MAIL_REGISTER_BODY_HTML'],$verifier,$verifier);
			$email->formate_html(sprintf($lang['L_MAIL_REGISTER_MESSAGE'], $pseudo, $pass));
			$email->AddAddress($mail,$pseudo);

			if($email->Send()) {
				$sql = 'INSERT INTO '.TABLE_USERS.' 
						(pseudo, email, pass,  date_register, level, clef, langue ) 
						VALUES  
						(\''.$pseudo.'\',\''.$mail.'\',\''.md5($pass).'\','.time().',1,\''.$clef.'\',\''.$cf->config['default_langue'].'\')';
				if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,30,__FILE__,__LINE__,$sql);
				// On passe à l'étape suivante
				$etape = 3;
			}else{
				$alerte = $lang['L_EMAIL_NON_ENVOYE'];
				$etape = 2;
			}
		}
		break;
	case '1';
		// Si la checkbox a bien été cochée on affiche la seconde étape de l'enregistrement
		if ($_POST['validation'] == true) $etape = 2;
		break;
}

//
// TRAITEMENT DE L'AFFICHAGE
switch($etape)
{
	case '4': // Retour à la page où le user se situait OU lien vers l'édition du profile
		$tpl->assign_block_vars('etape4', array());
		$tpl->assign_vars(array(
			'ETAPE'					=> 4,
			'S_CONNECTER'			=> formate_url('login.php')
		));	
		break;
	case '3':
		$tpl->assign_block_vars('etape3', array());
		$tpl->assign_vars(array(
			'ETAPE'				=> 3,
			'ALERTE'			=> $alerte
		));	
		break;		
	case '2': // ENREGISTREMENT du login / mail / mdp
		$tpl->assign_block_vars('etape2', array());
		$tpl->assign_vars(array(
			'ALERTE'		=>	$alerte,
			'L_QUESTION_BOTS'	=>	$cf->config['register_question'],
			'VALIDER'		=>	$lang['VALIDER'],
			'ETAPE'			=> 2
		));	
		break;
	case '1': // VALIDATION du réglement
	default : 
		$tpl->assign_block_vars('etape1', array());
		$reglement = (file_exists(PATH_REGLEMENT))? @file_get_contents(PATH_REGLEMENT):$lang['REGLEMENT'];
		$reglement = preg_replace("/\n /",'<br />',$reglement);
		$tpl->assign_vars(array(
			'ETAPE'			=>	1,
			'REGLEMENT'		=>	$reglement
		));	
		break;
}

	

include_once($root.'page_haut.php');

$tpl->set_filenames(array(
	  'body' =>  $root .'html/register.html'
));


$tpl->assign_vars(array(
	'CAPTCHA'			=>	dsp_crypt(0,1),
	'IMAGE_OK'			=>	$img['valide'] ,
	'IMAGE_NOK'			=>	$img['invalide'] 
));

$tpl->pparse('body');
include_once($root.'page_bas.php');
$tpl->afficher_page();
?>
