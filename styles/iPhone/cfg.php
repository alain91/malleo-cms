<?php
// Chemin de ce style
$path = $root.$style_path.$style_name.'/';
// Couleurs principales du theme qui permettront de generer les adresses mail sous forme d'image
if (!defined('COULEUR_TEXTE')){
	define('COULEUR_TEXTE','#2b4375');
	define('COULEUR_BACKGROUND','#e6ecf2');
}

// Valide / Invalide  OK / NOK
$img['valide'] 		= $path.'images/check_accept.png';
$img['invalide'] 	= $path.'images/check_cancel.png';

// Fleches
$img['refresh'] 	= $path.'images/arrow_refresh.png';
$img['up'] 			= $path.'images/arrow_up.png';
$img['down'] 		= $path.'images/arrow_down.png';


$img['effacer'] 	= $path.'images/icon_effacer.png';
$img['editer'] 		= $path.'images/icon_modifier.png';
$img['options'] 	= $path.'images/icon_zone_admin.png';
$img['picker'] 		= $path.'images/icon_colorpicker.png';
$img['cadenas'] 	= $path.'images/icon_droits.png';
$img['apercu'] 		= $path.'images/icon_apercu.png';
$img['digicode'] 	= $path.'images/icon_digicode.png';


// Les boutons
$img['dupliquer'] 		= $path.'images/bouton_dupliquer_'.$user['langue'].'.png';
$img['nouveau'] 		= $path.'images/bouton_nouveau_'.$user['langue'].'.png';
$img['precedent'] 		= $path.'images/bouton_precedent_'.$user['langue'].'.png';
$img['suivant'] 		= $path.'images/bouton_suivant_'.$user['langue'].'.png';
$img['membres'] 		= $path.'images/bouton_membres_'.$user['langue'].'.png';
$img['droits'] 			= $path.'images/bouton_droits_'.$user['langue'].'.png';
$img['repondre'] 		= $path.'images/bouton_repondre_'.$user['langue'].'.png';
$img['reponse_rapide'] 	= $path.'images/bouton_reponse_rapide_'.$user['langue'].'.png';
$img['mail'] 			= $path.'images/bouton_mail_'.$user['langue'].'.gif';
$img['mp'] 				= $path.'images/bouton_mp_'.$user['langue'].'.gif';

/* icone profil */
$img['gtalk'] 		= $path.'images/profil_gtalk.png';
$img['icq'] 		= $path.'images/profil_icq.png';
$img['msn'] 		= $path.'images/profil_msn.png';
$img['yahoo'] 		= $path.'images/profil_yahoo.png';

/* icone bbcode code */
$img['code_html4strict']	= $path.'images/code_html4strict.png';
$img['code_sql']			= $path.'images/code_sql.png';
$img['code_php']			= $path.'images/code_php.png';

/* icone genre */
$img['homme']	= $path.'images/sexe_homme.png';
$img['femme']	= $path.'images/sexe_femme.png';

?>