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
class cache 
{
	// Generer des logs ?
	var $logs = false;
	// contient un temps timestamp
	var $time_file;
	// tableau contenant la liste actions à enchainer
	var $jobs;
	// temps timestamp actuel
	var $time_now;
	// liste de fichiers
	var $files=array();
	// liste de messages d'erreurs
	var $error_files=array();
	// listing statique des fichiers de cache
	var $files_cache=array();
	// Masque a appliquer sur les fichiers et dossiers
	var $umask = 0777;
	// Contenu du .htaccess cree dans les dossiers contenant des images
	var $htaccess = 
'<Limit POST PUT DELETE>
	Order Allow,Deny
	Deny from All
</Limit>';
	// Contenu du .htaccess cree dans les dossiers contenant des données sensibles
	var $htaccess_secure = 
'<Limit GET POST PUT DELETE>
	Order Allow,Deny
	Deny from All
</Limit>';
	// repertoires proteges
	var $protect_dir = array('cache/data','cache/sessions');
	// cache global activable (global = regroupement des cache en 1 unique fichier), moins d'appels disque
	var $activer_cache_global = false;
	// Contenu du cache global
	var $_Cache;
	// fichier conservera le cache
	var $file_cache_global;
	
	function cache()
	{	
		global $root;
		$this->time_now=time();	
		$this->file_cache_global = $root.'cache/data/cache_global.php';
	}

	function initialiser_config_cache(){
		global $cf,$root;
		$cf->config['cache_duree_bannis'] = 90;
		$cf->config['cache_duree_listing_blocs_html'] = 3600;
		
		$this->files_cache['listing_users'] =		array($root.'cache/data/liste_users',	'return cache_liste_users();',							$cf->config['cache_duree_listing_users']);
		$this->files_cache['listing_rangs'] =		array($root.'cache/data/liste_rangs',	'return cache_liste_rangs();',							$cf->config['cache_duree_listing_rangs']);
		$this->files_cache['listing_smileys'] =		array($root.'cache/data/liste_smileys',	'return cache_liste_smileys();',						$cf->config['cache_duree_listing_modules']);
		$this->files_cache['listing_plugins'] =		array($root.'cache/data/liste_plugins',	'return cache_liste_plugins();',						$cf->config['cache_duree_listing_modules']);
		$this->files_cache['listing_modules'] =		array($root.'cache/data/liste_modules',	'global $map; return $map->lister_modules();',			$cf->config['cache_duree_listing_modules']);
		$this->files_cache['listing_blocs_html'] =	array($root.'cache/liste_blocs_html',	'return lister_blocs_html();',							$cf->config['cache_duree_listing_blocs_html']);
		$this->files_cache['listing_ip'] =			array($root.'cache/data/liste_ip',		'global $session; return $session->liste_ip();',		$cf->config['cache_duree_listing_ip']);
		$this->files_cache['listing_bannis'] =		array($root.'cache/data/liste_bannis',	'global $droits,$c; return $droits->creer_liste_bannis();',$cf->config['cache_duree_bannis']);
		$this->files_cache['listing_bots'] =		array($root.'cache/data/liste_bots',	'global $bots; return $bots->lister_bots();',				$cf->config['cache_duree_listing_modules']);
	}
	
	function init_purge(){
		global $root,$lang;
		load_lang('cache');
		
		// On charge tous les fichiers contenant les infos de purge
		$this->lister_plugins_files('/_admin_purge_cache.php');

		// Ajout de fichiers à la liste des fichiers à purger
		$this->ajouter_fichier_purge('racine',		$lang['DESTROY_CACHE_RACINE'],		$root.'cache/',0);
		$this->ajouter_fichier_purge('data',		$lang['DESTROY_CACHE_DATA'],		$root.'cache/data/',0);
		$this->ajouter_fichier_purge('sessions',	$lang['DESTROY_CACHE_SESSIONS'],	$root.'cache/sessions/',0);
		$this->ajouter_fichier_purge('modeles',		$lang['DESTROY_CACHE_MODELES'],		$root.'cache/modeles/',0);
		$this->ajouter_fichier_purge('smileys',		$lang['DESTROY_CACHE_SMILEY'],		$root.'cache/smileys/',0);
		$this->ajouter_fichier_purge('digicode',	$lang['DESTROY_CACHE_DIGICODE'],	$root.'cache/digicode/',0);
	}
	
