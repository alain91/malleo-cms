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

function charge_blocs($retour){
	global $tpl,$root,$lang;
	$bloc = ereg_replace('{|}','',$retour[0]);
	if (ereg('HTML_',$bloc))
	{
		// BLOC HTML
		$id_bloc_html = intval(ereg_replace('HTML_','',$bloc));
		include_once($root.'plugins/blocs/html/mod.php');
		$tpl->pparse('HTML_'.$id_bloc_html,true);
	}elseif (file_exists($root.'plugins/blocs/'.$bloc.'/mod.php'))
	{
		include_once($root.'plugins/blocs/'.$bloc.'/mod.php');
		$tpl->pparse($bloc,true);
	}
	if (trim($tpl->buffer) == '')
	{
		$tpl->set_filenames(array(
			  'body_admin' => $root.'html/admin_modele_bloc_vide.html'
		));
		$tpl->assign_vars(array(
			'L_TITRE'	=> $_POST['RequeteAjax'],
			'L_MESSAGE'	=> $lang['L_ALERTE_BLOC_VIDE']
		));
		$tpl->pparse('body_admin',true);
		return $tpl->buffer;
	}else{
		return $tpl->buffer;
	}
}

function charge_zones(){ 
	global $i; 
	$i++; 
	return '<div id="dragableBoxesColumn'.$i.'"></div>';
}

function charge_blocs_apercu(){ 
	global $i,$root,$tpl,$cf,$c,$lang,$map,$user,$session,$id_bloc_html,$cache,$liste_plugins,$cache; 
	$i++;
	if (array_key_exists($i,$map->data['map']))
	{
		$colonne='';
		$map->data['map'][$i] = array_reverse($map->data['map'][$i]);
		foreach($map->data['map'][$i] as $key=>$value)
		{
			if ($value == 'module')
			{
				$colonne .= '<table class="standard"><tr><td class="row1">'.$lang['L_MODULE_PRINCIPAL'].'</td></tr></table>';
			}elseif(ereg('HTML_',$value)){
					$id_bloc_html = intval(ereg_replace('HTML_','',$value));
					include($root.'plugins/blocs/html/mod.php');
					$tpl->pparse($value,true);
					$colonne .= $tpl->buffer;			
			}else{
				include_once($root.'plugins/blocs/'.$value.'/mod.php');
				$tpl->pparse($value,true);	
				$colonne .= $tpl->buffer;
			}
		}
		return $colonne;
	}else{
		return '';
	}
}

function charge_handle()
{
	global $i,$map;
	$i++;
	if (array_key_exists($i,$map->data['map']))
	{
		$colonne='';
		// Les résultats doivent être classés dans le sens inverse
		// Ceci est du à un prérequis de la class javascript qui "fait tomber les blocs par le haut"
		$map->data['map'][$i] = array_reverse($map->data['map'][$i]);
		foreach($map->data['map'][$i] as $key=>$value)
		{
			$colonne .= '<div id="zone_'.$value.'">{'.$value.'}</div>';
		}
		return $colonne;
	}else{
		return '';
	}
}

function tester_modele($gabaris,$map,$fichier=false)
{
	global $lang,$root;
	if ($fichier == true && file_exists($fichier))return $lang['L_ETAT_OK'];
	// Test d'existence du gabaris
	if (!file_exists($root.'data/modeles/'.$gabaris.'/squelette.txt')) return $lang['L_GABARIS_INDISPONIBLE'];
	// Test de cohérence de la map
	if (!is_string($map))  return $lang['L_ENREGISTREMENT_MAP_FAILED'];
	$map = unserialize($map);
	if (!is_array($map) || (sizeof($map)==0)) return $lang['L_ENREGISTREMENT_MAP_FAILED'];
	// Test d'existence de chaque bloc
	foreach ($map as $key=>$val)
	{
		foreach($val as $k=>$v)
		{
			if (!file_exists($root.'plugins/blocs/'.$v.'/mod.php') && $v != 'module'&& !ereg('HTML_',$v))	return sprintf($lang['BLOC_SUPPRIME'],$v);
		}
	}
	return $lang['L_ETAT_OK'];
}
	
class Assemblage
{
	var $map;
	var $id_modele;
	var $chemin_blocs;
	var $nbre_zones;
	var $data = '';
	var $data_file = ''; // contenu du fichier .html
	var $html_file; // nom du fichier html
	var $module;
	var $listing_pages;
	
