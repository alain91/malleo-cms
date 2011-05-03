<?php
if (!defined('PROTECT'))
{
	die("Tentative de Hacking");
}
global $base;
$requetes['INSERT INTO '.$prefixe.'modelisation_tables'] = 
	"INSERT INTO `".$prefixe."modelisation_tables` (`nom_champs` ,`type_saisie` ,`lang` ,`param` ,`page`)VALUES 
	('activer_digicode', 'bool', 'Q_ACTIVER_DIGICODE', 'possibilites_bool', 'Config_Generale'), 
	('digicode_acces_zone_admin', 'text', 'Q_CODE_DIGICODE', '10', 'Config_Generale');";

$requetes['INSERT INTO '.$prefixe.'config'] = 
	"INSERT INTO `".$prefixe."config` (`data` ,`valeur`)
	VALUES ('activer_digicode', '1'), ('digicode_acces_zone_admin', '0000');";	

$requetes['UPDATE TABLE '.$prefixe.'config : Version 1.2'] = 
	"UPDATE `".$prefixe."config` SET `valeur` = '1.2' WHERE `data` = 'version_cms' LIMIT 1 ;";
	
$requetes['ALTER DATABASE : charset par defaut = utf8_unicode_ci'] = 
	"ALTER DATABASE `".$base."` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;";
?>