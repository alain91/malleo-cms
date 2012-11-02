<?php
if (!defined('PROTECT'))
{
	die("Tentative de Hacking");
}
// Connexion a la base
include_once($root.'config/config.php');
require_once($root.'class/class_mysql.php');		
$c = new sql_db($hote, $utilisateur, $password, $base, false);
if(!$c->db_connect_id)
{
	die("Impossible de se connecter à la base de données");
}
// Chargement du Cache
require_once($root.'class/class_cache.php');
$cache= new cache();

// Config generale 
require_once($root.'class/class_config.php');
$cf = new config();
$cf->appel_config();

// On applique la configuration des modules au cache
$cache->initialiser_config_cache();

// Protection des variables
protection_variables();

//  Gestion de la session et mise en place des droits
require_once($root.'class/class_droits.php');
$droits = new droits();

require_once($root.'class/class_session.php');
$session = new session($cf->config);
//$user = $session->new_session();

// On charge le template
include_once($root.'class/class_template.php');
$tpl = new Template($root);

// Plugins
$liste_plugins = $cache->appel_cache('listing_plugins',true);

require_once($root.'class/class_modules.php');
$mod = new modules($root);

global $c, $cf, $users, $droits, $style_path, $style_name, $liste_plugins, $tpl;
	
$disabled = true;
$dir = $root.'plugins/modules/';
$excludes = array('.','..','.htaccess','index.html');
$modules_non_acceptables = array('profil','messagerie','membres');

// Enregistrement du module par defaut
if (isset($_POST['module_defaut'])){
	$sql = 'UPDATE '.TABLE_CONFIG.' SET valeur="'.$_POST['module'].'" WHERE data="default_module"';
	$c->sql_query($sql);
	$cf->appel_config('LECTURE',true);
}

$tpl->set_filenames(array(
  'body_install' => $root.'install/html/etape7.html'
));
$tpl->titre_page = $tpl->titre_navigateur = $lang['L_INSTALLATION_MODULES'];

//
// Modules
$ch = @opendir($dir);
while ($module = @readdir($ch))
{
	if (!in_array($module,$excludes) && is_dir($dir.$module)) {
		if (!array_key_exists($module,$liste_plugins)) $mod->ajoute_module($module,1,'');
		$tpl->assign_block_vars('liste_modules', array(
			'MODULE'	=> $module
		));
		if (in_array($module,$modules_non_acceptables))	$tpl->assign_block_vars('liste_modules.non_acceptable', array());
		if ($cf->config['default_module'] == $module)	$tpl->assign_block_vars('liste_modules.checked', array());
	}
}

// Purge du cache
$cache->purger_cache();

if ($cf->config['default_module'] != '') $disabled = false;

$tpl->assign_vars(array(
	'L_EXPLAIN_MODULES'		=> $lang['L_EXPLAIN_MODULES'],
	'L_MODULES'				=> $lang['L_MODULES'],
	'L_DEFINIR_DEFAUT'		=> $lang['L_DEFINIR_DEFAUT'],
	'DISABLED_DEFAULT'=> (($disabled==false)? 'disabled':''),
	'DISABLED_ETAPE_SUIVANTE'=> (($disabled==true)? 'disabled':'')
));

?>