	function Assemblage()
	{
		$this->chemin_blocs = 'plugins/blocs/';
	
	}
	
	//
	// Enregistre un nouveau modèle en base
	function insert_modele()
	{
		global $c;
		$sql = 'INSERT INTO '.TABLE_MODELES.' (gabaris, titre_modele) 
				VALUES (\''.$this->data['gabaris'].'\',\''.$this->data['titre_modele'].'\')';
		if (!$res=$c->sql_query($sql)) message_die(E_ERROR,7,__FILE__,__LINE__,$sql);
		$this->id_modele = $c->sql_nextid();
		return true;
	}
	
	//
	// Enregistre un nouveau modèle de type fichier en base
	function insert_modele_fichier($titre,$fichier)
	{
		global $c;
		$sql = 'INSERT INTO '.TABLE_MODELES.' (titre_modele, fichier) 
				VALUES (\''.$titre.'\',\''.$fichier.'\')';
		if (!$res=$c->sql_query($sql)) message_die(E_ERROR,7,__FILE__,__LINE__,$sql);
		$this->id_modele = $c->sql_nextid();
		return true;
	}
	
	//
	// Lecture des infos sur le modèle demandé
	
	function lecture_modele()
	{
		global $c;
		$sql = 'SELECT id_modele, gabaris, titre_modele, map, fichier FROM '.TABLE_MODELES.' WHERE id_modele='.$this->id_modele;
		if (!$resultat=$c->sql_query($sql)) message_die(E_ERROR,8,__FILE__,__LINE__,$sql);
		$this->data = $c->sql_fetchrow($resultat);
		
	}
	
	//
	// 
	function supprimer_modele()
	{
		global $c;
		$this->lecture_modele();
		if ($this->data['fichier']!= null && file_exists($this->data['fichier'])){
			@unlink($this->data['fichier']);
		}
		$sql = 'DELETE FROM '.TABLE_MODELES.' WHERE id_modele='.$this->id_modele;
		if (!$resultat=$c->sql_query($sql)) message_die(E_ERROR,9,__FILE__,__LINE__,$sql);
	}
	
	
	//
	// lecture du fichier squelette
	function lecture_squelette()
	{
		global $root;
		$file = $root.'data/modeles/'.$this->data['gabaris'].'/squelette.txt';		
		if (!file_exists($file ) || !$this->map = @file_get_contents($file )) error404(10);
		return true;
	}
	
	//
	// Prépare la map pour les blocs dynamiques
	function prepare_map_apercu()
	{
		global $i;
		if (!$this->lecture_squelette()) return false;
		$i=0;
		$this->map = preg_replace_callback("/BLOC/",'charge_blocs_apercu',$this->map);
		$this->map = preg_replace_callback("|\{[a-z0-9_-]+\}|",'charge_blocs',$this->map);
		$this->nbre_zones = $i;
		return true;
	}	
	
	//
	// Prépare la map pour les blocs dynamiques
	function prepare_map_dynamique()
	{
		global $i;
		if (!$this->lecture_squelette()) return false;
		$i=0;
		$this->map = eregi_replace('<td','<td class="zones"',$this->map);
		$this->map = preg_replace_callback("/BLOC/",'charge_zones',$this->map);
		$this->map = preg_replace_callback("|\{[a-z0-9_-]+\}|",'charge_blocs',$this->map);
		$this->nbre_zones = $i;
		return true;
	}
	
	//
	// Lis le fichier .html
	
	function Lire_Fichier_HTML()
	{
		if ($this->data_file == '') $this->data_file = file_get_contents($this->html_file);
		return $this->data_file;
	}
	
	//
	// Renvoie le nom du fichier .html à utiliser
	
	function Cache_Template($module,$force=false)
	{
		global $root,$cache,$cf;
		if (defined('IPHONE') && $cf->config['imposer_modele_iphones']!=''){
			$this->html_file = $cache->cache_tpl($root.'cache/modeles/_modele_iphone_.html','global $map; return $map->monter_template_via_modele(\''.$cf->config['imposer_modele_iphones'].'\');', 86400);
		}else{
			$this->html_file = $root.'cache/modeles/'.$module.'.html';
			if (!file_exists($this->html_file) || $force==true){
				$tps = ($force==true)?0:86400;
				$this->html_file = $cache->cache_tpl($this->html_file,'global $map; return $map->monter_template(\''.$module.'\');', $tps);
			}
		}
		return $this->html_file;
	}	
	
	function monter_template($module)
	{
		global $c,$i;
		$sql = 'SELECT id_modele, gabaris, map, fichier 
				FROM '.TABLE_MODULES.' as P LEFT JOIN '.TABLE_MODELES.' as M 
				ON (P.modele=M.id_modele)
				WHERE P.module="'.$module.'" OR P.id_module="'.$module.'" LIMIT 1';
		if (!$resultat=$c->sql_query($sql)) message_die(E_ERROR,11,__FILE__,__LINE__,$sql);
		$this->data = $c->sql_fetchrow($resultat);
		if ($this->data['fichier'] != null){
			return file_get_contents($this->data['fichier']);
		}elseif(is_string($this->data['map'])){
			$this->data['map'] = unserialize($this->data['map']);
			$this->lecture_squelette();
			$i=0;
			$this->map = preg_replace_callback("/BLOC/",'charge_handle',$this->map);
			return $this->map;
		}else{
			error404(11);
		}
	}
	
	function monter_template_via_modele($modele)
	{
		global $c,$i;
		$sql = 'SELECT id_modele, gabaris, map, fichier 
				FROM '.TABLE_MODELES.'
				WHERE titre_modele="'.$modele.'" LIMIT 1';
		if (!$resultat=$c->sql_query($sql)) message_die(E_ERROR,11,__FILE__,__LINE__,$sql);
		$this->data = $c->sql_fetchrow($resultat);
		if ($this->data['fichier'] != null){
			return file_get_contents($this->data['fichier']);
		}else{
			$this->data['map'] = unserialize($this->data['map']);
			$this->lecture_squelette();
			$i=0;
			$this->map = preg_replace_callback("/BLOC/",'charge_handle',$this->map);
			return $this->map;
		}
	}
	
	//
	// Crée les options d'un menu déroulants  de blocs, le parametre exlus permet de sortir du listing les blocs déjà utilisés
	function lister_blocs_dispo($exclus=array())
	{
		global $liste_plugins,$root,$cache,$lang;
		include_once($root.'class/class_plugins.php');
		$plugin = new plugins();
		$liste_blocs = '<option value="">'.$lang['L_SELECTIONNER_BLOC'].'</option>';
		if(!$ch = @opendir($this->chemin_blocs)) 
		message_die(E_ERROR,12,__FILE__,__LINE__);
		while ($bloc = @readdir($ch))
		{
			if ($bloc[0] != '.'
				&& is_dir($this->chemin_blocs.$bloc) && $bloc != 'menu_horizontal'
				&& $bloc != 'html' && !in_array($bloc,$exclus))
			{
				if (!array_key_exists($bloc,$liste_plugins)){
					$plugin->install_plugin($bloc,1);
				}
				$liste_blocs .= '<option value="'.$bloc.'">'.$bloc.'</option>';				
			}
		}
		@closedir($ch);
		return $liste_blocs;
	}
	//
	// Crée les options d'un menu déroulants  de blocs HTML, le parametre exlus permet de sortir du listing les blocs déjà utilisés
	function lister_blocs_html_dispo($exclus=array())
	{
		global $c,$lang,$root,$lang;
		include_once($root.'plugins/blocs/html/prerequis.php');
		$liste_blocs = '<option value="">'.$lang['L_SELECTIONNER_BLOC'].'</option>';
				
		$sql = 'SELECT id, titre FROM '.TABLE_HTML.' ORDER BY TITRE ASC';
		if (!$resultat = $c->sql_query($sql)) message_die(E_ERROR,1020,__FILE__,__LINE__,$sql);
		while ($row = $c->sql_fetchrow($resultat))
		{
			if (!in_array($row['id'],$exclus))
			{
				$liste_blocs .= '<option value="HTML_'.$row['id'].'">'.$row['titre'].'</option>';				
			}
		}
		return $liste_blocs;
	}
	
	//
	// Liste les modules existants
	
	function lister_modules()
	{
		global $c;
		$liste = array();
		$sql = 'SELECT module, virtuel, modele, style FROM '.TABLE_MODULES;
		if (!$resultat = $c->sql_query($sql)) message_die(E_ERROR,13,__FILE__,__LINE__,$sql);
		if ($c->sql_numrows($resultat) > 0)
		{
			while($res = $c->sql_fetchrow($resultat))
			{
				$liste[$res['module']] = $res;
			}
		}
		return $liste;
	}
}
?>