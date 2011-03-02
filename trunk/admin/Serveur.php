<?php
/*
|------------------------------------------------------------------------------------------------------------
| Software: Malleo ( CMS )
| Contact:  SP - http://www.malleo-cms.com
| Support: http://www.malleo-cms.com?module=forum
|  Documentation : Support: http://www.malleo-cms.com?module=wiki
|------------------------------------------------------------------------------------------------------------
|  Author: Stephane RAJALU
|  Copyright (c) 2008-2009, Stephane RAJALU All Rights Reserved
|------------------------------------------------------------------------------------------------------------
|  License: Distributed under the CECILL V2 License
|  This program is distributed in the hope that it will be useful - WITHOUT 
|  ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or 
|  FITNESS FOR A PARTICULAR PURPOSE. 
|
| Please read Licence_CeCILL_V2-en.txt
| SVP lisez Licence_CeCILL_V2-fr.txt
|------------------------------------------------------------------------------------------------------------
*/
if ( !defined('PROTECT_ADMIN') )
{
	die("Tentative de Hacking");
}
load_lang('serveur');

$tpl->set_filenames(array('body_admin' => $root.'html/admin_serveur.html'));

// TRAITEMENT
if (isset($_POST['action']) || isset($_GET['action']))
{
	$action = (isset($_POST['action']))? $_POST['action']:$_GET['action'] ;
	switch ($action)
	{
		case 'phpinfo':
			phpinfo();
			exit;
	}
}

// Parametres
$liste_parametres = array('allow_url_fopen','post_max_size','register_globals','safe_mode','sql.safe_mode','SMTP','smtp_port',
'session.name','session.save_path','session.use_cookies','session.use_only_cookies','session.use_trans_sid');
foreach($liste_parametres AS $param){
	
	$val = get_cfg_var($param);
	$val = ($val == '' || $val == '0')?'Off':(($val == '1')?'On':$val);
	$tpl->assign_block_vars('liste_parametres', array(
		'PARAMETRE'	=> $param,
		'VALEUR'	=> $val,	
	));
}

$tpl->assign_vars(array(
	 'L_MAGIC_QUOTES_GPC'		=>	'magic_quotes_gpc',
	 'L_MAGIC_QUOTES_RUNTIME'	=>	'magic_quotes_runtime',
	
	 'VERSION_MYSQL'			=>	(function_exists('mysql_get_client_info'))?mysql_get_client_info():$lang['L_INDISPONIBLE'],
	 'VERSION_PHP'				=>	(function_exists('phpVersion'))?phpVersion():$lang['L_INDISPONIBLE'],
	 'VERSION_APACHE'			=>	(function_exists('apache_get_version'))?apache_get_version():$lang['L_INDISPONIBLE'],
	 'LISTE_MODULES_APACHE'		=>	(function_exists('apache_get_modules'))?implode(', ',apache_get_modules()):$lang['L_INDISPONIBLE'],
	 'LISTE_MODULES_PHP'		=>	(function_exists('get_loaded_extensions'))?implode(', ',get_loaded_extensions()):$lang['L_INDISPONIBLE'],
	 'INFORMATIONS_COMPLETES'	=>	formate_url('action=phpinfo',true),
	 'VERSION_ACTUELLE_CMS'		=>	$cf->config['version_cms'],
	 'MAGIC_QUOTES_GPC'			=>	(function_exists('get_magic_quotes_gpc'))?get_magic_quotes_gpc():$lang['L_INDISPONIBLE'],
	 'MAGIC_QUOTES_RUNTIME'		=>	(function_exists('get_magic_quotes_runtime'))?get_magic_quotes_runtime():$lang['L_INDISPONIBLE']
));

?>