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
$nbre_tags = 30;
$taille_min = 80;
$taille_max = 180;
// NE rien modifier en dessous cette ligne
// -------------------------------------------------------------------------
require_once($root.'plugins/modules/blog/prerequis.php');
load_lang_bloc('blog_nuage');
// Par defaut on affiche les tags du blog principal
if (!isset($module)) $module = 'blog';


$cache->files_cache['bloc_blog_nuage'] = array($root.'cache/data/bloc_blog_nuage','return cache_liste_mots('.$nbre_tags.','.$taille_min.','.$taille_max.');',1300);


function cache_liste_mots($nbre_tags,$taille_min,$taille_max){
	global $c,$module;
	
	$hexa = array(	'00','01','02','03','04','05','06','07','08','09',
				'A0','A1','A2','A3','A4','A5','A6','A7','A8','A9',
				'B0','B1','B2','B3','B4','B5','B6','B7','B8','B9',
				'C0','C1','C2','C3','C4','C5','C6','C7','C8','C9',
				'D0','D1','D2','D3','D4','D5','D6','D7','D8','D9',
				'E0','E1','E2','E3','E4','E5','E6','E7','E8','E9',
				'F0','F1','F2','F3','F4','F5','F6','F7','F8','F9',
				'AA','AB','AC','AD','AE','AF','BA','BB','BC','BD','BE','BF',
				'CA','CB','CC','CD','CE','CF','DA','DB','DC','DD','DE','DF',
				'EA','EB','EC','ED','EE','EF','FA','FB','FC','FD','FE','FF');
	shuffle($hexa);
	$taille_pas = ceil(($taille_max-$taille_min) / $nbre_tags);

	$sql =  'SELECT b.tags, c.module 
			FROM '.TABLE_BLOG_BILLETS.' AS b
			LEFT JOIN '.TABLE_BLOG_CATS.' AS c
			ON (b.id_cat=c.id_cat)
			WHERE tags IS NOT NULL AND tags != ""
			ORDER BY nbre_coms DESC, nbre_vues DESC, date_parution DESC 
			LIMIT 500';
	if (!$resultat=$c->sql_query($sql)) message_die(E_ERROR,31,__FILE__,__LINE__,$sql);
	$tags = $liste_tags = array();
	while ($row = $c->sql_fetchrow($resultat))
	{
		$mots = explode(' ',$row['tags']);
		if (sizeof($mots)>0){
			foreach ($mots as $mot){
				if (array_key_exists($row['module'],$liste_tags) && 
						array_key_exists($mot,$liste_tags[$row['module']])){
					$liste_tags[$row['module']][$mot]++;
				}else{
					$liste_tags[$row['module']][$mot] = 1;
				}
			}
		}
	}
	if (sizeof($liste_tags)>0){
		asort($liste_tags,SORT_NUMERIC);
		$liste_tags = array_reverse($liste_tags);
		foreach($liste_tags as $module=>$les_tags){
			$i=0;
			foreach($les_tags as $tag=>$nbre){

				if ($i<$nbre_tags){
					$hexa_keys = array_rand($hexa , 3);
					$tags[$module][$tag]['COLOR'] = $hexa[$hexa_keys[0]].$hexa[$hexa_keys[1]].$hexa[$hexa_keys[2]];
					$tags[$module][$tag]['SIZE'] = $taille_max;
					$taille_max = $taille_max-$taille_pas;
					$i++;
				}else{
					break;
				}
			}
		}
	}
	return $tags;
}

$liste_tags  = $cache->appel_cache('bloc_blog_nuage');

//chargement du template
$tpl->set_filenames(array(  'blog_nuage' => $root.'plugins/blocs/blog_nuage/html/bloc_blog_nuage.html'));

if (array_key_exists($module,$liste_tags) && sizeof($liste_tags[$module]) > 0 ){
	foreach ($liste_tags[$module] as $key=>$val){
		$tpl->assign_block_vars('liste_tags', array(
			'COLOR' => $val['COLOR'],
			'TAG' => $key,
			'SIZE'=> $val['SIZE'],
			'URL' => formate_url('mode=liste&tag='.$key,true)
		));
	}
}else{
	$tpl->assign_block_vars('noliste_tags', array());
}


?>