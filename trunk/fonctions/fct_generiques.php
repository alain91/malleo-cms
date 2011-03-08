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
if ( !defined('PROTECT') )
{
	die("Tentative de Hacking");
}

//
// Fonction de debuguage
function _p($array){
	echo '<pre>';
	die(print_r($array).'</pre>');
}
//
// Charge le fichier de configuration d'un style
function load_style($style=null){
	global $user,$root,$style_path,$style_name,$cf,$img;
	$style_name = '';
	
	// Est ce que on surf sous un iPhone ?
	if (!defined('IPHONE') 
		&& preg_match('/(symbian|smartphone|midp|wap|phone|pocket|mobile|pda|psp|iphone|ipod)/i', strtolower($_SERVER['HTTP_USER_AGENT']))){
			define('IPHONE',true);
	}
	
	// Theme par defaut si il existe
	if (file_exists($root.$style_path.$cf->config['default_style'].'/cfg.php')){
		$style_name = $cf->config['default_style'];
		include_once($root.$style_path.$cf->config['default_style'].'/cfg.php');
	}
	
	// Theme impose
	if ($style != null && file_exists($root.$style_path.$style.'/cfg.php')){
		$style_name = $style;
	}
	// Theme utilisateur
	elseif ($user['level']>1 && file_exists($root.'styles/'.$user['style'])
		&& file_exists($root.$style_path.$user['style'].'/cfg.php')){
		$style_name = $user['style'];
	}
	// iPhone ?
	elseif ($user['user_id'] == 1 && defined('IPHONE')){
		$style_name = $cf->config['default_style_iphone'];
	}

	// 1er theme trouve
	if (!isset($style_name) || empty($style_name)){
		$chemin = $root.'styles/';
		$ch = @opendir($chemin);
		while ($style = @readdir($ch))
		{
			$ext = pathinfo($style);
			if ($style[0] != '.' && is_dir($chemin.$style)) {
				$style_name = $style;
				break;
			}
		}
		@closedir($ch);
	}
	if (!empty($style_name)) include_once($root.$style_path.$style_name.'/cfg.php');
	return $style_name;
}

//
// FONCTION secondaide permettant juste une meilleure lecture
function include_once_file($file,$else_file=false){
	global $root,$lang;
	$file = $root.$file;
	if (file_exists($file)){
		include_once($file);
	}elseif($else_file!=false){
		$else_file = $root.$else_file;
		if (file_exists($else_file)){ 
			include_once($else_file);
		}
	}
}
//
// Fonction permettant de charger automatiquement le fichier de langue d'un module
// Si celui-ci n'existe pas dans la langue du user, la langue francaise sera chargee par defaut
function select_langue(){
	global $user,$cf,$root;
	if (is_array($user) && array_key_exists('langue',$user) && is_dir($root.'lang/'.$user['langue'])){
		return $user['langue'];
	}elseif(isset($cf)){
		return $cf->config['default_langue']; 
	}else{
		return 'fr';
	}
}

function load_lang_mod($mod)
{
	include_once_file('plugins/modules/'.$mod.'/lang_'.select_langue().'.php',
					   'plugins/modules/'.$mod.'/lang_fr.php');
	return true;
}

function load_lang_bloc($bloc)
{
	include_once_file('plugins/blocs/'.$bloc.'/lang_'.select_langue().'.php',
					   'plugins/blocs/'.$bloc.'/lang_fr.php');
	return true;
}

function load_lang($fichier)
{
	include_once_file('lang/'.select_langue().'/lang_'.$fichier.'.php',
					   'lang/fr/lang_'.$fichier.'.php');
	return true;
}

//
// Fusionne les images
function load_images_mod($module=false){
	if ($module == false) return;
	
	global $root,$style_path,$style_name,$img;
	
	$file = $root.'plugins/modules/'.$module.'/images.php';
	if (file_exists($file)){
		$images = array();
		include($file);
		if (function_exists('array_diff_key'))$images = array_diff_key($images, $img);
		$img = array_merge($img,$images);
	}
}
function load_images_bloc($bloc=false){
	if ($bloc == false) return;
	global $root,$style_path,$style_name,$img;
	$file = $root.'plugins/blocs/'.$bloc.'/images.php';
	if (file_exists($file)){
		$images = array();
		include($file);
		if (function_exists('array_diff_key'))$images = array_diff_key($images, $img);
		$img = array_merge($img,$images);
	}
}

