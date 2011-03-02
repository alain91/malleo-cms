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

$tpl->set_filenames(array('forum'=>$root.'plugins/modules/forum/html/categories.html'));

// Par defaut on affiche toutes les categories, sauf si une en particulier est spécifiée
$cat_specifique = (isset($_GET['id_cat']))? ' AND c.id_cat='.intval($_GET['id_cat']):'';

// Forums autorises pour l'utilisateur
$forums_autorises = array();
foreach ($user['rules'][$module] AS $id_forum =>$acces){
	if ($acces['voir'] == 1) $forums_autorises[] = $id_forum;
}
$forums_autorises = (sizeof($forums_autorises)==0)? "''":implode(',',$forums_autorises);

// LISTING des Catégories
$sql = 'SELECT DISTINCT c.id_cat, c.titre_cat, c.desc_cat
		FROM '.TABLE_FORUM_CATS.' AS c 
		LEFT JOIN '.TABLE_FORUM_FORUMS.' AS f
			ON (c.id_cat=f.id_cat)
		WHERE module="'.$module.'"
		'.$cat_specifique.' 
		AND f.id_forum IN ('.$forums_autorises.')
		ORDER BY c.ordre ASC,f.ordre ASC, c.id_cat ASC, f.id_forum ASC';
if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,701,__FILE__,__LINE__,$sql); 
if ($c->sql_numrows($resultat) == 0){
	if ($user['level'] == 10){
		$f->initialiser_forum_demo();
		header('location: '.formate_url('',true));
	}else{
		error404(726);
	}
}
while($row = $c->sql_fetchrow($resultat))
{
	// Navlinks
	if ($tpl->titre_navigateur == ''){
		$tpl->titre_navigateur = ($cat_specifique=='')?$module:$row['titre_cat'].' :: '.$module;
		$tpl->titre_page = ($cat_specifique=='')?$module:$row['titre_cat'];
		if ($cat_specifique!=''){
			$tpl->meta_description = $row['desc_cat'];
			$tpl->url_canonique = formate_url('mode=cat&id_cat='.$cat_specifique,true);
		}else{
			$tpl->meta_description .= ' '.$row['titre_cat'];
			$tpl->url_canonique = formate_url('',true);
		}
		$session->make_navlinks(ucfirst($module),formate_url('',true));
		if ($cat_specifique!='')
		{
			$session->make_navlinks($row['titre_cat'],formate_url('mode=cat&id_cat='.$row['id_cat'],true));
		}
	}
	
	// Affichage des categories
	$tpl->assign_block_vars('liste_cats', array(
		'TITRE_CAT'	=> $row['titre_cat'],
		'DESC_CAT'	=> $row['desc_cat'],
		'URL_CAT'	=> formate_url('mode=cat&id_cat='.$row['id_cat'],true)
	));
	
	// Recherche des forums 
	$f->afficher_forums($row['id_cat'],'liste_cats.liste_forums');
	
	// Topics Recents
	$f->afficher_topics_recents($row['id_cat'],'id_cat','liste_cats.recents',$cf->config['forum_nbre_recents_index']);
}
?>