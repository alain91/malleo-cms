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
if ( !defined('PROTECT') )
{
	die("Tentative de Hacking");
}
global $tpl,$cf,$cache,$root,$lang;
load_lang_bloc('whoisonline');

function lister_whoisonline_groupes()
{
	global $c;
	$sql = 'SELECT g.group_id, g.titre, g.couleur
		FROM '.TABLE_GROUPES.' AS g
		WHERE type=1 
		AND visible is true
		AND group_id>3 
		ORDER BY ordre ASC, titre ASC';
	if (!$resultat = $c->sql_query($sql)) message_die(E_ERROR,17,__FILE__,__LINE__,$sql); 
	$groupes = array();
	while($row = $c->sql_fetchrow($resultat))
	{
		$groupes[] = $row;
	}		
	return $groupes;
}
function lister_whoisonline()
{
	global $c,$bots;
	$liste['online'] = $liste['bots_online'] = $liste['bots_today'] = $liste['today'] = '';
	$liste['invites_online'] = $liste['invites_today'] = 0;
	// temps timestamp d'il y a 15 minutes
	$online = (time() -  15*60);
	// temps il y a 24 H
	$vingtquatre = (time()-86400);

	$sql = 'SELECT u.user_id, u.pseudo, s.date_lastvisite, s.pseudo as bot
			FROM '.TABLE_SESSIONS.' AS s 
			LEFT JOIN  '.TABLE_USERS.' AS u 
				ON (s.user_id=u.user_id) 
			WHERE s.date_lastvisite >= '.$vingtquatre.'
			ORDER BY u.pseudo ASC,s.date_lastvisite DESC';

	if (!$resultat = $c->sql_query($sql)) message_die(E_ERROR,17,__FILE__,__LINE__,$sql); 
	if ($c->sql_numrows($resultat) > 0 )
	{
		$users_online = array();
		$bots_online = array();
		while($row = $c->sql_fetchrow($resultat))
		{
			if (!in_array($row['pseudo'],$users_online) 
			|| ($row['user_id']==1 && !empty($row['bot']) && !in_array($row['bot'],$bots_online) 
			|| ($row['user_id']==1 && empty($row['bot']))))
			{
				$users_online[] = $row['pseudo'];
				$bots_online[] = $row['bot'];
				
				// Connecté en ce moment
				if ($row['date_lastvisite'] > $online)
				{
					if ($row['user_id'] == 1 && $row['bot']!='')
					{
						if ($liste['bots_online'] != '') $liste['bots_online'] .= ", ";
						$liste['bots_online'] .= $bots->formate_bots($row['bot']);
					}
					elseif ($row['user_id'] == 1)
					{
						$liste['invites_online']++;
					}else{
						if ($liste['online'] != '') $liste['online'] .= ", ";
						$liste['online'] .= formate_pseudo($row['user_id'],$row['pseudo']);
					}
				}
				// Connecté dans les dernières 24H
				if ($row['user_id'] == 1 && $row['bot']!='')
				{
					if ($liste['bots_today'] != '') $liste['bots_today'] .= ", ";
					$liste['bots_today'] .= $bots->formate_bots($row['bot']);
				}
				elseif ($row['user_id'] == 1)
				{
					$liste['invites_today']++;
				}else{
					// depuis minuit
					if ($liste['today'] != '') $liste['today'] .= ", ";
					$liste['today'] .= formate_pseudo($row['user_id'],$row['pseudo']);
				}
			}
		}
	}
	if ($liste['bots_today'] != '') $liste['today'] .= ", ";
	$liste['today'] .= $liste['bots_today'];
	if ($liste['bots_online'] != '') $liste['online'] .= ", ";
	$liste['online'] .= $liste['bots_online'];
	return $liste;
}

// Appel du cache 
// 3 minutes histoire d'alléger le serveur aux moment d'affluence, ce chiffre peut être augmetné mais pas au delà de 15 minutes au risque de perdre trop de données.
$cache->files_cache['whoisonline_liste'] =	array($root.'cache/data/whoisonline',		 'return lister_whoisonline();', $cf->config['cache_duree_whoisonline']);
$cache->files_cache['whoisonline_groupes'] =array($root.'cache/data/whoisonline_groupes', 'return lister_whoisonline_groupes();', $cf->config['cache_duree_whoisonline_groupe']);

// Formatage des donnees necessitant des clefs de langue. On ne peut pas les mettre en cache
$liste = $cache->appel_cache('whoisonline_liste');
$s	= ($liste['invites_online']>1)?'s':'';
$st	= ($liste['invites_today']>1)?'s':'';
if ($liste['invites_online']>0)
{	
	if ($liste['online'] != '') $liste['online'] .= ', ';
	$liste['online'] .= sprintf($lang['L_INVITES'],$liste['invites_online'],$s);
}
if ($liste['invites_today']>0)
{
	if ($liste['today'] != '') $liste['today'] .= ', ';
	$liste['today'] .= sprintf($lang['L_INVITES'],$liste['invites_today'],$st);
}
if (trim($liste['online']) == '')	$liste['online'] = $lang['L_PERSONNE'];
if (trim($liste['today']) == '')	$liste['today'] = $lang['L_PERSONNE'];


$tpl->set_filenames(array('whoisonline' => $root.'plugins/blocs/whoisonline/html/mod_whoisonline.html'));
// Liste des Groupes 
$groupes = $cache->appel_cache('whoisonline_groupes');
if (sizeof($groupes)>0){
	$tpl->assign_block_vars('afficher_legende', array());
	foreach ($groupes as $key=>$val)
	{
		$tpl->assign_block_vars('liste_groupes', array(
			'STYLE'	=> ($val['couleur']!='')? ' style="color:'.$val['couleur'].';"':'',
			'LIEN'	=>	formate_url('index.php?module=membres&action=groupe&groupe='.$val['group_id']),
			'GROUPE'=>	$val['titre']
		));
	}
}
// en ligne
$nbre_membres_online = substr_count($liste['online'],'class="pseudo"');
$s_nbre_membres_online	= ($nbre_membres_online>1)?'s':'';
$nbre_bots_online = substr_count($liste['online'],'class="bot"');
$s_nbre_bots_online	= ($nbre_bots_online>1)?'s':'';

$s_nbre_invites_online	= ($liste['invites_online']>1)?'s':'';

// aujourd'hui
$nbre_membres_today = substr_count($liste['today'],'class="pseudo"');
$s_nbre_membres_today	= ($nbre_membres_today>1)?'s':'';
$nbre_bots_today = substr_count($liste['today'],'class="bot"');
$s_nbre_bots_today	= ($nbre_bots_today>1)?'s':'';

$s_nbre_invites_today	= ($liste['invites_today']>1)?'s':'';

$tpl->assign_vars(array(
	'NBRE_ONLINE'	=> sprintf($lang['L_NBRE_EN_LIGNE'],$nbre_membres_online,$s_nbre_membres_online,$nbre_bots_online,$s_nbre_bots_online,$liste['invites_online'],$s_nbre_invites_online),
	'NBRE_TODAY'	=> sprintf($lang['L_NBRE_EN_LIGNE'],$nbre_membres_today,$s_nbre_membres_today,$nbre_bots_today,$s_nbre_bots_today,$liste['invites_today'],$s_nbre_invites_today),
	'TODAY'			=> $liste['today'],
	'ONLINE'		=> $liste['online'],
));


?>