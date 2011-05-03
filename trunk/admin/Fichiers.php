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

// PARAMETRES
// --------------------------------------------------------------------------------------------
$ext_apercu = array('php','js','xml','htaccess','css','ini','pl','sql','cache','txt','html','jpg','jpeg','ico','gif','png','bmp','tiff','swf');
$ext_images = array('jpg','jpeg','ico','gif','png','bmp','tiff');
$ext_edition = array('php','js','xml','htaccess','css','ini','pl','sql','cache','txt','html');
$fichiers_interdits = array('../','config.php');

$activer_edition = true;
$activer_suppression = true;
$activer_upload = true;
// --------------------------------------------------------------------------------------------
load_lang('fichiers');

$tpl->set_filenames(array('body_admin' => $root.'html/admin_fichiers.html'));

// Entréé: Octets
// Sortie : Ko, Mo, Go
function formate_taille_fichier($size){
	global $lang;
	if ($size < 1024){
		return sprintf($lang['L_OCTETS'],$size );
	}elseif($size < 1048576){
		return sprintf($lang['L_KOCTETS'],round(($size/1024),2));
	}elseif($size < 1073741824){
		return sprintf($lang['L_MOCTETS'],round(($size/1024/1024),2));
	}else{
		return sprintf($lang['L_GOCTETS'],round(($size/1024/1024/1024),2));
	}
}

// Espace disque
function dirsize($dir)
{
	$size=0;
	$ch = @opendir($dir);
	while ($file = @readdir($ch))
	{
		if ($file[0] != ".") {
			if (is_dir($dir.$file)){
				$size += dirsize($dir.$file.'/');
			}else{
				$size += filesize($dir.$file);
			}
		}
	}
	@closedir($ch);
	return $size;
}
// Extension
function extension($fichier)
{
	$ext = pathinfo($fichier);
	 return (isset($ext['extension']))?strtolower($ext['extension']):'';
}
	
// Choix de l'icone
function iconefile($file){
	global $root;
	$chemin_icones = $root. 'data/icones_fichiers/';
	if (is_dir($file)){
		$icone = 'folder.png';
	}else{
		switch(extension($file)){
			case'htaccess':					$icone = 'shield.png';break;
			case'zip':						$icone = 'page_white_compressed.png';break;
			case'html':						$icone = 'html.png';break;
			case'txt':						$icone = 'page_white.png';break;
			case'pdf':						$icone = 'page_white_acrobat.png';break;
			case'as':						$icone = 'page_white_actionscript.png';break;
			case'sql':						$icone = 'page_white_database.png';break;
			case'doc':						$icone = 'page_white_word.png';break;
			case'php':						$icone = 'page_white_php.png';break;
			case'xls':						$icone = 'page_white_excel.png';break;
			case'xml':						$icone = 'page_white_code_red.png';break;
			case'css':						$icone = 'css.png';break;
			case'ppt':case'pps':			$icone = 'page_white_powerpoint.png';break;
			case'fla':case'swf':			$icone = 'page_white_flash.png';break;
			case'wav':case'mp3':case'ogg':	$icone = 'cd.png';break;
			case'wvx':case'avi':case'mpg':case'mpeg':case'mp4':	$icone = 'film.png';break;
			case'jpg':case'jpeg':case'gif':case'bmp':case'png':case'tiff':	$icone = 'page_white_picture.png';break;		
			default:						$icone = 'inconnu.png';break;		
		}	
	}	
	return '<img src="'.$chemin_icones.$icone.'" />';
}

	
//
// Verifie l'unicite d'un fichier dans un dossier, et si il existe deja on trouve un nom de fichier approchant disponible
function nom_unique($nom_fichier,$destination,$nom_teste=false,$cpt=1){
	if ($nom_teste==false) $nom_teste = supprimer_accents(utf8_decode($nom_fichier));
	if (file_exists($destination.$nom_teste) && $cpt<10)
	{
		$ext = pathinfo($nom_teste);
		$nom_teste = eregi_replace('.'.$ext['extension'],'',$nom_fichier).'_'.$cpt.'.'.$ext['extension'];
		$cpt++;
		$nom_teste = nom_unique($nom_fichier,$destination,$nom_teste,$cpt);			
	}
	return $nom_teste;
}


// TRAITEMENT
$action = null;
if (isset($_POST['action']) || isset($_GET['action']))
{
	$action = (isset($_POST['action']))? $_POST['action']:$_GET['action'] ;
}
$show_path = urldecode((isset($_GET['show_path']) && is_dir($_GET['show_path']) && ($_GET['show_path'] !='../'))?$_GET['show_path']:$root);
if (!preg_match('#'.preg_quote(dirname(realpath($_SERVER['SCRIPT_FILENAME'])), '#').'#i',realpath($show_path))) $show_path =	$root;

