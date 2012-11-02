<?php
if (!defined('PROTECT'))
{
	die("Tentative de Hacking");
}
global $lang,$erreur;
$lang['L_BOITE_AUX_LETTRES_PSEUDO'] = 'Boite aux Lettres';
$lang['L_NEWMP'] = 'Nouveau Message Priv&eacute;';
$lang['L_NEWMAIL'] = 'Nouvel Email';
$lang['L_INBOX'] = 'Messages re&ccedil;us';
$lang['L_OUTBOX'] = 'Messages en cours';
$lang['L_SENTBOX'] = 'Messages envoy&eacute;s';
$lang['L_SAVEBOX'] = 'Archives';
$lang['L_CONTACTS'] = 'Contacts';
$lang['L_OPTIONS'] = 'Options';
$lang['L_AUCUN_MESSAGE'] = 'Aucun message';
$lang['L_LU'] = 'Lu';
$lang['L_NONLU'] = 'Non Lu';
$lang['L_DE'] = 'De';
$lang['L_SUJET'] = 'Sujet';
$lang['L_DATE'] = 'Date';
$lang['L_A'] = 'A';
$lang['L_OK'] = 'Ok';
$lang['L_MARQUER_LU'] = 'Marquer comme Lu(s)';
$lang['L_MARQUER_NONLU'] = 'Marquer comme Non Lu(s)';
$lang['L_ARCHIVER'] = 'Archiver';
$lang['L_SUPPRIMER'] = 'Supprimer';
$lang['L_CHERCHER_PSEUDO'] = 'Rechercher';
$lang['L_MESSAGE'] = 'Message';
$lang['L_REMPLISSEZ_CHAMPS'] = 'Remplissez tous les champs';
$lang['L_ENVOYER'] = 'Envoyer';
$lang['L_AUCUN_CONTACT'] = 'Aucun contact';
$lang['L_ID'] = 'ID';
$lang['L_PSEUDO'] = 'Pseudo';
$lang['L_ECRIRE'] = 'Ecrire';
$lang['L_REPONDRE'] = 'R&eacute;pondre';
$lang['L_RANG'] = 'Rang';
$lang['L_GESTION'] = 'Gestion';
$lang['L_DELETE'] = 'Supprimer';
$lang['L_AJOUTER'] = 'Ajouter';
$lang['L_COPIE_MAIL'] = 'Voulez vous &ecirc;tre averti(e) par email des nouveaux Messages Priv&eacute;s ?';
$lang['L_ACCEPTER_MP'] = 'Acceptez-vous les messages priv&eacute;s de tout le monde, ou seulement de vos contacts?';
$lang['L_ACCEPTER_MAIL'] = 'Acceptez-vous les emails de tout le monde, ou seulement de vos contacts?';
$lang['L_MESSAGE_REFUS'] = 'Message que les personnes non autoris&eacute;es aux MP/Emails recevront.';
$lang['L_MSG_REFUS'] = 'Vous ne faites pas partie des personnes autoris&eacute;es &agrave; envoyer un message &agrave; cette personne.';
$lang['L_MSG_ABSENCE'] = 'Message Automatique: Je ne suis pas l&agrave; en ce moment je vous r&eacute;pondrai en revenant';
$lang['L_MSG_ABSENCE_TITRE'] = 'Re: ';
$lang['L_ABSENT_SITE'] = 'Activer le mod "Absent du site" ?';
$lang['L_ABSENT_SITE_MSG'] = 'Message d\'absence du site';
$lang['L_TAPER_ICI'] = 'Pseudo recherch&eacute;';
$lang['L_LECTURE'] = 'Lecture d\'un message';
$lang['L_TITRE_BOX'] = 'Op&eacute;ration sur Messagerie';
$lang['L_MP_ENVOYE'] = 'Votre Message a bien &eacute;t&eacute; envoy&eacute;. <br />Si vos destinataires acceptent les MPs ils pourront les lire lors de leur prochaine visite. <br />Vous pouvez v&eacute;rifier d&egrave;s maintenant si les destinataires acceptent les MP en allant dans le dossier "Messages en cours"';
$lang['L_MAIL_ENVOYE'] = 'Votre Message a bien &eacute;t&eacute; envoy&eacute;.';
$lang['L_MP_NON_ENVOYE'] = 'Votre Message n\'a pu &ecirc;tre envoy&eacute; !<br /> Le(s) destinataire(s) n\'existe(nt) pas ou n\'accepte(nt) pas les Messages Priv&eacute;s';
$lang['L_OUI'] = 'Oui';
$lang['L_NON'] = 'Non';
$lang['L_TOUS'] = 'Tous';
$lang['L_CONTACTS'] = 'Contacts';
$lang['L_ENREGISTRER'] = 'Enregistrer';
$lang['L_MP_SUJET'] = 'Message Priv&eacute;';
$lang['L_MP_BODY_HTML'] = 'Vous avez re&ccedil;u un nouveau message priv&eacute;, pour vous rendre dans votre bo&icirc;te de r&eacute;ception cliquez sur le lien ci-contre: <a href="%s">%s</a>';
$lang['L_MESSAGE_RECU'] = 'Message Re&ccedil;u';
$lang['L_MAIL_SUJET'] = 'Message par Email';
$lang['L_RE'] = 'RE: ';
$lang['L_COCHER'] = 'Tout cocher';
$lang['L_DECOCHER'] = 'Tout d&eacute;cocher';

$lang['L_ENTETE_EMAIL'] = 'L\'EXPEDITEUR  %s (%s) A ENVOYE CE MAIL PAR LE SITE. SI VOUS LUI REPONDEZ, PENSEZ A CHANGER L\'ADRESSE EMAIL PAR LA SIENNE OU BIEN REPONDEZ PAR LE SITE. Si celui-ci contient des propos pouvant vous porter atteinte n\'h&eacute;sitez pas &agrave; le signaler au webmaster du site.';
$lang['L_ALERTE_ENTETE'] = 'L\'email envoy&eacute; sera pr&eacute;c&eacute;d&eacute; de ceci:';

$lang['R_messagerie_email'] = 'Envoyer un Email';
$lang['R_messagerie_mp'] = 'Envoyer un MP';
$lang['R_messagerie_voir'] = 'Voir sa messagerie';

//Emails group&eacute;s
$lang['L_MAILGROUP'] = 'Email group&eacute;';
$lang['L_MPGROUP'] = 'MP group&eacute;';
$lang['L_MAIL_NON_ENVOYE'] = 'Email non envoy&eacute;;';



// 1200 a 1299
$erreur[1200] = 'Seuls les membres peuvent poss&eacute;der une messagerie priv&eacute;e';
$erreur[1201] = 'Impossible de lister les MPs de cette bo&icirc;te';
$erreur[1202] = 'Impossible de trouver les utilisateurs sp&eacute;cifi&eacute;s';
$erreur[1203] = 'Impossible d\'envoyer le MP';
$erreur[1204] = 'Vous n\'&ecirc;tes pas autoris&eacute;(e) &agrave; lire ce message';
$erreur[1205] = 'Impossible de marquer le message comme lu';
$erreur[1206] = 'Impossible de lire le d&eacute;tail de ce MP';
$erreur[1207] = 'Vous devez remplir tous les champs !';
$erreur[1208] = 'Impossible de lister les contacts';
$erreur[1209] = 'Impossible de supprimer ce MP';
$erreur[1210] = 'Impossible de mettre &agrave; jour les pr&eacute;f&eacute;rences utilisateur';
?>
