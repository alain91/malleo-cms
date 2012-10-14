<?php
define('PROTECT',true);
$root = '../../../';
$lang=$user=array();
require_once($root.'chargement.php');
$style_name=load_style();

if (isset($_POST['id_jeu']) && $user['level']>8)
{
	// init
	require_once($root.'plugins/modules/arcade/prerequis.php');
	$arcade = new arcade_admin();
	$arcade->clean($_GET);
	$arcade->clean($_POST);

	$tpl->set_filenames(array('body_admin' => $root.'plugins/modules/arcade/html/admin_arcade_jeux_editer.html'));
	
	//
	// Listing des infos du jeu
	$sql = 'SELECT DISTINCT j.id_jeu,j.dossier_jeu,j.nom_jeu,j.description,j.controles,j.actif,
			j.variable,j.nom_swf,j.fps, j.score_sens,
			j.score_max,j.score_max_user_id,u1.pseudo AS score_max_pseudo,
			j.score_ultime,j.score_ultime_user_id,u2.pseudo AS score_ultime_pseudo,
			j.image_petite,j.image_grande,j.largeur,j.hauteur,j.date_ajout 
			FROM '.TABLE_ARCADE_JEUX.' AS j
			LEFT JOIN '.TABLE_USERS.' AS u1
				ON (j.score_max_user_id=u1.user_id)
			LEFT JOIN '.TABLE_USERS.' AS u2
				ON (j.score_ultime_user_id=u2.user_id)
			WHERE j.id_jeu='.$arcade->id_jeu.' 
			LIMIT 1';

	if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,1300,__FILE__,__LINE__,$sql); 
	if ($c->sql_numrows($resultat) == 0){
		$tpl->assign_block_vars('aucun_jeu_correspond',array());
	}else{
		$tpl->assign_block_vars('fiche_jeu',array());
		$row = $c->sql_fetchrow($resultat);
			
		// Controles
		$controles = '';
		for($i=0;$i<=4;$i++){
			$selected = ($row['controles']==$i)?' selected="selected"':'';
			$controles .= "\n".'<option value="'.$i.'"'.$selected.'>'.$arcade->formate_controles($i).'</option>';
		}
		
		// Sens scores
		$score_sens = '';
		if ($row['score_sens']==0){
		$score_sens = "\n".'<option value=0>'.$lang['L_DESCENDANT'].'</option>'.'<option value=1>'.$lang['L_ASCENDANT'].'</option>';
		}else{
		$score_sens = "\n".'<option value=1>'.$lang['L_ASCENDANT'].'</option>'.'<option value=0>'.$lang['L_DESCENDANT'].'</option>';
		}
		
		$tpl->assign_vars(array(
				'ID_JEU'			=>	$row['id_jeu'],
				'NOM_JEU'			=>	$row['nom_jeu'],
				'DESCRIPTION'		=>	$row['description'],
				'VARIABLE'			=>	$row['variable'],
				'CONTROLES'			=>	$row['controles'],
				'SWF'				=>	$row['nom_swf'],
				'LARGEUR'			=>	$row['largeur'],
				'HAUTEUR'			=>	$row['hauteur'],
				'FPS'				=>	$row['fps'],
				'ACTIF_OUI'			=>	($row['actif']==1)?' checked="checked"':'',
				'ACTIF_NON'			=>	($row['actif']==0)?' checked="checked"':'',
				'IMAGE_PETITE'		=>	$row['image_petite'],
				'IMAGE_GRANDE'		=>	$row['image_grande'],
				'IMAGE_ROOT'		=>	$arcade->path_games. $row['dossier_jeu']. '/',
				'SELECT_CONTROLES'	=>	$controles,
				'SCORE_SENS'		=>	$score_sens,
				'S_SUPP'			=>	formate_url('action=supprimer&id_jeu='.$row['id_jeu'],true),
		));
	}
	
	// Clefs de langues / images
	$arcade->declarer_clefs_lang();
	
	$tpl->pparse('body_admin');
	$tpl->afficher_page();
}

?>