<?php
if ( !defined('PROTECT') )
{
	die("Tentative de Hacking");
}
load_lang('time');
//
// SECURITE

if (($user['user_id']!=$user_id && $user['level']<9) || $user['level']<2){
	message_die(E_WARNING,1110,'',''); 
}
		
//
// CHARGEMENT des outils images

require_once($root.'class/class_image.php');

//
// Listing des avatars dispos

class listing_avatars extends image{

	//
	// Affiche une miniature de l'image avec des infos utiles
	
	function miniature_image($image,$dir){
		global $tpl,$user_id;
		$taille = getimagesize($dir.$image);
		$tpl->assign_block_vars('liste_miniatures', array(
			'AVATAR'	=> $dir.$image,
			'TITRE'		=> substr($image,0,16),
			'POIDS'		=> round(filesize($dir.$image)/1024,2).'Ko',
			'LARGEUR'	=> $taille['0'],
			'HAUTEUR'	=> $taille['1'],
			'DATE'		=> date('d/m/Y',filectime($dir.$image)),
			'SELECT'	=> formate_url('user_id='.$user_id.'&mode=configuration&action=select_avatar&fichier='.$image,true),
			'DELETE'	=> formate_url('user_id='.$user_id.'&mode=configuration&action=effacer_avatar&fichier='.$image,true),
		));
	}
}
$image = new listing_avatars();
$image->file_max_size = $cf->config['avatar_taille_max'];
$image->file_max_largeur = $cf->config['avatar_largeur_max'];
$image->file_max_hauteur = $cf->config['avatar_hauteur_max'];
$image->rep_taille_max = $cf->config['avatar_taille_rep'];
		
//
// INFORMATIONS user

$sql = 'SELECT u.*    
		FROM '.TABLE_USERS.' as u 
		WHERE u.user_id='.$user_id.' LIMIT 1';
if (!$resultat = $c->sql_query($sql)) message_die(E_ERROR,1100,__FILE__,__LINE__,$sql); 
$row = $c->sql_fetchrow($resultat);

