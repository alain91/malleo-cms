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

if(!isset($_GET['id_forum']))
{
	error404();
	exit;
}
$id_forum = intval($_GET['id_forum']);

// Autorise a lire ou voir le forum ?
if (!$droits->check($module,$id_forum,'voir')){
	error404(725);	
}

$start = (isset($_GET['start']) && $_GET['start']>0)? intval($_GET['start']):0;


$tpl->set_filenames(array('forum'=>$root.'plugins/modules/forum/html/forum.html'));
		

//
// INFOS sur le forum
$sql = 'SELECT f.titre_forum, f.id_forum, f.icone_forum, f.status_forum, c.titre_cat, c.id_cat, c.desc_cat, count(t.id_forum)  as max
		FROM '.TABLE_FORUM_FORUMS.' as f 
		LEFT JOIN '.TABLE_FORUM_TOPICS.' as t 
			ON (f.id_forum=t.id_forum) 
		LEFT JOIN '.TABLE_FORUM_CATS.' as c 
			ON (f.id_cat=c.id_cat) 
		WHERE f.id_forum='.$id_forum.' 
		AND c.module=\''.$module.'\' 
		GROUP BY f.id_forum 
		LIMIT 1';
if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,704,__FILE__,__LINE__,$sql); 
if ($c->sql_numrows($resultat)==0)
{
	error404();
	exit;
}else{
	$row = $c->sql_fetchrow($resultat);
	$f->id_forum = $row['id_forum'];
	$nbre_topics = $row['max'];
	$tpl->titre_navigateur = $row['titre_forum'].' :: '.$row['titre_cat'];
	$tpl->titre_page = '<img src="data/icones_forum/'.$row['icone_forum'].'" alt="'.$row['titre_forum'].'" align="left" />&nbsp;'.$row['titre_forum'];
	$tpl->meta_description = $row['desc_cat'];
	$tpl->url_canonique = formate_url('mode=forum&id_forum='.$row['id_forum'].'&start='.$start,true);
	// Navlinks
	$session->make_navlinks(array(
		ucfirst($module)	=> formate_url('',true),
		$row['titre_cat']	=> formate_url('mode=cat&id_cat='.$row['id_cat'],true),
		$row['titre_forum']	=> formate_url('mode=forum&id_forum='.$row['id_forum'],true)
	));

	// Nouveau topic ?
	if (($droits->check($module,$row['id_forum'],'ecrire') || $user['level']>9) && $row['status_forum'] == 1 ) $tpl->assign_block_vars('nouveau', array());
	// Fusion de sujets ?
	if ($droits->check($module,$row['id_forum'],'moderer') || $user['level']>9) $tpl->assign_block_vars('fusionner', array());
	// Verrouiller le forum
	if (($droits->check($module,$row['id_forum'],'verrouiller') || $user['level']>9)	&& $row['status_forum']==1) $tpl->assign_block_vars('verrouiller', array());
	if (($droits->check($module,$row['id_forum'],'verrouiller') || $user['level']>9)	&& $row['status_forum']==0) $tpl->assign_block_vars('deverrouiller', array());

}

$f->affiche_sous_forums(true,'sous_forum','forum','recents');
$f->affiche_liste_annonces('t.id_forum='.$id_forum);
$f->affiche_liste_topics($start,$cf->config['forum_topics_par_forum'],'t.id_forum='.$id_forum);

// PAGINATION
include($root.'fonctions/fct_affichage.php');
//
// LISTING des topics
$tpl->assign_vars(array(
	'I_NOUVEAU'			=>	$img['nouveau'],
	'I_FUSIONNER'		=>	$img['fusionner'],
	'I_VERROUILLER'		=>	$img['verrouiller'],
	'I_DEVERROUILLER'	=>	$img['deverrouiller'],
	'S_NOUVEAU'			=>	formate_url('mode=NouveauTopic&id_forum='.$id_forum,true),
	'S_FUSIONNER'		=>	formate_url('mode=FusionnerTopics&id_forum='.$f->id_forum,true),
	'S_VERROUILLER'		=>	formate_url('mode=VerrouillerForum&id_forum='.$f->id_forum,true),		
	'S_DEVERROUILLER'	=>	formate_url('mode=DeVerrouillerForum&id_forum='.$f->id_forum,true),	
	'PAGINATION'		=>	create_pagination($start, 'mode=forum&id_forum='.$f->id_forum.'&start=', $nbre_topics , $cf->config['forum_topics_par_forum'],$lang['L_TOPIC'])
));
?>