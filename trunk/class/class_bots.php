<?php
/*
|------------------------------------------------------------------------------------------------------------
| Software: Malleo ( CMS )
| Contact:  BalHack - http://www.balhack.com
| Contact:  SP - http://www.malleo-cms.com
| Support: http://www.malleo-cms.com?module=forum
|  Documentation : Support: http://www.malleo-cms.com?module=wiki
|------------------------------------------------------------------------------------------------------------
|  Author: Balhack & Stephane RAJALU
|  Copyright (c) 2008-2009, Balhack & Stephane RAJALU All Rights Reserved
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

class bots{

	// 
	// Liste de base des principaux bots de la toile
	var $bot_list = array(
		'AdsBot [Google]'			=> array('AdsBot-Google', 'http://www.google.com/webmasters/bot.html'),
		'Alexa [Bot]'				=> array('ia_archiver', 'http://pages.alexa.com/help/webmasters/index.html'),
		'Alta Vista [Bot]'			=> array('Scooter/', 'http://www.altavista.com/'),
		'Ask Jeeves [Bot]'			=> array('Ask Jeeves', 'http://about.ask.com/en/docs/about/webmasters.html'),
		'Baidu [Spider]'			=> array('Baiduspider+(', 'http://www.baidu.com/search/spider.htm'),
		'Exabot [Bot]'				=> array('Exabot/', 'http://www.exalead.com/search'),
		'FAST Enterprise [Crawler]'	=> array('FAST Enterprise Crawler', 'http://www.pagesjaunes.fr/'),
		'FAST WebCrawler [Crawler]'	=> array('FAST-WebCrawler/', 'http://fast.no/us/products/fast_web_search/crawler_faq'),
		'Francis [Bot]'				=> array('http://www.neomo.de/', 'http://www.neomo.de/'),
		'Gigabot [Bot]'				=> array('Gigabot/', 'http://www.gigablast.com/'),
		'Google Adsense [Bot]'		=> array('Mediapartners-Google', 'http://www.google.com/webmasters/bot.html'),
		'Google Desktop'			=> array('Google Desktop', 'http://www.google.com/webmasters/bot.html'),
		'Google Feedfetcher'		=> array('Feedfetcher-Google', 'http://www.google.com/webmasters/bot.html'),
		'Google [Bot]'				=> array('Googlebot', 'http://www.google.com/webmasters/bot.html'),
		'Heise IT-Markt [Crawler]'	=> array('heise-IT-Markt-Crawler', ''),
		'Heritrix [Crawler]'		=> array('heritrix/1.', 'http://www.cs.washington.edu/research/networking/websys/'),
		'IBM Research [Bot]'		=> array('ibm.com/cs/crawler', 'http://www.almaden.ibm.com/cs/crawler/'),
		'ICCrawler [Crawler]'		=> array('ICCrawler - ICjobs', 'http://www.iccenter.net/'),
		'ichiro [Crawler]'			=> array('ichiro/2', 'http://www.goo.ne.jp/'),
		'Majestic-12 [Bot]'			=> array('MJ12bot/', 'http://www.majestic12.co.uk/projects/dsearch/mj12bot.php'),
		'Metager [Bot]'				=> array('MetagerBot/', 'http://metager.de'),
		'MSN NewsBlogs [Bot]'		=> array('msnbot-NewsBlogs/', 'http://search.msn.com/msnbot.htm'),
		'MSN [Bot]'					=> array('msnbot/', 'http://search.msn.com/msnbot.htm'),
		'MSNbot Media [Bot]'		=> array('msnbot-media/', 'http://search.msn.com/msnbot.htm'),
		'NG-Search [Bot]'			=> array('NG-Search/', 'http://www.ng-search.com'),
		'Nutch [Bot]'				=> array('Nutch', 'http://lucene.apache.org/'),
		'OmniExplorer [Bot]'		=> array('OmniExplorer_Bot/', 'http://www.omni-explorer.com'),
		'Online link [Validator]'	=> array('online link validator', ''),
		'Orange [Spider]'			=> array('OrangeSpider', 'http://www.orangeslicer.com'),
		'psbot [Picsearch]'			=> array('psbot', 'http://www.picsearch.com/bot.html'),
		'Seekport [Bot]'			=> array('Seekbot/', 'http://www.seekbot.net/bot.html'),
		'Sensis [Crawler]'			=> array('Sensis Web Crawler', 'http://www.sensis.com.au/'),
		'SEO Crawler'				=> array('SEO search Crawler/', ''),
		'Seoma [Crawler]'			=> array('Seoma [SEO Crawler]', ''),
		'SEOSearch [Crawler]'		=> array('SEOsearch/', ''),
		'Snappy [Bot]'				=> array('Snappy', 'http://www.urltrends.com/'),
		'Steeler [Crawler]'			=> array('http://www.tkl.iis.u-tokyo.ac.jp/~crawler/', 'http://www.tkl.iis.u-tokyo.ac.jp/~crawler/'),
		'Synoo [Bot]'				=> array('SynooBot/', 'http://www.synoo.com/search/bot.html'),
		'Telekom [Bot]'				=> array('crawleradmin.t-info@telekom.de', 'http://www.telekom.de'),
		'TurnitinBot [Bot]'			=> array('TurnitinBot/', 'http://www.turnitin.com/robot/crawlerinfo.html'),
		'Voyager [Bot]'				=> array('voyager/1.0', 'http://www.kosmix.com/crawler.html'),
		'W3 [Sitesearch]'			=> array('W3 SiteSearch Crawler', ''),
		'W3C [Linkcheck]'			=> array('W3C-checklink/', ''),
		'W3C [Validator]'			=> array('W3C_*Validator', 'http://validator.w3.org/docs/checklink.html'),
		'WiseNut [Bot]'				=> array('http://www.WISEnutbot.com', 'http://www.wisenut.com/'),
		'YaCy [Bot]'				=> array('yacybot', 'http://www.yacy.net'),
		'Yahoo MMCrawler [Bot]'		=> array('Yahoo-MMCrawler/', 'http://help.yahoo.com/help/us/ysearch/slurp'),
		'Yahoo Slurp [Bot]'			=> array('Yahoo! DE Slurp', 'http://help.yahoo.com/help/us/ysearch/slurp'),
		'Yahoo [Bot]'				=> array('Yahoo! Slurp', 'http://help.yahoo.com/help/us/ysearch/slurp'),
		'YahooSeeker [Bot]'			=> array('YahooSeeker/', 'http://help.yahoo.com/help/us/ysearch/crawling/crawling-01.html'),
	);
	
	//
	// Renvoie la liste des bots enregistrés, et charge le cache au besoin
	function liste_bots(){
		global $cache;
		if (isset($this->liste_bots)){
			return $this->liste_bots;
		}else{
			return $this->liste_bots = $cache->appel_cache('listing_bots');
		}
	}
	
	//
	// formate un bot
	function formate_bots($robot_name){
		if (!isset($this->liste_bots))$this->liste_bots();
		if (array_key_exists($robot_name,$this->liste_bots)){
			return '<a href="'.$this->liste_bots[$robot_name]['robot_url'].'" class="bot">'.$robot_name.'</a>';	
		}else{
			return '<a class="bot">'.$robot_name.'</a>';	
		}
	}
	
	//
	// Force la maj du cache
	function update_cache(){
		global $cache;
		$this->liste_bots = $cache->appel_cache('listing_bots',true);
	}

	//
	// Fonction utilisée pour génerer le cache, 
	// elle renvoie un tableau contenant les informations  de la tables a_robots
	function lister_bots(){
		global $c;
		$sql = 'SELECT robot_user_agent, robot_name, robot_url, robot_actif 
				FROM ' . TABLE_ROBOTS . '
				ORDER BY LENGTH(robot_user_agent) DESC';
		if (!$resultat = $c->sql_query($sql)) message_die(E_ERROR,64,__FILE__,__LINE__,$sql);
		if ($c->sql_numrows($resultat) == 0 ){
			$this->initialise_liste_robots();
			$liste = $this->lister_bots();
		}else{
			$liste = array();
			while($row = $c->sql_fetchrow($resultat)){
				$liste[$row['robot_name']] = $row;
			}
		}
		return $liste;
	}

	//
	// Recherche à quel bot correspond un user_agent complet
	// Détection des users agents pouvant être des bots
	function rechercher_robots(){
		if (!is_array($this->liste_bots())) return false;
		$existe = false;
		foreach ($this->liste_bots() AS $row){
			if ($row['robot_user_agent'] && preg_match('#' . str_replace('\*', '.*?', preg_quote($row['robot_user_agent'], '#')) . '#i', $_SERVER['HTTP_USER_AGENT']))
			{
				if ($row['robot_actif']==1){
					return $row;
				}else{
					$existe = true;
				}
			}
		}
		// On en profite pour regarder si il ne s'agit pas d'un nouveau robot
		// On recherche dans le user agent 3 mots clefs : bot /  spider / crawler
		if ($existe == false && preg_match('#(bot|spider|crawl)#i', $_SERVER['HTTP_USER_AGENT'])){
			$this->ajoute_robot_user_agent($_SERVER['HTTP_USER_AGENT']);
		}
		return false;
	}
	
	//
	// Site tout neuf, ou une liste de robots trop longue a entrainé un truncate de la table
	// On ajoute une liste par défaut de bots
	function initialise_liste_robots(){
		global $c;
		if (sizeof($this->bot_list)==0) return false;
		$sql = 'INSERT INTO ' . TABLE_ROBOTS . ' (robot_name, robot_user_agent, robot_url, robot_actif)  VALUES ';
		$sql2 = '';
		foreach($this->bot_list AS $robot_name=>$robot_spec){
			if ($sql2!='') $sql2.=',';
			$sql2 .= '(\''.$robot_name.'\',\''.$robot_spec[0].'\',\''.$robot_spec[1].'\',1)';
		}
		if ($sql2!=''){
			$sql .= $sql2;
			if (!$resultat = $c->sql_query($sql)) message_die(E_ERROR,65,__FILE__,__LINE__,$sql); 
		}
		return true;
	}
	
	//
	// Ajout d'un user_agent suspecté d'être un bot
	// Par défaut il est ajouté en inactif.	
	function ajoute_robot_user_agent($user_agent){
		global $c;
		preg_match("#([\w]+?://[\w\#$%&~/.\-;:=,?@\[\]+]*)#si",$user_agent,$url);
		$robot_url = (isset($url[1]))? '\''.$url[1].'\'':'null';
		preg_match("#([\w]+?(bot|spider|crawl))|((bot|spider|crawl)(\w]+?))#si",$user_agent,$nom);
		$robot_nom = (isset($nom[1]))? '\''.$nom[1].'\'':'null';
		$sql = 'INSERT INTO ' . TABLE_ROBOTS . ' (robot_name,robot_user_agent,robot_url,robot_actif) VALUES	
		('.$robot_nom.',\''.$user_agent.'\','.$robot_url.',0)';
		if (!$resultat = $c->sql_query($sql)) message_die(E_ERROR,66,__FILE__,__LINE__,$sql);
		$this->update_cache();
	}
	
	//
	// Active/Desactive l'utilisation d'un robot
	function active_robot($id_robot){
		global $c;
		$sql = 'UPDATE ' . TABLE_ROBOTS . ' SET robot_actif=true WHERE id_robot='.$id_robot;
		if (!$resultat = $c->sql_query($sql)) message_die(E_ERROR,67,__FILE__,__LINE__,$sql);
		$this->update_cache();
	}	
	function desactive_robot($id_robot){
		global $c;
		$sql = 'UPDATE ' . TABLE_ROBOTS . ' SET robot_actif=false WHERE id_robot='.$id_robot;
		if (!$resultat = $c->sql_query($sql)) message_die(E_ERROR,67,__FILE__,__LINE__,$sql);
		$this->update_cache();		
	}

	//
	// Supprime un bot via son ID
	function supprime_bot($id_robot){
		global $c;
		$sql = 'DELETE FROM ' . TABLE_ROBOTS . ' WHERE id_robot='.$id_robot;
		if (!$resultat = $c->sql_query($sql)) message_die(E_ERROR,68,__FILE__,__LINE__,$sql);
		$this->update_cache();		
	}
	
	//
	// Ajoute un robot manuellement depuis la zone d'administration
	// Par défaut on active le robot
	function ajoute_robot($saisie){
		global $c;
		$robot_name = protection_chaine($saisie['robot_name']);
		$robot_user_agent = protection_chaine($saisie['robot_user_agent']);
		$robot_url = protection_chaine($saisie['robot_url']);
		if (empty($robot_name) || empty($robot_user_agent)) return false;
		
		$sql = 'INSERT INTO ' . TABLE_ROBOTS . ' (robot_name, robot_user_agent, robot_url, robot_actif) VALUES	
		(
			\''.$robot_name.'\',
			\''.$robot_user_agent.'\',
			\''.$robot_url.'\',		
			1
		)';
		if (!$resultat = $c->sql_query($sql)) message_die(E_ERROR,66,__FILE__,__LINE__,$sql);	
		$this->update_cache();
	}
	
	//
	// Edite les informations sur un robot
	function edite_robot($saisie){
		global $c;
		$robot_name = protection_chaine($saisie['robot_name']);
		$robot_user_agent = protection_chaine($saisie['robot_user_agent']);
		$robot_url = protection_chaine($saisie['robot_url']);
		if (empty($robot_name) || empty($robot_user_agent) || empty($saisie['id_robot'])) return false;
		
		$sql = 'UPDATE ' . TABLE_ROBOTS . ' SET
					robot_name=\''.$robot_name.'\', 
					robot_user_agent=\''.$robot_user_agent.'\', 
					robot_url=\''.$robot_url.'\'
				WHERE id_robot='.intval($saisie['id_robot']);
		if (!$resultat = $c->sql_query($sql)) message_die(E_ERROR,66,__FILE__,__LINE__,$sql);	
		$this->update_cache();
	}
}
?>