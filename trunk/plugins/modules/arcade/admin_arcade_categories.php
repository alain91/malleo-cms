<?php
if ( !defined('PROTECT_ADMIN') )
{
	die("Tentative de Hacking");
}
// Init
require($root.'plugins/modules/arcade/prerequis.php');
$arcade = new arcade_admin();

$ext_ok = array('gif','GIF','png','PNG','jpg','JPG','jpeg','JPEG');
$edit_module = $liste_icones = $select = $select_icone = $image_par_defaut = '';
$hidden_action = '<input type="hidden" name="action" value="ajouter" />	';

$tpl->set_filenames(array('body_admin' => $root.'plugins/modules/arcade/html/admin_arcade_categories.html'));

// TRAITEMENT
$arcade->clean($_GET);
$arcade->clean($_POST);
$action = null;
if (isset($_POST['action']) || isset($_GET['action']))
{
	$action = (isset($_POST['action']))? $_POST['action']:$_GET['action'];
	
	switch ($action)
	{
		case 'move':
			$sens  = ($_GET['sens']=='up')? '+':'-';
			require_once($root.'fonctions/fct_formulaires.php');
			deplacer_id_tableau(TABLE_ARCADE_CATS, 'id_cat', 'ordre', 'ASC', $arcade->id_cat, $sens, ' WHERE module=\''.$arcade->salle.'\'');
			header('location: '.$base_formate_url);
			break;
		case 'ajouter' : 
			$arcade->ajouter_categorie();
			header('location: '.$base_formate_url);
			break;
		case 'editer':
			$arcade->editer_categorie();
			header('location: '.$base_formate_url);
			break;
		case 'supprimer':
			$arcade->supprimer_categorie();
			header('location: '.$base_formate_url);
			break;
		case 'edit':
			$hidden_action = '<input type="hidden" name="action" value="editer" />	';
			break;
	}
}


// Module selectionne OU tous les modules Arcade
$sql_module= (isset($arcade->salle))? 'm.module=\''.$arcade->salle.'\'':'m.module=\'arcade\' OR m.virtuel=\'arcade\'';

$sql = 'SELECT m.module,a.titre_salle,c.id_cat,c.titre_cat,c.nbre_jeux_cat,c.icone 
		FROM '.TABLE_ARCADE_CATS.' AS c
		RIGHT JOIN '.TABLE_MODULES.' AS m
		ON (c.module=m.module)
		LEFT JOIN '.TABLE_ARCADE_MODULES.' AS a
		ON (m.module=a.module)
		WHERE '.$sql_module.' ORDER BY m.id_module,c.ordre ASC';

if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,1300,__FILE__,__LINE__,$sql); 
$reg = array();
while($row = $c->sql_fetchrow($resultat))
{
	$reg[$row['module']][] = $row;
}

$liste_modules = array();
foreach ($reg as $module=>$data){

	// Liste des modules
	$tpl->assign_block_vars('liste_modules',array(
		'MODULE'		=> $module,
		'TITRE_SALLE'	=> $data[0]['titre_salle']
	));
	
	$liste_modules[] = $module;
	
	// Liste des categories
	$t = 1;
	foreach ($data as $k=>$v){
		if ($v['id_cat']==''){
			$tpl->assign_block_vars('liste_modules.aucun_resultat',array());
		}else{
			// Affichage des categories
			$tpl->assign_block_vars('liste_modules.liste_cats',array(
				'ID_CAT'			=> $v['id_cat'],
				'TITRE_CAT'			=> $v['titre_cat'],
				'NBRE_JEUX_CATS'	=> $v['nbre_jeux_cat'],
				'ICONE'				=> ($v['icone']!='')?'<img src="'.$v['icone'].'" alt="'.$v['titre_cat'].'" />':'',
				'S_CAT'				=>	formate_url('index.php?module='.$module.'&mode=cat&id_cat='.$v['id_cat']),
				'S_UP'				=>	formate_url('action=move&sens=up&id_cat='.$v['id_cat'].'&salle='.$module,true),
				'S_DOWN'			=>	formate_url('action=move&sens=down&id_cat='.$v['id_cat'].'&salle='.$module,true),
				'S_EDIT'			=>	formate_url('action=edit&id_cat='.$v['id_cat'],true),
				'S_SUPP'			=>	formate_url('action=supprimer&id_cat='.$v['id_cat'],true)
			));
		}
		
		// Edition d'une categorie
		if ($action == 'edit' && $arcade->id_cat==$v['id_cat']){
			$edit_module = $module;
			$image_par_defaut = $select_icone = $v['icone'];
			$tpl->assign_vars(array(
				'TITRE_CAT'	=> $v['titre_cat']
			));
		}
		
		// Monter / descendre
		$nbre_cats = sizeof($data);
		if ($nbre_cats>1 && $t>1) $tpl->assign_block_vars('liste_modules.liste_cats.monter',array());
		if ($nbre_cats>1 && $t<$nbre_cats) $tpl->assign_block_vars('liste_modules.liste_cats.descendre',array());
		$t++;
	}
}

