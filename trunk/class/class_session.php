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

class session
{
	var $user;
	var $id_session;
	var $time;
	var $info_user;
	var $config;
	var $liste_ip;
	var $ip;
	var $navlink = '';

	//
	// initialisation des infos de session
	function session($config)
	{
		global $root;
		$this->time = time();
		$this->path_cache = $root.'cache/sessions/';
		$this->cf = $config;
		$this->get_ip();
	}
	
	//
	// création d'une session pour un visiteur
	function new_session(){
		global $c,$cache;
		// ------------------------------------------------------
		// On verifie que cette IP n'est pas bannie
		if (!$this->ip_bannie()) return false;
		
		// ------------------------------------------------------
		// Liste des sessions recentes
		$this->liste_ip = $this->listing_ip();
		
		// ------------------------------------------------------
		// Recherche ID de session
		if(isset($_COOKIE[$this->cf['cookie_name']]) && $_COOKIE[$this->cf['cookie_name']]!= null){
			// On recupere le cookie si il existe	
			$this->id_session = eregi_replace("[^a-z0-9]",'',$_COOKIE[$this->cf['cookie_name']]);
		}else{
			// Reconnaissance par l'ip
			if (array_key_exists($this->ip,$this->liste_ip) && $this->liste_ip[$this->ip]['user_id'] < 2 ){
				$this->id_session = $this->liste_ip[$this->ip]['id_session'];
			}else{
				// On charge la session PHP
				session_start();
				$this->id_session = session_id();
			}
		}
		
		// ------------------------------------------------------
		// Recherche de corélation id de session<->utilisateur
		$session = false;
		foreach($this->liste_ip AS $ip => $sess){
			// Session recente donc trouvee en cache
			if($sess['id_session'] == $this->id_session){
				$session = $this->id_session;
				break;
			}
		}
		if ($session == false){
			// Recherche directe en base
			// La session existe en base  ?
			$sql = 'SELECT id_session,user_id,date_lastvisite,user_ip 
					FROM '.TABLE_SESSIONS.' WHERE id_session=\''.$this->id_session.'\'
					ORDER BY date_lastvisite DESC LIMIT 1';
			if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,5,__FILE__,__LINE__,$sql); 
			if ($c->sql_numrows($resultat)==0){
				// La session n'existe pas en base alors on la crée
				$sql = 'INSERT INTO '.TABLE_SESSIONS.' (id_session,user_id,date_lastvisite,user_ip) 
					VALUES (\''.$this->id_session.'\',1,\''.$this->time.'\',\''.$this->get_ip().'\')';
				if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,5,__FILE__,__LINE__,$sql); 
			}
			$this->listing_ip(true);
		}
		// ------------------------------------------------------
		// Informations sur l'utilisateur
		$cache->files_cache['infos_user'] = array($this->path_cache.$this->id_session, 'global $session; return $session->infos_user();',$this->cf['cache_session_user']);
		$this->info_user = $cache->appel_cache('infos_user');
		return $this->info_user ;
	}
	
	//
	// Connecte le user et met a jour les cache
	function login($user_id,$cookie=false){
		global $cache,$droits,$c;
		// On détruit la session
		$this->destroy_session();
		// On crée un nouvel id de session
		if (!session_id()) session_start();
		@session_regenerate_id();
		$this->id_session = session_id();
		// On enregistre l'utilisateur
		$droits->charge_droits_user($user_id,true); // On force le chargement de ces droits à chaque login
		$sql = 'INSERT INTO '.TABLE_SESSIONS.' (id_session,user_id,date_lastvisite,user_ip) VALUES 
				(\''.$this->id_session.'\','.$user_id.','.$this->time.',\''.$this->ip.'\')';
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,5,__FILE__,__LINE__,$sql); 
		// on place le cookie
		$expire = ($cookie==true)? ($this->time+$this->cf['cookie_time']):($this->time+3600);
		setcookie($this->cf['cookie_name'], $this->id_session, $expire, $this->cf['path']);
		// Update cache
		$this->listing_ip(true);
	}
	
	//
	// Deconnecte un utilisateur
	function logout(){
		global $c;
		// Session zone admin
		if (isset($_SESSION['digicode_TTL']))$_SESSION['digicode_TTL'] = 0;
		// Session publique
		$this->destroy_session(false);
		// Création d'une session invite
		if (!session_id()) session_start();
		session_regenerate_id();
		$this->id_session = session_id();
		$sql = 'INSERT INTO '.TABLE_SESSIONS.' (id_session,user_id,date_lastvisite,user_ip) 
			VALUES (\''.$this->id_session.'\',1,\''.$this->time.'\',\''.$this->get_ip().'\')';
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,5,__FILE__,__LINE__,$sql); 
		$this->listing_ip(true);
	}
	
	//
	// Destruction  d'une session
	function destroy_session($delete=true)
	{
		global $c;
		// en base
		if ($delete==true){
			$sql = 'DELETE FROM '.TABLE_SESSIONS.' 
					WHERE date_lastvisite < '.($this->time - $this->cf['cookie_time']).
					' OR id_session=\''.$this->id_session.'\'';
			if (!$c->sql_query($sql)) message_die(E_ERROR,4,__FILE__,__LINE__,$sql);
		}
		// en cache
		if (file_exists($this->path_cache.$this->id_session)){
			unlink($this->path_cache.$this->id_session);
		}
		// chez le visiteur: le cookie
		setcookie($this->cf['cookie_name'], null, ($this->time-3600)); // cookie malleo
		setcookie(get_cfg_var('session.name'), null, ($this->time-3600)); // eventuelle session type PHPSESSID
		$this->id_session = null;
		// Purge du dossier
		$this->purge_dossier_sessions();
	}
	
	//
	// Purge du dossier de sessions
	function purge_dossier_sessions(){
		if (!is_dir($this->path_cache)) return;
		$ch = @opendir($this->path_cache);
		while ($file = @readdir($ch))
		{
			if (!is_dir($this->path_cache.$file)
				&& $file!='.htaccess' 
				&& (filemtime($this->path_cache.$file)<($this->time - 3600))
				&& is_writable($this->path_cache.$file)){
				@unlink($this->path_cache.$file);
			}
		}
		@closedir($ch);
	}
	
	//
	// Recuperation des infos sur le user
	function infos_user()
	{
		global $c,$droits;
		// Recuperation des infos sur le user.
		$sql = 'SELECT s.*, u.* 
				FROM '.TABLE_SESSIONS.' AS s   
				LEFT JOIN '.TABLE_USERS.' AS u 
					ON (s.user_id=u.user_id) 
				WHERE s.id_session=\''.$this->id_session.'\' 
				ORDER BY date_lastvisite DESC LIMIT 1';

		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,6,__FILE__,__LINE__,$sql); 
		if ($c->sql_numrows($resultat) > 0)
		{
			$row = $c->sql_fetchrow($resultat);
			foreach ($row as $key => $value)
			{
				$infos[$key]= $value;
			}
			// On a à faire à un Robot, on actualise les données en fonction
			// On renseigne le Robot_Name dans la session
			if ($infos['user_id']==1){
				global $bots;
				if($robot=$bots->rechercher_robots()){
					$sql = 'UPDATE '.TABLE_SESSIONS.' SET 
								date_lastvisite='.$this->time.',
								pseudo=\''.$robot['robot_name'].'\'
							WHERE id_session=\''.$this->id_session.'\'';
					if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,6,__FILE__,__LINE__,$sql);
					$infos['date_lastvisite'] = $this->time;
					$infos['pseudo']= $robot['robot_name'];
					$infos['localisation']= $robot['robot_url'];
				}
				$infos['langue'] = $this->cf['default_langue'];
			}
			// toutes les 5 minutes on rafraichit la date de derniere visite
			if ($infos['date_lastvisite'] < ($this->time-300)){
				// Maj de la date de dernier passage
				$sql = 'UPDATE '.TABLE_SESSIONS.' SET date_lastvisite='.$this->time.' 
						WHERE id_session=\''.$this->id_session.'\'';
				if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,6,__FILE__,__LINE__,$sql);
				$infos['date_lastvisite'] = $this->time;
			}
			$infos['user_connecte']= ($infos['user_id']>1)?1:0;			
			$infos['rules']= $droits->charge_droits_user($infos);
			$infos['groupes']= $droits->user_groupes;
			return $this->info_user = $infos;
		}
		return false;
	}
	
	//
	// fournit la liste des ip
	function listing_ip($force=false){
		global $cache;
		return $this->liste_ip = $cache->appel_cache('listing_ip',$force);
	}
	
	//
	// on recupere les IP uniques, id_session des membres connectes dans l'heure.
	// au dela ils devront accepter les cookies.
	function liste_ip()
	{
		global $c;
		$sql = 'SELECT DISTINCT user_ip, id_session, user_id  
				FROM '.TABLE_SESSIONS.'
				WHERE date_lastvisite>'.($this->time-3600).' 
				ORDER BY date_lastvisite ASC';
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,34,__FILE__,__LINE__,$sql);
		$liste_ip = array();
		while ($row = $c->sql_fetchrow($resultat))
		{
			$liste_ip[$row['user_ip']]['id_session'] = $row['id_session'];
			$liste_ip[$row['user_ip']]['user_id'] = $row['user_id'];
		}
		return $liste_ip;
	}
	
	//
	// IP du visiteur
	function get_ip() 
	{
		if (!isset($this->ip)){
		    if (isset($_SERVER['HTTP_CLIENT_IP'])){ 
				$this->ip = $_SERVER['HTTP_CLIENT_IP']; 
		    }elseif(isset($_SERVER['HTTP_X_FORWARDED_FOR'])){ 
		        $this->ip = $_SERVER['HTTP_X_FORWARDED_FOR']; 
		    }else{ 
		        $this->ip = $_SERVER['REMOTE_ADDR']; 
		    }
		}
		return $this->ip;
	}
	
	//
	// Test IP bannie
	function ip_bannie(){
		global $droits,$lang;
		$droits->Charge_bannis();
		if (!is_array($droits->liste_bannis)) return true;
		if (array_key_exists(2,$droits->liste_bannis)){
			foreach ($droits->liste_bannis[2] as $pattern=>$val){
				if (ereg($pattern,$this->ip)){
					load_lang('bannis');
					$fin_ban = ($val['fin_ban']==0)? $lang['L_DEFINITIF']:date('d/m/Y h\h s\m\i\n',$val['fin_ban']);
					die(sprintf($lang['L_ALERTE_BAN_IP'],$fin_ban,$val['raison_ban']));
				}
			}
		}
		return true;
	}
	//
	// Creation des navlinks visuels
	// les navlinks sont portes dans la session pour un futur projet : le tracage des utilisateurs sur le site
	function make_navlinks($texte,$lien=false)
	{
		if (is_array($texte) && $lien==false){
			foreach ($texte as $k=>$v) $this->navlink[] = array($k,$v);
		}else{
			$this->navlink[] = array($texte,$lien);
		}
		return true;
	}
	
	//
	// trace l'activite des membres sur le site
	function tracer_page(){
		global $c,$tpl;
		
		// le Traceur est-il activé ?
		if($this->cf['activer_traceur'] == 1){
			$libelle_page = $tpl->titre_page;
			$elmts = explode(' :: ',$libelle_page);
			array_reverse($elmts);
			$libelle_page = implode('> ',$elmts);
			
			$sql = 'INSERT INTO '.TABLE_SESSIONS_SUIVIES.' (user_id,pseudo,url_page,libelle_page,date) VALUES 
			('.$this->info_user['user_id'].',\''.$this->info_user['pseudo'].'\',\''.addslashes($_SERVER['REQUEST_URI']).'\',
			\''.addslashes($libelle_page).'\','.$this->time.')';
			if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,34,__FILE__,__LINE__,$sql);
			
			// On supprime les logs superieurs à 3 mois
			// Pour ne pas trop surcharger Malleo on le fait uniquement  :
			// si le visiteur n'est pas membre du site et 1 fois sur 2 en moyenne
			if ($this->info_user['user_id'] == 1  && $this->time%2){
				$sql = 'DELETE FROM '.TABLE_SESSIONS_SUIVIES.' WHERE date < '.($this->time-7862400);
				if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,34,__FILE__,__LINE__,$sql);
			}
		}
		return true;
	}
}
?>