//
// Lit le contenu d'un fichier distant via fsockopen
function fsockopen_file_get_contents($url){
	if (!function_exists('fsockopen')) return false;

	$parts = parse_url($url);
	// gethostbyname suppose d'avoir un serveur DNS fonctionnel pour obtenir l'IP du serveur
	// Celà suppose aussi qu'une ip fournie en url ne fonctionnerait pas
	if ($parts['host'] != gethostbyname($parts['host'])){
		$port = ( !empty($parts['port']) ) ? $parts['port'] : 80;
		$errno= $errstr = '';
		if ($fsock = @fsockopen($parts['host'], $port, $errno, $errstr)){
			@fputs($fsock, "GET ".$parts['path']." HTTP/1.1\r\n");
			@fputs($fsock, "HOST: " . $parts['host'] . "\r\n");
			@fputs($fsock, "Connection: close\r\n\r\n");
			$data ='';
			while( !@feof($fsock) )
			{
				$data .= @fread($fsock, 200000);
			}
			@fclose($fsock);
			// On masque les erreurs 404, par contre les autres erreurs seront visibles
			if (!preg_match('#404#i', $data)){
				preg_match('#Content-Length\: ([0-9]+)[^ /][\s]+#i', $data, $filesize);
				return substr($data, strlen($data) - $filesize[1],$filesize[1]);
			}
		}
	}
	return false;
}


//
// Affiche un message

function affiche_message($handle,$message,$redirect){
	global $root,$tpl,$lang;
	$tpl->set_filenames(array(
			$handle => $root.'html/box.html'
	));
	$tpl->assign_vars(array(
		'L_TITRE_BOX'	=> $lang['L_TITRE_BOX'],
		'L_RETOUR'		=> $lang['L_RETOUR_DIRECT'],
		'L_MESSAGE'		=> $lang[$message],
		'REDIRECT'		=> $redirect
	));
}

//
// Fonction permettant d'afficher une erreur de saisie 

function erreur_saisie($noeud,$message,$parametres=false){
	global $tpl;
	$tpl->assign_block_vars($noeud, array());
   	$tpl->assign_var('ALERTE_ERREUR_SAISIE',$message);
	if (is_array($parametres)){
		$tpl->assign_vars($parametres);
	}
}

//
// FONCTION de erreur HTTP type 403/404 : pages non autorisees/non existantes

function error404($msg=false)
{
	global $c,$root,$user,$cf,$tpl,$lang,$erreur,$style_path,$style_name,$session;
	$style_name = load_style();
	// Fichier de langue des erreurs
	if(file_exists($root.'lang/fr/lang_error.php')){ 
			include_once($root.'lang/fr/lang_error.php');
	}
	if (is_array($user) && array_key_exists('langue',$user) && file_exists($root.'lang/'.$user['langue'].'/lang_error.php')){
		include_once($root.'lang/'.$user['langue'].'/lang_error.php');
	}
	// SI un BUG survient avant le chargement des elements de base on tente de relancer le minimum
	$msg = ($msg==false || !array_key_exists($msg,$erreur))?$lang['PAGE_NOT_FOUND']: $erreur[$msg];
	if(!isset($session))$session->make_navlinks($msg,formate_url('',true));
	$tpl->set_filenames(array('body' => $root.'html/error_404.html'));
	$tpl->assign_vars(array(
		'MSG'		=> $msg,
		'L_RETOUR'	=> $lang['L_RETOUR']
	));
	$tpl->assign_block_vars('retour', array());
	define('ERROR_404',true);
	include_once($root.'page_haut.php');
	$tpl->pparse('body');
	include_once($root.'page_bas.php');
	$tpl->afficher_page(); 
	exit;
}

//
//  FONCTION de DEBUGUAGE PRINCIPALE

