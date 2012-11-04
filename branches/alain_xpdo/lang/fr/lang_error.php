<?php

// Niveau d'erreur
global $code_erreur;
$code_erreur = array();
$code_erreur[E_ERROR]='Erreur';
$code_erreur[E_WARNING]='Alerte';
$code_erreur[E_PARSE]='Erreur d\'analyse';
$code_erreur[E_NOTICE]='Probl&egrave;me de d&eacute;claration';
$code_erreur[E_CORE_ERROR]='Core Error';
$code_erreur[E_CORE_WARNING]='Core Warning';
$code_erreur[E_COMPILE_ERROR]='Erreur de compilation';
$code_erreur[E_COMPILE_WARNING]='Alerte de compilation';
$code_erreur[E_USER_ERROR]='Erreur sp&eacute;cifique';
$code_erreur[E_USER_WARNING]='Alerte sp&eacute;cifique';
$code_erreur[E_USER_NOTICE]='Note sp&eacute;cifique';
$code_erreur[E_ALL]='Erreur sans incidence';
// Constante Globale ajoutee depuis PHP5
if (!defined('E_STRICT')) define('E_STRICT',2048);
$code_erreur[E_STRICT]='Suggestion d\'am&eacute;lioration';


$lang['L_SQL'] = 'D&eacute;tails SQL';
$lang['SQL_CODE'] = 'Code erreur';
$lang['SQL_MESSAGE'] = 'Message d\'erreur';
$lang['SQL_REQUETE'] = 'Requ&ecirc;te';
$lang['L_EMPLACEMENT'] = 'Emplacement';
$lang['LIGNE'] = 'Ligne';
$lang['FICHIER'] = 'Fichier';
$lang['L_RETOUR'] = 'Retour &agrave; la page pr&eacute;c&eacute;dente';
$lang['PAGE_NOT_FOUND'] = 'La page demand&eacute;e n\'existe pas OU vous n\'&ecirc;tes pas autoris&eacute; &agrave; la consulter.<br /> Contactez le fondateur du site pour plus de d&eacute;tails.';