if (!is_dir($arcade->chemin_icones)){
	// Le dossier data/icones_arcade/ n'existe pas
	$tpl->assign_block_vars('dossier_data_arcade',array());
}else{
	// Liste d'icones
	$ch = @opendir($arcade->chemin_icones);
	while ($icone = @readdir($ch))
	{
		$ext = pathinfo($icone);
		if ($icone != '.' && $icone != '..' && array_key_exists('extension',$ext) && in_array($ext['extension'],$ext_ok)) {
			if ($image_par_defaut == '') $image_par_defaut = $arcade->chemin_icones.$icone;
			$selected=($select_icone==$arcade->chemin_icones.$icone)?' selected="selected"':'';
			$liste_icones .= "\n ".'<option value="'.$arcade->chemin_icones.$icone.'"'.$selected.'>'.preg_replace('/.'.$ext['extension'].'/','',$icone).'</option>';
		}
	}
	@closedir($ch);
	if ($liste_icones == ''){
		// Le dossier data/icones_arcade/ est vide
		$tpl->assign_block_vars('dossier_data_arcade',array());
	}
}

// Menu deroulant des modules disponibles
$select_module = '';
foreach ($liste_modules as $k=>$v){
	$selected = ($action == 'edit' && $edit_module==$v)? ' selected="selected"':'';
	$select_module .= '<option'.$selected.'>'.$v.'</option>'."\n";
}

//
// Jeux orphelins
$sql = 'SELECT count(j.id_jeu) as nbre_jeux_cat
		FROM '.TABLE_ARCADE_JEUX.' AS j
		LEFT JOIN '.TABLE_ARCADE_CATS_JEUX.' AS cj
		ON (j.id_jeu=cj.id_jeu) 
		WHERE cj.id_jeu is null';
if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,1300,__FILE__,__LINE__,$sql); 
if ($c->sql_numrows($resultat)>0){
	$row = $c->sql_fetchrow($resultat);
	$tpl->assign_vars(array(
		'NBRE_JEUX_ORPHELINS'=> $row['nbre_jeux_cat'],
		'ICONE_ORPHELINS'=> $arcade->chemin_icones.$arcade->icone_orphelins,
	));
}

// Clefs de langues / images
$arcade->declarer_clefs_lang();

$tpl->assign_vars(array(
	'L_GESTION_CATEGORIE'	=> ($edit_module!='')? $lang['L_EDITER_CATEGORIE']:$lang['L_AJOUTER_CATEGORIE'],
	'ICONE_PAR_DEFAUT'		=> $image_par_defaut,
	'SELECT_ICONE'			=> $liste_icones,
	'SELECT_MODULE'			=> $select_module,
	'HIDDEN_ACTION'			=> $hidden_action,
	'DISPLAY'				=> ($edit_module!='')? '':'display:none;'
));
?>