function message_die($CodeErreur,$NumErreur, $err_file, $err_line, $sql = '')
{
	// On masque les erreur E_STRICT non graves
	if (defined('E_STRICT') && $CodeErreur == E_STRICT) return false;
	
	global $c,$root,$user,$cf,$session,$tpl,$lang,$erreur,$code_erreur,$style_path,$style_name;
	$error_msg = $sql_code = $sql_msg = $sql;
	
	// On charge le fichier de langue FR
	if(file_exists($root.'lang/fr/lang_error.php')){ 
			include_once($root.'lang/fr/lang_error.php');
	}
	if (is_array($user) && array_key_exists('langue',$user) && ($user['langue']!='fr') && file_exists($root.'lang/'.$user['langue'].'/lang_error.php')){
		include_once($root.'lang/'.$user['langue'].'/lang_error.php');
	}
	if (!is_array($user)){
		global $cf;
		$user['langue'] = $cf->config['default_langue'];
	}
	
	// Si le moteur de template n'est par charge on le charge
	if (!is_object($tpl)){
		include_once($root.'class/class_template.php');
		$tpl = new Template($root);
		$style_name = load_style('BlueLight');
	}
	$tpl->set_filenames(array('body' => $root.'html/error_msg.html'));
	
	// FORMATAGE du message
	$error_msg = (is_array($erreur) && array_key_exists($NumErreur, $erreur))? $erreur[$NumErreur]:$NumErreur;
	if ($sql != '' && !is_array($sql))
	{
		$tpl->assign_block_vars('SQL_MSG', array());
		$sql_error = $c->sql_error();
		$sql_code = $sql_error['code'];
		$sql_msg = $sql_error['message'];
		require_once($root.'librairies/geshi/geshi.php');
		$geshi = new GeSHi($sql, 'SQL');
		$sql = $geshi->parse_code();
	}
	if ($err_file != '' || $err_line!= '') $tpl->assign_block_vars('emplacement', array());
	if($session!=null)$session->make_navlinks($error_msg,formate_url('#',true));
	
	$tpl->assign_vars(array(
		'L_EMPLACEMENT'	=> $lang['L_EMPLACEMENT'],
		'L_LIGNE'		=> $lang['LIGNE'],
		'L_FICHIER'		=> $lang['FICHIER'],
		'L_SQL'			=> $lang['L_SQL'],
		'L_SQL_CODE'	=> $lang['SQL_CODE'],
		'L_SQL_MSG'		=> $lang['SQL_MESSAGE'],
		'L_SQL_REQUETE'	=> $lang['SQL_REQUETE'],
		'L_RETOUR'		=> $lang['L_RETOUR'],
		'SQL_CODE'		=> $sql_code,
		'SQL_MSG'		=> $sql_msg,
		'SQL_REQUETE'	=> $sql,
		'ERROR_LINE'	=> $err_line,
		'ERROR_FILE'	=> $err_file,
		'ERROR_CODE'	=> $code_erreur[$CodeErreur],
		'ERROR_MSG'		=> $error_msg
	));
	define('MESSAGE_DIE',true);
	$tpl->assign_block_vars('retour', array());
	include_once($root.'page_haut.php');
	$tpl->pparse('body');
	include_once($root.'page_bas.php');
	$tpl->afficher_page();
	exit;
}
$old_error_handler = @set_error_handler('message_die');




//
// CREE une liste d'utilisateurs afin de les colorer sans avoir a effectuer des jointures dans chaque extension
function cache_liste_users()
{
	global $c;
	$sql = 'SELECT u.user_id, u.pseudo, g.couleur  
			FROM '.TABLE_USERS.' AS u 
			LEFT JOIN '.TABLE_GROUPES_INDEX.' AS i
				ON (u.user_id=i.user_id)
			LEFT JOIN '.TABLE_GROUPES.' AS g
				ON (i.group_id=g.group_id)
			WHERE (i.accepte=1 OR i.user_id is null)
				AND g.type=true 
			GROUP BY u.user_id
			ORDER BY ordre ASC';
	if (!$resultat=$c->sql_query($sql)) message_die(E_ERROR,17,__FILE__,__LINE__,$sql);
	$users = array();
	while($row = $c->sql_fetchrow($resultat))
	{
		$users[$row['user_id']] = $row;
	}
	return $users;
}

//
// Mets en cache la liste des rangs
function cache_liste_rangs(){
	global $c;
	$sql = 'SELECT id_rang, titre, image, msg 
			FROM '.TABLE_RANGS.' 
			ORDER BY msg ASC';
	if (!$resultat = $c->sql_query($sql)) message_die(E_ERROR,1101,__FILE__,__LINE__,$sql); 
	$liste_rangs = array();
	while($row = $c->sql_fetchrow($resultat))
	{
		$liste_rangs[$row['id_rang']] = $row;
	}
	return $liste_rangs;
}

//
// Mets en cache la liste des plugins installes
function cache_liste_plugins(){
	global $c;
	$sql = 'SELECT plugin, type, version 
			FROM '.TABLE_PLUGINS;
	if (!$resultat = $c->sql_query($sql)) message_die(E_ERROR,1101,__FILE__,__LINE__,$sql); 
	$liste_plugins = array();
	while($row = $c->sql_fetchrow($resultat))
	{
		$liste_plugins[$row['plugin']] = $row;
	}
	return $liste_plugins;
}

