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

$tpl->set_filenames(array(
	  'body_admin' => $root.'html/admin_modele_wizard.html'
));

$tpl->assign_vars(array(

	'STYLE_PATH'				=> $root.$style_path.$style_name,
));

$etape = (isset($_POST['etape']))? intval($_POST['etape']):1;

switch ($etape)
{
	case '3': 
		// On charge la partie 3 du template
		$tpl->assign_block_vars('etape3', array());
		// On spécifie dans quel modèle on travaille
		$map->id_modele = intval($_POST['id_modele']);
		// On charge les infos du modèle en question
		$map->lecture_modele();
		// map non enregistrée?
		if ($map->data['map']==''){
			message_die(E_ERROR,50,'','');
		}
		// On dépile la conf de cette map
		$map->data['map'] = unserialize($map->data['map']);	
		// On prépare l'apercu
		$map->prepare_map_apercu();
		
		// On remplis le gabaris avec les blocs
		$tpl->assign_vars(array(
			'APERCU_MAP'		=> $map->map			
		));
		break;
		
	case '2':
		// Enregistrement en base des données saisies :
		$map->data['gabaris'] = $_POST['gabaris'];
		$map->data['titre_modele'] = str_replace("\'","''",protection_chaine($_POST['titre_modele']));
		$map->insert_modele();

		// Affichage des champs de l'étape 2
		$tpl->assign_block_vars('etape2', array());
		
		// On prépare la MAP dynamique
		$map->prepare_map_dynamique();
		
		// On liste les blocs dispos
		$map->lister_blocs_dispo();
		
		$tpl->assign_vars(array(
			'MAP'				=> $map->map,
			'NBRE_ZONES'		=> $map->nbre_zones,
			'LISTE_BLOCS'		=> $map->lister_blocs_dispo(),
			'LISTE_BLOCS_HTML'	=> $map->lister_blocs_html_dispo(),
			'ID_MODELE'			=> $map->id_modele,
			'TITRE'				=> $_POST['titre_modele'],
			'GABARIS'			=> $_POST['gabaris']
		));
		$cache->purger_cache();
		break;
	case '1':
		$tpl->assign_block_vars('etape1', array());
		$nbre_cols = 3;
		$chemin = 'data/modeles/';
		$liste_modeles = array();
		$ch = @opendir($chemin);
		$i = 0;
		while ($modele = @readdir($ch))
		{
			if ($modele[0] != '.' && is_dir($chemin.$modele))
			{
				if ($i%$nbre_cols == 0) $tpl->assign_block_vars('etape1.ligne', array());
				$liste_modeles[] = $modele;
				$tpl->assign_block_vars('etape1.ligne.liste_modeles', array(
					'INFOS'	=> @file_get_contents($chemin.$modele.'/infos.txt'),
					'IMAGE'	=> $chemin.$modele.'/apercu.png',
					'TITRE'	=> $modele			
				));
				$i++;
			}
		}
		@closedir($ch);
		$tpl->assign_vars(array(
			'TITRE'				=> $_POST['titre_modele'],
			'PCT_COL'			=> round(100/$nbre_cols).'%'
		));
		break;
}


?>