$select_langue = '';
$action = null;
if (isset($_GET['action']) ||isset($_POST['action']))
{
	$action = (isset($_POST['action']))?$_POST['action']:$_GET['action'];
	$profil->user_id = (isset($_POST['user_id']))?$_POST['user_id']:intval($_GET['user_id']);
	switch ($action){
		case 'upload_avatar':
			// ENVOI d'AVATAR
			if (isset($_FILES) && is_array($_FILES)) 
			{
				$image->post = $_FILES;
				$image->nom_champ = 'avatar';
				$image->destination = $dir_avatars.$user_id.'/';
				$msg = $image->verification_upload();
				if($msg!=1){
					message_die(E_ERROR,$msg,'','');
				}
				$profil->update_element('avatar=\''.$dir_avatars.$user_id.'/'.$image->nom_unique.'\'');
			}
			break;
		case 'lier_avatar':
			$file = mysql_real_escape_string($_POST['avatar']);
			if (isset($_POST['avatar']) && ($_POST['avatar'] != '') && verifie_existance_image($file))
			{
				$image->destination = $dir_avatars.$user_id.'/';
				$msg = $image->download_image_distante($file);
				// si l'URL a change on considere que l'image a ete copiee
				if ($image->nom_unique != ''){
					$profil->update_element('avatar=\''.$image->destination.$image->nom_unique.'\'');
				}else{
					message_die(E_ERROR,$msg,'','');
				}
			}
			header('Location: '.formate_url('user_id='.$user_id.'&mode=configuration',true));
			exit;
		case 'effacer_avatar':
			$file = mysql_real_escape_string(preg_replace('[/\]','',$_GET['fichier']));
			if (file_exists($dir_avatars.$user_id.'/'.$file))
			{
				@unlink($dir_avatars.$user_id.'/'.$file);
			}
			header('Location: '.formate_url('user_id='.$user_id.'&mode=configuration',true));
			break;
		case 'select_avatar':
			$file = mysql_real_escape_string(preg_replace('/[\/\\]/','',$_GET['fichier']));
			if (file_exists($dir_avatars.$user_id.'/'.$file))
			{
				$profil->update_element('avatar=\''.$dir_avatars.$user_id.'/'.$file.'\'');
			}
			header('Location: '.formate_url('user_id='.$user_id.'&mode=configuration',true));
			exit;
		case 'select_langue':
			$lg = substr(preg_replace('/[^a-z]/','',$_GET['langue']),0,2);
			if (file_exists($dir_langues.'/'.$lg))
			{
				$profil->update_element('langue=\''.$lg.'\'');
			}
			header('Location: '.formate_url('user_id='.$user_id.'&mode=configuration',true));
			exit;
		case 'select_style':
			$style = preg_replace('/[^a-z0-9_-]/i','',$_GET['style']);
			if (is_dir($dir_styles.$style))
			{
				$profil->update_element('style=\''.$style.'\'');
			}
			header('Location: '.formate_url('user_id='.$user_id.'&mode=configuration',true));
			exit;
		case 'signature':
			$profil->update_element('signature=\''.str_replace("\'","''",$_POST['signature']).'\'');
			break;
		case 'contact':
			// EMAIL (si different creation d'une clef envoyee par mail + desactivation du compte)
			if ($row['email'] != $_POST['email']){
				if (!preg_match("/^[-+.\w]{1,64}@[-.\w]{1,64}\.[-.\w]{2,6}$/i", $_POST['email']))message_die(E_WARNING,1109,'','');
				$profil->reactivation_email($_POST['email'],$row['pseudo']);
			}
			// Messengers
			$profil->update_element('msn=\''.str_replace("\'","''",$_POST['msn']).'\',gtalk=\''.str_replace("\'","''",$_POST['gtalk']).'\',icq=\''.str_replace("\'","''",$_POST['icq']).'\',yahoo=\''.str_replace("\'","''",$_POST['yahoo']).'\'');
			break;
		case 'informations':
			$date_naissance = $_POST['annee'].'-'.$_POST['mois'].'-'.$_POST['jour'];
			$profil->update_element('site_web=\''.str_replace("\'","''",$_POST['site_web']).'\',localisation=\''.str_replace("\'","''",$_POST['localisation']).'\',etat_civil='.intval($_POST['etat_civil']).',sexe='.intval($_POST['sexe']).',date_naissance=\''.$date_naissance.'\'');
			break;
		case 'parametres':
			$profil->update_element('fuseau='.intval($_POST['fuseau']));
			break;
		case 'compte':
			// CHANGEMENT de MDP
			if ($_POST['ancien_mdp']!='' || $_POST['nouveau_mdp']!='' || $_POST['nouveau_mdp2']!='' ){
				// champs vides
				if (empty($_POST['ancien_mdp']) || empty($_POST['nouveau_mdp']) || empty($_POST['nouveau_mdp2'])) message_die(E_WARNING,1106,'','');
				// Ancien mdp errone
				if ($row['pass'] != md5($_POST['ancien_mdp']))message_die(E_WARNING,1107,'','');
				// Nouveaux mdp différents
				if ($_POST['nouveau_mdp'] != $_POST['nouveau_mdp2'])message_die(E_WARNING,1108,'','');
				// ENREGISTREMENT
				$profil->update_element('pass=\''.md5($_POST['nouveau_mdp']).'\'');
			}		
			// -----------------------------------------------------------------
			// ADMIN
			// -----------------------------------------------------------------
			if ($user['level']>8){
				// ACTIVATION compte +  Niveau d'Acces
				// FAIBLESSE a corriger
				$profil->update_element('rang='.intval($_POST['rang']).',level='.intval($_POST['level']).',actif='.intval($_POST['actif']));
			}
			break;
	}
}
//
// Affichage configuration
$date_naissance = explode('-',$row['date_naissance']);
include_once($root.'fonctions/fct_formulaires.php');

//
// LANGUE

$ch = @opendir($dir_langues);
while ($lg = @readdir($ch))
{
	if ($lg != '.' && $lg != '..' && is_dir($dir_langues.$lg)) {
		$tpl->assign_block_vars('liste_langues', array(
			'ICONE'		=> 'data/flags/'.$lg.'.gif',
			'LANG'		=> (array_key_exists('L_LANG_'.$lg,$lang))? $lang['L_LANG_'.$lg]:'',
			'SELECT'	=> formate_url('user_id='.$user_id.'&mode=configuration&action=select_langue&langue='.$lg,true)		
		));
	}
}
@closedir($ch);

//
// Styles

$ch = @opendir($dir_styles);
while ($style = @readdir($ch))
{
	if ($style != '.' && $style != '..' && is_dir($dir_styles.$style)) {
		$tpl->assign_block_vars('liste_styles', array(
			'ICONE'		=> $dir_styles.$style.'/apercu.jpg',
			'LANG'		=> $style,
			'SELECT'	=> formate_url('user_id='.$user_id.'&mode=configuration&action=select_style&style='.$style,true)		
		));
	}
}
@closedir($ch);

//
// Rangs

$liste_rangs = $cache->appel_cache('listing_rangs');
$rangs = '<option value="0">'.$lang['L_AUTOMATIQUE'].'</option>';
foreach ($liste_rangs as $id=>$val){
	if ($val['msg']==''){
		$selected = ($row['rang']==$id)? ' selected="selected"':'';
		$rangs .= '<option value="'.$val['id_rang'].'"'.$selected.'>'.$val['titre'].'</option>';
	}
}

