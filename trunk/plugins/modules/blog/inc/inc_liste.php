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
load_lang('time');
$start = (isset($_GET['start'])&& $_GET['start']>0)? intval($_GET['start']):0;
$nbre_billets = $cf->config['blog_bpp'];

$tpl->set_filenames(array('blog' => $root.'plugins/modules/blog/html/liste_billets.html'));
//
// Titre de page 
$tpl->titre_navigateur = $module;
$tpl->titre_page = $module;

// Navlinks
$session->make_navlinks(ucfirst($module),formate_url('',true));
	
// Montage de la requete SQL
$option = $where = '';
if (isset($_GET['date']) && preg_match('|(\d{1,2})/(\d{1,2})/(\d{4})|',$_GET['date'],$matches)){
	$where = 'AND date_parution < '.mktime(23,59,59,$matches[2],$matches[1],$matches[3]).' AND '.mktime(0,0,1,$matches[2],$matches[1],$matches[3]).' < date_parution';

}elseif(isset($_GET['categorie'])){
	$where = 'AND c.id_cat='.intval($_GET['categorie']);
	$tpl->url_canonique = formate_url('mode=liste&categorie='.intval($_GET['categorie']).'&start='.$start,true);
	$option = '&categorie='.intval($_GET['categorie']);
}elseif(isset($_GET['tag'])){
	$where = 'AND tags LIKE \'%'.str_replace("\'","''",$_GET['tag']).'%\'';
	$option = '&tag='.$_GET['tag'];
	$tpl->url_canonique = formate_url('mode=liste&tag='.$_GET['tag'].'&start='.$start,true);
}else{
	$tpl->url_canonique = formate_url('mode=liste&start='.$start,true);
}
$where .= ' AND (date_parution < '.time().' OR  (auteur='.$user['user_id'].'))';

$sql = 'SELECT id_billet, titre_billet, billet, auteur, pseudo, date_redaction, 
				date_parution, nbre_coms, nbre_vues, c.id_cat, c.titre_cat   
		FROM '.TABLE_BLOG_BILLETS.' as b 
		LEFT JOIN '.TABLE_USERS.' as u
			ON (b.auteur=u.user_id)
		LEFT JOIN '.TABLE_BLOG_CATS.' as c
			ON (b.id_cat=c.id_cat)
		WHERE c.module="'.$module.'" '.$where.'
		ORDER BY date_parution DESC, id_billet DESC
		LIMIT '.$start.','.($nbre_billets+1);

if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,502,__FILE__,__LINE__,$sql); 

if ($c->sql_numrows($resultat) == 0 ){
	
	//
	// Pas de billet 
	$tpl->assign_block_vars('no_liste_billets', array());

}else{
	
	//
	// LISTING des billets
	$billets  = array();
	$b = 0;
	while($row = $c->sql_fetchrow($resultat))
	{
		$billets[] = $row;
		$b++;
	}

	// Creation du jeton de securite
	if (!session_id()) @session_start();
	$jeton = md5(uniqid(rand(), TRUE));
	$_SESSION['jeton'] = $jeton;
	$_SESSION['jeton_timestamp'] = $session->time;
				
	// Titres + Referencement
	if (isset($_GET['categorie'])){
		$session->make_navlinks($billets[0]['titre_cat'],formate_url('mode=liste&categorie='.$billets[0]['id_cat'],true));
		$tpl->titre_navigateur = $billets[0]['titre_cat']. ' :: '. $module;
		$tpl->titre_page = $billets[0]['titre_cat'];
	}

	//
	// AFFICHAGE des Billets
	for ($a=0;$a<$b;$a++)
	{
		if ($a == $nbre_billets) break;
		$Dp = date('j n Y',$billets[$a]['date_parution']);
		$Dp = explode(' ',$Dp);
		$billet_tronque = $post->bbcode2html(preg_replace("/\[pagebreak\](.*)/si",'...<br />',$billets[$a]['billet']));
		$mots_restants = (str_word_count($billets[$a]['billet']) - str_word_count($billet_tronque));
		$tpl->assign_block_vars('liste_billets', array(
			'TITRE_BILLET'		=> $billets[$a]['titre_billet'],
			'JOUR'				=> $Dp[0],
			'MOIS'				=> $lang['mois_court'][$Dp[1]],
			'ANNEE'				=> $Dp[2],
			'BILLET'			=> $billet_tronque,
			'CATEGORIE'			=> $billets[$a]['titre_cat'],
			'AUTEUR'			=> formate_pseudo($billets[$a]['auteur'],$billets[$a]['pseudo']),
			'NBRE_COMS'			=> sprintf($lang['L_COMS'],$billets[$a]['nbre_coms'],(($billets[$a]['nbre_coms']>1)?'s':'')),	
			'NBRE_VUES'			=> sprintf($lang['L_VU'],$billets[$a]['nbre_vues'],(($billets[$a]['nbre_vues']>1)?'s':'')),	
			'NBRE_MOTS'			=> sprintf($lang['L_SUITE'],$mots_restants,(($mots_restants>1)?'s':'')),	
			'S_BILLET'			=> formate_url('mode=billet&id_billet='.$billets[$a]['id_billet'],true),
			'S_CATEGORIE'		=> formate_url('mode=liste&categorie='.$billets[$a]['id_cat'],true),
			'EDITER'			=> formate_url('mode=saisie&id_billet='.$billets[$a]['id_billet'].'&action=editer',true),
			'SUPPRIMER'			=> formate_url('mode=saisie&id_billet='.$billets[$a]['id_billet'].'&action=supprimer&jeton='.$jeton,true)
		));
		if (preg_match('/\[pagebreak\]/',$billets[$a]['billet']))$tpl->assign_block_vars('liste_billets.suite', array());
		if ($droits->check($module,0,'editer') || $billets[$a]['auteur'] == $user['user_id'])$tpl->assign_block_vars('liste_billets.editer', array());
		if ($droits->check($module,0,'supprimer') || $billets[$a]['auteur'] == $user['user_id'])$tpl->assign_block_vars('liste_billets.supprimer', array());
		if ($droits->check($module,0,'commenter') && $cf->config['blog_ok_coms'] == 1) $tpl->assign_block_vars('liste_billets.commentaires_ok', array());
	}
	
	// Pages precedentes et suivantes
	if ($start > 0) $tpl->assign_block_vars('liste_precedent', array());
	if ($b > $nbre_billets) $tpl->assign_block_vars('liste_suivant', array());
}

//
// Affichage du lien de saisie
// On est Admin OU autorise a saisie un billet

if ($droits->check($module,0,'poster')) $tpl->assign_block_vars('nouveau_message', array());

// pagination par date 
$dateP = (isset($_GET['date']))?'&date='.$_GET['date']:'';
$tpl->assign_vars(array(
	'I_PRECEDENT'	=> $img['precedent'],
	'I_SUIVANT'		=> $img['suivant'],
	'I_NOUVEAU'		=> $img['nouveau'],
	'S_SUIVANT'		=> formate_url('mode=liste'.$option.'&start='.($start+$nbre_billets).$dateP,true),
	'S_PRECEDENT'	=> formate_url('mode=liste'.$option.'&start='.($start-$nbre_billets).$dateP,true),
	'S_NOUVEAU'		=> formate_url('mode=saisie',true)
));
?>
