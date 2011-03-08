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
load_lang('smileys');
$tpl->set_filenames(array('body_admin' => $root.'html/admin_gestion_smileys.html'));
$hidden_action = 'ajouter';

// Chargement du fichier
include_once($root.'fonctions/fct_smileys.php');

// TRAITEMENT
if (isset($_POST['action']) || isset($_GET['action']))
{
	$action = (isset($_POST['action']))? $_POST['action']:$_GET['action'] ;
	switch ($action)
	{
		case 'ajouter':
			if (!isset($_POST['image']) || !isset($_POST['titre']) || empty($_POST['titre'])){
				$tpl->assign_block_vars('alerte_saisie', array());
			}else{
				$image = mysql_escape_string($_POST['image']);
				$titre = protection_chaine($_POST['titre']);
				$tag = protection_chaine($_POST['tag']);
				$sql = 'INSERT INTO '.TABLE_SMILEYS.' (titre_smiley, url_smiley,tag_smiley) 
						VALUES (\''.str_replace("\'","''",$titre).'\',
								\''.str_replace("\'","''",$image).'\',
								\''.str_replace("\'","''",$tag).'\')';
				if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,58,__FILE__,__LINE__,$sql);
				$cache->cache_tpl($root.'cache/smileys/emotions.html', 'return creer_cache_emotions();', 0);
				header('location: '.$base_formate_url);				
			}
			break;
		case 'supprimer':
				if (intval($_GET['id_smiley'])>0){
					$sql = 'DELETE FROM '.TABLE_SMILEYS.' WHERE id_smiley='.intval($_GET['id_smiley']);
					if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,59,__FILE__,__LINE__,$sql); 
					$cache->cache_tpl($root.'cache/smileys/emotions.html', 'return creer_cache_emotions();', 0);
					header('location: '.$base_formate_url);
				}
				break;
		case 'move':
				$sens  = ($_GET['sens']=='up')? '+':'-';
				require_once($root.'fonctions/fct_formulaires.php');
				deplacer_id_tableau(TABLE_SMILEYS, 'id_smiley', 'ordre', 'ASC', intval($_GET['id_smiley']), $sens);
				$cache->cache_tpl($root.'cache/smileys/emotions.html', 'return creer_cache_emotions();', 0);
				header('location: '.$base_formate_url);
				break;
	}
}


//
// AFFICHAGE
$sm_installes = array();
$sql = 'SELECT id_smiley, titre_smiley, tag_smiley, url_smiley  
		FROM '.TABLE_SMILEYS.' 
		ORDER BY ordre ASC';
if (!$resultat = $c->sql_query($sql)) message_die(E_ERROR,1101,__FILE__,__LINE__,$sql); 
if (($nbre_pages = $c->sql_numrows($resultat)) == 0){
	$tpl->assign_block_vars('aucun_smiley', array());
}else{
	$t=1;
	while($row = $c->sql_fetchrow($resultat))
	{
		$tpl->assign_block_vars('liste_smileys', array(
			'TITRE'		=> $row['titre_smiley'],
			'TAG'		=> $row['tag_smiley'],
			'SUPPRIMER'	=> formate_url('action=supprimer&id_smiley='.$row['id_smiley'],true),
			'MONTER'	=> formate_url('action=move&sens=up&id_smiley='.$row['id_smiley'],true),
			'DESCENDRE'	=> formate_url('action=move&sens=down&id_smiley='.$row['id_smiley'],true),
			'IMAGE'		=> (file_exists(PATH_SMILEYS.$row['url_smiley']))?'<img src="'.PATH_SMILEYS.$row['url_smiley'].'" alt="'.$row['titre_smiley'].'" />':$lang['L_SMILEY_SUPPRIME_SUR_LE_DISQUE'],
		));
		$sm_installes[] = $row['url_smiley'];
		
		// Monter / descendre
		if ($nbre_pages>1 && $t>1) $tpl->assign_block_vars('liste_smileys.monter',array());
		if ($nbre_pages>1 && $t<$nbre_pages) $tpl->assign_block_vars('liste_smileys.descendre',array());
		$t++;
	}
}

// Menu deroulant
$select_image = '';
$image_defaut = '';
$format_autorises = array('gif','png','jpg','jpeg','ico');
$ch = @opendir(PATH_SMILEYS);
while ($sm = @readdir($ch))
{
	$extension = pathinfo(strtolower(PATH_SMILEYS.$sm));
	if ($sm[0] != '.' && in_array($extension['extension'],$format_autorises) 
			&& !in_array($sm,$sm_installes)){
		if ($image_defaut=='') $image_defaut = PATH_SMILEYS.$sm;
		$select_image .= "\n".'<option value="'.$sm.'">'.$sm.'</option>';
	}
}
@closedir($ch);

// Smileys dispos ou non ?
if ($select_image != ''){
	$tpl->assign_block_vars('smileys_presents', array());
}else{
	$tpl->assign_block_vars('smileys_absents', array());
}

$tpl->assign_vars(array(
	'I_SUPPR'				=> $img['effacer'],
	'I_UP'					=> $img['up'],
	'I_DOWN'				=> $img['down'],
	'PATH_SMILEYS'			=> PATH_SMILEYS,
	'IMAGE'					=> $select_image,
	'SMILEY_PAR_DEFAUT'		=> $image_defaut
));


?>