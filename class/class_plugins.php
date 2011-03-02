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
class plugins
{
	var $liste_plugins = '';
	
	function get_liste_plugins($force=false){
		global $cache;
		if ($force==true){
			$this->liste_plugins = $cache->appel_cache('listing_plugins',true);
		}elseif (!isset($this->liste_plugins) || empty($this->liste_plugins))
		{
			$this->liste_plugins = $cache->appel_cache('listing_plugins');
		}
		return $this->liste_plugins;
	}
	//
	// Execute les requetes SQL situees dans plugins/modules/Nom_Du_Module/infos.xml
	// dans la structure <module><install><requete>CREATE ... </requete><requete>UPDATE ... </requete>...
	function install_plugin($plugin,$type){
		global $root,$c,$cf,$liste_plugins,$cache,$prefixe;
		$dir_plug = ($type==0)? 'modules':'blocs';
		$file = $root.'plugins/'.$dir_plug.'/'.$plugin.'/infos.xml';
		$this->get_liste_plugins();
		if (!array_key_exists($plugin, $this->liste_plugins) && file_exists($file)){
			$xml = simplexml_load_file($file);
			$this->declarer_plugin($plugin,$type,$xml->version);
			if (is_object($xml->install) && is_object($xml->install->requete)){
				foreach($xml->install->requete as $sql){
					// On remplace le prefixe par celui définis par l'auteur du mod
					if (is_object($xml->prefixe) && $xml->prefixe != ''){
						$sql = preg_replace('#([` ])'.$xml->prefixe.'#i','\\1'.$prefixe,$sql);
						
					// A defaut de précision, on considère que le prefixe est a_
					}else{
						$sql = preg_replace('#([` ])a_#i','\\1'.$prefixe,$sql);
					}
					if (!$resultat=$c->sql_query($sql)) message_die(E_ERROR,31,__FILE__,__LINE__,$sql);
				}
			}
			$cache->purger_cache();
		}
	}
	
	//
	// Declare le module dans la table plugins
	function declarer_plugin($plugin,$type,$version){
		global $c,$cache,$cf;
		$sql = 'INSERT INTO '.TABLE_PLUGINS.' (plugin, type, version) VALUES ("'.$plugin.'",'.$type.',"'.$version.'")';
		if (!$resultat=$c->sql_query($sql)) message_die(E_ERROR,31,__FILE__,__LINE__,$sql);
		$this->get_liste_plugins(true);
	}
	
	//
	// Update le plugin specifie en parametre
	function update_plugin($plugin,$type){
		global $root,$c;
		
		$this->get_liste_plugins();
		
		// RECUPERATION de la VERSION courante
		$version_courante = $this->liste_plugins[$plugin]['version'];
		$version_atteinte = $version_courante;

		$extract = array();
		$dir_plug = ($type==0)? 'modules':'blocs';
		$file = $root.'plugins/'.$dir_plug.'/'.$plugin.'/infos.xml';

		// RECUPERATION des requetes de MAJ dans le XML
		if (array_key_exists($plugin, $this->liste_plugins) && file_exists($file)){
			$dom = new DomDocument();
			$dom->load($file);
 			$listeVersions = $dom->getElementsByTagName('step');
			foreach($listeVersions as $version){
				$id_version = $version->getAttribute("id_version");
				if ($id_version > $version_atteinte) $version_atteinte = $id_version;
				$requetes = $version->getElementsByTagName('requete');
				foreach($requetes as $req){
					$extract[$id_version][] = $req->firstChild->nodeValue;
				}
			} 
			ksort($extract);
		}
		
		// EXECUTION des REQUETES de MAJ
		if (sizeof($extract)>0){
			foreach($extract as $id_version=>$paquet){
				if ($id_version >= $version_courante){
					foreach ($paquet as $id=>$sql){
						if (!$resultat=$c->sql_query($sql)) message_die(E_ERROR,31,__FILE__,__LINE__,$sql);
					}
				}
			}
		}
		// MAJ du numero de version en base
		if ($version_atteinte>$version_courante){
			$sql = 'UPDATE '.TABLE_PLUGINS.' SET version="'.$version_atteinte.'" 
			WHERE plugin="'.$plugin.'" AND type='.$type;
			if (!$resultat=$c->sql_query($sql)) message_die(E_ERROR,31,__FILE__,__LINE__,$sql);
			
			$this->get_liste_plugins(true);
		}
	}
}

?>