switch ($action)
{
	case 'upload':
			$destination = urldecode($_POST['show_path']);
			if (isset($_FILES) && is_array($_FILES)){
				if ($_FILES['fichier']['error'] == UPLOAD_ERR_OK){
					$nom_fichier = nom_unique($_FILES['fichier']['name'],$destination);
					if (is_uploaded_file($_FILES['fichier']['tmp_name']))
					{
					 	@move_uploaded_file($_FILES['fichier']['tmp_name'], $destination.$nom_fichier);
						@chmod($destination.$nom_fichier, 0777);
					}
				}
			}
			header('location: '.$base_formate_url.'&show_path='.urlencode($destination));
			break;
	case 'editer':
		$file = urldecode($_GET['fichier']);
		$ext = pathinfo($file);
		// Securite
		if (!file_exists($file) || !is_readable($file)
			|| in_array($ext['basename'],$fichiers_interdits)  
			|| !in_array($ext['extension'],$ext_edition)
			|| !preg_match('#'.preg_quote(dirname(realpath($_SERVER['SCRIPT_FILENAME'])), '#').'#i',dirname(realpath($file)))){
			header('location: '.$base_formate_url);
		}
		$show_path = dirname($file);
		$tpl->assign_block_vars('editer', array());
		$tpl->assign_vars(array(
			'CONTENU_FICHIER'	 => htmlentities(file_get_contents($file)),
			'NOM_FICHIER'		 => $ext['basename'],
		));
		break;	
	case 'EnregistrerEditer':
		$file = urldecode($_GET['fichier']);
		$ext = pathinfo($file);
		// Securite
		if (!file_exists($file) || !is_readable($file)
			|| in_array($ext['basename'],$fichiers_interdits)  
			|| !in_array($ext['extension'],$ext_edition)
			|| !preg_match('#'.preg_quote(dirname(realpath($_SERVER['SCRIPT_FILENAME'])), '#').'#i',dirname(realpath($file)))){
			header('location: '.$base_formate_url);
		}
		$show_path = dirname($file);
		file_put_contents($file,stripslashes($_POST['fichier']));
		header('location: '.$base_formate_url.'&show_path='.urlencode(dirname($file).'/'));
		break;
	case 'supprimer':
		$file = urldecode($_GET['fichier']);
		$ext = pathinfo($file);
		// Securite
		if (!file_exists($file) || !is_writable($file)
			|| !isset($_GET['confirme'])
			|| $_GET['confirme'] != 1
			|| in_array($ext['basename'],$fichiers_interdits)
			|| !preg_match('#'.preg_quote(dirname(realpath($_SERVER['SCRIPT_FILENAME'])), '#').'#i',dirname(realpath($file)))){
			header('location: '.$base_formate_url);
		}else{
			@unlink($file);
		}
		header('location: '.$base_formate_url.'&show_path='.urlencode(dirname($file).'/'));
		break;
	case 'apercu':
		$file = urldecode($_GET['fichier']);
		$ext = pathinfo($file);
		// Securite
		if (!file_exists($file) || !is_readable($file)
			|| in_array($ext['basename'],$fichiers_interdits)  
			|| !in_array($ext['extension'],$ext_apercu)
			|| !preg_match('#'.preg_quote(dirname(realpath($_SERVER['SCRIPT_FILENAME'])), '#').'#i',dirname(realpath($file)))){
			header('location: '.$base_formate_url);
		}
		$show_path = dirname(urldecode($file));
		switch($ext['extension']){
			case 'php':				$code='php';break;
			case 'js':				$code='javascript';break;
			case 'xml':				$code='xml';break;
			case 'htaccess':		$code='apache';break;
			case 'css':				$code='css';break;
			case 'ini':				$code='ini';break;
			case 'pl':				$code='perl';break;
			case 'sql':				$code='sql';break;
			case 'cache':
			case 'txt':				$code='text';break;
			case 'html':			$code='html4strict';break;
			default:				$code=null;break;
		}
		// On regarde une animation flash
		if ($ext['extension']=='swf'){
			$tpl->assign_block_vars('apercu_flash', array());
		
		
		// On regarde une image
		}elseif (in_array($ext['extension'],$ext_images)){
			$tpl->assign_block_vars('apercu_image', array());
		
		// On colore du code
		}elseif($code != null){
			$tpl->assign_block_vars('apercu_code', array());
			$file = file_get_contents($file);
			require_once($root.'librairies/geshi/geshi.php');
			$type_demande= (file_exists($root.'librairies/geshi/geshi/'.extension($file).'.php'))? extension($file):'php';
			$geshi = new GeSHi(str_replace('\t\t','\t',utf8_encode(html_entity_decode($file))), $code);
			$geshi->enable_line_numbers(GESHI_NORMAL_LINE_NUMBERS);
			$file = $geshi->parse_code();
		}
		$tpl->assign_vars(array(
			'CONTENU_FICHIER'	 => $file,
			'NOM_FICHIER'		 => $ext['basename'],
		));
		break;
	case 'arbo':
	default:
		$tpl->assign_block_vars('arbo', array());
		$class = $classd = 'row2';
		$ch = @opendir($show_path);
		while ($file = @readdir($ch))
		{
			if ($file[0] != '.' && is_dir($show_path.$file)) {
				$tpl->assign_block_vars('arbo.liste_dossiers', array(
					'CLASS'				=> $classd = ($classd!='row1')?'row1':'row2',
					'ICONE_FICHIER'		=> iconefile($show_path.$file),
					'NOM_FICHIER'		=> $file,
					'DATE_FICHIER'		=> formate_date(filemtime($show_path.$file),'d m Y H i','FORMAT_DATE',$user['fuseau']),
					'PERMS_FICHIER'		=> substr(sprintf('%o',fileperms($show_path.$file)),-4),
					'TAILLE_FICHIER'	=> formate_taille_fichier(dirsize($show_path.$file.'/')),
					'U_OPEN'			=> formate_url('show_path='.urlencode($show_path.$file.'/'),true)
				));	
				$tpl->assign_block_vars('arbo.liste_dossiers.dossier', array());
			}elseif($file != '.' && $file != '..'){
				$tpl->assign_block_vars('arbo.liste_fichiers', array(
					'CLASS'				=> $class = ($class!='row1')?'row1':'row2',
					'ICONE_FICHIER'		=> iconefile($show_path.$file),
					'NOM_FICHIER'		=> $file,
					'DATE_FICHIER'		=> formate_date(filemtime($show_path.$file),'d m Y H i','FORMAT_DATE',$user['fuseau']),
					'PERMS_FICHIER'		=> substr(sprintf('%o',fileperms($show_path.$file)),-4),
					'TAILLE_FICHIER'	=> formate_taille_fichier(filesize($show_path.$file)),
					'URL_FICHIER'		=> $show_path.$file,
					'U_OPEN'			=> formate_url('action=apercu&fichier='.urlencode($show_path.$file),true),
					'U_EDITER'			=> formate_url('action=editer&fichier='.urlencode($show_path.$file),true),
					'U_SUPPRIMER'		=> formate_url('action=supprimer&fichier='.urlencode($show_path.$file),true),
				));
				// On propose l'apercu
				if (in_array(extension($file),$ext_apercu) && !in_array($file,$fichiers_interdits)){
					$tpl->assign_block_vars('arbo.liste_fichiers.apercu', array());
					// On affiche l'image au survole 
					if (in_array(extension($file),$ext_images)){
						$tpl->assign_block_vars('arbo.liste_fichiers.apercu.image', array());
					}
				}
				// Outils d'edition et suppression
				if (is_writable($show_path.$file)&& !in_array($file,$fichiers_interdits)){
					if ($activer_edition == true && in_array(extension($file),$ext_edition)) $tpl->assign_block_vars('arbo.liste_fichiers.editer', array());
					if ($activer_suppression == true) $tpl->assign_block_vars('arbo.liste_fichiers.supprimer', array());
				}
			}
		}
		@closedir($ch);
		if (is_writable($show_path) && $activer_upload == true){
			$tpl->assign_block_vars('upload', array());
		}
		break;
}


// Liens de retour
$dossiers = explode('/',ereg_replace('/$','',$show_path));
$liste_dossiers = '';
foreach($dossiers AS $dir){
	$liste_dossiers .= $dir.'/';
	$tpl->assign_block_vars('retour', array(
		'DOSSIER'	=> ($dir=='.')?$lang['L_RACINE_SITE']:$dir,
		'URL'		=> formate_url('show_path='.urlencode($liste_dossiers),true)
	));	
}



$tpl->assign_vars(array(
	'L_TAILLE_MAX'				=>	sprintf($lang['L_TAILLE_MAX'],get_cfg_var('post_max_size')),
	'TAILLE_MAX'				=>	intval(get_cfg_var('post_max_size'))*1024*1024,
	'SHOW_PATH'					=>	urlencode($show_path),	
	
	'I_EDITER'					=>	$img['editer'],
	'I_EFFACER'					=>	$img['effacer'],
	
	
));

?>