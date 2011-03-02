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
class modules
{
	var $module;
	var $root;
	
	function modules($root)
	{
		$this->root = $root;
	}
	
	function clean($v)
	{
		return str_replace("\'","''",$v);
	}
	
	function ajoute_module($module,$modele,$style)
	{
		global $c,$droits,$root,$cache;
		$sql = 'INSERT INTO '.TABLE_MODULES.' (module, modele, style) 
				VALUES (\''.$this->clean($module).'\','.intval($modele).',\''.$this->clean($style).'\')';
		if (!$resultat=$c->sql_query($sql)) message_die(E_ERROR,31,__FILE__,__LINE__,$sql);
		$this->module= $module;
		include_once($root.'class/class_plugins.php');
		$plugin = new plugins();
		$plugin->install_plugin($module,0);
		$this->update_cache();
		$droits->init_regles($this->module);
		$cache->purger_cache();
	}
	
	function ajoute_module_virtuel($module,$virtuel,$style,$modele)
	{
		global $c,$droits;
		$sql = 'INSERT INTO '.TABLE_MODULES.' (module, virtuel, modele, style) 
				VALUES (\''.$this->clean($module).'\',\''.$this->clean($virtuel).'\','.intval($modele).',\''.$this->clean($style).'\')';
		if (!$resultat=$c->sql_query($sql)) message_die(E_ERROR,31,__FILE__,__LINE__,$sql);
		$this->module= $module;
		$this->update_cache();
		$droits->init_regles($this->module,$this->clean($virtuel));
		$cache->purger_cache();
	}
	
	function supprime_module($id)
	{
		global $c,$droits;
		$sql = 'SELECT module FROM '.TABLE_MODULES.' WHERE id_module='.intval($id);
		if (!$resultat=$c->sql_query($sql)) message_die(E_ERROR,32,__FILE__,__LINE__,$sql);
		$row = $c->sql_fetchrow($resultat);
		$droits->delete_regle('module',$row['module']);
		$sql = 'DELETE FROM '.TABLE_MODULES.' WHERE id_module='.intval($id);
		if (!$resultat=$c->sql_query($sql)) message_die(E_ERROR,32,__FILE__,__LINE__,$sql);
	}
	
	function update_module($id,$style,$modele)
	{
		global $c;
		$sql = 'UPDATE '.TABLE_MODULES.' SET
				modele='.intval($modele).',
				style=\''.$this->clean($style).'\' 
				WHERE id_module='.intval($id);
		if (!$resultat=$c->sql_query($sql)) message_die(E_ERROR,31,__FILE__,__LINE__,$sql);
		$this->id_module=intval($id);
		$this->update_cache();
	}	
	
	function update_module_virtuel($style,$modele,$id)
	{
		global $c;
		$sql = 'UPDATE '.TABLE_MODULES.' SET
				modele='.intval($modele).',
				style=\''.$this->clean($style).'\'
				WHERE id_module='.intval($id);
		if (!$resultat=$c->sql_query($sql)) message_die(E_ERROR,31,__FILE__,__LINE__,$sql);
		$this->id_module=intval($id);
		$this->update_cache();
	}
	
	function nom_module($id_module)
	{
		global $c;
		$sql = 'SELECT module FROM '.TABLE_MODULES.' WHERE id_module='.intval($id_module);
		if (!$resultat=$c->sql_query($sql)) message_die(E_ERROR,31,__FILE__,__LINE__,$sql);
		$row=$c->sql_fetchrow($resultat);
		return $row['module'];
	}
	
	function update_cache()
	{
		global $map,$cache;
		if ($this->module=='') $this->module=$this->nom_module($this->id_module);
		include_once($this->root.'class/class_assemblage.php');
		$map = new Assemblage();
		$map->module = $this->module;
		$map->Cache_Template($this->module,true);
		$map->listing_modules = $cache->appel_cache('listing_modules',true);
	}
	
	//
	// Modules disponibles
	function lister_modules_dispos($liste_modules_installes)
	{
		$chemin = 'plugins/modules/';
		$liste_modules = '';
		$ch = @opendir($chemin);
		while ($module = @readdir($ch))
		{
			if ($module != "." && $module != ".." && is_dir($chemin.$module) && !in_array($module,$liste_modules_installes)) {
				$liste_modules .= "\n ".'<option>'.$module.'</option>';
			}
		}
		@closedir($ch);
		return $liste_modules;
	}

	//
	// MODELES disponibles
	function lister_modeles_dispos($SelectModele='')
	{
		global $c,$lang,$root;
		load_lang('modeles');
		include_once($root.'class/class_assemblage.php');
		
		$sql = 'SELECT id_modele,titre_modele,gabaris,map,fichier FROM '.TABLE_MODELES.' ORDER BY titre_modele ASC';
		if (!$resultat=$c->sql_query($sql)) message_die(E_ERROR,17,__FILE__,__LINE__,$sql);
		$liste_modeles = '';
		while($row = $c->sql_fetchrow($resultat))
		{
			$etat = tester_modele($row['gabaris'],$row['map'],$row['fichier']);
			if ($etat == $lang['L_ETAT_OK']){
				$selected = ($SelectModele == $row['id_modele'])?' selected':'';
				$liste_modeles .= "\n ".'<option value="'.$row['id_modele'].'"'.$selected.'>'.$row['titre_modele'].'</option>';
			}
		}
		return $liste_modeles;
	}
	
	//
	// STYLES disponibles
	function lister_styles_dispos($SelectStyle='')
	{
		global $lang;
		$chemin = 'styles/';
		$liste_styles = '<option value="">'.$lang['L_AUCUN_STYLE'].'</option>';
		$ch = @opendir($chemin);
		while ($style = @readdir($ch))
		{
			if ($style != "." && $style != ".." && is_dir($chemin.$style)) {
				$selected = ($SelectStyle == $style)?' selected="selected"':'';
				$liste_styles .= "\n ".'<option'.$selected.'>'.$style.'</option>';
			}
		}
		@closedir($ch);
		return $liste_styles;
	}
}
?>