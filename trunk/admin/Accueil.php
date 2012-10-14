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
if ( !defined('PROTECT_ADMIN') )
{
	die("Tentative de Hacking");
}
//Rechargement des versions
$reload_module = (isset($_GET['reload_module']))?intval($_GET['reload_module']):0;

$url_check_version_malleo = 'http://www.malleo-cms.com/versions/malleo.txt';
load_lang('accueil');

$version_dir = $root.'data/versions';
if(!is_dir($version_dir)) mkdir($version_dir, 0777);
if ($reload_module == 1){
	if (($retour = fsockopen_file_get_contents($url_check_version_malleo)) != false){
		file_put_contents($version_dir.'/malleo.txt', $retour);
	}
}

$version_malleo = '- ? -';

if(is_file($version_dir.'/malleo.txt')) $version_malleo = file_get_contents($version_dir.'/malleo.txt');

$path_blocnotes = 'data/blocnotes_admin.php';
if (isset($_POST['blocnotes']))
{
	$file = @fopen($path_blocnotes, 'w+');
	@fwrite($file, stripslashes($_POST['blocnotes']));
	@fclose($file);
	header('location: '.$base_formate_url);
	exit;
}
$notes = (file_exists($path_blocnotes))? @file_get_contents($path_blocnotes):'';

include_once($root.'class/class_posting.php');
$post=new posting();


$tpl->set_filenames(array('body_admin' => $root.'html/admin_accueil.html'));

// Alerte mdp digicode jamais changé
if ($cf->config['activer_digicode']==1 && $cf->config['digicode_acces_zone_admin'] == '0000') $tpl->assign_block_vars('alerte_digicode', array());

// Alerte version malleo obsolete
if ($version_malleo != '- ? -' && $cf->config['version_cms'] < $version_malleo) $tpl->assign_block_vars('alerte_version_malleo', array());

// MODULES installes
$sql = 'SELECT id_module,module,virtuel,p.version 
		FROM '.TABLE_MODULES.' as m 
		LEFT JOIN '.TABLE_PLUGINS.' as p
			ON (m.module=p.plugin)
		WHERE virtuel is NULL
		ORDER BY m.virtuel ASC, m.module ASC';
if (!$resultat=$c->sql_query($sql)) message_die(E_ERROR,17,__FILE__,__LINE__,$sql);

while($row = $c->sql_fetchrow($resultat))
{
	$file = $root.'plugins/modules/'.$row['module'].'/infos.xml';
	$version_dir = $root.'data/versions/'.$row['module'];
	if(!is_dir($version_dir)) mkdir($version_dir, 0777);
	if (file_exists($file)){
		$xml = simplexml_load_file($file);
		
		// Check version
		$version_officielle = '- ? -';
		if (!empty($xml->check_version) AND $reload_module == 1){
			if (($retour = fsockopen_file_get_contents($xml->check_version)) != false){
				file_put_contents($version_dir.'/version.txt', $retour);
			}
		}
		if(is_file($version_dir.'/version.txt')) $version_officielle = file_get_contents($version_dir.'/version.txt');
		
		
		// version actuelle
		// affiche un lien UPDATE en cas de maj disponible
		$version_actuelle = ($row['version'] < $xml->version)? '':$row['version'];
		$tpl->assign_block_vars('liste_modules', array(
			'TITRE_MODULE'		=> $xml->libelle,
			'AUTEUR'			=> $xml->auteur,
			'SITE'				=> $xml->site_auteur,
			'VERSION_OFFICIELLE'=> $version_officielle,
			'VERSION_ACTUELLE'	=> $row['version'],
			'U_LIEN_UPGRADE'	=> formate_url('admin.php?module=admin/Modules.php&action=update&plugin='.$row['module'])
		));	
		if ($row['version'] < $xml->version)$tpl->assign_block_vars('liste_modules.update', array());
	}
}


// Equipe
$sql = 'SELECT DISTINCT(u.user_id), u.pseudo, u.level, s.date_lastvisite  
		FROM '.TABLE_USERS.' as u
		LEFT JOIN '.TABLE_SESSIONS.' as s
			ON (u.user_id=s.user_id)
		WHERE u.level > 8
		ORDER BY s.date_lastvisite DESC, u.level DESC, u.pseudo DESC';
if (!$resultat=$c->sql_query($sql)) message_die(E_ERROR,17,__FILE__,__LINE__,$sql);
$f = $a = 0;
$equipe=array();
while($row = $c->sql_fetchrow($resultat))
{
	if (!in_array($row['user_id'],$equipe)){
		$equipe[]= $row['user_id'];
		if ($row['level'] == 9){
			$tpl->assign_block_vars('admins', array(
				'PSEUDO'		=> formate_pseudo($row['user_id'],$row['pseudo']),
				'DATE_VISITE'	=> ($row['date_lastvisite']!=0)?formate_date($row['date_lastvisite'],'d m Y H i','FORMAT_DATE',$user['fuseau']):$lang['L_AUCUNE_SESSION_RECENTE'],
			));
			$a++;
		}else{
			$tpl->assign_block_vars('fondateurs', array(
				'PSEUDO'		=> formate_pseudo($row['user_id'],$row['pseudo']),
				'DATE_VISITE'	=> ($row['date_lastvisite']!=0)?formate_date($row['date_lastvisite'],'d m Y H i','FORMAT_DATE',$user['fuseau']):$lang['L_AUCUNE_SESSION_RECENTE'],
			));
			$f++;
		}
	}
}
if ($f==0)$tpl->assign_block_vars('aucun_fondateur', array());
if ($a==0)$tpl->assign_block_vars('aucun_administrateur', array());

// Bloc Notes
if (isset($_GET['action']) && $_GET['action'] == 'edition'){
	$tpl->assign_block_vars('afficher_saisie', array());
}else{
	$tpl->assign_block_vars('afficher_notes', array());
}

$tpl->assign_vars(array(
	'VERSION_MYSQL'				=>	mysql_get_client_info(),
	'VERSION_PHP'				=>	phpVersion(),
	'VERSION_APACHE'			=>	(function_exists('apache_get_version'))?apache_get_version():$lang['L_INDISPONIBLE'],
	'VERSION_ACTUELLE_CMS'		=>	$cf->config['version_cms'],
	'NOTES'						=>	$notes,
	'NOTES_FORMATEES'			=>	$post->bbcode2html($notes),
	'U_EDIT_BLOC_NOTES'			=>	formate_url('action=edition',true),
	
	'NOUVELLE_VERSION_CMS_MALLEO'=> sprintf($lang['L_NOUVELLE_VERSION_CMS_MALLEO'],$cf->config['version_cms'],$version_malleo),

	'I_REFRESH'					=>	$img['refresh'],
	'I_EDIT'					=>	$img['editer'],
	'REALOAD_LINK'				=>	formate_url('admin.php?module=admin/Accueil.php&reload_module=1')
));

?>
