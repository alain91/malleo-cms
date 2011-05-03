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
require($root.'plugins/modules/forum/prerequis.php');
$select = $select_icone = $image_par_defaut = '';
$edit_id_forum = $edit_id_cat = 0;
$ext_ok = array('gif','GIF','png','PNG','jpg','JPG','jpeg','JPEG');
$chemin_icones = 'data/icones_forum/';
$hidden = '<input type="hidden" name="action" value="ajouter_cat" />';
$titre_cat = $lang['L_AJOUTER_CAT'];

$tpl->set_filenames(array('body_admin' => $root.'plugins/modules/forum/html/admin_categories.html'));

// TRAITEMENT
if (isset($_POST['action']) || isset($_GET['action']))
{
	$action = (isset($_POST['action']))? $_POST['action']:$_GET['action'] ;
	
	// Controles Categorie
	if (($action == 'ajouter_cat' || $action == 'editer_cat')
			&& (trim($_POST['titre'])=='' || trim($_POST['module'])=='')){
		erreur_saisie('erreur_saisie',$lang['L_REMPLIR_TITRE'],array(
					'TITRE'=>isset($_POST['titre'])?stripslashes($_POST['titre']):'',
					'DESCRIPTION'=>isset($_POST['post'])?stripslashes($_POST['description']):''));
		$edit_id_cat = stripslashes($_POST['module']);
		if ($action == 'ajouter_cat') $action = '';
		if ($action == 'editer_cat') $action = 'editcat';
		$_GET = $_POST;
	}
	// Controles Forum
	if (($action == 'ajouter_forum' || $action == 'editerforum')
			&& (trim($_POST['titre_forum'])=='' || trim($_POST['parent_forum'])=='')){
		erreur_saisie('erreur_saisie',$lang['L_TOUT_REMPLIR']);
		$edit_id_forum = stripslashes($_POST['parent_forum']);
		if ($action == 'ajouter_forum') $action = '';
		if ($action == 'editerforum') $action = 'editforum';
		$_GET = $_POST;
	}

	switch ($action)
	{
		case 'ajouter_cat':
				$sql = 'INSERT INTO '.TABLE_FORUM_CATS.' (titre_cat, desc_cat, module)
						VALUES (\''.str_replace("\'","''",protection_chaine($_POST['titre'])).'\',
								\''.str_replace("\'","''",protection_chaine($_POST['description'])).'\',
								\''.str_replace("\'","''",protection_chaine($_POST['module'])).'\')';	
				if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,707,__FILE__,__LINE__,$sql); 
				$f->maj_liste_forums();
				header('location: '.$base_formate_url);
				break;
		case 'movecat':
				$sens  = ($_GET['sens']=='up')? '+':'-';
				require_once($root.'fonctions/fct_formulaires.php');
				deplacer_id_tableau(TABLE_FORUM_CATS, 'id_cat', 'ordre', 'ASC', intval($_GET['id_cat']), $sens);
				$f->maj_liste_forums();
				header('location: '.$base_formate_url);
				break;
		case 'moveforum':
				$sens  = ($_GET['sens']=='up')? '+':'-';
				require_once($root.'fonctions/fct_formulaires.php');
				deplacer_id_tableau(TABLE_FORUM_FORUMS, 'id_forum', 'ordre', 'ASC', intval($_GET['id_forum']), $sens, ' WHERE id_cat='.intval($_GET['id_cat']));
				$f->maj_liste_forums();
				header('location: '.$base_formate_url);
				break;
		case 'ajouter_forum':
				$sql = 'INSERT INTO '.TABLE_FORUM_FORUMS.' (titre_forum, parent_forum, icone_forum, id_cat)
						VALUES (\''.str_replace("\'","''",protection_chaine($_POST['titre_forum'])).'\',
								\''.intval($_POST['parent_forum']).'\',
								\''.str_replace("\'","''",protection_chaine($_POST['icone_forum'])).'\',
								\''.intval($_POST['id_cat']).'\')';	
				if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,708,__FILE__,__LINE__,$sql);
				// Ajout de regles par defaut pour ce forum
				$id_forum = $c->sql_nextid();
				$infos = $f->Get_module_titre_forum($id_forum);
				$f->Droits_Ajoute_noeud($id_forum,$infos['module'],$infos['titre_forum']);
				// MAJ du cache
				$f->maj_liste_forums();
				header('location: '.$base_formate_url);
				break;
		case 'supforum':
				$id_forum=intval($_GET['id_forum']);
				// Suppression des regles
				$infos = $f->Get_module_titre_forum($id_forum);
				$droits->delete_regle('noeud',$id_forum,$infos['module']);
				
				// suppression des posts
				$sql = 'DELETE FROM '.TABLE_FORUM_POSTS.' WHERE id_topic IN ( SELECT id_topic FROM '.TABLE_FORUM_TOPICS.' WHERE id_forum='.$id_forum.')';	
				if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,709,__FILE__,__LINE__,$sql);
				// suppression des topics
				$sql = 'DELETE t,tnl,ts,tf FROM '.TABLE_FORUM_TOPICS.' AS t
								LEFT JOIN '.TABLE_FORUM_TOPICS_NONLUS.' AS tnl
									ON (t.id_topic=tnl.id_topic)
								LEFT JOIN '.TABLE_FORUM_TOPICS_SUIVIS.' AS ts
									ON (t.id_topic=ts.id_topic)
								LEFT JOIN '.TABLE_FORUM_TOPICS_FAVORIS.' AS tf
									ON (t.id_topic=tf.id_topic)
						WHERE t.id_forum='.$id_forum;	
				if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,709,__FILE__,__LINE__,$sql);
				// suppression du forum
				$sql = 'DELETE FROM '.TABLE_FORUM_FORUMS.' WHERE id_forum='.$id_forum;	
				if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,709,__FILE__,__LINE__,$sql);
				$f->maj_liste_forums();
				header('location: '.$base_formate_url);
				break;
		case 'supcat':
				$id_cat=intval($_GET['id_cat']);
				// suppression des posts
				$sql = 'DELETE FROM '.TABLE_FORUM_POSTS.' WHERE id_topic IN ( SELECT id_topic FROM '.TABLE_FORUM_TOPICS.' as t LEFT JOIN '.TABLE_FORUM_FORUMS.' as f ON (t.id_forum=f.id_forum) WHERE f.id_cat='.$id_cat.')';	
				if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,710,__FILE__,__LINE__,$sql);
				// suppression des topics
				$sql = 'DELETE t,tnl,ts,tf FROM '.TABLE_FORUM_TOPICS.' AS t
								LEFT JOIN '.TABLE_FORUM_TOPICS_NONLUS.' AS tnl
									ON (t.id_topic=tnl.id_topic)
								LEFT JOIN '.TABLE_FORUM_TOPICS_SUIVIS.' AS ts
									ON (t.id_topic=ts.id_topic)
								LEFT JOIN '.TABLE_FORUM_TOPICS_FAVORIS.' AS tf
									ON (t.id_topic=tf.id_topic)
								WHERE t.id_forum IN ( 
										SELECT id_forum FROM '.TABLE_FORUM_FORUMS.' WHERE id_cat='.$id_cat.')';
				if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,710,__FILE__,__LINE__,$sql);
				// suppression du forum
				$sql = 'DELETE FROM '.TABLE_FORUM_FORUMS.' WHERE id_cat='.$id_cat;	
				if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,710,__FILE__,__LINE__,$sql);
				// suppression de la catégorie
				$sql = 'DELETE FROM '.TABLE_FORUM_CATS.' WHERE id_cat='.$id_cat;	
				if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,710,__FILE__,__LINE__,$sql);
				$f->maj_liste_forums();
				header('location: '.$base_formate_url);
				break;
		case 'editforum':
				$edit_id_forum=intval($_GET['id_forum']);
				break;
		case 'editer_cat':
				$sql = 'UPDATE '.TABLE_FORUM_CATS.' SET 
							titre_cat=\''.str_replace("\'","''",protection_chaine($_POST['titre'])).'\',
							desc_cat=\''.str_replace("\'","''",protection_chaine($_POST['description'])).'\'
						WHERE id_cat='.intval($_POST['id_cat']);	
				if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,707,__FILE__,__LINE__,$sql); 
				$f->maj_liste_forums();
				header('location: '.$base_formate_url);
				break;
		case 'editcat':
				$edit_id_cat=intval($_GET['id_cat']);
				$titre_cat = $lang['L_EDITER_CAT'];
				break;
		case 'editerforum':
				$sql = 'UPDATE '.TABLE_FORUM_FORUMS.' SET 
							titre_forum=\''.str_replace("\'","''",protection_chaine($_POST['titre_forum'])).'\',
							parent_forum='.intval($_POST['parent_forum']).',
							icone_forum=\''.str_replace("\'","''",protection_chaine($_POST['icone_forum'])).'\' 
						WHERE id_forum='.intval($_POST['id_forum']);	
				if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,708,__FILE__,__LINE__,$sql); 
				// Ajout de regles par defaut pour ce forum
				$id_forum = intval($_POST['id_forum']);
				$infos = $f->Get_module_titre_forum($id_forum);
				$droits->regles = $cache->appel_cache('listing_regles');
				if (!array_key_exists($id_forum,$droits->regles[$infos['module']])){
					// Si le noeud de ce module n'existe pas on l'initialise
					$f->Droits_Ajoute_noeud($id_forum,$infos['module'],$infos['titre_forum']);
				}elseif($droits->regles[$infos['module']][$id_forum]['voir'][1]['alias']!=$infos['titre_forum']){
					// Si le titre a été modifié on le met à jour 
					$f->Droits_Edite_Alias($id_forum,$infos['module'],$infos['titre_forum']);
				}
				// MAJ du cache
				$f->maj_liste_forums();
				header('location: '.$base_formate_url);
				break;
	}
}
//
// Listing des icones de catégories
$ch = @opendir($chemin_icones);
while ($icone = @readdir($ch))
{
	$ext = pathinfo($icone);
	if ($icone != "." && $icone != ".." 
		&& array_key_exists('extension',$ext) && in_array($ext['extension'],$ext_ok)) {
		if ($image_par_defaut == '') $image_par_defaut = $icone;
		$select_icone[] = array($icone,ereg_replace('.'.$ext['extension'],'',$icone));
	}
}
@closedir($ch);

