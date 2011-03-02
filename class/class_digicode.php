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
if ( !defined('PROTECT_ADMIN') )
{
	die("Tentative de Hacking");
}

require_once($root.'class/class_image.php');
class digicode extends image
{
	// Combien de cases pour former le digicode
	var $nbre_cases = 25;
	// Combien de colonnes dans le damier
	var $nbre_colonnes = 5;
	// Caracteres possibles
	var $caracteres_autorises = array(0,1,2,3,4,5,6,7,8,9);
	// Longueur clef par caractere
	var $longueur_clef = 5;
	// Liste des clefs pour cette tentative de connexion
	var $clefs = array();
	// Repertoire de cache	
	var $url_cache='cache/digicode/';
	var $format = 'png';
	var $taille_texte = 14;
	// Largeur des cases
	var $largeur_cases = 30;
	var $police = array();
	var $couleur_texte = '#000000';
	var $couleur_fond = '#FFFFFF';
	
	//
	// Init
	function digicode(){
		global $cf,$root;
		// Polices dispos
		$this->police[] = $root.'data/fonts/verdana.ttf';
		$this->police[] = $root.'data/fonts/Alanden_.ttf';
		
		$this->code_acces_za = ereg_replace('[^0-9]','',$cf->config['digicode_acces_zone_admin']);
		if ($this->code_acces_za == '')$this->code_acces_za='0000';
	}

	//
	// Cree une image avec l'adresse en texte.	
	function generer_image_case_digicode($texte,$url){
		$this->image = imagecreatetruecolor($this->largeur_cases,$this->largeur_cases);
		$rgb = $this->html2rgb($this->couleur_fond);
		$background = ImageColorAllocate ($this->image, $rgb[0],$rgb[1],$rgb[2]); 
		imagefill($this->image,0,0,$background);	
		// Parasites
		$this->genere_parasites($texte);
		// Texte
		$this->position_x=4;
		$this->position_y=20;
		shuffle($this->police);
		$this->inserer_texte($texte,$this->taille_texte,$this->couleur_texte,$this->police[0]);
		// On enregistre
		$this->extension = $this->format;
		$this->save_image($url);
	}
	//
	// Cree des symboles parasites pour que la taille des images ne soit pas constante
	function genere_parasites($texte){
		// Rectangle
		$rgb = $this->html2rgb('#d2ee95');
		$couleur_motifs = ImageColorAllocate($this->image, $rgb[0],$rgb[1],$rgb[2]);
		$position = mt_rand(0,$this->largeur_cases);
		imagefilledrectangle($this->image, $position, $position, ($position+20), ($position+10), $couleur_motifs);
		// elypse
		$rgb = $this->html2rgb('#cfe2f0');
		$couleur_motifs = ImageColorAllocate($this->image, $rgb[0],$rgb[1],$rgb[2]);
		$position = mt_rand(0,$this->largeur_cases);
		imagefilledellipse($this->image, $position, $position, $position, $position, $couleur_motifs);
		// Si il n'y a pas de texte on rajoute un peu de couleur noire
		$texte=trim($texte);
		if (empty($texte)){
			$rgb = $this->html2rgb($this->couleur_texte);
			$couleur_motifs = ImageColorAllocate($this->image, $rgb[0],$rgb[1],$rgb[2]);
			imageline($this->image, mt_rand(0,$this->largeur_cases), mt_rand(0,$this->largeur_cases), mt_rand(0,$this->largeur_cases), mt_rand(0,$this->largeur_cases), $couleur_motifs);
			$couleur_motifs = ImageColorAllocate($this->image, $rgb[0],$rgb[1],$rgb[2]);
			imageline($this->image, mt_rand(0,$this->largeur_cases), mt_rand(0,$this->largeur_cases), mt_rand(0,$this->largeur_cases), mt_rand(0,$this->largeur_cases), $couleur_motifs);
			$couleur_motifs = ImageColorAllocate($this->image, $rgb[0],$rgb[1],$rgb[2]);
			imageline($this->image, mt_rand(0,$this->largeur_cases), mt_rand(0,$this->largeur_cases), mt_rand(0,$this->largeur_cases), mt_rand(0,$this->largeur_cases), $couleur_motifs);
		}
	}
	
	//
	// Genere une image pour la case du digicode
	function appel_image_case_digicode($numero,$clef){
		if (empty($numero) || empty($clef)) return false;
		global $root;
		if (!is_dir($root.$this->url_cache)) $this->creer_dossier_image($root.$this->url_cache);
		$url = $root.$this->url_cache.md5($clef).'.'.$this->format;
		if (!file_exists($url)){
			$this->generer_image_case_digicode($numero,$url);
		}
		return $url;
	}
	
