<?php
if (!defined('PROTECT'))
{
	die("Tentative de Hacking");
}
$requetes['INSERT INTO '.$prefixe.'modelisation_tables'] = 
	"INSERT INTO `".$prefixe."modelisation_tables` (`nom_champs` ,`type_saisie` ,`lang` ,`param` ,`page`) VALUES 
	('activer_lightbox', 'bool', 'Q_ActiverLightBox', 'possibilites_bool', 'Config_Generale');";

$requetes['INSERT INTO '.$prefixe.'config'] = 
	"INSERT INTO `".$prefixe."config` (`data` ,`valeur`) VALUES ('activer_lightbox', '1');";	

$requetes['UPDATE TABLE '.$prefixe.'config : Version 1.1'] = 
	"UPDATE `".$prefixe."config` SET `valeur` = '1.1' WHERE `data` = 'version_cms' LIMIT 1 ;";
?>