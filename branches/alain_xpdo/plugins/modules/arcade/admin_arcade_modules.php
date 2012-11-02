<?php
if ( !defined('PROTECT_ADMIN') )
{
	die("Tentative de Hacking");
}

// Init
require($root.'plugins/modules/arcade/prerequis.php');
$arcade = new arcade_admin();


$tpl->set_filenames(array('body_admin' => $root.'plugins/modules/arcade/html/admin_arcade_modules.html'));

// TRAITEMENT
if (isset($_POST['action']) || isset($_GET['action']))
{
	$action = (isset($_POST['action']))? $_POST['action']:$_GET['action'];
	$arcade->clean($_GET);
	$arcade->clean($_POST);
	
	// Recherche de tous les ID de jeux dans le module
	if ($action != 'enregistrer'){
		$arcade->module = $arcade->salle;
		$arcade->jeu = $arcade->liste_id_jeux();
	}
	
	// Titre salle obligatoire
	if (($action == 'enregistrer') && trim($_POST['titre_salle']==''))
	{
		message_die(E_WARNING,1313,'','');
	}
	
	switch ($action)
	{
		// Enregistrement de la configuration
		case 'enregistrer' : 
			$arcade->enregistrer_config_salle();
			header('location: '.$base_formate_url);
			break;
		case 'activer':				$arcade->activer_jeux();break;
		case 'desactiver':			$arcade->desactiver_jeux();break;
		case 'mettre_zero':			$arcade->mettre_zero_scores();break;
		case 'mettre_ultime_zero':	$arcade->mettre_zero_scores_ultime();break;
	}
	header('location: '.formate_url('',true));
}

//
// AFFICHAGE des modules (salles d'arcade)

$sql = 'SELECT module FROM '.TABLE_MODULES.' WHERE module=\'arcade\' OR virtuel=\'arcade\' ORDER BY id_module ASC';
if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,1300,__FILE__,__LINE__,$sql); 
while($row = $c->sql_fetchrow($resultat))
{
	$arcade->module = $row['module'];
	$arcade->Get_config();
	$tpl->assign_block_vars('liste_modules', array(
		'MODULE'					=>	$arcade->module,
		'TITRE_SALLE'				=>	$arcade->config['titre_salle'],
		'DESCRIPTION_SALLE'			=>	$arcade->config['description_salle'],
		'AFFICHAGE_FICHE_JEUX_'		.$arcade->config['affichage_fiche_jeux']	=> ' selected="selected"',
		'AFFICHAGE_JEU_'			.$arcade->config['affichage_jeu']			=> ' selected="selected"',
		'AFFICHAGE_MOD_'			.$arcade->config['affichage_mod']			=> ' selected="selected"',
		'NBRE_JEUX_PAR_PAGE_'		.$arcade->config['nbre_jeux_page']			=> ' selected="selected"',
		'NBRE_COLONNES_'			.$arcade->config['nbre_colonnes']			=> ' selected="selected"',
		'NBRE_COLONNES_JEUX_'		.$arcade->config['nbre_colonnes_jeux']		=> ' selected="selected"',
		'ACTIVER_FAVORIS_'			.$arcade->config['activer_favoris']			=> ' checked="checked"',
		'ACTIVER_MAIL_CHAMPION_'	.$arcade->config['activer_mail_champion']	=> ' checked="checked"',
		'ACTIVER_MP_CHAMPION_'		.$arcade->config['activer_mp_champion']		=> ' checked="checked"',
		'U_METTRE_0_SCORES'			=>	formate_url('action=mettre_zero&salle='.$row['module'],true),
		'U_METTRE_0_SCORES_ULTIMES'	=>	formate_url('action=mettre_ultime_zero&salle='.$row['module'],true),
		'U_DESACTIVER_JEUX'			=>	formate_url('action=desactiver&salle='.$row['module'],true),
		'U_ACTIVER_JEUX'			=>	formate_url('action=activer&salle='.$row['module'],true),
	));
}

$arcade->declarer_clefs_lang();


?>