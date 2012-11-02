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
class config
{
	// Configuration de Malleo disponible dans ce tableau
	var $config;
	
	function config(){
		global $cache,$root,$cf;
		// Configuration generale mise en cache 12H
		// On ne peut pas la centraliser avec les autres car c'est elle qui permet d'engendrer les autres regles
		$cache->files_cache['listing_config'] = array($root.'cache/data/liste_config','global $cf; return $cf->lire_config();',43200);		
	}
	
	function appel_config($mode='LECTURE', $data=false)
	{
		global $cache;
		switch($mode)
		{
			case 'AJOUTER': 
				$this->insert_config($data);
				$this->config = $cache->appel_cache('listing_config',true);
				break;
			case 'MODIFIER':
				$this->update_config($data);
				$this->config = $cache->appel_cache('listing_config',true);
				break;
			case 'SUPPRIMER': 
				$this->delete_config($data);
				$this->config = $cache->appel_cache('listing_config',true);
				break;
			case 'LECTURE':  
			default : 
				$force = ($data==true)? true:false;
				$this->config = $cache->appel_cache('listing_config',$force);
				break;
		}
	}
	//
	// Récupération de la configuration générale
	
	function lire_config()
	{
		global $c;
		$sql = 'SELECT data, valeur FROM '.TABLE_CONFIG;
		if (!$resultat = $c->sql_query($sql)) message_die(E_ERROR,3,__FILE__,__LINE__,$sql);
		$config = array();
		while ($row = $c->sql_fetchrow($resultat))
		{
			$config[$row['data']]= $row['valeur'];
		}
		return $config;
	}
	
	//
	// On met a jour les champs fournis si ils existent
	
	function update_config($data)
	{
		global $c;
		// On parcours les données fournies
		if (!is_array($data) || sizeof($data)==0) return true;
		foreach ($data as $key=>$val)
		{
			// on regarde si la clef existe dans $this->config
			if (array_key_exists($key,$this->config))
			{
				// si la valeur est différente d'avant on met à jour
				if ($this->config[$key] != $val)
				{
					$sql = 'UPDATE '.TABLE_CONFIG.' SET valeur=\''.str_replace("\'","''",$val).'\' WHERE data=\''.$key.'\'';
					if (!$resultat = $c->sql_query($sql)) message_die(E_ERROR,3,__FILE__,__LINE__,$sql);
				}
			}
		}
	}
	
	//
	// Ajoute des couples donnee/valeur dans la table de configuration
	
	function insert_config($data)
	{
		global $c;
		// On parcours les données fournies
		if (!is_array($data) || sizeof($data)==0) return true;
		foreach ($data as $key=>$val)
		{
			// on regarde si la clef existe dans $this->config
			if (!array_key_exists($key,$this->config))
			{
				$sql = 'INSERT INTO '.TABLE_CONFIG.' (valeur,data) VALUES (\''.str_replace("\'","''",$val).'\',\''.$key.'\')';
				if (!$resultat = $c->sql_query($sql)) message_die(E_ERROR,3,__FILE__,__LINE__,$sql);
			}
		}
	}
	
	//
	// Supprime des enregistrement de la table de configuration
	
	function delete_config($data)
	{
		global $c;
		if (is_array($data)){
			$liste_data = explode(',',$data);
			if ($liste_data=='')$liste_data='""';
		}
		$sql = 'DELETE FROM '.TABLE_CONFIG.' WHERE data IN ('.$liste_data.')';
		if (!$resultat = $c->sql_query($sql)) message_die(E_ERROR,3,__FILE__,__LINE__,$sql);
	}
	
}

?>