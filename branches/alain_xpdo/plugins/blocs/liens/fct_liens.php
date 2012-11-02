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
//
// Renvoit l'image corrigée

function corriger_image_liens($image,$titre)
{
	global $root;
	if ($image != '') 
	{
		if (!preg_match('/http:\/\//i',$image)) $image = $root.$image; 
		$image = '<img src="'.$image.'" border="0" alt="'.$titre.'" title="'.$titre.'" align="center" />';
	}else{
		$image= '';
	}
	return $image;
}

//
// Crée la mise en cache du contenu du mod lien.

function creer_cache_mod_lien()
{
	global $tpl,$cf,$c,$lang,$root;
	$tpl->set_filenames(array(
	      'cache_liens' => $root.'plugins/blocs/liens/html/mod_cache_liens.html'
	));
	$ordre = ($cf->config['liens_aleatoire'] == '1')? ' RAND() ' : ' ordre ASC ';
	$limit = ($cf->config['liens_nbre_liens'] == '0')? ' 100 ' : $cf->config['liens_nbre_liens'];

	$sql = 'SELECT id_lien, titre, lien, vignette 
		FROM '. TABLE_LIENS .' 
		ORDER BY '.$ordre.' 
		LIMIT '.$limit;
	if (!($result = $c->sql_query($sql))) message_die(E_ERROR,1017,__FILE__,__LINE__,$sql);
	while ($row = $c->sql_fetchrow($result))
	{
		$titre = $row['titre'];
		$image = '';
		if ($row['vignette'] != '')
		{
			$image = '<img src="'.$row['vignette'].'" border="0" alt="'.$row['titre'].'" title="'.$row['titre'].'" />';
			$titre = '';
		}	
		$tpl->assign_block_vars('row_liens', array(
			'IMAGE'	=> $image,
			'TITRE' => $titre,
			'LIEN'	=> $row['lien']
		));
	}

	// on affiche le scroll ? 
	if ($cf->config['liens_scroll'] == 1) $tpl->assign_block_vars('scroll', array());
	$tpl->pparse('cache_liens',TRUE);
	return $tpl->buffer;
}






?>