//
// Mets en cache la liste des smileys installes
function cache_liste_smileys(){
	global $c;
	$sql = 'SELECT titre_smiley, url_smiley, tag_smiley  FROM '.TABLE_SMILEYS.' ORDER BY ordre ASC';
	if (!$resultat = $c->sql_query($sql)) message_die(E_ERROR,1101,__FILE__,__LINE__,$sql); 
	$liste_smileys = array();
	while($row = $c->sql_fetchrow($resultat))
	{
		$liste_smileys['data/smileys/'.$row['url_smiley']] = $row['titre_smiley'];
		if (!empty($row['tag_smiley'])){
			$liste_smileys['tags'][] = $row['tag_smiley'];
			$liste_smileys['urls'][] = '[img]data/smileys/'.$row['url_smiley'].'[/img]';
		}
	}
	return $liste_smileys;
}
//
// FORMATE une URL
// fonction centrale permettant d'uniformiser leur forme
// fonction ideale pour creer du rewriting

unset($base_formate_url);
function formate_url($args,$base=false)
{
	// BASE de redirection :
	// exemple : index.php?module=blog
	global $base_formate_url,$cf;
	// SEPARATEUR de php.ini
	$separateur = @ini_get('arg_separator.output');
	// SEPARATEUR a ajouter
	$et = strpos($base_formate_url, '?') ? $separateur : '?';
	$url = ($base == true)? $base_formate_url.((!empty($args))? $et.$args:''):$args;
	// COMPATIBILITE W3C
	$url = str_replace('&amp;','&',$url); 	
	$url = str_replace('&','&amp;',$url);
	return $url;
}

//
// TRANSFORME un pseudo en lien cliquable coloré renvoyant vers le profile de l'utilisateur
function formate_pseudo($user_id,$pseudo){
	global $users,$root,$cache,$bots;
	if($user_id==null||$pseudo==null) return;
	if ($user_id==1 && $pseudo!='invit&eacute;'){
		return $bots->formate_bots($pseudo);
	}elseif($user_id==1){
		return '<span class="pseudo">'.$pseudo.'</span>';
	}
	if (!is_array($users)) $users = $cache->appel_cache('listing_users');
	$style=(array_key_exists($user_id,$users) && !empty($users[$user_id]['couleur']))? ' style="color:'.$users[$user_id]['couleur'].'"':'';
	$url = ($user_id>1)? formate_url('index.php?module=profil&user_id='.$user_id):'#';
	return '<a href="'.$url.'" class="pseudo" '.$style.'>'.$pseudo.'</a>';
}

//
// TRANSFORME le nom d'un groupe en lien cliquable renvoyant vers la liste des membres du groupe

function formate_groupe($nom_groupe, $id_groupe, $couleur_groupe=''){
	$style = ($couleur_groupe!='')? ' style="color:'.$couleur_groupe.'"':'';
	return '<a href="'.formate_url('index.php?module=membres&action=groupe&groupe='.$id_groupe).'" class="groupe"'.$style.'>'.$nom_groupe.'</a>';
}

//
// TRANSFORME une date timestamp en un libelle comprehensible tout en respectant le format fournis, la langue, et le fuseau horaire du visiteur

function formate_date($time,$format,$langue,$fuseau)
{
	global $lang;
	// Heure d'été ou heure d'hiver?
	if (date('I') == 1){
		$time += 3600;
	}
	// On ramene l'heure enregistrée à l'heure GMT à l'heure GMT
	$decalage_GMT_serveur = date('Z');
	if ($decalage_GMT_serveur<0){
		// décalage = - secondes etats unis / amérique du sud
		$time = $time + intval($decalage_GMT_serveur);
	}else{
		// décalage = + secondes Asie / orient
		$time = $time - intval($decalage_GMT_serveur);
	}
	return vsprintf($lang[$langue],explode(' ',date($format,($time+($fuseau*3600)))));	
}


//
// Protection contre les failles XCRF /images n'en etant pas vraiment
function verifie_existance_image($url){
	return true;
	$errno = 0;
	$sortie = $errstr = '';
	$parts = parse_url($url);
	if (!array_key_exists('host',$parts)) 	return true;
	$port = ( !empty($parts['port']) ) ? $parts['port'] : 80;
	if ($parts['host'] == gethostbyname($parts['host'])) return false;
	if (function_exists('fsockopen') && $fp = @fsockopen($parts['host'], $port, $errno, $errstr, 5)){
		@fputs($fp, "GET ".$parts['path']." HTTP/1.1\r\n");
		@fputs($fp, "HOST: " . $parts['host'] . "\r\n");
		@fputs($fp, "Connection: Close\r\n\r\n");
		while (!feof($fp)){
			$sortie .= @fread($fp, 99999);
		}
		return (preg_match('/Content-Type: image/',$sortie))? true:false;
		fclose($fp);
	}
	return false;
}
?>