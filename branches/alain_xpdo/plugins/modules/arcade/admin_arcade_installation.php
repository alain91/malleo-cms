<?php
if ( !defined('PROTECT_ADMIN') )
{
	die("Tentative de Hacking");
}
// Modele de fichier info.xml d'un jeu
/*
<?xml version="1.0" encoding="ISO-8859-1"?>
<!-- JEU ADAPTE POUR L'ARCADE MALLEO -->
<game> 
  <titre>Abba the Fox</titre> 
  <adapteur>SP</adapteur> 
  <url_adapteur>http://www.malleo-cms.com</url_adapteur> 
  <variable>Abbathefox</variable> 
  <largeur>600</largeur> 
  <hauteur>450</hauteur> 
  <image_petite>Abbathefox_petite.gif</image_petite> 
  <image_grande>Abbathefox_grande.gif</image_grande> 
  <swf>Abbathefox.swf</swf> 
  <description>Tu es un renard à bord d'un ULM, tu dois attraper les enveloppes sans te faire toucher par les insectes et oiseaux. Rapportes tes enveloppes le plus vite possible au bureau de poste</description>
  <fps>20</fps>
  <score_sens>1</score_sens>
  <controles>2</controles>
</game> 
*/

// Init
require($root.'plugins/modules/arcade/prerequis.php');
$arcade = new arcade_admin();

// Verification de l'arbo
$arcade->check_data_dirs();

$tpl->set_filenames(array('body_admin' => $root.'plugins/modules/arcade/html/admin_arcade_installation.html'));

// TRAITEMENT
$arcade->clean($_GET);
$arcade->clean($_POST);
$action = null;
if (isset($_POST['action']) || isset($_GET['action']))
{
	$action = (isset($_POST['action']))? $_POST['action']:$_GET['action'];
	
	switch ($action)
	{
		case 'ajouter' : 
			// Ajout des jeux selectionnes
			if (isset($_POST['dossiers']) && is_array($_POST['dossiers']) && sizeof($_POST['dossiers'])>0
				&& isset($_POST['id_cat']) && is_array($_POST['id_cat']) && sizeof($_POST['id_cat'])>0){
				$erreurs = array();
				foreach ($_POST['dossiers'] as $v){
					// installation du jeu
					$msg = $arcade->ajouter_jeu($v);
					if ($msg !== true){
						$erreurs[$v] = $msg;
					}else{
						foreach ($_POST['id_cat'] as $cat){
							// Affectation du jeu dans la categorie selectionnee
							$arcade->affecter_jeu_dans_categorie($arcade->id_jeu,intval($cat));
						}
					}
				}
				// On met a jour les compteurs
				$arcade->update_stats_cats();
				
				if (sizeof($erreurs)==0){
					// On affiche une fenetre de confirmation
					affiche_message('body_admin','L_JEUX_INSTALLES',formate_url('',true));
				}else{
					// Affichage des erreurs si il y en a
					erreur_saisie('erreur_saisie',sprintf($lang['L_BUG_INSTALL_JEUX'],implode('<li>',$erreurs)));
				}
			}else{
				// Merci de selectionner au moins 1 jeu et 1 categorie
				erreur_saisie('erreur_saisie',$lang['L_BUG_SELECT_JEUX_ET_CATS']);
			}
			break;
	}
}

//
// Liste des dossiers en attente
$cpt=0;
$ch = @opendir($arcade->path_games.'_installer/');
while ($jeu = @readdir($ch))
{
	$path_jeu = $arcade->path_games.'_installer/'.$jeu.'/';
	$xml_file = $path_jeu . $arcade->xml_file;

	if (is_file($path_jeu) && $jeu != '.htaccess' ){
		$ext = pathinfo($path_jeu);
		switch($ext['extension']){
		
			// implémenté avec PHP 5 mais plante avec la version 5.2.6
			case 'rar':
				// Si la librairie RAR n'est pas installée
				if (!function_exists('rar_open')) break;
				if (($rar_file = rar_open($root.$arcade->path_games.'_installer/'.$jeu)) === TRUE){
					$list = rar_list($rar_file);
					foreach($list as $file){
					    $entry = rar_entry_get($rar_file, $file);
					    $entry->extract($arcade->path_games.'_installer/'); // extraction dans le dossier courant
					}
					rar_close($rar_file);
					if (file_exists($arcade->path_games.'_installer/'.$ext['filename'])){
						chmod($arcade->path_games.'_installer/'.$ext['filename'],$arcade->umask);
					}
					@unlink($arcade->path_games.'_installer/'.$jeu);
					header('location: '.formate_url('',true));
				}
				break;
				
			case 'zip':
				// Si la librairie ZIP n'est pas installée
				if (!class_exists('ZipArchive')) break;
				
				$zip = new ZipArchive;
				if ($zip->open($arcade->path_games.'_installer/'.$jeu) === TRUE) {
				    $zip->extractTo($arcade->path_games.'_installer/');
				    $zip->close();
					if (file_exists($arcade->path_games.'_installer/'.$ext['filename'])){
						chmod($arcade->path_games.'_installer/'.$ext['filename'],$arcade->umask);
					}
					@unlink($arcade->path_games.'_installer/'.$jeu);
					header('location: '.formate_url('',true));
				}
				break;
		}
	}elseif ($jeu != '.' && $jeu != '..' && is_dir($path_jeu)){
		if (file_exists($xml_file)){
			$etat = $arcade->check_xml_file($path_jeu);
			
			// Detail des jeux
			$tpl->assign_block_vars('liste_jeux', array(
				'IMAGE_PETITE'		=>	($arcade->xml->image_petite!='')?'<img src="'.$path_jeu.$arcade->xml->image_petite.'" alt="'.$arcade->xml->titre.'" />':'',
				'DOSSIER'			=>	$jeu,
				'TITRE'				=>	$arcade->xml->titre,
				'DESCRIPTION'		=>	$arcade->xml->description,
				'ADAPTEUR'			=>	$arcade->xml->adapteur,
				'LARGEUR'			=>	$arcade->xml->largeur,
				'HAUTEUR'			=>	$arcade->xml->hauteur,
				'SWF'				=>	$path_jeu . $arcade->xml->swf,
				'URL_ADAPTEUR'		=>	formate_url($arcade->xml->url_adapteur),
			));
		}else{
			// Le XML n\'existe pas
			$etat = sprintf($lang['L_ERREUR_PATH_XML'], $xml_file);			
			$tpl->assign_block_vars('liste_jeux', array(
				'TITRE'		=>	$jeu,
			));
		}
		
		// Archive complete ?
		if ($etat !== true){
			$tpl->assign_block_vars('liste_jeux.etat', array(
				'ETAT'				=>	$etat
			));
		}
		$cpt++;
	}
}
@closedir($ch);

// Aucun jeu en attente
if ($cpt==0){
	$tpl->assign_block_vars('aucun_resultat', array());
}

// Liste des categories
$arcade->affiche_menu_deroulant_categories();


// Clefs de langue
$arcade->declarer_clefs_lang();


?>