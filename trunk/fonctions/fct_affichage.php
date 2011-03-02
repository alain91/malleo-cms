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

function create_pagination($item_courant, $url, $max_items, $items_page,$libelle, $affichage_court=false)
{
	global $lang;
	load_lang('affichage');
	// SORTIE
	// PRECEDENTE 6 7 8 9 [10] 11 12 13 14 SUIVANTE
	$avant = 4; // Nbre de pages AVANT
	$apres = 4; // Nbre de pages APRES
	$item = 0;	// Point de départ de la pagination
	if ($items_page == 0){
		$nbre_pages = $page_courante = 0;
	}else{
		$nbre_pages = ceil($max_items/$items_page); // Nbre de pages Total (arrondit au dessus)
		$page_courante = floor($item_courant/$items_page)+1;
	}
	
	$min_affiche = ($page_courante-$avant);
	$max_affiche = ($page_courante+$avant);
	$sortie='';
	for($i=1;$i<=$nbre_pages;$i++){
		if (($i>=$min_affiche) && ($i<=$max_affiche))
		{
			$Numero = ($item_courant==$item)? '<b>'.$i.'</b>':$i;
			$sortie .= '<a href="'.formate_url($url.$item,true).'">'.$Numero.'</a>';
		}
		$item+=$items_page;		
	}
	
	if ($affichage_court==true && $nbre_pages>$max_affiche){
		$sortie .= '...<a href="'.formate_url($url.($nbre_pages*$items_page-$items_page),true).'">'.$nbre_pages.'</a>';
	}
	
	//
	//  SORTIE ici en affichage COURT
	if ($affichage_court==true) return $sortie;
	
	$premier = ($page_courante>1 && $nbre_pages>1)?'<a href="'.formate_url($url.'0',true).'">'.$lang['L_PREMIERE_PAGE'].'</a>':'';
	$dernier = ($page_courante<$nbre_pages && $nbre_pages>1)?'<a href="'.formate_url($url.(floor($nbre_pages*$items_page)-$items_page),true).'">'.$lang['L_DERNIERE_PAGE'].'</a>':'';
	
	//Nbre resultats
	$s=($max_items>1)?'s':'';
	$nbre_resultat = sprintf($lang['L_PAGE_RESULTAT'],$max_items,$libelle.$s,$page_courante,$nbre_pages);
	
	$sortie = ($nbre_pages>1)?$nbre_resultat.$premier.$sortie.$dernier:'';
	return $sortie;
}


?>