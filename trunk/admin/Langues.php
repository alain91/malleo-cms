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
load_lang('update_langues');

if (isset($_POST['html'])){
	$sens = 'html';
	$code_langue = ereg_replace('[^a-z]{0,2}','',$_POST['lang']);
	if (!is_dir($root.'lang/'.$code_langue))$code_langue = 'fr';
	scanne_zones();
}elseif(isset($_POST['accents'])){
	$sens = 'accents';
	$code_langue = ereg_replace('[^a-z]{0,2}','',$_POST['lang']);
	if (!is_dir($root.'lang/'.$code_langue))$code_langue = 'fr';
	scanne_zones();
}

function update_fichier_langue($fichier)
{
	global $tpl,$root,$img,$sens;
	if(filesize($fichier) == 0){
		$tpl->assign_block_vars('vide', array(
			'FICHIER'	=> $fichier
		));
	}else{
		$file = fopen($fichier, 'r');
		//fseek($file,1);
		$content = fread($file, filesize($fichier));
		fclose($file);

		$depart = md5($content);
		switch($sens){
			case 'html': $content = str_to_html($content,true);break;
			case 'accents':$content =  html_to_str($content,false);break;
			default: return false;
		}
		$fin = md5($content);
		$class= 'standard';
		if ($depart != $fin)
		{
			if (is_writable($fichier))
			{
	 			$file = fopen($fichier, 'w+');
				fwrite($file,$content);
				fclose($file); 
				$etat = true;
				$class= 'Alerte';
			}else{
				$etat = false;
			}
		}else{
			$etat = true;
		}
		$tpl->assign_block_vars('liste_langues', array(
			'FICHIER'	=> $fichier,
			'DEPART'	=> $depart,
			'FIN'		=> $fin,
			'CLASS'		=> $class,
			'ETAT'		=> ($etat==true)? $root.$img['valide']: $root.$img['invalide']
		));
	}
}

function scanne_zones(){
	global $code_langue,$zones;
	//
	// On charge tous les fichiers de langue generique
	$chemin = './lang/'.$code_langue.'/';
	$ch = @opendir($chemin);
	while ($fichier = @readdir($ch))
	{
		if ($fichier != "." && $fichier != ".." && ereg('lang_',$fichier))
		{
			update_fichier_langue($chemin.$fichier);
		}
	}
	@closedir($ch);

	//
	// On charge tous les fichiers de langue des blocs
	$chemin = './plugins/blocs/';
	$ch = @opendir($chemin);
	while ($fichier = @readdir($ch))
	{
		if ($fichier != "." && $fichier != ".." && file_exists($chemin.$fichier.'/lang_'.$code_langue.'.php'))
		{
			update_fichier_langue($chemin.$fichier.'/lang_'.$code_langue.'.php');
		}
	}
	@closedir($ch);		

	//
	// On charge tous les fichiers de langue des modules
	$chemin = './plugins/modules/';
	$ch = @opendir($chemin);
	while ($fichier = @readdir($ch))
	{
		if ($fichier != "." && $fichier != ".." && file_exists($chemin.$fichier.'/lang_'.$code_langue.'.php'))
		{
			update_fichier_langue($chemin.$fichier.'/lang_'.$code_langue.'.php');
		}
	}
	@closedir($ch);	
}

// Liste des code langues
$chemin = $root.'lang/';
$options = '';
$ch = @opendir($chemin);
while ($fichier = @readdir($ch))
{
	if ($fichier != "." && $fichier != ".." && is_dir($chemin.$fichier))
	{
		$options .= "\n".'<option>'.$fichier.'</option>';
	}
}
@closedir($ch);


$tpl->set_filenames(array(
	'body_admin' => $root.'html/admin_update_langue.html'
));



$tpl->assign_vars(array(
	'LANGUE'				=> $options,
	'ZONES_SCANNEES'		=> './lang/XX/<br />./plugins/blocs/<br />./plugins/modules/',
));

?>