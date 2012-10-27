<?php


$lang['L_GESTION_PARAMETRES'] = 'Configuration des emails';
$lang['L_EXPLAIN_PARAMETRES'] = 'Votre site a la possibilit&eacute; d\'envoyer des emails &agrave; vos utilisateurs, que se soit lors de l\'inscription, ou pour &ecirc;tre avertis d\'un nouveau message. Pour se faire vous devez sp&eacute;cifier ici la m&eacute;thode d\'envoi des emails. Deux m&eacute;thodes sont support&eacute;es, la fonction mail() de PHP et la connexion au serveur SMTP de votre choix. 
<br/>Je vous conseille de choisir la fonction mail() si votre h&eacute;bergeur l\'autorise, sinon sp&eacute;cifiez un compte smtp pour une connexion POP3';
$lang['L_CONFIG_GENERALE'] = 'Configuration avanc&eacute;e';
$lang['L_PARAMETRE'] = 'Param&egrave;tre';
$lang['L_VALEUR'] = 'Valeur';
$lang['L_ENREGISTRER'] = 'Enregistrer';
$lang['L_MAIL_SMTP'] = 'Utiliser la m&eacute;thode SMTP?';
$lang['L_MAIL_SECURE'] = 'Activer la connexion s&eacute;curis&eacute;e ?';
$lang['L_MAIL_URL_SERVEUR'] = 'URL du serveur ';
$lang['L_MAIL_PORT_SERVEUR'] = 'Num&eacute;ro de port';
$lang['L_MAIL_USERNAME'] = 'Utilisateur';
$lang['L_MAIL_PASSWORD'] = 'Mot de passe';
$lang['L_AIDE'] = 'Aide';
$lang['L_AUTRES_PARAM_NON_LUS'] = 'Les autres param&egrave;tres ne seront pas lus, donc mettez ce que vous voulez';
$lang['L_MAIL_FROM'] = 'Adresse email de r&eacute;ponse';
$lang['L_MAIL_FROMNAME'] = 'Nom de l\'exp&eacute;diteur affich&eacute;';

$lang['L_MAIL_REGISTER_SUJET'] = 'Bienvenue sur %s';
$lang['L_MAIL_REGISTER_SUJET_MESSAGE'] = 'Vos identifiants';
$lang['L_MAIL_REGISTER_BODY_HTML'] = "Vous venez de vous inscrire sur notre site et nous vous en remercions. Afin de valider votre boite mail, et vous identifier, je vous invite &agrave; cliquer sur le lien suivant: <a href=\"%s\">%s</a>";
$lang['L_MAIL_REGISTER_MESSAGE'] = "pseudo: %s <br />mot de passe: %s";

$lang['L_MAIL_MAILPERDU_SUJET'] = 'Vos identifiants';
$lang['L_MAIL_MAILPERDU_BODY_HTML'] = "Vous avez rempli le formulaire de r&eacute;initialisation de mot de passe. Celui-ci a bien &eacute;t&eacute; r&eacute;initialis&eacute; avec un mot de passe g&eacute;n&eacute;r&eacute; automatiquement, je vous invite &agrave; cliquer sur <a href=\"%s\">le lien suivant</a> pour vous connecter d&egrave;s maintenant."; 
$lang['L_MAIL_MAILPERDU_MESSAGE'] = "Voici vos nouveaux identifiants : <br /><br />pseudo: %s <br /><br />mot de passe: %s";



$lang['L_EXPLICATION_MAIL'] = "Cet email a &eacute;t&eacute; envoy&eacute; via un site internet auquel vous &ecirc;tes inscrit, si vous ne souhaitez plus recevoir d\'emails de ce site, allez dans les options du module pour suspendre l\'envoi d'emails. Vous pouvez &eacute;galement demander &agrave; un fondateur de supprimer votre compte.";
$lang['L_URL_SITE'] = 'Page d\'accueil du site';


?>