	//
	// LISTE tous les fichiers de cache 
	// conserve le temps de validite
	// indique quelle fonction appeller pour le reconstruire
	function appel_cache($handle,$force=false){
		if (!array_key_exists($handle,$this->files_cache)) return;
 		if ($force == true){
			return $this->cache_donnees($this->files_cache[$handle][0].'.php',$this->files_cache[$handle][1],$this->files_cache[$handle][2],true);
		}else{
			return $this->cache_donnees($this->files_cache[$handle][0].'.php',$this->files_cache[$handle][1],$this->files_cache[$handle][2]);
		}
	}
	//
	// Liste les fichiers d'un dossier
	// Execute une action sur les fichiers trouves
	function lister_fichiers($action,$chemin)
	{
		if (!is_dir($chemin)) return true;
		$ch = @opendir($chemin);
		while ($fichier = @readdir($ch))
		{
			if ($fichier[0] != '.' && $fichier != '.htaccess' && !is_dir($chemin.$fichier))
			{
				switch($action){
					case 'tester_effacer_fichiers': if (!$this->tester_effacer_fichiers($chemin.$fichier))return false;
				}
			}
		}
		@closedir($ch);	
		return true;
	}
	
	//
	// Liste les dossiers d'un repertoire fournit en parametre
	
	function lister_dossiers($chemin)
	{
		if (!is_dir($chemin)) return true;
		$ch = @opendir($chemin);
		while ($fichier = @readdir($ch))
		{
			if (substr($fichier,0,1) != "." && is_dir($chemin.$fichier))
			{
				$this->files[] = $chemin.$fichier;
			}
		}
		@closedir($ch);	
	}
	
	//
	// Charge un par un les fichiers de plugins definis dans $file
	
	function lister_plugins_files($file)
	{
		global $root;
		$this->lister_dossiers($root.'plugins/modules/');
		$this->lister_dossiers($root.'plugins/blocs/');
		foreach($this->files as $k=>$dir)
		{
			if (file_exists($dir.$file)) include_once($dir.$file);
		}
	}
	
	//
	// EFFACE le fichier passe en parametre
	
	function delete_file($file)
	{
		if (!is_writable($file)) return false;
		if(unlink($file)) return true; else return false;
	}
	
	function ajouter_fichier_purge($handle,$libelle,$dir,$time)
	{
		$this->jobs[$handle] = array(
			'LANG'=>$libelle,
			'DIR'=>$dir,
			'TIME'=>$time
		);	
	}
	
	function tester_effacer_fichiers($fichier)
	{
		// Si le fichier a dépassé la date d'expiration
		if (filemtime($fichier) < $this->time_file)
		{
			if (!$this->delete_file($fichier))
			{
				$this->error_files[] = $fichier;
				return false;
			}
		}
		return true;
	}
	
	function purger_cache($Handle=false)
	{
		global $tpl,$img;
		$this->init_purge();
		foreach($this->jobs as $k=>$v)
		{
			$this->time_file=($this->time_now - $v['TIME']);
			$etat = $this->lister_fichiers('tester_effacer_fichiers',$v['DIR']);
			if ($Handle!=false){
				$error = (sizeof($this->error_files)>0)? '<br />'.implode('<br />',$this->error_files):'';
				$tpl->assign_block_vars($Handle, array(
					'ACTION'	=>	$v['LANG'],
					'ERROR'		=>	$error,
					'ETAT'		=>	($etat==true)? $img['valide']: $img['invalide']
				));
				// REINITIALISATION des erreurs
				$this->error_files = array();
			}
		}
	}

	//
	// Renvoie le contenu du cache
	function generer_cache_global(){
		return $this->_Cache;
	}
	
	//
	// Si un fichier est stocké dans le cache global on en extrait le contenu.
	function lire_cache_global($fichier,$secondes){
		if (!is_array($this->_Cache)){
			$this->_Cache = $this->cache_donnees($this->file_cache_global, 'return $this->generer_cache_global();',3600,false);
		}
		return (is_array($this->_Cache) 
			&& array_key_exists($fichier,$this->_Cache) 
			&& (time()<($this->_Cache[$fichier]['time']+$secondes)))?$this->_Cache[$fichier]['data']:false;
	}
	
