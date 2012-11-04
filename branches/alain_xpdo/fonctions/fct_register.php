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
$root = '../';
require_once($root.'chargement.php');
load_lang('register');

//
// Test d'existence du pseudo
if (isset($_POST['pseudo'])){
    $pseudo=trim($_POST['pseudo']);
	if (empty($pseudo))die($lang['L_REMPLIR_TOUT']);
	//if (nettoyage_nom($_POST['pseudo']) != utf8_encode($_POST['pseudo'])) die($lang['PSEUDO_NOK'].' : '.nettoyage_nom($_POST['pseudo']));
	$droits->charge_bannis();
	if (array_key_exists(0,$droits->liste_bannis)){
		if(array_key_exists(nettoyage_nom($pseudo),$droits->liste_bannis[0])){
			die($lang['L_PSEUDO_BANNI']);
		}
	}
	$sql = 'SELECT user_id FROM '.TABLE_USERS.' WHERE pseudo=\''.nettoyage_nom($pseudo).'\' LIMIT 1';
	if (!$resultat = $c->sql_query($sql)) message_die(E_ERROR,15,__FILE__,__LINE__,$sql);
	die(($c->sql_numrows($resultat) > 0)?$lang['PSEUDO_NOK']:'OK'); // le pseudo existe deja
//
// Test de coherence de l'adresse email
}elseif(isset($_POST['email'])){
    $email=trim($_POST['email']);
	if (empty($email))die($lang['L_REMPLIR_TOUT']);
	$droits->charge_bannis();
	if (array_key_exists(1,$droits->liste_bannis)){
		foreach ($droits->liste_bannis[1] as $pattern=>$val){
			if (preg_match('/'.$pattern.'/',$email)){
				die($lang['L_EMAIL_BANNI']);
			}
		}
	}
	//die( ((preg_match("/^[-+.\w]{1,64}@[-.\w]{1,64}\.[-.\w]{2,6}$/i", $email))?'OK':$lang['MAIL_NOK']));
	die( ((preg_match("/^[-+\w]+(\.[-+\w]+)*@[-+\w]+(\.[-+\w]+)*\.[a-z]{2,6}$/", $email))?'OK':$lang['MAIL_NOK']));

//
// Test de coherence des mots de passe saisis
}elseif(isset($_POST['pass2'])){
    $pass2=trim($_POST['pass2']);
    $pass1=isset($_POST['pass1'])?trim($_POST['pass1']):'';
	if (empty($pass2) || empty($pass1))die($lang['L_REMPLIR_TOUT']);
	die(($pass1 == $pass2)?'OK':$lang['PASS2_NOK']);
//
// Test de complexite du mdp saisi
// Complexite du mdp /5 ( au moins 6 caracteres, des chiffres, lettres , des maj et min, et caracteres exotiques)
}elseif(isset($_POST['pass1'])){
    $pass1=trim($_POST['pass1']);
	if (empty($pass1))die($lang['L_REMPLIR_TOUT']);
	$n = 0;
	if (strlen($_POST['pass1'])>5)$n++;
	if (preg_match("/[A-Z]/",$pass1)) $n++;
	if (preg_match("/[a-z]/",$pass1)) $n++;
	if (preg_match("/[0-9]/",$pass1)) $n++;
	if (preg_match("/[^\w]/",$pass1)) $n++;	
	die(($n>3)?'OK':$lang['PASS1_NOK']);

}
?>
