<?php
if ( !defined('PROTECT_ADMIN') )
{
	die("Tentative de Hacking");
}
// Init
require($root.'plugins/modules/arcade/prerequis.php');
$arcade = new arcade_admin();
$tpl->set_filenames(array('body_admin' => $root.'plugins/modules/arcade/html/admin_arcade_triche.html'));

// TRAITEMENT
$arcade->clean($_GET);
$arcade->clean($_POST);
if (isset($_POST['supprimer_rapports'])){
	$arcade->supprimer_rapports_de_triche();
	header('location: '.$base_formate_url);
	exit;
}elseif (isset($_POST['retablir_scores'])){
	$arcade->retablir_scores();
	header('location: '.$base_formate_url);
	exit;
}elseif(isset($_POST['supprimer_tous_les_rapports'])){
	$arcade->supprimer_tous_les_rapports_de_triche();
	header('location: '.$base_formate_url);
	exit;
}

$sql = 'SELECT t.id_triche,t.score,t.date,t.flashtime,t.temps_reel,t.type_triche,t.fps,
		t.user_id,u.pseudo,
		t.id_jeu,j.nom_jeu,j.image_petite,j.dossier_jeu
		FROM '.TABLE_ARCADE_TRICHES.' AS t
		LEFT JOIN '.TABLE_ARCADE_JEUX.' AS j
			ON (t.id_jeu=j.id_jeu)
		LEFT JOIN '.TABLE_USERS.' AS u
			ON (t.user_id=u.user_id)
		ORDER BY t.user_id DESC,j.nom_jeu ASC,t.date';
if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,1300,__FILE__,__LINE__,$sql); 
if ($c->sql_numrows($resultat)==0){

	// Aucun rapport de triche
	$tpl->assign_block_vars('aucun_rapport',array());
}else{

	// Parcours des rapports de triche
	$rapports = array();
	load_lang('time');
	while($row = $c->sql_fetchrow($resultat))
	{
		// Regroupement des cas de triche par jour
		$rapports[date('Ymd',$row['date'])][] = $row;
	}
	// Tri inverse suivant les clefs
	krsort($rapports);
	$i = 0;
	foreach ($rapports as $date=>$rapport){

		$fdate = formate_date($rapport[0]['date'],'N j n Y','FORMAT_DATE_ARCADE_TRICHE',$user['fuseau']);
		$fdate = explode(' ',$fdate);
		$fdate = $lang['jour'][$fdate[0]].' '.$fdate[1].' '.$lang['mois'][$fdate[2]].' '.$fdate[3];
		$tpl->assign_block_vars('liste_jours',array(
			'JOUR'		=> $fdate,
			'CPT'		=> $i
		));
		
		// Liste des rapports
		foreach ($rapport as $dossier){
			// Affichage des categories
			$tpl->assign_block_vars('liste_jours.liste_rapports',array(
				'IMAGE_PETITE'			=> $arcade->path_games.$dossier['dossier_jeu'].'/'.$dossier['image_petite'],
				'NOM_JEU'				=> $dossier['nom_jeu'],
				'ID_TRICHE'				=> $dossier['id_triche'],
				'PSEUDO'				=> $arcade->formate_pseudo($dossier['user_id'],$dossier['pseudo']),
				'DATE'					=> date('H\Hi',$dossier['date']),
				'SCORE'					=> $dossier['score'],
				'TYPE_TRICHE'			=> $arcade->libelle_type_triche($dossier['type_triche']),
				'TIMEFLASH'				=> $arcade->afficher_duree_temps($dossier['flashtime']),
				'REALTIME'				=> $arcade->afficher_duree_temps($dossier['temps_reel']),
				'FPS'					=> $dossier['fps']
			));
		}
		$i++;
	}
}
// Clefs de langues / images
$arcade->declarer_clefs_lang();
?>