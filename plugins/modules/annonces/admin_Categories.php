<?php
/*
|------------------------------------------------------------------------------------------------------------
| Software: Malleo ( CMS )
| Contact:  alain91 - http://www.malleo-cms.com
| Support: http://www.malleo-cms.com?module=forum
| Documentation : Support : 
|------------------------------------------------------------------------------------------------------------
|  Author: Alain GANDON
|  Copyright (c) 2011, Alain GANDON All Rights Reserved
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

defined('PROTECT_ADMIN') OR die("Tentative de Hacking");

defined('ANNONCES_PATH') OR define('ANNONCES_PATH', dirname(__FILE__));

require_once(ANNONCES_PATH.'/class/class_core.php');
Core::setup();
//
// initialisation de certaines variables
$chemin_icones = 'data/icones_cat_annonces/';
$module_select = '';
$ext_ok = array('gif','png','jpg','jpeg');
$image = $liste_images = $image_par_defaut = '';
$tpl->assign_vars(array(
	'HIDDEN_ACTION'	=> 'ajouter'
));

require(ANNONCES_PATH.'/prerequis.php');
$tpl->set_filenames(array('body_admin' => ANNONCES_PATH.'/html/admin_categories.html'));

Helper::cleanGPC();

// TRAITEMENT
if (isset($_POST['action']) || isset($_GET['action']))
{
	$action = (isset($_POST['action']))? $_POST['action']:$_GET['action'];	
	// controles
	if (($action == 'ajouter' || $action == 'editer') && 
		(empty($_POST['titre']))) {
		erreur_saisie('erreur_saisie',$lang['L_TOUT_REMPLIR'],array(
				'TITRE'=>isset($_POST['titre'])?$_POST['titre']:''));
		if ($action == 'ajouter') $action = '';
		if ($action == 'editer') $action = 'edit';
		$_GET = $_POST;
	}
	
	switch ($action)
	{
		case 'move':
			$sens  = ($_GET['sens']=='up')? '+':'-';
			require_once($root.'fonctions/fct_formulaires.php');
			deplacer_id_tableau(TABLE_ANNONCES_CATS, 'id_cat', 'ordre', 'ASC', intval($_GET['id_cat']), $sens);
			$cache->appel_cache('listing_blog_cat',true);
			header('location: '.$base_formate_url);
			break;
		case 'ajouter':
			$titre = $_POST['titre'];
			$image = empty($_POST['image']) ? '' : $_POST['image'];
			$id_module = $_POST['id_module'];
			$sql = 'INSERT INTO '.TABLE_ANNONCES_CATS.' (titre_cat, image_cat) VALUES (
				\''.Helper::sql_escape($titre).'\',
				\''.Helper::sql_escape($image).'\')';
			$resultat = $c->sql_query($sql) OR message_die(E_ERROR,510,__FILE__,__LINE__,$sql); 
			$cache->appel_cache('listing_blog_cat',true);
			header('location: '.$base_formate_url);
			break;
		case 'editer':
			$titre = $_POST['titre'];
			$image = empty($_POST['image']) ? '' : $_POST['image'];
			$id_cat = intval($_POST['id_cat']);
			$sql = 'UPDATE '.TABLE_ANNONCES_CATS.' SET 
					titre_cat=\''.Helper::sql_escape($titre).'\',
					image_cat=\''.Helper::sql_escape($image).'\'
					WHERE id_cat='.$id_cat;
			$resultat = $c->sql_query($sql) OR message_die(E_ERROR,513,__FILE__,__LINE__,$sql); 
			$cache->appel_cache('listing_blog_cat',true);
			header('location: '.$base_formate_url);
			break;
		case 'supprimer':	
			$id_cat = intval($_GET['id_cat']);
			$sql = 'DELETE FROM '.TABLE_ANNONCES_CATS.' WHERE id_cat='.$id_cat;
			$resultat = $c->sql_query($sql) OR message_die(E_ERROR,511,__FILE__,__LINE__,$sql);
			$cache->appel_cache('listing_blog_cat',true);
			header('location: '.$base_formate_url);
			break;
		case 'edit':
			$id_cat = intval($_GET['id_cat']);
			$sql = 'SELECT titre_cat, image_cat FROM '.TABLE_ANNONCES_CATS.' WHERE id_cat = '.$id_cat;
			$resultat = $c->sql_query($sql) OR message_die(E_ERROR,512,__FILE__,__LINE__,$sql); 
			$row = $c->sql_fetchrow($resultat);
			$tpl->assign_vars(array(
				'HIDDEN_ACTION'	=> 'editer',
				'HIDDEN'		=> '<input type="hidden" name="id_cat" value="'.$id_cat.'" />',
				'TITRE'			=> $row['titre_cat']
			));
			$titre = $row['titre_cat'];
			$image = $row['image_cat'];
	}
}

$module_select = 'annonces';

//
// AFFICHAGE des Categories

$sql = 'SELECT id_cat, titre_cat, image_cat, ordre 
		FROM '.TABLE_ANNONCES_CATS.' 
		ORDER BY ordre ASC, titre_cat ASC';
$resultat = $c->sql_query($sql) OR message_die(E_ERROR,509,__FILE__,__LINE__,$sql);
$liste_cats = array();
while($row = $c->sql_fetchrow($resultat))
{
	$liste_cats[$module_select][] = $row;
}
$sql = 'SELECT module FROM '.TABLE_MODULES.'
		WHERE module="'.$module_select.'" OR virtuel="'.$module_select.'" 
		ORDER BY module ASC';
$resultat = $c->sql_query($sql) OR message_die(E_ERROR,509,__FILE__,__LINE__,$sql);
$select_list = '';
while($row = $c->sql_fetchrow($resultat))
{
	$tpl->assign_block_vars('liste_modules', array(
		'MODULE'	=> ucfirst($module_select)
	));
	if (array_key_exists($row['module'],$liste_cats))
	{
		$tpl->assign_block_vars('liste_modules.ok', array());
		$t=1;
		foreach ($liste_cats[$row['module']] as $k=>$v)
		{
			$tpl->assign_block_vars('liste_modules.ok.cat', array(
				'TITRE'		=> $v['titre_cat'],
				'IMAGE'		=> $chemin_icones.$v['image_cat'],
				'S_UP'		=> formate_url('action=move&sens=up&id_cat='.$v['id_cat'],true),
				'S_DOWN'	=> formate_url('action=move&sens=down&id_cat='.$v['id_cat'],true),
				'S_EDIT'	=> formate_url('action=edit&id_cat='.$v['id_cat'],true),
				'S_SUPP'	=> formate_url('action=supprimer&id_cat='.$v['id_cat'],true),
			));
			
			// Monter / descendre
			$nbre_cats = sizeof($liste_cats[$row['module']]);
			if ($nbre_cats>1 && $t>1) $tpl->assign_block_vars('liste_modules.ok.cat.monter',array());
			if ($nbre_cats>1 && $t<$nbre_cats) $tpl->assign_block_vars('liste_modules.ok.cat.descendre',array());
			$t++;
		}
	}else{
		$tpl->assign_block_vars('liste_modules.nok', array());	
	}
	$selected = ($module_select==$row['module'])?' selected="selected"':'';
	$select_list .= '<option'.$selected.'>'.$row['module'].'</option>';
}


//
// Listing des icones de catégories
if (file_exists($chemin_icones))
{
	$ch = @opendir($chemin_icones);
	while ($icone = @readdir($ch))
	{
		$ext = pathinfo($icone);
		if ($icone[0] != '.' && in_array(strtolower($ext['extension']),$ext_ok)) {
			if ($image_par_defaut == '') $image_par_defaut = $icone;
			$selected = ($image == $icone)?' selected="selected"':'';
			$liste_images .= "\n ".'<option value="'.$icone.'"'.$selected.'>'.basename($icone,'.'.$ext['extension']).'</option>';
		}
	}
	@closedir($ch);
}

$tpl->assign_vars(array(
	'IMAGE'					=> $liste_images,
	'MODULE'				=> $select_list,
	'ICONE_PAR_DEFAUT'		=> ($image!='')?$image:$image_par_defaut,
	
	'I_DOWN'				=> $img['down'],
	'I_UP'					=> $img['up'],
	'I_EDITER'				=> $img['editer'],
	'I_EFFACER'				=> $img['effacer'],
));

?>