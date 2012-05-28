<?php
if (!defined('PROTECT'))
{
	die("Tentative de Hacking");
}
global $base;
	
$requetes['UPDATE `'.$prefixe.'config` : Version 1.3'] = 
	"UPDATE `".$prefixe."config` SET `valeur` = '1.3' WHERE `data` = 'version_cms' LIMIT 1 ;";

$requetes['UPDATE `'.$prefixe.'config` SET `valeur` = \'admin/Accueil.php\''] = 
	"UPDATE `".$prefixe."config` SET `valeur` = 'admin/Accueil.php' WHERE `data` = 'default_module_admin' LIMIT 1 ;";
	
$requetes['INSERT INTO '.$prefixe.'config'] = 
	"INSERT INTO `".$prefixe."config` (`data` ,`valeur`)VALUES 
	('default_langue', 'fr'),
	('activer_traceur', '0'), 
	('default_style_iphone', 'iPhone'),(
	'imposer_modele_iphones', '');";
	
$requetes['ALTER TABLE '.$prefixe.'groupes'] = 
	"ALTER TABLE `".$prefixe."groupes` ADD `visible` BINARY NOT NULL DEFAULT '1';";

$requetes['ALTER TABLE '.$prefixe.'modeles ADD `fichier`'] = 
	"ALTER TABLE `".$prefixe."modeles` ADD `fichier` VARCHAR( 255 ) NULL ;";	
	
$requetes['ALTER TABLE '.$prefixe.'modeles CHANGE `gabaris`'] = 
	"ALTER TABLE `".$prefixe."modeles` CHANGE `gabaris` `gabaris` VARCHAR( 30 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL;";

$requetes['INSERT INTO '.$prefixe.'modelisation_tables'] = 
	"INSERT INTO `".$prefixe."modelisation_tables` (`nom_champs` ,`type_saisie` ,`lang` ,`param` ,`page`) VALUES 
('default_langue', 'select', 'Q_Langue', 'lister_langues', 'Config_Generale'), 
('activer_traceur', 'bool', 'Q_ActiverTraceur', 'possibilites_bool', 'Config_Generale'),
( 'default_style_iphone', 'select', 'Q_Style_iphone', 'lister_styles', 'Config_Generale'),
( 'imposer_modele_iphones', 'select', 'Q_Modele_impose_iphones', 'lister_modeles', 'Config_Generale');";

$requetes['CREATE TABLE '.$prefixe.'robots'] = 
	"CREATE TABLE `".$prefixe."robots` (
	  `id_robot` smallint(5) unsigned zerofill NOT NULL auto_increment,
	  `robot_user_agent` varchar(255) collate utf8_unicode_ci default NULL,
	  `robot_name` varchar(45) collate utf8_unicode_ci default NULL,
	  `robot_url` varchar(255) collate utf8_unicode_ci default NULL,
	  `robot_actif` binary(1) NOT NULL default '0',
	  PRIMARY KEY  (`id_robot`),
	  KEY `robot_user_agent` (`robot_user_agent`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
	
$requetes['CREATE TABLE `'.$prefixe.'sessions_suivies'] = 
	"CREATE TABLE `".$prefixe."sessions_suivies` (
	`user_id` INT UNSIGNED NOT NULL ,
	`pseudo` VARCHAR( 45 ) NOT NULL ,
	`date` INT UNSIGNED NOT NULL ,
	`url_page` VARCHAR( 255 ) NULL ,
	`libelle_page` VARCHAR( 255 ) NULL ,
	INDEX ( `user_id` , `pseudo` )
	) ENGINE = MYISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
		
$requetes['ALTER TABLE '.$prefixe.'sessions'] = 
	"ALTER TABLE `".$prefixe."sessions` ADD `pseudo` VARCHAR( 45 ) NULL ;";
	
$requetes['ALTER TABLE '.$prefixe.'smileys'] = 
	"ALTER TABLE `".$prefixe."smileys` ADD `tag_smiley` VARCHAR( 10 ) NULL AFTER `titre_smiley` ;";

$requetes['ALTER TABLE '.$prefixe.'users'] = 
	"ALTER TABLE `".$prefixe."users` CHANGE `fuseau` `fuseau` VARCHAR( 3 ) NOT NULL DEFAULT '1';";
?>