$liste_forums = $liste_cats = array();
//
// Listing des catégories
$sql = 'SELECT id_cat, titre_cat, desc_cat, module FROM '.TABLE_FORUM_CATS.' 
		ORDER BY module ASC, ordre ASC';
if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,706,__FILE__,__LINE__,$sql); 
while($row = $c->sql_fetchrow($resultat))
{
	$liste_cats[$row['module']][] = $row;
}

//
// Listing des forums
$sql = 'SELECT id_forum, titre_forum, status_forum, parent_forum, icone_forum, id_cat, ordre 
		FROM '.TABLE_FORUM_FORUMS.' 
		ORDER BY id_cat ASC, ordre ASC';
if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,706,__FILE__,__LINE__,$sql); 
while($row = $c->sql_fetchrow($resultat))
{
	$liste_forums[$row['parent_forum']][] = $row;
}

//
// Affiche une select liste des forums specifies en parametre

function admin_select_liste_forums($array_liste,$id_cat,$id_parent,$id_select=0,$pre='',$select_liste='')
{
	global $lang;
	if ($select_liste=='') $select_liste = '<option value="0">'.$lang['L_RACINE'].'</option>';
	if (array_key_exists($id_parent,$array_liste))
	{
		foreach($array_liste[$id_parent] as $k=>$v)
		{
			if ($v['id_cat']==$id_cat)
			{
				$selected = ($id_select==$v['id_forum'])?' selected="selected"':'';
				$select_liste .= "\n".'<option value="'.$v['id_forum'].'"'.$selected.'>'.$pre.'_'.$v['titre_forum'].'</option>';		
				$select_liste = admin_select_liste_forums($array_liste,$id_cat,$v['id_forum'],$id_select,$pre.'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;|',$select_liste);
			}
		}
	}
	return $select_liste;
}