	//
	// Affiche le digicode
	function afficher_digicode(){
		global $tpl,$root,$lang;
		$this->purger_cache_digicode();
		$tpl->set_filenames(array(
			'body_admin' => $root.'html/admin_digicode.html'
		));
		$liste_cases = array_pad($this->caracteres_autorises,$this->nbre_cases,' ');
		shuffle($liste_cases);
		include_once($root.'fonctions/fct_maths.php');
		$i = 0;
		foreach($liste_cases AS $numero){
			$clef = generate_key($this->longueur_clef);
			$this->clefs[$numero] = $clef;
			if ($i%$this->nbre_colonnes ==0) $tpl->assign_block_vars('tr', array());
			$tpl->assign_block_vars('tr.case', array(
				'IMAGE'	=> $this->appel_image_case_digicode(' '.$numero,$clef),
				'CLEF'	=> $clef,
			));
			$i++;
		}
		unset($liste_cases);
		$this->encoder_code_access();
		$this->declarer_clefs_langue();
		$nbre_tentatives = 0;
		if (isset($_SESSION['digicode_nbre_tentatives']) && $_SESSION['digicode_nbre_tentatives']>0){
			$tpl->assign_block_vars('alerte_tentatives', array());
			$nbre_tentatives = $_SESSION['digicode_nbre_tentatives'];
		}
		$tpl->assign_vars(array(
			'LARGEUR_DIGICODE'	=> ($this->largeur_cases*$this->nbre_colonnes+35),
			'NBRE_COLONNES'		=> $this->nbre_colonnes,
			'L_ALERTE_TENTATIVES'	=> sprintf($lang['L_ALERTE_TENTATIVES'],$nbre_tentatives)
		));		
	}
	
	//
	// Encode la clef de la zone admin pour la stocker en session
	function encoder_code_access(){
		$caracteres = str_split($this->code_acces_za,1);
		$code_crypte = '';
		// Parcours du code secret
		foreach($caracteres AS $c){	$code_crypte .= $this->clefs[$c]; }
		$_SESSION['digicode_clef_acceptee'] = $code_crypte;
	}

	
	//
	// Verifie que le code saisi corresponde bien au code definis par les fondateurs
	function verifier_code($vars){
		$this->purger_cache_digicode();
		if (!isset($vars['code']) 
			|| empty($vars['code']) 
			|| !isset($_SESSION['digicode_clef_acceptee']) 
			|| empty($_SESSION['digicode_clef_acceptee']))
		{
			$this->redirect_echec();
		}else{
			$code_saisis = eregi_replace('[^a-z0-9]','',$vars['code']);	
			if ($code_saisis != $_SESSION['digicode_clef_acceptee']){
				$this->redirect_echec();
			}else{
				$_SESSION['digicode_TTL'] = time();
				$_SESSION['digicode_clef_acceptee'] = null;
				$_SESSION['digicode_nbre_tentatives'] = 0;
				header('location: '.$this->retour);
				exit;
			}
		}
	}
	
	//
	// Redirection apres tentative ratee
	function redirect_echec(){
		$_SESSION['digicode_clef_acceptee'] = null;
		$_SESSION['digicode_nbre_tentatives'] = (isset($_SESSION['digicode_nbre_tentatives']))?$_SESSION['digicode_nbre_tentatives']+1:1;
		if ($_SESSION['digicode_nbre_tentatives']>2){
			global $droits,$session,$lang,$user;
			$this->alerte_mail_fondateurs();
			$droits->ban_ip($session->ip,time(),sprintf($lang['L_DIGICODE_HACK'],$user['pseudo']));
			header('location: ./index.php');
		}
		$this->afficher_digicode();
	}
	
	//
	// Mail pour prevenir les fondateurs
	function alerte_mail_fondateurs(){
		global $root,$cf,$c,$lang,$user,$session;
		load_lang('emails');
		
		require_once($root.'class/class_mail.php');
		$email = new mail();
		$email->Subject = $lang['L_DIGICODE_ALERTE_HACKING'];
		$email->titre_message = $lang['L_DIGICODE_ALERTE_HACKING_DETAIL'];
		$email->message_explain = $lang['L_DIGICODE_ALERTE_HACKING_EXPLAIN'];
		$email->formate_html(sprintf($lang['L_DIGICODE_ALERTE_HACKING_MSG'], date('d/m/Y H\hi'), $user['pseudo'], $session->ip, gethostbyaddr($session->ip)));

		$sql = 'SELECT email, pseudo FROM '.TABLE_USERS.' WHERE level>8';
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,1300,__FILE__,__LINE__,$sql);
		while($row = $c->sql_fetchrow($resultat)){
			$email->AddAddress($row['email'],$row['pseudo']);
		}
		$email->Send();	
	}
	
	//
	// Purger le dossier des images
	function purger_cache_digicode(){
		global $root;
		$this->url_cache = $root.$this->url_cache;
		if (!is_dir($this->url_cache)) return true;
		
		$ch = @opendir($this->url_cache);
		while ($fichier = @readdir($ch))
		{
			if ($fichier != '.' && $fichier != '..' 
				&& $fichier != '.htaccess' && !is_dir($this->url_cache.$fichier))
			{
				@unlink($this->url_cache.$fichier);
			}
		}
		@closedir($ch);	
		return true;	
	}
	
	//
	// Declaration des clefs de langue
	function declarer_clefs_langue(){
		global $tpl,$lang,$img;
		$tpl->assign_vars(array(
			'L_SAISISSEZ_VOTRE_CODE'	=> $lang['L_SAISISSEZ_VOTRE_CODE'],
			'L_ENTRER'					=> $lang['L_ENTRER'],
			'L_EFFACER'					=> $lang['L_EFFACER'],
			'L_ADMIN_DIGICODE'			=> $lang['L_ADMIN_DIGICODE'],
			'L_ADMIN_DIGICODE_EXPLAIN'	=> $lang['L_ADMIN_DIGICODE_EXPLAIN'],
			'I_DIGICODE'				=> $img['digicode'],
		));
	}
}


?>