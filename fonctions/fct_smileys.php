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
// Rafraichir le cache pour voir le changement de nombre de colonnes
global $nbre_cols;
$nbre_cols = 6;

// Chargement des smileys
function creer_cache_emotions(){
	global $root,$tpl,$nbre_cols,$nbre_lignes,$c,$lang;
	load_lang('smileys');
	$tpl->set_filenames(array('smileys' => $root.'html/liste_smileys.html'));

	$sql = 'SELECT titre_smiley, url_smiley  FROM '.TABLE_SMILEYS.' ORDER BY ordre ASC';
	if (!$resultat = $c->sql_query($sql)) message_die(E_ERROR,1101,__FILE__,__LINE__,$sql);
	$cols=$lignes=0	;
	while($row = $c->sql_fetchrow($resultat))
	{
		if ($cols%$nbre_cols == 0){
			$tpl->assign_block_vars('ligne', array());
			$cols = 0;
		}
		$tpl->assign_block_vars('ligne.colonne', array(
			'TITRE'		=> utf8_encode(str_replace("'","\'",str_replace("&#039;","'",$row['titre_smiley']))),
			'IMAGE'		=> PATH_SMILEYS.$row['url_smiley']
		));
		$cols++;
	}
	$tpl->assign_vars(array(
		'TITRE_PAGE'	=> utf8_encode($lang['TITRE_PAGE'])
	));
	
	$tpl->pparse('smileys',true);
	return $tpl->buffer;
}

?>
