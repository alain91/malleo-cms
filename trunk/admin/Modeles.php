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
global $lang;
load_lang('modeles');

include_once($root.'class/class_assemblage.php');
$map = new Assemblage;
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

$action = '';
if (isset($_POST['action']) || isset($_GET['action']))
{
	$action = (isset($_POST['action']))?$_POST['action']:$_GET['action'];
	// La saisie du titre est obligatoire
	if ($action == 'upload' && (!isset($_POST['titre_modele']) || empty($_POST['titre_modele']))){
		erreur_saisie('erreur_saisie',$lang['ALERTE_VEUILLEZ_METTRE_TITRE_FICHIER']);
		$action = 'accueil';
	}
}
switch ($action){
	case 'upload':
		$destination = 'data/modeles/';
		// Telechargement
		if (isset($_FILES) && is_array($_FILES)){
			if ($_FILES['fichier']['error'] == UPLOAD_ERR_OK){
				$nom_fichier = nom_unique($_FILES['fichier']['name'],$destination);
				if (is_uploaded_file($_FILES['fichier']['tmp_name']))
				{
					@move_uploaded_file($_FILES['fichier']['tmp_name'], $destination.$nom_fichier);
					@chmod($destination.$nom_fichier, 0777);
					$fichier = $destination.$nom_fichier;
				}
			}
		}
		if (isset($fichier)){
			$map->insert_modele_fichier(str_replace("\'","''",protection_chaine($_POST['titre_modele'])),$fichier);
		}
		header('location: '.$base_formate_url);
		break;
	case 'supprimer':
		$map->id_modele = intval($_GET['id_modele']);
		$map->supprimer_modele();
		header('location: '.$base_formate_url);
		exit;
	case 'editer':
		//
		// EDITION d'une MAP
		$map->id_modele = intval($_GET['id_modele']);
		$map->lecture_modele();
		$map->data['map'] = (!is_string($map->data['map'])) ? (string) '':unserialize($map->data['map']);

		$map->prepare_map_dynamique();
		$tpl->set_filenames(array(
		  'body_admin' => $root.'html/admin_modeles_edition.html'
		));
		$Load_Ajax_List = array();

		if (is_array($map->data['map'])){
			foreach($map->data['map'] as $k=>$v)
			{
				foreach($v as $key=>$value)
				{
					if ($value != 'module') $Load_Ajax_List[] = $value;
					$tpl->assign_block_vars('liste_blocs', array(
						'TITRE'		=> '',
						'ID'		=> $value,
						'CONTENU'	=> ($value=='module')?$lang['L_MODULE_PRINCIPAL']:'<div id="bloc_'.$value.'"></div>',
						'POSITION'	=> $k				
					));
				}
			}
		}else{
			$tpl->assign_block_vars('liste_blocs', array(
				'TITRE'		=> '',
				'ID'		=> 'module',
				'CONTENU'	=> $lang['L_MODULE_PRINCIPAL'],
				'POSITION'	=> 1				
			));
		}
		
		$tpl->assign_vars(array(
			'STYLE_PATH'		=> $root.$style_path.$style_name,
			'LOAD_AJAX_LIST'	=> implode("','",$Load_Ajax_List),
			'L_MODULE'			=> $lang['L_MODULE_PRINCIPAL'],
			'URL_SUBMIT'		=> formate_url('',true),
			'MAP'				=> $map->map,
			'NBRE_ZONES'		=> $map->nbre_zones,
			'LISTE_BLOCS'		=> $map->lister_blocs_dispo(),
			'LISTE_BLOCS_HTML'	=> $map->lister_blocs_html_dispo(),
			'ID_MODELE'			=> $map->id_modele,
			'TITRE_MODELE'		=> $map->data['titre_modele']			
		));
		$cache->purger_cache();
		break;
	case 'accueil':
	default:
		//
		// LISTE DES MODELES
		
		$tpl->set_filenames(array(
		  'body_admin' => $root.'html/admin_modeles.html'
		));

		$sql = 'SELECT id_modele, titre_modele , gabaris, map, fichier 
				FROM '.TABLE_MODELES.' 			
				ORDER BY titre_modele ASC';
		if (!$resultat=$c->sql_query($sql)) message_die(E_ERROR,16,__FILE__,__LINE__,$sql);
		$class = 'row1';
		while($row = $c->sql_fetchrow($resultat))
		{
			$etat = tester_modele($row['gabaris'],$row['map'],$row['fichier']);
			$class = ($etat != $lang['L_ETAT_OK'])?'rowAlerte':'row1';
			$tpl->assign_block_vars('liste_modeles', array(
				'TITRE'		=> $row['titre_modele'],
				'GABARIS'	=> 'data/modeles/'.$row['gabaris'].'/apercu.png',
				'ETAT'		=> $etat,
				'CLASS'		=> $class,
				'EDITER'	=> formate_url('action=editer&id_modele='.$row['id_modele'],true),
				'SUPP'		=> formate_url('action=supprimer&id_modele='.$row['id_modele'],true)
			));
			
			// on affiche l'edition et le gabaris uniquement pour les modèle créés par l'assistant
			if ($row['fichier'] == null){
				$tpl->assign_block_vars('liste_modeles.assistant', array());
			}
		}

		// Upload
		$swtich_upload = (is_writable('data/modeles/'))? 'upload':'no_upload';
		$tpl->assign_block_vars($swtich_upload, array());
		
		$tpl->assign_vars(array(
			'L_TAILLE_MAX'		=> sprintf($lang['L_TAILLE_MAX'],get_cfg_var('post_max_size')),
			'TAILLE_MAX'		=> intval(get_cfg_var('post_max_size'))*1024*1024,
			'I_EFFACER'			=> $img['effacer'],
			'I_EDITER'			=> $img['editer'],
		));
		break;
}

?>