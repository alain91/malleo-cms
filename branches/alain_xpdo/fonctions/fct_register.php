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
$root = '../';
require_once($root.'chargement.php');
load_lang('register');

//
// Test d'existence du pseudo
if (isset($_POST['pseudo'])){
	if ($_POST['pseudo']=='')die($lang['L_REMPLIR_TOUT']);
	//if (nettoyage_nom($_POST['pseudo']) != utf8_encode($_POST['pseudo'])) die($lang['PSEUDO_NOK'].' : '.nettoyage_nom($_POST['pseudo']));
	$droits->charge_bannis();
	if (array_key_exists(0,$droits->liste_bannis)){
		if(array_key_exists(nettoyage_nom($_POST['pseudo']),$droits->liste_bannis[0])){
			die($lang['L_PSEUDO_BANNI']);
		}
	}
	$sql = 'SELECT user_id FROM '.TABLE_USERS.' WHERE pseudo=\''.nettoyage_nom($_POST['pseudo']).'\' LIMIT 1';
	if (!$resultat = $c->sql_query($sql)) message_die(E_ERROR,15,__FILE__,__LINE__,$sql);
	die((($c->sql_numrows($resultat) > 0)?$lang['PSEUDO_NOK']:'OK'));
//
// Test de coherence de l'adresse email
}elseif(isset($_POST['email'])){
	if ($_POST['email']=='')die($lang['L_REMPLIR_TOUT']);
	$droits->charge_bannis();
	if (array_key_exists(1,$droits->liste_bannis)){
		foreach ($droits->liste_bannis[1] as $pattern=>$val){
			if (preg_match('/'.$pattern.'/',$_POST['email'])){
				die($lang['L_EMAIL_BANNI']);
			}
		}
	}
	die( ((preg_match("/^[-+.\w]{1,64}@[-.\w]{1,64}\.[-.\w]{2,6}$/i", $_POST['email']))? 'OK':$lang['MAIL_NOK']));

//
// Test de coherence des mots de passe saisis
}elseif(isset($_POST['pass2'])){	
	if ($_POST['pass2']=='' || $_POST['pass1']=='')die($lang['L_REMPLIR_TOUT']);
	die(($_POST['pass1'] == $_POST['pass2'])?'OK':$lang['PASS2_NOK']);
//
// Test de complexite du mdp saisi
// Compexite du mdp  /5 ( au moins 6 caracteres, des chiffres, lettres , des maj et min, et caracteres exotiques)
}elseif(isset($_POST['pass1'])){
	if ($_POST['pass1']=='')die($lang['L_REMPLIR_TOUT']);
	$n = 0;
	if (strlen($_POST['pass1'])>5)$n++;
	if (preg_match("/[A-Z]/",$_POST['pass1'])) $n++;
	if (preg_match("/[a-z]/",$_POST['pass1'])) $n++;
	if (preg_match("/[0-9]/",$_POST['pass1'])) $n++;
	if (preg_match("/[^a-zA-Z0-9]/",$_POST['pass1'])) $n++;	
	if ($n<=3){
		die($lang['PASS1_NOK']);
	}else{
		die( 'OK' );
	}	
}
?>