//
// Affiche les forums choisis

function admin_affiche_forums($liste_forums,$id_cat,$id_forum, $parent=0,$pre='')
{
	global $tpl;
	// securite
	if (!is_array($liste_forums) || sizeof($liste_forums)==0 || !array_key_exists($parent,$liste_forums)){
		//  Aucun forum dans la categorie
		$tpl->assign_block_vars('liste_modules.cats.no_forums',array());
		return ;
	}
		
	$aucun_forum = true;
	
	// Listing des forums de cette categorie
	foreach ($liste_forums[$parent] as $key=>$val)
	{
		if ($val['id_cat']==$id_cat)
		{
			// Affichage
			$tpl->assign_block_vars('liste_modules.cats.forums',array(
				'TITRE'		=>	$val['titre_forum'],
				'ICONE'		=>	'data/icones_forum/'.$val['icone_forum'],
				'PRE'		=>	($pre!='')?$pre.'|_':$pre,
				'S_UP'		=>	formate_url('action=moveforum&sens=up&id_forum='.$val['id_forum'].'&id_cat='.$val['id_cat'],true),
				'S_DOWN'	=>	formate_url('action=moveforum&sens=down&id_forum='.$val['id_forum'].'&id_cat='.$val['id_cat'],true),
				'S_EDIT'	=>	formate_url('action=editforum&id_forum='.$val['id_forum'],true),
				'S_SUPP'	=>	formate_url('action=supforum&id_forum='.$val['id_forum'],true)
			));
			
			// Edition
			if($id_forum==$val['id_forum'])
			{
				$tpl->assign_block_vars('editforum',array(
					'TITRE'			=>	$val['titre_forum'],
					'ID_FORUM'		=>	$val['id_forum'],
					'SELECT_FORUM'	=>	admin_select_liste_forums($liste_forums,$id_cat,0,$val['parent_forum']),
					'SELECT_ICONE'	=>	admin_select_liste_icones($val['icone_forum']),
					'ICONE'			=>	$val['icone_forum']
				
				));
			}
			// des sous forums ?
			if (array_key_exists($val['id_forum'],$liste_forums))
			{
				admin_affiche_forums($liste_forums,$id_cat,$id_forum,$val['id_forum'],$pre.'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
			}
			
			// Desactivation du message 0 forums
			$aucun_forum = false;
		}
	}
	
	if ($aucun_forum == true){
		//  Aucun forum dans la categorie
		$tpl->assign_block_vars('liste_modules.cats.no_forums',array());
	}
	return;
}

// 
// Liste les icones

function admin_select_liste_icones($select='')
{
	global $select_icone;
	$liste_icones = '';
	foreach ($select_icone as $k=>$v)
	{
		$selected=($select==$v[0])?' selected="selected"':'';
		$liste_icones .= "\n ".'<option value="'.$v[0].'"'.$selected.'>'.$v[1].'</option>';
	}
	return $liste_icones;
}

//
// AFFICHAGE des modules
$sql = 'SELECT id_module, module FROM '.TABLE_MODULES.' 
		WHERE module="forum" OR virtuel="forum" ORDER BY module ASC';
if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,705,__FILE__,__LINE__,$sql); 
$select_module=$selected='';
while($row = $c->sql_fetchrow($resultat))
{
	// Affichages des modules
	$tpl->assign_block_vars('liste_modules',array(
		'MODULE'	=> $row['module']
	));
	
	// Affichages des categories des modules
	if (array_key_exists($row['module'],$liste_cats))
	{
		$t = 1;
		foreach ($liste_cats[$row['module']] as $k=>$v){
		
			// Affichage CAT
			$tpl->assign_block_vars('liste_modules.cats',array(
				'TITRE_CAT'	=>	$v['titre_cat'],
				'DESC_CAT'	=>	$v['desc_cat'],
				'ID_CAT'	=>	$v['id_cat'],
				'S_UP'		=>	formate_url('action=movecat&sens=up&id_cat='.$v['id_cat'],true),
				'S_DOWN'	=>	formate_url('action=movecat&sens=down&id_cat='.$v['id_cat'],true),
				'S_EDIT'	=>	formate_url('action=editcat&id_cat='.$v['id_cat'],true),
				'S_SUPP'	=>	formate_url('action=supcat&id_cat='.$v['id_cat'],true),
				'SELECT_FORUM'=> admin_select_liste_forums($liste_forums,$v['id_cat'],0,0)
			));
			
			// Edition Cat
			if ($edit_id_cat==$v['id_cat'])
			{
				$tpl->assign_vars(array(
					'TITRE'			=> $v['titre_cat'],
					'DESCRIPTION'	=> $v['desc_cat'],					
					'HIDDEN_IDCAT'	=> '<input type="hidden" name="id_cat" value="'.$v['id_cat'].'" />'
				));
				$selected = ' selected="selected"';
				$hidden = '<input type="hidden" name="action" value="editer_cat" />';
			}
			
			// Listing des forums de cette categorie
			admin_affiche_forums($liste_forums,$v['id_cat'],$edit_id_forum, 0);
			
			// Monter / descendre
			$nbre_cats = sizeof($liste_cats[$row['module']]);
			if ($nbre_cats>1 && $t>1) $tpl->assign_block_vars('liste_modules.cats.monter',array());
			if ($nbre_cats>1 && $t<$nbre_cats) $tpl->assign_block_vars('liste_modules.cats.descendre',array());
			$t++;
		}
	}else{
		// Aucune categorie dans ce forum
		$tpl->assign_block_vars('liste_modules.nocats',array());
	}
	$select_module .='<option'.$selected.'>'.$row['module'].'</option>';
}


$tpl->assign_vars(array(
	'ICONE_PAR_DEFAUT'				=> $image_par_defaut,
	'L_AJOUTER_CAT'					=> $titre_cat,
	'SELECT_MODULE'					=> $select_module,
	'HIDDEN'						=> $hidden,
	'SELECT_ICONE'					=> admin_select_liste_icones(),
	'I_DOWN'						=> $img['down'],
	'I_UP'							=> $img['up'],
	'I_NOUVEAU'						=> $img['nouveau'],
	'I_EDITER'						=> $img['editer'],
	'I_EFFACER'						=> $img['effacer']
));

?>