<?php

if ( !defined('PROTECT_ADMIN') )
{
	die("Tentative de Hacking");
}
load_lang('traceur');
$tpl->set_filenames(array(
	'body_admin' => $root.'html/admin_traceur.html'
));
function str_court($string){
	if(strlen($string)>=40){
		$string = substr($string, 0, 37).'...';
	}
	return $string;
}

function affiche_activite($time_min, $time_max, $pseudo){
	global $c,$tpl,$user,$cf;
	$tpl->assign_block_vars('activite', array());
	$sql = 'SELECT user_id,pseudo,url_page,libelle_page,date FROM '.TABLE_SESSIONS_SUIVIES;
	if ($time_min != null) 	$sql_array[] = ' date > '.$time_min;
	if ($time_max != null) 	$sql_array[] = ' date < '.$time_max;
	if ($pseudo != null) 	$sql_array[] = ' pseudo LIKE \'%'.$pseudo.'%\'';
	if (isset($sql_array)) $sql .= ' WHERE '.implode(' AND ',$sql_array);
	$sql .= ' ORDER BY DATE desc';
	if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,69,__FILE__,__LINE__,$sql);
	if ($c->sql_numrows($resultat) == 0){
		$tpl->assign_block_vars('activite.aucune_activite', array());
	}else{
		$i=0;
		while($row = $c->sql_fetchrow($resultat))
		{
			$couleur = ($i % 2) ? "row1" : "row2" ; 
			$i++;
			$tpl->assign_block_vars('activite.liste_activites', array(
				'CLASS'					=> $couleur,
				'PSEUDO'				=> formate_pseudo($row['user_id'],$row['pseudo']),
				'LIBELLE_PAGE'			=> $row['libelle_page'],
				'URL_PAGE'				=> formate_url($row['url_page']),
				'URL_PAGE_COURT'		=> str_court(eregi_replace($cf->config['path'].'index.php\?','',$row['url_page'])),
				'DATE'					=> formate_date($row['date'],'d m H i','FORMAT_DATE_TRACEUR',$user['fuseau']),
			));
		}
	}
}

// Parametres
if (isset($_POST['intervalle']) || isset($_GET['intervalle']))
{
	$intervalle = (isset($_POST['intervalle']))? $_POST['intervalle']:$_GET['intervalle'] ;
}else{
	$intervalle = 900;
}
$time_min = $session->time - $intervalle;
$time_max = $session->time;
if (isset($_GET['time_max']))$time_max =  intval($_GET['time_max']);
if (isset($_GET['time_min']))$time_min =  intval($_GET['time_min']);

//
// AFFICHAGE
// Si le traceur est désactivé on avertis
if ($cf->config['activer_traceur'] == 0){
	erreur_saisie('erreur_saisie',$lang['ALERTE_TRACEUR_DESACTIVE']);
}

