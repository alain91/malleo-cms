<?php
if (!defined('PROTECT'))
{
	die("Tentative de Hacking");
}
$requetes['ALTER DATABASE : charset par defaut = utf8_unicode_ci'] = 
	"ALTER DATABASE `".$base."` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;";
	
$requetes['CREATE TABLE '.$prefixe.'bloc_html'] = 
	"CREATE TABLE `".$prefixe."bloc_html` (
	  `id` int(11) unsigned NOT NULL auto_increment,
	  `titre` varchar(150) NOT NULL default '',
	  `texte` text,
	  PRIMARY KEY  (`id`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";


$requetes['CREATE TABLE '.$prefixe.'bloc_menuh'] = 
	"CREATE TABLE `".$prefixe."bloc_menuh` (
	  `id_lien` int(11) unsigned NOT NULL auto_increment,
	  `titre_lien` varchar(100) NOT NULL default '',
	  `lien` varchar(255) default NULL,
	  `switch` varchar(100) default NULL,
	  `module` varchar(100) default NULL,
	  `accesskey` char(1) default NULL,
	  `image` varchar(255) default NULL,
	  `id_parent` int(11) unsigned NOT NULL default '0',
	  `ordre` int(5) unsigned NOT NULL default '9999',
	  PRIMARY KEY  (`id_lien`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

$requetes['INSERT INTO '.$prefixe.'bloc_menuh'] = 
	"INSERT INTO `".$prefixe."bloc_menuh` (`titre_lien`, `lien`, `switch`, `module`, `accesskey`, `image`, `id_parent`, `ordre`) VALUES
	('Accueil', 'index.php', '', NULL, 'a', 'data/icons/house.png', 0, 0),
	('Admin', 'admin.php', 'switch_fondateur', NULL, 'x', 'data/icons/wrench.png', 0, 1),
	('Pages Web', 'index.php?module=pages', '', NULL, '', '', 0, 2),
	('Forum', 'index.php?module=forum', '', NULL, '', '', 0, 3),
	('Blog', 'index.php?module=blog', '', NULL, '', '', 0, 4),
	('Wiki', 'index.php?module=wiki', '', NULL, '', '', 0, 5),
	('Membres', 'index.php?module=membres', '', NULL, 'm', 'data/icons/group.png', 0, 6),
	('Messagerie', 'index.php?module=messagerie', 'user_authentifie', NULL, '', 'data/icons/email_edit.png', 0, 7),
	('Profil', 'index.php?module=profil', 'user_authentifie', NULL, '', 'data/icons/user_suit.png', 0, 8),
	('D&eacute;connecter', 'login.php?logout', 'user_authentifie', NULL, '', 'data/icons/disconnect.png', 0, 9),
	('Connecter', 'login.php', 'user_non_authentifie', NULL, 'l', 'data/icons/connect.png', 0, 10),
	('s''enregister', 'register.php', 'user_non_authentifie', NULL, '', 'data/icons/vcard_add.png', 0, 11);";

$requetes['CREATE TABLE '.$prefixe.'config'] = 
	"CREATE TABLE `".$prefixe."config` (
	  `data` varchar(30) NOT NULL default '',
	  `valeur` varchar(255) NOT NULL default '',
	  PRIMARY KEY  (`data`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

$requetes['INSERT INTO '.$prefixe.'config'] = 
	"INSERT INTO `".$prefixe."config` (`data`, `valeur`) VALUES
	('nom_site', 'Mon Site Malleo'),
	('description_site', 'Description de mon site'),
	('path', '".$param['PATH']."'),
	('adresse_site', '".$param['ADRESSE']."'),
	('cookie_name', 'malleo'),
	('cookie_time', '2592000'),
	('charset', 'UTF-8'),
	('default_langue', 'fr'),
	('default_module', ''),
	('default_module_admin', 'admin/Accueil.php'),
	('default_style', 'BlueLight'),
	('default_style_iphone', 'iPhone'),
	('imposer_modele_iphones', ''),
	('activer_menuh', '1'),
	('activer_lightbox', '1'),
	('activer_digicode', '1'), 
	('activer_traceur', '0'),
	('digicode_acces_zone_admin', '0000'),
	('wysiwyg_editor', 'tinymce'),
	('register_question', 'Combien font 4+4 en toutes lettres ?'),
	('register_question_reponse', 'huit'),
	('cache_session_user', '900'),
	('cache_menuh_html', '86400'),
	('cache_menuh_pages_admin', '86400'),
	('cache_duree_listing_ip', '86400'),
	('cache_duree_listing_users', '43200'),
	('cache_duree_listing_rangs', '86400'),
	('cache_duree_listing_modules', '43200'),
	('mail_smtp', '0'),
	('mail_secure', '0'),
	('mail_url_serveur', '".$param['ADRESSE']."'),
	('mail_port_serveur', '25'),
	('mail_username', 'pseudo'),
	('mail_password', 'password'),
	('mail_from', 'webmaster@".eregi_replace('www.','',$param['ADRESSE'])."'),
	('mail_fromname', 'Le Webmaster'),
	('version_cms', '1.3');";

$requetes['CREATE TABLE '.$prefixe.'droits_fonctions'] = 
	"CREATE TABLE `".$prefixe."droits_fonctions` (
	  `id_regle` int(11) unsigned NOT NULL default '0',
	  `nom_fonction` varchar(100) NOT NULL default '',
	  `valeur` char(1) default NULL,
	  PRIMARY KEY  (`id_regle`,`nom_fonction`),
	  KEY `nom_fonction` (`nom_fonction`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

$requetes['CREATE TABLE '.$prefixe.'droits_regles'] = 
	"CREATE TABLE `".$prefixe."droits_regles` (
	  `id_regle` int(11) unsigned NOT NULL auto_increment,
	  `module` varchar(30) NOT NULL default '',
	  `id_noeud` int(11) unsigned NOT NULL default '0',
	  `id_groupe` int(11) unsigned NOT NULL default '0',
	  `alias` varchar(100) default NULL,
	  PRIMARY KEY  (`module`,`id_noeud`,`id_groupe`),
	  KEY `id_regle` (`id_regle`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

$requetes['CREATE TABLE '.$prefixe.'groupes'] = 
	"CREATE TABLE `".$prefixe."groupes` (
	  `group_id` int(11) unsigned NOT NULL auto_increment,
	  `titre` varchar(150) NOT NULL default '',
	  `description` varchar(255) default NULL,
	  `icone` varchar(255) default NULL,
	  `couleur` varchar(15) default NULL,
	  `type` binary(1) NOT NULL default '0',
	  `user_id` int(11) unsigned default NULL,
	  `visible` BINARY NOT NULL DEFAULT '1',
	  `ordre` int(11) unsigned NOT NULL default '999',
	  PRIMARY KEY  (`group_id`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

$requetes['INSERT INTO '.$prefixe.'groupes'] = 
	"INSERT INTO `".$prefixe."groupes` (`group_id`, `titre`, `description`, `icone`, `couleur`, `type`, `user_id`, `ordre`) VALUES
	(1, 'invites', 'Les personnes n''ayant pas activ&eacute; leur compte', 'user-info-48x48.png', '#999999', '1', NULL, 0),
	(2, 'membres', 'Les actifs sans privil&egrave;ges', 'user-48x48.png', '#6699CC', '1', NULL, 1),
	(3, 'admins', 'Les actifs avec privil&egrave;ges', 'Administrator-48x48.png', '#CC0000', '1', NULL, 2)";

$requetes['CREATE TABLE '.$prefixe.'groupes_index'] = 
	"CREATE TABLE `".$prefixe."groupes_index` (
	  `group_id` int(11) unsigned NOT NULL default '0',
	  `user_id` int(11) unsigned NOT NULL default '0',
	  `accepte` binary(1) NOT NULL default '0',
	  KEY `group_id` (`group_id`,`user_id`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

$requetes['CREATE TABLE '.$prefixe.'modeles'] = 
	"CREATE TABLE `".$prefixe."modeles` (
	  `id_modele` int(11) unsigned NOT NULL auto_increment,
	  `gabaris` VARCHAR( 30 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL,
	  `titre_modele` varchar(30) default NULL,
	  `map` text,
	  `fichier` VARCHAR( 255 ) NULL,
	  PRIMARY KEY  (`id_modele`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

$requetes['INSERT INTO '.$prefixe.'modeles'] = 
	"INSERT INTO `".$prefixe."modeles` (`id_modele`, `gabaris`, `titre_modele`, `map`) VALUES
	(1, '1_col', 'Pleine Page', 'a:1:{i:1;a:1:{i:0;s:6:\"module\";}}');";


$requetes['CREATE TABLE '.$prefixe.'modelisation_tables'] = 
	"CREATE TABLE `".$prefixe."modelisation_tables` (
	  `nom_champs` varchar(30) NOT NULL default '',
	  `type_saisie` varchar(10) NOT NULL default '',
	  `lang` varchar(30) NOT NULL default '',
	  `param` varchar(100) default NULL,
	  `page` varchar(30) NOT NULL default '',
	  PRIMARY KEY  (`nom_champs`),
	  KEY `page` (`page`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

$requetes['INSERT INTO '.$prefixe.'modelisation_tables'] = 
	"INSERT INTO `".$prefixe."modelisation_tables` (`nom_champs`, `type_saisie`, `lang`, `param`, `page`) VALUES
	('nom_site', 'text', 'Q_Nom_site', '30', 'Config_Metas'),
	('adresse_site', 'text', 'Q_Adresse_site', '30', 'Config_Generale'),
	('path', 'text', 'Q_Chemin_site', '10', 'Config_Generale'),
	('cookie_name', 'text', 'Q_Cookie_nom', '10', 'Config_Generale'),
	('cookie_time', 'text', 'Q_Cookie_temps', '10', 'Config_Generale'),
	('default_langue', 'select', 'Q_Langue', 'lister_langues', 'Config_Generale'), 
	('default_style', 'select', 'Q_Style', 'lister_styles', 'Config_Generale'),
	('default_style_iphone', 'select', 'Q_Style_iphone', 'lister_styles', 'Config_Generale'),
	('default_module', 'text', 'Q_DefaultPage', '30', 'Config_Generale'),
	('default_module_admin', 'text', 'Q_DefaultPage_Admin', '30', 'Config_Generale'),
	('imposer_modele_iphones', 'select', 'Q_Modele_impose_iphones', 'lister_modeles', 'Config_Generale'),
	('activer_menuh', 'bool', 'Q_ActiverMenu', 'possibilites_bool', 'Config_Generale'),
	('activer_lightbox', 'bool', 'Q_ActiverLightBox', 'possibilites_bool', 'Config_Generale'),
	('activer_digicode', 'bool', 'Q_ACTIVER_DIGICODE', 'possibilites_bool', 'Config_Generale'), 
	('activer_traceur', 'bool', 'Q_ActiverTraceur', 'possibilites_bool', 'Config_Generale'),
	('digicode_acces_zone_admin', 'text', 'Q_CODE_DIGICODE', '10', 'Config_Generale'),
	('cache_session_user', 'text', 'Q_Cache_session_user', '10', 'Config_Cache'),
	('cache_menuh_html', 'text', 'Q_cache_menuh_html', '10', 'Config_Cache'),
	('cache_menuh_pages_admin', 'text', 'Q_cache_menuh_pages_admin', '10', 'Config_Cache'),
	('user_id', 'text', 'L_USER_ID', '2', 'Utilisateurs'),
	('pseudo', 'text', 'L_PSEUDO', '20', 'Utilisateurs'),
	('email', 'email', 'L_EMAIL', '20', 'Utilisateurs'),
	('actif', 'bool', 'L_ACTIF', 'possibilites_bool', 'Utilisateurs'),
	('level', 'select', 'L_LEVEL', 'lister_levels', 'Utilisateurs'),
	('avatar', 'image', 'L_AVATAR', '25', 'Utilisateurs'),
	('style', 'select', 'L_STYLE', 'lister_styles', 'Utilisateurs'),
	('site_web', 'text', 'L_SITE_WEB', '30', 'Utilisateurs'),
	('langue', 'select', 'L_LANG', 'lister_langues', 'Utilisateurs'),
	('date_register', 'date', 'L_DATE_REGISTER', 'j:n:Y', 'Utilisateurs'),
	('points', 'text', 'L_POINTS', '5', 'Utilisateurs'),
	('msg', 'text', 'L_MESSAGES', '10', 'Utilisateurs'),
	('msn', 'text', 'L_MSN', '10', 'Utilisateurs'),
	('yahoo', 'text', 'L_YAHOO', '10', 'Utilisateurs'),
	('icq', 'text', 'L_ICQ', '10', 'Utilisateurs'),
	('gtalk', 'text', 'L_GTALK', '10', 'Utilisateurs'),
	('sexe', 'text', 'L_SEXE', '10', 'Utilisateurs'),
	('etat_civil', 'text', 'L_ETAT_CIVIL', '10', 'Utilisateurs'),
	('localisation', 'text', 'L_LOCALISATION', '10', 'Utilisateurs'),
	('mail_smtp', 'bool', 'L_MAIL_SMTP', 'possibilites_bool', 'Emails'),
	('mail_secure', 'bool', 'L_MAIL_SECURE', 'possibilites_bool', 'Emails'),
	('mail_url_serveur', 'text', 'L_MAIL_URL_SERVEUR', '20', 'Emails'),
	('mail_port_serveur', 'text', 'L_MAIL_PORT_SERVEUR', '5', 'Emails'),
	('mail_username', 'text', 'L_MAIL_USERNAME', '20', 'Emails'),
	('mail_password', 'password', 'L_MAIL_PASSWORD', '20', 'Emails'),
	('mail_from', 'text', 'L_MAIL_FROM', '20', 'Emails'),
	('mail_fromname', 'text', 'L_MAIL_FROMNAME', '20', 'Emails'),
	('wysiwyg_editor', 'select', 'Q_WYSIWYG', 'lister_wysiwyg', 'Config_Generale'),
	('register_question', 'text', 'Q_QUESTION_ANTI_BOTS', '30', 'Config_Generale'),
	('register_question_reponse', 'text', 'Q_REPONSE_ANTI_BOTS', '30', 'Config_Generale'),
	('description_site', 'text', 'Q_DESCRIPTION_SITE', '30', 'Config_Metas'),
	('cache_duree_listing_ip', 'text', 'Q_CACHE_DUREE_IP', '10', 'Config_Cache'),
	('cache_duree_listing_users', 'text', 'Q_CACHE_DUREE_LISTING_USERS', '10', 'Config_Cache'),
	('cache_duree_listing_rangs', 'text', 'Q_CACHE_DUREE_LISTING_RANGS', '10', 'Config_Cache'),
	('cache_duree_listing_modules', 'text', 'Q_CACHE_DUREE_LISTING_MODULES', '10', 'Config_Cache');";

$requetes['CREATE TABLE '.$prefixe.'modules'] = 
	"CREATE TABLE `".$prefixe."modules` (
	  `id_module` int(11) unsigned NOT NULL auto_increment,
	  `module` varchar(30) NOT NULL default '',
	  `modele` int(5) unsigned NOT NULL default '0',
	  `style` varchar(30) default NULL,
	  `virtuel` varchar(30) default NULL,
	  PRIMARY KEY  (`id_module`),
	  KEY `module` (`module`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

$requetes['CREATE TABLE '.$prefixe.'plugins'] = 
	"CREATE TABLE `".$prefixe."plugins` (
	  `plugin` varchar(30) NOT NULL,
	  `type` tinyint(1) NOT NULL,
	  `version` varchar(10) NOT NULL,
	  PRIMARY KEY  (`plugin`,`type`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

$requetes['CREATE TABLE '.$prefixe.'rangs'] = 
	"CREATE TABLE `".$prefixe."rangs` (
	  `id_rang` int(11) unsigned NOT NULL auto_increment,
	  `titre` varchar(100) NOT NULL default '',
	  `image` varchar(255) default NULL,
	  `msg` int(11) unsigned default NULL,
	  PRIMARY KEY  (`id_rang`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

$requetes['INSERT INTO '.$prefixe.'rangs'] = 
	"INSERT INTO `".$prefixe."rangs` (`titre`, `image`, `msg`) VALUES ('Nouveau', 'data/rangs/1.gif', 0);";
	
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
	
$requetes['CREATE TABLE '.$prefixe.'sessions'] = 
	"CREATE TABLE `".$prefixe."sessions` (
	  `id_session` varchar(32) NOT NULL default '',
	  `user_id` int(11) unsigned NOT NULL default '1',
	  `date_lastvisite` int(11) unsigned NOT NULL default '0',
	  `user_ip` varchar(15) NOT NULL default '',
	  `pseudo` VARCHAR( 45 ) NULL ,
	  PRIMARY KEY  (`id_session`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
	
$requetes['CREATE TABLE '.$prefixe.'sessions_suivies'] = 
	"CREATE TABLE `".$prefixe."sessions_suivies` (
	`user_id` INT UNSIGNED NOT NULL ,
	`pseudo` VARCHAR( 45 ) NOT NULL ,
	`date` INT UNSIGNED NOT NULL ,
	`url_page` VARCHAR( 255 ) NULL ,
	`libelle_page` VARCHAR( 255 ) NULL ,
	INDEX ( `user_id` , `pseudo` )
	) ENGINE = MYISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
	
$requetes['CREATE TABLE '.$prefixe.'smileys'] = 
	"CREATE TABLE `".$prefixe."smileys` (
	  `id_smiley` int(10) unsigned NOT NULL auto_increment,
	  `url_smiley` varchar(255) NOT NULL,
	  `titre_smiley` varchar(50) default NULL,
	  `tag_smiley` VARCHAR( 10 ) NULL,
	  `ordre` int(10) unsigned NOT NULL default '999',
	  PRIMARY KEY  (`id_smiley`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

$requetes['INSERT INTO '.$prefixe.'smileys'] = 
	"INSERT INTO `".$prefixe."smileys` ( `url_smiley`, `tag_smiley`, `titre_smiley`, `ordre`) VALUES
	('icon_lol.gif', 'mdr', 'lol', 0),
	('icon_sad.gif', ' :( ', 'Je suis triste', 1),
	('icon_neutral.gif', ' :| ', 'J''en reste sans voix', 2),
	('icon_smile.gif', ' :) ', 'je souris', 3),
	('icon_surprised.gif', ':||', 'Je suis surpris', 4),
	('icon_eek.gif', ':-s', 'Je suis choqu&eacute;', 5),
	('icon_evil.gif', ':-[', 'M&eacute;chant', 6),
	('icon_wink.gif', ';-)', 'Clin d''oeil', 7),
	('icon_frown.gif', '::|', 'Je fronce des sourcils', 8),
	('icon_shame.gif', ':8)', 'J''ai honte', 9),
	('icon_razz.gif', ' :p ', 'Je tire la langue', 10),
	('icon_cool.gif', ':o)', 'Coool', 11),
	('icon_whistle.gif', ':siffle:', 'Sifflote', 12),
	('icon_zen.gif', ':-)', 'Zen', 13),
	('icon_bye.gif',  'a+', 'Bye Bye', 14),
	('icon_sleep.gif', ':dodo:', 'Je dors', 15),
	('icon_him.gif', ':lui:', 'C''est lui', 16),
	('icon_sulk.gif', ':((', 'Je boude', 17),
	('icon_very_sad.gif',':triste:', 'Je suis tr&egrave;s triste', 18),
	('icon_cry.gif', ':(s', 'Je pleure', 19),
	('icon_angry.gif', ':colere:', 'Je suis en col&egrave;re', 20),
	('icon_ban.gif', ':boulet:', 'Je suis un boulet', 21),
	('icon_wall.gif', ':mur:', 'Je me tape la t&ecirc;te contre le mur', 22),
	('icon_smoke.gif', ':fume:', 'Je fume', 23),
	('icon_slap.gif', ':fouet:', 'Un coup de fouet', 24),
	('icon_pirate.gif', ':pirate:', 'Pirate !', 25),
	('icon_pc.gif', ':geek:', 'Geek Inside', 26),
	('icon_gentleman.gif', ':man:', 'Gentleman', 27),
	('icon_woman.gif', ':woman:', 'Une femme', 28);";


$requetes['CREATE TABLE '.$prefixe.'users'] = 
	"CREATE TABLE `".$prefixe."users` (
	  `user_id` int(11) unsigned NOT NULL auto_increment,
	  `pseudo` varchar(30) NOT NULL default '',
	  `pass` varchar(80) NOT NULL default '',
	  `email` varchar(150) NOT NULL default '',
	  `date_register` int(11) unsigned NOT NULL default '0',
	  `date_naissance` date NOT NULL default '0000-00-00',
	  `sexe` tinyint(4) unsigned NOT NULL default '0',
	  `etat_civil` tinyint(1) unsigned NOT NULL default '0',
	  `localisation` varchar(255) default NULL,
	  `actif` tinyint(2) unsigned NOT NULL default '0',
	  `msg` int(11) unsigned NOT NULL default '0',
	  `points` int(11) unsigned NOT NULL default '0',
	  `avatar` varchar(150) default 'data/avatars/default_avatar.gif',
	  `langue` varchar(2) NOT NULL default 'fr',
	  `level` int(3) unsigned NOT NULL default '0',
	  `style` varchar(50) default NULL,
	  `clef` varchar(30) default NULL,
	  `fuseau` varchar(3) NOT NULL default '1',
	  `site_web` varchar(255) default NULL,
	  `msn` varchar(255) default NULL,
	  `yahoo` varchar(255) default NULL,
	  `gtalk` varchar(255) default NULL,
	  `icq` varchar(255) default NULL,
	  `signature` mediumtext,
	  `rang` int(11) unsigned NOT NULL default '0',
	  PRIMARY KEY  (`user_id`),
	  KEY `pseudo` (`pseudo`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

$requetes['INSERT INTO '.$prefixe.'users'] = 
	"INSERT INTO `".$prefixe."users` (`pseudo`, `pass`, `email`, `date_register`, `date_naissance`, `sexe`, `etat_civil`, `localisation`, `actif`, `msg`, `points`, `avatar`, `langue`, `level`, `style`, `clef`, `fuseau`, `site_web`, `msn`, `yahoo`, `gtalk`, `icq`, `signature`, `rang`) VALUES
	('invit&eacute;', '', '', ".time().", '2008-11-01', 0, 0, NULL, 0, 0, 0, 'data/avatars/default_avatar.gif', 'fr', 1, NULL, NULL, '0', NULL, NULL, NULL, NULL, NULL, NULL, 0);";
	
$requetes['CREATE TABLE '.$prefixe.'users_bannis'] = 
	"CREATE TABLE `".$prefixe."users_bannis` (
	  `id_ban` int(10) unsigned NOT NULL auto_increment,
	  `type_ban` tinyint(3) unsigned NOT NULL,
	  `pattern_ban` varchar(250) NOT NULL,
	  `debut_ban` int(11) unsigned NOT NULL,
	  `fin_ban` int(11) unsigned NOT NULL default '0',
	  `raison_ban` text,
	  PRIMARY KEY  (`id_ban`),
	  KEY `valeur_ban` (`pattern_ban`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
?>