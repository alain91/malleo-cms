<?php
if ( !defined('PROTECT') )
{
	die("Tentative de Hacking");
}
if (isset($_POST['enregistrer'])){
		if ($user['user_id']!=$user_id && $user['level']<9){
			die(); // A mettre au propre
		}
		$profil->clean($_POST);
		$profil->update_categorie();		
}else{
	$profil->clean($_GET);
	$profil->lecture_profil();
	if (sizeof($profil->cat)==0)$profil->creer_user_profil();

	$tpl->assign_vars(array(
		'TITRE_CAT'		=> $profil->cat[$profil->id_cat]['titre_cat'],
		'USER_ID'		=> $profil->user_id,
		'CATEGORIE'		=> $profil->id_cat,
		'TEXTE'			=> (($profil->cat[$profil->id_cat]['texte']==null)? $profil->cat[$profil->id_cat]['modele']:$profil->cat[$profil->id_cat]['texte']),
		'L_ENREGISTRER'	=> $lang['L_ENREGISTRER']
	));

	//
	// Titre de page 
	$tpl->titre_navigateur = $profil->cat[$profil->id_cat]['pseudo'];
	$tpl->titre_page = $profil->cat[$profil->id_cat]['pseudo'];

	// Navlinks
	$session->make_navlinks(sprintf($lang['L_LIBELLE_PROFIL'],$profil->cat[$profil->id_cat]['pseudo']),formate_url('user_id='.$profil->cat[$profil->id_cat]['user_id'],true));

	// On charge le wysiwyg
	if ($cf->config['wysiwyg_editor']!='') include_once($root.'fonctions/fct_'.$cf->config['wysiwyg_editor'].'.php');
}
?>