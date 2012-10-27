<?php
if (!defined('PROTECT'))
{
	die("Tentative de Hacking");
}
global $base;
	
$requetes['UPDATE `'.$prefixe.'config` : Version 1.3'] = 
	"UPDATE `".$prefixe."config` SET `valeur` = '1.3' WHERE `data` = 'version_cms' LIMIT 1 ;";
$requetes['UPDATE `'.$prefixe.'modelisation_tables` : Path param 10->30'] = 
	"UPDATE `".$prefixe."modelisation_tables` SET `param` = '30' WHERE `nom_champs` = 'path' LIMIT 1 ;";
	$requetes['UPDATE `'.$prefixe.'modelisation_tables` : Nom Cookie param 10->30'] = 
	"UPDATE `".$prefixe."modelisation_tables` SET `param` = '30' WHERE `nom_champs` = 'cookie_name' LIMIT 1 ;";

?>