$mode = null;
if (isset($_POST['mode']) || isset($_GET['mode']))
{
	$mode = (isset($_POST['mode']))? $_POST['mode']:$_GET['mode'] ;
}
switch ($mode)
{
	case 'purge':
		if (isset($_GET['purge'])){
			$sql = 'DELETE FROM '.TABLE_SESSIONS_SUIVIES
				.' WHERE date '.urldecode($_GET['purge']);
			if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,69,__FILE__,__LINE__,$sql);
		}
		// Logs de + de 2 mois
		$sql = 'SELECT count(url_page) AS cpt FROM '.TABLE_SESSIONS_SUIVIES
			.' WHERE date < '.($session->time - 5270400);
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,69,__FILE__,__LINE__,$sql);
		$row = $c->sql_fetchrow($resultat);
		$DeuxMois = $row['cpt'];
		// Logs de + de 1 mois
		$sql = 'SELECT count(url_page) AS cpt FROM '.TABLE_SESSIONS_SUIVIES
			.' WHERE date < '.($session->time - 2678400);
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,69,__FILE__,__LINE__,$sql);
		$row = $c->sql_fetchrow($resultat);
		$UnMois = $row['cpt'];
		// Logs du mois
		$sql = 'SELECT count(url_page) AS cpt FROM '.TABLE_SESSIONS_SUIVIES
			.' WHERE date > '.($session->time - 2678400);
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,69,__FILE__,__LINE__,$sql);
		$row = $c->sql_fetchrow($resultat);
		$Mois = $row['cpt'];
		
		$tpl->assign_block_vars('purge', array());
		$tpl->assign_vars(array(
			'DEUX_MOIS'	=> $DeuxMois,
			'UN_MOIS'	=> $UnMois,
			'MOIS'		=> $Mois,
			'U_DEUX_MOIS'	=> formate_url('mode=purge&purge='.urlencode('< '.($session->time - 5270400)),true),
			'U_UN_MOIS'	=> formate_url('mode=purge&purge='.urlencode('< '.($session->time - 2678400)),true),
			'U_MOIS'	=> formate_url('mode=purge&purge='.urlencode('> '.($session->time - 2678400)),true),
		));
		break;
	case 'populaires':
		// Affichage des resultats
		$tpl->assign_block_vars('liste_pages', array());
		
		// Pseudos trouves
		$sql = 'SELECT libelle_page,url_page,count(url_page) AS cpt_pages FROM '.TABLE_SESSIONS_SUIVIES
			.' GROUP BY url_page ORDER BY cpt_pages DESC LIMIT 50';
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,69,__FILE__,__LINE__,$sql);
		if ($c->sql_numrows($resultat) == 0){
			$tpl->assign_block_vars('liste_pages.aucune_page', array());
		}else{
			$i=0;
			while($row = $c->sql_fetchrow($resultat))
			{
				$couleur = ($i % 2) ? "row1" : "row2" ; 
				$i++;
				$tpl->assign_block_vars('liste_pages.pages', array(
					'CLASS'					=> $couleur,
					'LIBELLE_PAGE'			=> $row['libelle_page'],
					'URL_PAGE'				=> formate_url($row['url_page']),
					'URL_PAGE_COURT'		=> str_court($row['url_page']),
					'NBRE_PAGES'			=> $row['cpt_pages'],
				));
			}
		}
		break;
	case 'suivre':
		// pseudo en parametre
		$pseudo = urldecode($_GET['pseudo']);
		$mode = 'suivre&intervalle='.$intervalle.'&pseudo='.urlencode($pseudo);
		
		// titre 
		$tpl->assign_block_vars('activite_de', array());
		$tpl->assign_vars(array('ACTIVITE_DE'	=> sprintf($lang['L_ACTIVITE_DE'],$pseudo)));
		
		// activité du pseudo sélectionné
		affiche_activite($time_min, $time_max, $pseudo);
		break;
	case 'chercher_pseudo' :
		// Affichage des resultats
		$tpl->assign_block_vars('liste_pseudos', array());
		
		// Pseudos trouves
		$sql = 'SELECT user_id,pseudo,count(url_page) AS cpt_pages FROM '.TABLE_SESSIONS_SUIVIES
			.' WHERE pseudo LIKE \'%'.str_replace('\'',"''",protection_chaine($_POST['pseudo'])).'%\''
			.' GROUP BY pseudo ORDER BY pseudo ASC LIMIT 20';
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,69,__FILE__,__LINE__,$sql);
		if ($c->sql_numrows($resultat) == 0){
			$tpl->assign_block_vars('liste_pseudos.aucun_pseudo', array());
		}else{
			$i=0;
			while($row = $c->sql_fetchrow($resultat))
			{
				$couleur = ($i % 2) ? "row1" : "row2" ; 
				$i++;
				$tpl->assign_block_vars('liste_pseudos.pseudos', array(
					'CLASS'					=> $couleur,
					'PSEUDO'				=> formate_pseudo($row['user_id'],$row['pseudo']),
					'NBRE_PAGES'			=> sprintf($lang['L_PAGES'],$row['cpt_pages']),
					'SUIVRE_PSEUDO'			=> formate_url('mode=suivre&intervalle='.$intervalle.'&pseudo='.urlencode($row['pseudo']),true)
				));
			}
		}
		break;
	case 'cherche':
		// Proposition de rechercher
		$tpl->assign_block_vars('chercher_pseudo', array());	
		break;
		
	case '15dernieres':
	default :
		// Activité globale dernières minutes
		affiche_activite($time_min, $time_max, null);
		$mode = '15dernieres&intervalle='.$intervalle;
		break;
}

$navlinks_time_apres_min = $time_max;
$navlinks_time_apres_max = $time_max + $intervalle;

$navlinks_time_avant_min = $time_min - $intervalle;
$navlinks_time_avant_max = $time_min;

$tpl->assign_vars(array(
	'SELECT_'.$intervalle=> ' selected="selected"',
	'U_CHERCHER_PSEUDO'	=> formate_url('mode=cherche',true),
	'U_15_DERNIERES'	=> formate_url('mode=15dernieres',true),
	'U_PAGES_POPULAIRES'=> formate_url('mode=populaires',true),
	'U_PURGE_ACTIVITES'	=> formate_url('mode=purge',true),
	
	'PERIODE_AFFICHEE'	=> sprintf($lang['L_PERIODE_AFFICHEE'],formate_date($time_min,'d m H i','FORMAT_DATE_TRACEUR',$user['fuseau']),formate_date($time_max,'d m H i','FORMAT_DATE_TRACEUR',$user['fuseau'])),
	'APRES'				=> sprintf($lang['L_PERIODE'],formate_date($navlinks_time_apres_min,'d m H i','FORMAT_DATE_TRACEUR',$user['fuseau']),formate_date($navlinks_time_apres_max,'d m H i','FORMAT_DATE_TRACEUR',$user['fuseau'])),
	'AVANT'				=> sprintf($lang['L_PERIODE'],formate_date($navlinks_time_avant_min,'d m H i','FORMAT_DATE_TRACEUR',$user['fuseau']),formate_date($navlinks_time_avant_max,'d m H i','FORMAT_DATE_TRACEUR',$user['fuseau'])),
	'U_APRES'			=> formate_url('mode='.$mode.'&time_min='.$navlinks_time_apres_min.'&time_max='.$navlinks_time_apres_max,true),
	'U_AVANT'			=> formate_url('mode='.$mode.'&time_min='.$navlinks_time_avant_min.'&time_max='.$navlinks_time_avant_max,true),
	
	'I_SUPPR'			=> $img['effacer'],
	'I_EDIT'			=> $img['editer'],
	'I_ACTIVER'			=> $img['valide'],
	'I_DESACTIVER'		=> $img['invalide']
));

?>