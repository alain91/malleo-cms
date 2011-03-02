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
global $lang,$style_name,$image;
load_lang('utilisateurs');

if (!class_exists('protection_adresse')){

	global $liste_etats_civils,$liste_sexes,$liste_levels;
	$liste_etats_civils = array(
		$lang['L_INDEFINI'],
		$lang['L_CELIBATAIRE'],
		$lang['L_AMOUREUX'],
		$lang['L_CONCUBINAGE'],
		$lang['L_PACSE'],
		$lang['L_MARIE'],
		$lang['L_VEUF']);
		
	$liste_sexes = array($lang['L_INDEFINI'],$lang['L_HOMME'],$lang['L_FEMME']);

	$liste_levels[0] = $liste_levels[1] = $lang['L_INVITE'];
	$liste_levels[2] = $liste_levels[3] = $liste_levels[4] = $liste_levels[5] = $liste_levels[6] = $liste_levels[7] = 	$liste_levels[8] = $lang['L_MEMBRE'];
	$liste_levels[9] = $lang['L_ADMIN'];
	$liste_levels[10] = $lang['L_FONDATEUR'];
	//
	// CHARGEMENT des outils images
	require_once($root.'class/class_image.php');

	class protection_adresse extends image{
		var $destination='cache/adresses/';
		var $format = 'png';
		var $taille_texte = 8;
		var $police = 'data/fonts/verdana.ttf';
		var $couleur_texte = '#000000';
		var $couleur_fond = '#FFFFFF';
		var $style_name = 'BlueLight';
		//
		// Cree une image avec l'adresse en texte.	
		function creer_adresse($texte,$url){
			// nouvelle image / On considere la longueur moyenne d'un caractere comme etant de 7.2 pixels
			$this->image = imagecreatetruecolor(round(strlen($texte)*7.2),16);
			// On colorie le background
			$rgb = $this->html2rgb($this->couleur_fond);
			$background = ImageColorAllocate ($this->image, $rgb[0],$rgb[1],$rgb[2]); 
			imagefill($this->image,0,0,$background);	
			// Transparance
			imagecolortransparent($this->image,$background);
			// On place notre adresse dedans
			$this->position_x=1;
			$this->position_y=14;
			$this->inserer_texte($texte,$this->taille_texte,$this->couleur_texte,$this->police);
			// On enregistre
			$this->extension = $this->format;
			$this->save_image($url);
		}
		
		//
		// Affiche l'image
		function get_image($adresse){
			if (empty($adresse)) return ;
			// Creation du dossier de stockage des adresse
			if (!is_dir($this->destination)) $this->creer_dossier_image($this->destination);
			// Construction de l'adresse
			$url = $this->destination.md5($adresse).'_'.$this->style_name.'.'.$this->format;
			// Si l'image n'existe pas encore on la cree
			if (!file_exists($url)){
				$this->creer_adresse($adresse,$url);
			}
			return $url;
		}
	}
	$image = new protection_adresse();
	if (defined('COULEUR_TEXTE')) 		$image->couleur_texte = COULEUR_TEXTE;
	if (defined('COULEUR_BACKGROUND'))	$image->couleur_fond = COULEUR_BACKGROUND;
	if (isset($style_name)) $image->style_name = $style_name;



	//
	// TRANSFORME une donnee utilisateur pour la rendre attrayante
	function formate_info_user($val,$rep){
		global $image;
		switch ($val)
		{
			case 'level': $rep = formate_level($rep);break;
			case 'langue': $rep = formate_langue($rep);break;
			case 'site_web': $rep = url_cliquable($rep);break;
			case 'email': $rep = strip_tags($rep);
			case 'yahoo':
			case 'gtalk':
			case 'icq':
			case 'msn':$img = $image->get_image($rep); $rep = (!empty($img))?'<img src="'.$img.'" />':'';break;
			case 'sexe':$rep = formate_sexe($rep);break;
			case 'etat_civil':$rep = formate_etat_civil($rep);break;
		}
		return $rep;
	}


	//
	// TRANSFORME un id de rang en une image
	function formate_rang($id_rang,$msg){
		global $liste_rangs,$cache;
		$rang ='';
		if (!is_array($liste_rangs)) $liste_rangs = $cache->appel_cache('listing_rangs');
		if (sizeof($liste_rangs) == 0) return '';
		if ($id_rang != 0){
			// RANG SPECIAL
			if ($liste_rangs[$id_rang]['image'] == ''){
				return $liste_rangs[$id_rang]['titre'];
			}else{
				return  '<img src="'.$liste_rangs[$id_rang]['image'].'" border="0" alt="'.$liste_rangs[$id_rang]['titre'].'" title="'.$liste_rangs[$id_rang]['titre'].'" />';
			}
		}else{
			// RANG AUTO (en fonction du nombre de message)
			foreach ($liste_rangs as $key=>$val){
				if ($val['msg'] != null && $val['msg'] <= $msg){
					$rang = ($val['image']=='')?$val['titre']:'<img src="'.$val['image'].'" border="0" alt="'.$val['titre'].'" title="'.$val['titre'].'" />';
				}
			}
			return $rang;
		}
	}


	// Transforme l'ID d'une langue en un drapeau formate
	function formate_langue($langue){
		return '<img src="data/flags/'.$langue.'.gif" border="0" alt="flag_'.$langue.'" />';
	}


	// Retourne le libelle d'un niveau: noob/member/admin/bird
	function formate_level($level){
		global $liste_levels;
		return $liste_levels[$level];
	}

	// Retourne le libelle d'un sexe : homme, femme, animal
	function formate_sexe($sexe){
		global $liste_sexes, $img;
		switch ($sexe){
			case 0: return;
			case 1: return '<img src="'.$img['homme'].'" alt="'.$liste_sexes[$sexe].'" title="'.$liste_sexes[$sexe].'" />';break;
			case 2: return '<img src="'.$img['femme'].'" alt="'.$liste_sexes[$sexe].'" title="'.$liste_sexes[$sexe].'" />';break;
			default: $liste_sexes[$sexe];
		}
	}

	// Retourne l'etat civil : celibataire, marie, pacse..
	function formate_etat_civil($etat){
		global $liste_etats_civils;
		if ($etat == 0) return;
		return $liste_etats_civils[$etat];
	}
}
?>