	//
	// On integre les nouvelles données dans le cache.
	function ajouter_cache_global($fichier,$data){
		$this->_Cache[$fichier]['data'] = $data;
		$this->_Cache[$fichier]['time'] = time();
		$this->cache_donnees($this->file_cache_global,'return $this->generer_cache_global();',0,true);
		return $this->_Cache;
	}
	
	//
	// Stocke en cache le resultat de la requete
	// $fichier : chemin du fichier de cache contenant la valeur
	// $fonction : nom de la fonction permettant le renvoie d'un tableau contenant les donnees a conserver sous la forme 'return NomFonction;'
	// $secondes : temps de validite du cache
	// $force : permet de mettre a jour le cache meme si le TTL n'est pas atteind
	function cache_donnees($fichier, $fonction, $secondes, $force=false)
	{
		// Dans le cache global ?
		if ($this->activer_cache_global == true
			&& $force == false
			&& $fichier != $this->file_cache_global
			&& is_array($retour = $this->lire_cache_global($fichier,$secondes))){
			return $retour;
		}
		
		if($force==false && file_exists($fichier) && $this->time_now<(filemtime($fichier)+$secondes) && filesize($fichier)>0){ 
			// le cache est actif et pas encore périmé
			if ($this->logs == true) $this->ecrit_fichier_log($fichier.'.txt',"\r \n".'lecture:'.date("d/m/Y H:i:s"));
			if (($data = @file_get_contents($fichier)) != false)
			{
				return unserialize(substr($data,31)); // 31 caracteres supprimés
			}
		}		
		$data =  eval($fonction);
		
		// Vidage du surplus de memoire sql
		global $c;
		if (isset($c->query_id)) $c->sql_freeresult();
		
		// enregistrement
		$sortie = $data;
		if (is_array($data)){
			// Cache global
			if ($this->activer_cache_global == true
				&& $fichier != $this->file_cache_global){	
				$this->ajouter_cache_global($fichier,$data);
				
			// Cache séparé
			}else{	
				// Creation du dossier si il n'existe pas
				if (!is_dir(dirname($fichier))) $this->creer_dossier(dirname($fichier));			
				
				$data = '<?php die("Acces INTERDIT"); ?>'.serialize($data); // 31 caracteres ajoutés	
				$this->ecrit_fichier($fichier,$data);
			}
			if ($this->logs == true) $this->ecrit_fichier_log($fichier.'.txt',"\r \n".'ecriture:'.date("d/m/Y H:i:s"));
		}
		return $sortie;
	}

	//
	// Copie en cache d'un template genere
	function cache_tpl($fichier, $fonction, $secondes)
	{
		// Si le cache n’existe pas ou si le temps est depasse.
		if(!file_exists($fichier) ||  $this->time_now>filemtime($fichier)+$secondes || filesize($fichier)==0)
		{
			if (!is_dir(dirname($fichier))) $this->creer_dossier(dirname($fichier));
			$this->ecrit_fichier($fichier,eval($fonction));
			// Vidage du surplus de memoire sql
			global $c;
			if (isset($c->query_id)) $c->sql_freeresult();
		}
		return $fichier;
	}
	
	//
	// cree un fichier
	function ecrit_fichier_log($fichier,$data){
		@touch($fichier);
		$file = @fopen($fichier, 'a');
		@flock($file, LOCK_EX);
		@fwrite($file, $data);
		@flock($file, LOCK_UN);
		@fclose($file);
		@chmod($fichier, $this->umask);
	}
	//
	// cree un fichier
	function ecrit_fichier($fichier,$data){
		if (empty($fichier)) return;
		@touch($fichier);
		$file = @fopen($fichier,'w');
		@flock($file, LOCK_EX);
		@fwrite($file, $data);
		@flock($file, LOCK_UN);
		@fclose($file);
		@chmod($fichier, $this->umask);
	}	
	
	function creer_dossier($dir){
		global $root;
		if (empty($dir) || file_exists($dir)) return;
		@mkdir($dir,$this->umask);
		chmod($dir, $this->umask);
		$file = @fopen($dir.'/.htaccess', 'w');
		$htaccess = $this->htaccess;
		
		// Si c'est un repertoire protege on n'utilise pas le même htaccess
		foreach($this->protect_dir as $dir_protect){
			if ($root.$dir_protect == $dir){
				$htaccess = $this->htaccess_secure;	
			}
		}
	    @fwrite($file,$htaccess);
	    @fclose($file);
		chmod($dir.'/.htaccess', $this->umask);
	}

}


?>