<?php
if ( !defined('PROTECT_ADMIN') )
{
	die("Tentative de Hacking");
}

// Init
$start = (isset($_GET['start']))? intval($_GET['start']):0;
$nbre_jeux_par_page = 10;
require($root.'plugins/modules/arcade/prerequis.php');
$arcade = new arcade_admin();

$tpl->set_filenames(array('body_admin' => $root.'plugins/modules/arcade/html/admin_arcade_jeux.html'));

// TRAITEMENT
$arcade->clean($_GET);
$arcade->clean($_POST);

// SECURITE
if (!isset($arcade->id_cat)){
	affiche_message('body_admin','L_ID_CAT_NOT_DEFINED',formate_url('admin.php?module=plugins/modules/arcade/admin_arcade_categories.php'));
}


$action = null;
if (isset($_POST['action']) || isset($_GET['action']))
{
	$action = (isset($_POST['action']))? $_POST['action']:$_GET['action'];
	if (isset($arcade->jeu) ){
			
	}
	switch ($action)
	{
		case 'move':
			$sens  = ($_GET['sens']=='up')? '+':'-';
			require_once($root.'fonctions/fct_formulaires.php');
			deplacer_id_tableau(TABLE_ARCADE_CATS, 'id_cat', 'ordre', 'ASC', $arcade->id_cat, $sens, ' WHERE module=\''.$arcade->salle.'\'');
			header('location: '.$base_formate_url);
			break;
		case 'editer': 				$arcade->editer_jeu();break;
		case 'supprimer':			$arcade->supprimer_jeu();break;
		case 'supprimer_totalement':$arcade->supprimer_totalement_jeu();break;
		case 'activer':				$arcade->activer_jeux();break;
		case 'desactiver':			$arcade->desactiver_jeux();break;
		case 'mettre_zero':			$arcade->mettre_zero_scores();break;
		case 'mettre_ultime_zero':	$arcade->mettre_zero_scores_ultime();break;
		case 'affecter':			$arcade->affecter_jeu_dans_categories();break;
	}
}

//
// Infos sur la categorie et implicitement sur le module

if ($arcade->id_cat==0){
	
	//  Jeux Orphelins
	
	$sql = 'SELECT count(j.id_jeu) as nbre_jeux_cat
		FROM '.TABLE_ARCADE_JEUX.' AS j
		LEFT JOIN '.TABLE_ARCADE_CATS_JEUX.' AS cj
		ON (j.id_jeu=cj.id_jeu) 
		WHERE cj.id_jeu is null';
	if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,1300,__FILE__,__LINE__,$sql); 
	$row = $c->sql_fetchrow($resultat);
	$tpl->assign_vars(array(
		'MODULE'			=>	'',
		'CATEGORIE'			=>	$lang['L_TITRE_CAT_ORPHELINS'],
		'ICONE_CATEGORIE'	=>	$arcade->chemin_icones.$arcade->icone_orphelins,
	));
}else{
	
	// Jeux appartenant a une categorie
	
	$sql =  'SELECT c.id_cat, c.titre_cat, c.nbre_jeux_cat, c.module, c.icone,
			m.titre_salle
			FROM '.TABLE_ARCADE_CATS.' AS c
			LEFT JOIN '.TABLE_ARCADE_MODULES.' AS m
			ON (c.module=m.module)
			WHERE c.id_cat='.$arcade->id_cat;
	if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,1300,__FILE__,__LINE__,$sql); 
	$row = $c->sql_fetchrow($resultat);
	$tpl->assign_vars(array(
		'MODULE'			=>	$row['titre_salle'].' ('.$row['module'].')',
		'CATEGORIE'			=>	$row['titre_cat'],
		'ICONE_CATEGORIE'	=>	$row['icone'],
	));
}
$nbre_jeux = $row['nbre_jeux_cat'];


//
// Listing des jeux 
$sql = 'SELECT DISTINCT j.id_jeu,j.dossier_jeu,j.nom_jeu,j.description,j.controles,j.actif,
		j.score_max,j.score_max_user_id,u1.pseudo AS score_max_pseudo,
		j.score_ultime,j.score_ultime_user_id,u2.pseudo AS score_ultime_pseudo,
		j.image_petite,j.image_grande,j.largeur,j.hauteur,j.date_ajout ';

if ($arcade->id_cat==0){
	$sql .= 'FROM '.TABLE_ARCADE_JEUX.' AS j
		LEFT JOIN '.TABLE_ARCADE_CATS_JEUX.' AS cj
		ON (j.id_jeu=cj.id_jeu)
		LEFT JOIN '.TABLE_USERS.' AS u1
			ON (j.score_max_user_id=u1.user_id)
		LEFT JOIN '.TABLE_USERS.' AS u2
			ON (j.score_ultime_user_id=u2.user_id) 
		WHERE cj.id_jeu is null ';
}else{
	$sql .= 'FROM '.TABLE_ARCADE_CATS_JEUX.' AS cj
		LEFT JOIN '.TABLE_ARCADE_JEUX.' AS j
			ON (cj.id_jeu=j.id_jeu)
		LEFT JOIN '.TABLE_USERS.' AS u1
			ON (j.score_max_user_id=u1.user_id)
		LEFT JOIN '.TABLE_USERS.' AS u2
			ON (j.score_ultime_user_id=u2.user_id)
		WHERE cj.id_cat='.$arcade->id_cat;
}
	$sql .= ' ORDER BY nom_jeu ASC
		LIMIT '.$start.','.$nbre_jeux_par_page;

if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,1300,__FILE__,__LINE__,$sql); 
if ($c->sql_numrows($resultat) == 0){
	$tpl->assign_block_vars('aucun_resultat',array());
}else{
	while($row = $c->sql_fetchrow($resultat))
	{
		// Affichage des categories
		$tpl->assign_block_vars('liste_jeux',array(
			'ID_JEU'			=>	$row['id_jeu'],
			'TITRE_JEU'			=>	$row['nom_jeu'],
			'DESCRIPTION'		=>	$row['description'],
			'CONTROLES'			=>	$row['controles'],
			'IMAGE_PETITE'		=>	$arcade->path_games. $row['dossier_jeu']. '/' .$row['image_petite'],
			'S_SUPP'			=>	formate_url('action=supprimer&jeu='.$row['id_jeu'].'&id_cat='.$arcade->id_cat,true)
		));
	}
	
	// PAGINATION (preparation)
	include($root.'fonctions/fct_affichage.php');
	$tpl->assign_vars(array(
		'PAGINATION'	=> create_pagination($start, 'id_cat='.$arcade->id_cat.'&start=', $nbre_jeux, $nbre_jeux_par_page,$lang['L_FICHE'])
	));
}


$tpl->assign_vars(array(
	'SELECT_OPTIONS_JEUX'	=> $arcade->select_options_jeux()
));

// Liste des categories
$arcade->affiche_menu_deroulant_categories();


// Clefs de langues / images
$arcade->declarer_clefs_lang();

?>