// ----------------------------------------------------------------------------------------
//            LISTE DES MESSAGES D'ERREUR
// ----------------------------------------------------------------------------------------
// 1 a 500 reservees au CMS
$erreur[1]  = 'Erreur inconnue';
$erreur[2]  = 'La page n\'existe pas OU vous n\'&ecirc;tes pas autoris&eacute; &agrave; la consulter.<br /> Contactez le fondateur du site pour plus de d&eacute;tails.';
$erreur[3]  = 'La table config ne r&eacute;pond pas, v&eacute;rifier son nom et sa d&eacute;claration dans la base';
$erreur[4]  = 'Impossible de supprimer la session p&eacute;rim&eacute;e';
$erreur[5]  = 'Impossible d\'initier la session';
$erreur[6]  = 'Impossible de r&eacute;cup&eacute;rer les infos de l\'utilisateur';
$erreur[7]  = 'Impossible d\'enregistrer ce nouveau mod&egrave;le';
$erreur[8]  = 'Impossible de lister les infos sur ce mod&egrave;le';
$erreur[9]  = 'Impossible de supprimer ce mod&egrave;le';
$erreur[10] = 'Impossible de lire ce gabarit, v&eacute;rifiez qu\'il n\'a pas &eacute;t&eacute; supprim&eacute; ou renomm&eacute; sur le FTP, et que le module est bien associ&eacute; &agrave; un mod&egrave;le fonctionnel.';
$erreur[11] = 'Impossible de lister les infos sur ce mod&egrave;le';
$erreur[12] = 'Impossible de lister ce dossier';
$erreur[13] = 'La table page ne r&eacute;pond pas';
$erreur[14] = 'La table de mod&eacute;lisation ne r&eacute;pond pas';
$erreur[15] = 'La table users ne r&eacute;pond pas';
$erreur[16] = 'Impossible de lister les mod&egrave;les';
$erreur[17] = 'Impossible de lister les utilisateurs';
$erreur[18] = 'Impossible d\'extraire les infos sur cet utilisateur';
$erreur[19] = 'Impossible de mettre a jour les infos sur cet utilisateur';
$erreur[20] = 'Impossible d\'extraire les infos sur cet utilisateur';
$erreur[21] = 'Impossible d\'enregistrer ce nouveau mod&egrave;le';
$erreur[22] = 'Acc&egrave;s interdit &agrave; la zone d\'administration';
$erreur[23] = 'La page demand&eacute;e est introuvable';
$erreur[24] = 'La table des utilisateurs de r&eacute;pond pas';
$erreur[25] = 'Impossible de mettre &agrave; jour la session';
$erreur[26] = 'Impossible de mettre &agrave; jour le cache session';
$erreur[27] = 'Impossible de mettre &agrave; jour la session';
$erreur[28] = 'Impossible de mettre &agrave; jour le cache session';
$erreur[29] = 'La table users ne r&eacute;pond pas';
$erreur[30] = 'Impossible de remplir la table users';
$erreur[31] = 'Impossible d\'ajouter ce module';
$erreur[32] = 'Impossible de supprimer le module';
$erreur[33] = 'Impossible d\'extraire les infos du module';
$erreur[34] = 'Impossible de lister les IPs';
$erreur[35] = 'Impossible d\'envoyer l\'email. Si vous n\'&ecirc;tes pas l\'administrateur contactez le pour le pr&eacute;venir.<br/> La configuration SMTP doit &ecirc;tre v&eacute;rifi&eacute;e';
$erreur[36] = 'Impossible de valider cette clef';
$erreur[37] = 'Impossible de lister les groupes';
$erreur[38] = 'Impossible d\'enregistrer ce groupe';
$erreur[39] = 'Impossible d\'&eacute;diter ce groupe';
$erreur[40] = 'Impossible de supprimer ce groupe';
$erreur[41] = 'Impossible de lister les valeurs';
$erreur[42] = 'Impossible d\'ajouter ce membre';
$erreur[43] = 'Impossible de supprimer ce membre';
$erreur[44] = 'Impossible d\'&eacute;tablir les droits par d&eacute;faut';
$erreur[45] = 'Impossible de supprimer les r&egrave;gles de ce module';
$erreur[46] = 'Impossible de mettre &agrave; jour les r&egrave;gles';
$erreur[47] = 'Impossible de supprimer les utilisateurs';
$erreur[48] = 'Impossible de lister les droits par d&eacute;faut';
$erreur[49] = 'Impossible de construire la pagination';
$erreur[50] = 'Impossible de construire l\'aper&ccedil;u de la page';
$erreur[51] = 'Impossible d\'ajouter cette r&egrave;gle';
$erreur[52] = 'Impossible d\'ajouter les fonctions associ&eacute;es &agrave; la r&egrave;gle';
$erreur[53] = 'Impossible de mettre &agrave; jour la valeur de cette fonction';
$erreur[54] = 'Impossible de supprimer la r&egrave;gle demand&eacute;e';
$erreur[55] = 'Nouveau module install&eacute; ! vous devez vous reconnecter pour que votre profile en tienne compte';
$erreur[56] = 'Votre jeton n\'est pas valide ou a expir&eacute;, veuillez retenter l\'op&eacute;ration.';
$erreur[57] = 'On ne peut venir en zone admin que depuis le site. Les raccourcis ne sont pas autoris&eacute;s par mesure de s&eacute;curit&eacute;.';
$erreur[58] = 'Impossible d\'ajouter ce smiley';
$erreur[59] = 'Impossible de supprimer ce smiley';
$erreur[60] = 'Vous devez imp&eacute;rativement saisir quelque chose pour utiliser cette fonction';
$erreur[61] = 'Ce pseudo a &eacute;t&eacute; banni du site. Vous ne pouvez pas vous enregistrer ou vous connecter avec.';
$erreur[62] = 'Cette adresse email ou le domaine de votre adresse (exemple: @toto.com) a &eacute;t&eacute; bannie du site. <br/>Vous ne pouvez pas vous enregistrer ou vous connecter avec.';
$erreur[63] = 'Impossible d\'executer cette requ&ecirc;te';
$erreur[64] = 'Impossible de lister les bots';
$erreur[65] = 'Impossible d\'initier la liste des bots';
$erreur[66] = 'Impossible d\'ajouter ce bot';
$erreur[67] = 'Activation/D&eacute;sactivation du Bot impossible';
$erreur[68] = 'Impossible de supprimer ce robot';
$erreur[69] = 'Impossible de trouver des traces';
$erreur[70] = 'Le module a &eacute;t&eacute; supprim&eacute;, le mod&egrave;le doit &ecirc;tre rafra&icirc;chi';

// Mod Blog
// 500 a 599

// Mod Statique
// 600 a 699

// Mod Forum
// 700 a 799

// Mod Pages
// 800 a 899

// Mod Wiki
// 900 a 999

// Bloc StyleChange
// 1000

// Bloc Liens
// 1010

// Bloc HTML
// 1020

// Module Membres
// 1030 a 1099

// Module Profile
// 1100 a 1199

// Module Messagerie
// 1200 a 1299

// Module Arcade
// 1300 a 1399

// Module Citations
// 1400 a 1499

// Module Petites Annonces
// 1500 a 1599

// Module Upload
// 1600 a 1699

?>