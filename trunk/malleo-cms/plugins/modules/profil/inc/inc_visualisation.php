<?php
if ( !defined('PROTECT') )
{
	die("Tentative de Hacking");
}

$tpl->set_filenames(array('profil' => $root.'plugins/modules/profil/html/module_profil.html'));
		
$sql = 'SELECT u.*, DATE_FORMAT(FROM_DAYS(TO_DAYS(NOW())-TO_DAYS(u.date_naissance)), "%Y") as age, s.date_lastvisite 
		FROM '.TABLE_USERS.' as u 
		LEFT JOIN '.TABLE_SESSIONS.' as s
			ON (u.user_id=s.user_id)
		WHERE u.user_id='.$user_id.' 
		ORDER BY date_lastvisite DESC LIMIT 1';
if (!$resultat = $c->sql_query($sql)) message_die(E_ERROR,1100,__FILE__,__LINE__,$sql); 
$row = $c->sql_fetchrow($resultat);

$date_naissance = explode('-',$row['date_naissance']);

$tpl->assign_vars(array(
	'PSEUDO'		=> $row['pseudo'],
	'AVATAR'		=> $row['avatar'],
	'SITE_WEB'		=> url_cliquable($row['site_web']),
	'SIGNATURE'		=> $post->bbcode2html($row['signature']),
	'L_MEMBER_SINCE'=> $lang['L_MEMBER_SINCE'],
	'DATE_MEMBER'	=> date('d/m/Y',$row['date_register']),
	'S_YAHOO'		=> $image->get_image($row['yahoo']),
	'S_GTALK'		=> $image->get_image($row['gtalk']),
	'S_MSN'			=> $image->get_image($row['msn']),
	'S_ICQ'			=> $image->get_image($row['icq']),
	'I_YAHOO'		=> $img['yahoo'],
	'I_GTALK'		=> $img['gtalk'],
	'I_MSN'			=> $img['msn'],
	'I_ICQ'			=> $img['icq'],
	'I_MAIL'		=> $img['mail'],
	'I_MP'			=> $img['mp'],
	'I_EDITER'		=> $img['editer'],
	'RANG'			=> formate_rang($row['rang'],$row['msg']),
	'LANGUE'		=> formate_langue($row['langue']),
	'POINTS'		=> $row['points'],
	'ETAT_CIVIL'	=> ($row['etat_civil']==0)?$lang['L_NON_RENSEIGNE']:formate_etat_civil($row['etat_civil']),
	'LOCALISATION'	=> ($row['localisation']=='')?$lang['L_NON_RENSEIGNE']:$row['localisation'],
	'MESSAGES'		=> $row['msg'],
	'SEXE'			=> ($row['sexe']==0)?$lang['L_NON_RENSEIGNE']:formate_sexe($row['sexe']),
	'DATE_NAISSANCE'=> ($row['date_naissance']=='0000-00-00')?$lang['L_NON_RENSEIGNE']:sprintf($lang['L_DATE_NAISSANCE_AGE'],$date_naissance[2],$date_naissance[1],$date_naissance[0],floor($row['age'])),
	'LAST_VISITE'	=> ($row['date_lastvisite']>0)?date('d/m/Y',$row['date_lastvisite']):$lang['L_NON_RENSEIGNE'],
	'S_EDIT_CONFIG'	=> formate_url('user_id='.$user_id.'&mode=configuration',true),
	'S_MP'			=> formate_url('index.php?module=messagerie&mode=newmp&a='.utf8_encode(html_entity_decode($row['pseudo']))),
	'S_MAIL'		=> formate_url('index.php?module=messagerie&mode=newmail&a='.utf8_encode(html_entity_decode($row['pseudo'])))
));


if ($row['yahoo']!='')	$tpl->assign_block_vars('yahoo', array());
if ($row['msn']!='')	$tpl->assign_block_vars('msn', array());
if ($row['icq']!='')	$tpl->assign_block_vars('icq', array());
if ($row['gtalk']!='')	$tpl->assign_block_vars('gtalk', array());
if ($row['site_web']!='')	$tpl->assign_block_vars('site_web', array());


if ($user['user_id']==$user_id || $user['level']>=9) $tpl->assign_block_vars('droit_edition', array());

//
// Titre de page 
$tpl->titre_navigateur = $tpl->titre_page = $row['pseudo'];
$tpl->meta_description = $row['pseudo'];
if(!empty($row['rang']))				$tpl->meta_description .= ' '. formate_rang($row['rang'],$row['msg']);
if($row['etat_civil']!=0)				$tpl->meta_description .= ' '. formate_etat_civil($row['etat_civil']);
if($row['sexe']!=0)						$tpl->meta_description .= ' '. formate_sexe($row['sexe']);
if(!empty($row['localisation']))		$tpl->meta_description .= ' '. $row['localisation'];
if(!empty($row['site_web']))			$tpl->meta_description .= ' '. url_cliquable($row['site_web']);
if($row['date_naissance']!='0000-00-00')$tpl->meta_description .= ' '. sprintf($lang['L_DATE_NAISSANCE_AGE'],$date_naissance[2],$date_naissance[1],$date_naissance[0],floor($row['age']));
if(!empty($row['signature']))			$tpl->meta_description .= ' '. $post->bbcode2html($row['signature']);

// Navlinks
$session->make_navlinks(sprintf($lang['L_LIBELLE_PROFIL'],$row['pseudo']),formate_url('user_id='.$row['user_id'],true));

//
// Affichage des Categories existantes
$profil->clean($_GET);
$profil->lecture_profil();
foreach($profil->cat as $id_cat=>$val)
{
	$tpl->assign_block_vars('categories_profil', array(
		'CATEGORIE'		=> $val['titre_cat'],
		'TEXTE'			=> $post->bbcode2html(($val['texte']=='')?(($val['modele']=='')?$lang['L_AUCUNE_INFO_SAISIE']:$val['modele']):$val['texte']),
		'S_EDIT_CAT'	=> formate_url('user_id='.$user_id.'&mode=categorie&id_cat='.$id_cat,true)
	));
	if ($user['user_id']==$user_id || $user['level']>=9) $tpl->assign_block_vars('categories_profil.droit_edition', array());
}
?>