//
// Niveaux
$levels = '';
$afficher_levels = array(0,2,9,10);
foreach($liste_levels as $level=>$libelle){
	if (in_array($level,$afficher_levels)){
		if ($user['level']>8){
			// l'intervenant est un fondateur  alors il peut tout faire
			$selected = ($row['level']==$level)?' selected="selected"':'';
			$levels .= "\n".'<option value="'.$level.'"'.$selected.'>'.$libelle.'</option>';	
		}elseif($user['level']==8 && $row['level']>8){
			// L'intervenant est un admin, et le profil est celui d'un fondateur
			// Il ne peut donc rien faire
			if ($row['level']==$level){
				$levels .= "\n".'<option value="'.$level.'" selected="selected">'.$libelle.'</option>';
			}
		}else{
			// Un admin ne peut pas créer de fondateur
			if ($level < 9){
				$selected = ($row['level']==$level)?' selected="selected"':'';
				$levels .= "\n".'<option value="'.$level.'"'.$selected.'>'.$libelle.'</option>';	
			}
		}
	}
}


// Seuls des admins et fondateurs peuvent modifier l'acces, le rang et le niveau
if ($user['level']>8 && $row['level']<=$user['level']) $tpl->assign_block_vars('admins', array());
// un admin peut modifier le mdp d'un membre, un fondateur celui d'un confrere ou d'un admin
if (($row['user_id']==$user['user_id']) || 
	($row['level']<9 && $user['level']>8) || 
	($user['level']==10)) $tpl->assign_block_vars('protection_mdp', array());

$tpl->assign_vars(array(
	'HIDDEN'			=> 	'<input type="hidden" name="user_id" value="'.$user_id.'" /><input type="hidden" name="mode" value="configuration" />',
	'SIGNATURE'			=> $row['signature'],
	'STATUS_DESACTIVE'	=> ($row['actif']==0)?' checked="checked"':'',
	'STATUS_ACTIVE'		=> ($row['actif']==1)?' checked="checked"':'',
	'LEVEL'				=> $levels,
	'AVATAR'			=> $row['avatar'],
	'EMAIL'				=> $row['email'],
	'MSN'				=> $row['msn'],
	'GTALK'				=> $row['gtalk'],
	'YAHOO'				=> $row['yahoo'],
	'ICQ'				=> $row['icq'],
	'I_YAHOO'			=> $img['yahoo'],
	'I_GTALK'			=> $img['gtalk'],
	'I_MSN'				=> $img['msn'],
	'I_ICQ'				=> $img['icq'],
	'SITE_WEB'			=> $row['site_web'],
	'JOUR'				=> lister_chiffres(1,31,$date_naissance[2]),
	'MOIS'				=> lister($lang['mois'],$date_naissance[1]),
	'ANNEE'				=> lister_chiffres(1920,date('Y'),$date_naissance[0]),
	'ETAT_CIVIL'		=> lister($liste_etats_civils,$row['etat_civil']),
	'FUSEAU'			=> lister($liste_fuseaux_horaires,$row['fuseau']),
	'SEXE'				=> lister($liste_sexes,$row['sexe']),
	'LOCALISATION'		=> $row['localisation'],
	'RANGS'				=> $rangs,
	'TAILLE_UTILISEE'	=> sprintf($lang['L_TAILLE_UTILISEE_FORMATEE'],round($image->dirsize($dir_avatars.$user_id.'/')/1024,2),round($cf->config['avatar_taille_rep']/1024,2), floor(round($image->dirsize($dir_avatars.$user_id.'/')/1024,2)*100/round($cf->config['avatar_taille_rep']/1024,2))),
	'L_INFOS_AVATAR'	=> sprintf($lang['L_INFOS_AVATAR'],$cf->config['avatar_largeur_max'],$cf->config['avatar_hauteur_max'],round($cf->config['avatar_taille_max']/1024,2),implode(', ',$image->ext_ok)),


	'TAILLE_MAX'		=> $cf->config['avatar_taille_max'],
	'L_MAX'				=> $cf->config['avatar_largeur_max']+10,
	'H_MAX'				=> $cf->config['avatar_hauteur_max']+40
));

//
// AVATARS

$image->lister_images_dir($dir_avatars.$user_id.'/');
$tpl->assign_vars(array(
	'I_DELETE'			=> $img['effacer']
));

		
//
// Titre de page 
$tpl->titre_navigateur = $tpl->titre_page = $row['pseudo'];

// Navlinks
$session->make_navlinks(sprintf($lang['L_LIBELLE_PROFIL'],$row['pseudo']),formate_url('user_id='.$row['user_id'],true));

// On charge le wysiwyg
if ($cf->config['wysiwyg_editor']!='') include_once($root.'fonctions/fct_'.$cf->config['wysiwyg_editor'].'.php');

?>
