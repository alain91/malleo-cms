<?php

global $lang;

$lang['L_SAISISSEZ_VOTRE_CODE'] = 'Saisie du code';
$lang['L_ENTRER'] = 'Entrer';
$lang['L_EFFACER'] = 'Effacer';
$lang['L_ADMIN_DIGICODE'] = 'Identification pour une session fondateur';
$lang['L_ADMIN_DIGICODE_EXPLAIN'] = 'Pour acc&eacute;der &agrave; la zone d\'administration vous devez &ecirc;tre fondateur, mais &eacute;galement saisir le code d&eacute;fini avec les autres fondateurs pour vous ouvrir une session sp&eacute;ciale d\'acc&eacute;s de 15 minutes. Au bout de 15 minutes d\'inactivit&eacute; vous devez ressaisir le code, mais tant que vous travaillez dans la zone d\'administration vous repoussez la fin de session de 15 minutes. Si c\'est la premi&egrave;re fois que vous vous connectez &agrave; la ZA le code par d&eacute;faut est 0000. Code &agrave; changer imp&eacute;rativement !';
$lang['L_ALERTE_TENTATIVES'] = 'Le code saisi est incorrect ! %s essai sur 3<br />Tu as le droit &agrave; 3 essais, au del&agrave; de ces 3 essais tu seras banni 1H du site.<br />Tous les fondateurs seront alors alert&eacute;s par email de cette tentative.';
$lang['L_DIGICODE_HACK'] = 'Vous avez tent&eacute; de hacker le digicode en utilisant le pseudo %s';
$lang['L_DIGICODE_ALERTE_HACKING'] = 'Tentative de hacking sur votre site !!!';
$lang['L_DIGICODE_ALERTE_HACKING_DETAIL'] = 'D&eacute;tails remont&eacute;s par le site';
$lang['L_DIGICODE_ALERTE_HACKING_EXPLAIN'] = 'Un individu a tent&eacute; de p&eacute;n&eacute;trer dans la zone d\'administration de votre site. Si ce n\'est pas une fausse alerte il est vivement conseill&eacute; de d&eacute;sactiver le compte et changer le mot de passe du compte utilis&eacute;.<br /> Si les attaques persistent renommez le fichier admin.php en autre chose, et changez tous les mots de passe des fondateurs. Il peut aussi &ecirc;tre judicieux de modifier le code du digicode pour un nombre tr&egrave;s complexe le temps que l\'attaque cesse. Le digicode accepte jusqu\'&agrave; 254 caract&egrave;res.';
$lang['L_DIGICODE_ALERTE_HACKING_MSG'] = 'Voici les d&eacute;tails sur la personne qui tente de p&eacute;n&eacute;trer dans la zone admin:<br /><br /><b>Heure</b>: %s<br /><b>Pseudo utilis&eacute;</b>: %s<br /><b>IP au moment de l\'attaque:</b>: %s<br /><b>Adresse Host</b>: %s<br /><br />Si vous subissez des d&eacute;g&acirc;ts ou une alt&eacute;ration de vos donn&eacute;es, vous pouvez porter plainte et fournir ces informations qui aideront les enqu&eacute;teurs &agrave; trouver qui a utilis&eacute; cette ip. L\'adresse Host permet g&eacute;n&eacute;ralement de d&eacute;duire le Fournisseur d\'Acc&egrave;s Internet de la personne. Si cette personne ne se cache pas derri&egrave;re un proxy Anonyme il sera facilement identifiable.';


?>