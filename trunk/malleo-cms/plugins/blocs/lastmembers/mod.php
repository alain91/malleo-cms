<?php
if ( !defined('PROTECT') )
{
	die("Tentative de Hacking");
}

if (!function_exists('formate_sexe')) include_once($root.'fonctions/fct_profil.php');

// Chargement du fichier de langue
load_lang_bloc('lastmembers');

// Chargement du template
$tpl->set_filenames(array(
   'lastmembers' => $root.'plugins/blocs/lastmembers/html/lastmembers.html')
);

// Définition du cache
$cache->files_cache['lastmembers'] = array($root.'cache/data/lastmembers','return cache_lastmembers();',1800);

$nbre_members = '10';

function cache_lastmembers()
{
	global $c, $nbre_members;
	//Nombre de membres à afficher
	$sql = 'SELECT DISTINCT user_id, pseudo, date_register, sexe 
		FROM '.TABLE_USERS.' 
		WHERE user_id > 1
		ORDER by date_register DESC 
		LIMIT '.$nbre_members.'';		
	if (!$resultat = $c->sql_query($sql)) message_die(E_ERROR,17,__FILE__,__LINE__,$sql); 
	$lastmembers = array();
	while($row = $c->sql_fetchrow($resultat))
	{
		$lastmembers[] = $row;
	
	}	
	return $lastmembers;
}	

$last_members->last_members = $cache->appel_cache('lastmembers');
	
foreach($last_members->last_members as $key=>$row)
{
	$tpl->assign_block_vars('inscrit', array(
		'PSEUDO' => formate_pseudo($row['user_id'],$row['pseudo']),
		'DATE_REGISTER' => date('d/m/Y',$row['date_register']),	
		'SEXE' => ($row['sexe']==0)?'':formate_sexe($row['sexe'])
	));
}

$tpl->assign_vars(array(
	'L_LAST_MEMBERS'	=> sprintf($lang['L_LAST_MEMBERS'], $nbre_members)
));
	
?>