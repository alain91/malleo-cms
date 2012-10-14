<?php
if (!defined('PROTECT'))
{
	die("Tentative de Hacking");
}
global $root;
// Si le fichier config n'est pas present on le cree
$fichier = $root.'config/config.php';
if (!file_exists($fichier) || (filesize($fichier) == 0)){
	header('location: index.php?etape=3');
}

// Initialisation de la connexion SQL
include_once($root.'config/config.php');
include_once($root.'config/constantes.php');
require_once($root.'class/class_mysql.php');		
$c = new sql_db($hote, $utilisateur, $password, $base, false);
if(!$c->db_connect_id)
{
	die("Impossible de se connecter à la base de données");
}

// RECHERCHE des tables existantes
$tables_existantes = array();
$sql = 'SHOW TABLES';
$resultat = $c->sql_query($sql); 
while($row = $c->sql_fetchrow($resultat)){
	$table = $row[key($row)];
	if (preg_match('/^'.$prefixe.'/',$table)) $tables_existantes[] = $table;
}
// Parametres d'adresse
unset($param);
$param['ADRESSE'] = $_SERVER['HTTP_HOST'];
$param['PATH'] = preg_replace('/install\/index.php/i','',$_SERVER['SCRIPT_NAME']);


// CHARGEMENT des REQUETES
unset($requetes);
global $requetes;
if (sizeof($tables_existantes) == 0 || !in_array($prefixe.'config',$tables_existantes)){
	// Nouvelle installation
	include_once($root.'install/versions/version_complete.php');
}else{
	// Recherche de la version courante
	$sql = 'SELECT valeur AS version_cms FROM '.$prefixe.'config WHERE data="version_cms" LIMIT 1';
	$resultat = $c->sql_query($sql); 
	if ($c->sql_numrows($resultat)>0){
		$row = $c->sql_fetchrow($resultat);
		if (file_exists($root.'install/versions/version_'.$row['version_cms'].'.php')){
			// Chargement des requetes de MAJ
			include_once($root.'install/versions/version_'.$row['version_cms'].'.php');
		}
	}
}

// EXECUTION des requetes
if (sizeof($requetes)==0){
	$tpl->assign_block_vars('aucune_requete', array());
}else{
	foreach($requetes as $key=>$req){
		$liste = (!($resultat = $c->sql_query($req))) ? 'liste_tables_nok':'liste_tables_ok';
		$erreur = $c->sql_error();
		$tpl->assign_block_vars($liste, array(
			'TABLE'		=> $key,
			'MESSAGE'	=> $erreur['message'],
			'SQL'		=> $req
		));
		if ($erreur['message']!='') $tpl->assign_block_vars($liste.'.erreur', array());
	}
}


$tpl->assign_vars(array(
	'L_EXPLAIN_CREATION_TABLES'		=> $lang['L_EXPLAIN_CREATION_TABLES'],
	'L_LISTE_TABLES_CREEES'			=> $lang['L_LISTE_TABLES_CREEES'],
	'L_AUCUNE_REQUETE'				=> $lang['L_AUCUNE_REQUETE'],
	'VALIDE'						=> $img['valide'],
	'INVALIDE'						=> $img['invalide']
));
?>
