<?php


class arcade_admin extends arcade{
	//  informations du jeu
	var $xml;
	// nom du fichier contenant les fichiers du jeu
	var $xml_file = 'info.xml';
	// Masque pour les fichiers copies
	var $umask = 0777;

	
	
	// nettoyage des donnees saisies par l'utilisateur
	function clean($vars)
	{
		foreach($vars as $key=>$val)
		{
			switch($key)
			{
				// tableau
				case 'id_triche':
				case 'id_cats':
				case 'jeu':  
				$this->$key = (is_array($val))? ((sizeof($val)==0)?'""':implode(',',$val)):intval($val); break;
				// entier
				case 'id_jeu':	
				case 'id_cat':	
				case 'nbre_jeux_page':	
				case 'nbre_colonnes':	
				case 'nbre_colonnes_jeux':	
				case 'affichage_fiche_jeux':	
				case 'affichage_jeu':	
				case 'largeur':	
				case 'hauteur':	
				case 'fps':	
				case 'controles':	
				case 'score_sens':	
				case 'actif':	
				case 'start':	$this->$key = intval($val); break;
				// Chaine alphanumerique avec - et _
				case 'salle':	
				case 'module':	$this->$key = preg_replace('/[^a-z0-9_-]/i','',$val); break;
				//Chaine de caracteres
				case 'affichage_mod':
				case 'titre_salle':
				case 'titre_cat':
				case 'icone':
				case 'description_salle':
				case 'nom_jeu':
				case 'description':
				case 'variable':
				case 'nom_swf':
				case 'image_petite':
				case 'image_grande':
				case 'module':	$this->$key = protection_chaine($val); break;
				// Binaire
				case 'activer_mail_champion':	
				case 'activer_chat':	
				case 'activer_favoris':	$this->$key = ($val==1)? 1:0; break;
			}
		}			
	}
	
	//
	// Enregistre la configuration de la salle selectionnee
	function enregistrer_config_salle(){
		global $c;
		$sql = 'UPDATE '.TABLE_ARCADE_MODULES.' SET
					titre_salle				=\''.	$this->titre_salle.'\',
					description_salle		=\''.	$this->description_salle.'\',
					affichage_fiche_jeux	='.		$this->affichage_fiche_jeux.',
					affichage_jeu			='.		$this->affichage_jeu.',
					affichage_mod			=\''.	$this->affichage_mod.'\',
					nbre_jeux_page			='.		$this->nbre_jeux_page.',
					nbre_colonnes_jeux		='.		$this->nbre_colonnes_jeux.',
					nbre_colonnes			='.		$this->nbre_colonnes.',
					activer_favoris			='.		$this->activer_favoris.',
					activer_mail_champion	='.		$this->activer_mail_champion.'
				WHERE module=\''.$this->module.'\'';
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,1300,__FILE__,__LINE__,$sql); 
	}
	
	//
	// Enregistre la nouvelle categorie dans une salle
	function ajouter_categorie(){
		global $c;
		$sql = 'INSERT INTO '.TABLE_ARCADE_CATS.' (module,titre_cat,icone) VALUES
				(\''.$this->module.'\',\''.	$this->titre_cat.'\',\''.	$this->icone.'\')';
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,1300,__FILE__,__LINE__,$sql); 
	}
	
	//
	// Supprime la categorie selectionnee
	function supprimer_categorie()
	{
		global $c;
		$sql = 'DELETE FROM '.TABLE_ARCADE_CATS.' WHERE id_cat='.$this->id_cat;
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,1300,__FILE__,__LINE__,$sql); 
		
		// Supprimer les association de jeux
		$sql = 'DELETE FROM '.TABLE_ARCADE_CATS_JEUX.' WHERE id_cat='.$this->id_cat;
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,1300,__FILE__,__LINE__,$sql); 
	}
	
	//
	// Enregistre les modifications apportees a une categorie
	function editer_categorie(){
		global $c;
		$sql = 'UPDATE '.TABLE_ARCADE_CATS.' SET
					module		=\''.	$this->module.'\',
					titre_cat	=\''.	$this->titre_cat.'\',
					icone		=\''.	$this->icone.'\'
				WHERE id_cat='.$this->id_cat;
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,1300,__FILE__,__LINE__,$sql); 
	}
	
	
	//
	// Ajoute un jeu dans l'arcade
	function ajouter_jeu($jeu){
		global $lang,$c,$root;
		$path_jeu = $this->path_games. '_installer/'.$jeu .'/';
		
		// On verifie la presence du dossier
		if (!is_dir($path_jeu))			return sprintf($lang['L_ERREUR_PATH_JEU'], $path_jeu);
		
		// On verifie que le dossier est inscriptible et donc deplacable
		if (!is_writable($path_jeu))	return sprintf($lang['L_ERREUR_CHMOD_JEU'], $path_jeu);
		
		// On verifie la presence du .xml
		$xml_file = $path_jeu . $this->xml_file;
		if (!file_exists($xml_file))	return sprintf($lang['L_ERREUR_PATH_XML'], $xml_file);
		
		// Verification du dossier
		if (($retour = $this->check_xml_file($path_jeu)) !== true) return $retour;

		// On declare le jeu
		$sql = 'INSERT INTO '.TABLE_ARCADE_JEUX.' (
					dossier_jeu, 
					nom_jeu, 
					description, 
					variable, 
					nom_swf, 
					controles, 
					score_sens, 
					image_petite, 
					image_grande, 
					largeur, 
					hauteur, 
					date_ajout,
					fps) 
				VALUES (
					\''.	protection_chaine($jeu).'\', 
					\''.	addslashes(protection_chaine($this->xml->titre)).'\', 
					\''.	addslashes(protection_chaine($this->xml->description)).'\', 
					\''.	protection_chaine($this->xml->variable).'\',
					\''.	protection_chaine($this->xml->swf).'\', 
					'.		intval($this->xml->controles).', 
					'.		intval($this->xml->score_sens).', 
					\''.	protection_chaine($this->xml->image_petite).'\', 
					\''.	protection_chaine($this->xml->image_grande).'\', 
					'.		intval($this->xml->largeur).', 
					'.		intval($this->xml->hauteur).', 
					'.		time().',
					'.		intval($this->xml->fps).')';
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,1300,__FILE__,__LINE__,$sql); 
		$this->id_jeu = $c->sql_nextid();
		
		// On deplace le jeu a la racine
		if (function_exists('rename')){
			@rename($this->path_games. '_installer/'.$jeu, $this->path_games.$jeu);
		}else{
			@mkdir($this->path_games.$jeu,$this->umask);
			$this->copydir($root.$this->path_games. '_installer/'.$jeu, $root.$this->path_games.$jeu);
		}

		return true;		
	}
	
	//
	// On attribue ce jeu dans les categories selectionnees
	function affecter_jeu_dans_categorie($id_jeu,$id_cat){
		global $c;
		$sql = 'INSERT INTO '.TABLE_ARCADE_CATS_JEUX.' (id_jeu,id_cat) VALUES ('.$id_jeu.','.$id_cat.')';
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,1300,__FILE__,__LINE__,$sql); 
	}
	
	//
	// Verifie la presence du xml du jeu, verifie egalement que tous les elements attendus sont presents dedans.
	function check_xml_file($path_jeu){
		global $lang;
		
		// On extrait les donnees contenues dans le .xml
		$this->xml = simplexml_load_file($path_jeu.$this->xml_file);
		
		// On verifie que tous les elements sont presents
		$elmts = array('titre','description','variable','image_petite','image_grande','swf',
						'score_sens','controles','largeur','hauteur','adapteur','url_adapteur','fps');
		foreach ($elmts as $k=>$v){
			if (!isset($this->xml->$v)) return sprintf($lang['L_ERREUR_ELEMENT_XML'], $v);
			if ($v!='score_sens' && empty($this->xml->$v)) return sprintf($lang['L_ERREUR_ELEMENT_XML_VIDE'], $v);
		} 		
		
		// Que les fichiers declares existent reellement
		// .xml + .swf  + petite_image + grande_image
		if (!file_exists($path_jeu.$this->xml->image_petite))	return sprintf($lang['L_ERREUR_FICHIER_MANQUANT'], $this->xml->image_petite);
		if (!file_exists($path_jeu.$this->xml->image_grande))	return sprintf($lang['L_ERREUR_FICHIER_MANQUANT'], $this->xml->image_grande);
		if (!file_exists($path_jeu.$this->xml->swf))			return sprintf($lang['L_ERREUR_FICHIER_MANQUANT'], $this->xml->swf);
		
		// Tout semble OK 
		return true;	
	}
	
	//
	// Met a jour les stats de chaque categorie
	function update_stats_cats(){
		global $c;
		$sql = 'SELECT count(j.id_jeu) AS cpt ,c.id_cat
				FROM '.TABLE_ARCADE_CATS.' as c LEFT JOIN '.TABLE_ARCADE_CATS_JEUX.' as j
				ON (c.id_cat=j.id_cat)
				GROUP BY c.id_cat';
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,1300,__FILE__,__LINE__,$sql);
		while($row = $c->sql_fetchrow($resultat)){
			$sql_update = 'UPDATE '.TABLE_ARCADE_CATS.' SET nbre_jeux_cat='.$row['cpt'].' WHERE id_cat='.$row['id_cat'];
			if (!$r = $c->sql_query($sql_update))message_die(E_ERROR,1300,__FILE__,__LINE__,$sql_update);
		}
	}
	
	//
	// Enregistre les modifications apportees a la fiche du jeu
	function editer_jeu(){
		global $c;
		$sql = 'UPDATE '.TABLE_ARCADE_JEUX.' SET 
					nom_jeu			=\''.	$this->nom_jeu.'\',
					description		=\''.	str_to_html($this->description).'\',
					variable		=\''.	$this->variable.'\',
					nom_swf 		=\''.	$this->nom_swf.'\',
					controles 		='.		$this->controles.',
					actif  			='.		$this->actif.',
					score_sens 		='.		$this->score_sens.',
					image_petite 	=\''.	$this->image_petite.'\',
					image_grande	=\''.	$this->image_grande.'\',
					largeur 		='.		$this->largeur.',
					hauteur 		='.		$this->hauteur.',
					fps				='.		$this->fps.'
				WHERE id_jeu='.$this->id_jeu;
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,1300,__FILE__,__LINE__,$sql);
	}
	
	//
	// Supprime une association jeu<=>categorie
	function supprimer_jeu(){
		global $c;
		if (!isset($this->jeu) || !isset($this->id_cat)) return false;
		$sql = 'DELETE FROM '.TABLE_ARCADE_CATS_JEUX.' 
				WHERE id_jeu IN ('.$this->jeu.')
				AND id_cat='.$this->id_cat;
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,1300,__FILE__,__LINE__,$sql);
		$this->update_stats_cats();
	}
	
	//
	// Supprime le jeu de la base, ainsi que dans toutes les tables associees
	function supprimer_totalement_jeu(){
		global $c;
		if (!isset($this->jeu)) return false;
		$this->id_jeu = $this->jeu;
		// On recupere le nom du jeu
		$sql = 'SELECT dossier_jeu FROM '.TABLE_ARCADE_JEUX.' WHERE id_jeu IN ('.$this->jeu.')';
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,1300,__FILE__,__LINE__,$sql);
		while($row = $c->sql_fetchrow($resultat)){
			// On deplace le dossier du jeu dans les jeux a supprimer du FTP
			if (function_exists('rename') && is_writable($this->path_games.$row['dossier_jeu'])){
				@rename($this->path_games.$row['dossier_jeu'], $this->path_games.'_supprimer/'.$row['dossier_jeu']);
			}
		}
		$sql = array();
		// Table jeux 
		$sql[] = 'DELETE FROM '.TABLE_ARCADE_JEUX.' WHERE id_jeu IN ('.$this->jeu.')';
		// Table d'association categories <-> jeux
		$sql[] = 'DELETE FROM '.TABLE_ARCADE_CATS_JEUX.' WHERE id_jeu IN ('.$this->jeu.')';
		// Table scores
		$sql[] = 'DELETE FROM '.TABLE_ARCADE_SCORES.' WHERE id_jeu IN ('.$this->jeu.')';
		// Table sessions
		$sql[] = 'DELETE FROM '.TABLE_ARCADE_SESSIONS.' WHERE id_jeu IN ('.$this->jeu.')';
		// Table favoris
		$sql[] = 'DELETE FROM '.TABLE_ARCADE_FAVORIS.' WHERE id_jeu IN ('.$this->jeu.')';
		// Table triche
		$sql[] = 'DELETE FROM '.TABLE_ARCADE_TRICHES.' WHERE id_jeu IN ('.$this->jeu.')';
		foreach ($sql as $s){
			if (!$resultat = $c->sql_query($s))message_die(E_ERROR,1300,__FILE__,__LINE__,$s);
		}
		// On met a jour le compteur de jeux des categories
		$this->update_stats_cats();
	}	
	
	//
	// Active les jeux selectionnes
	function activer_jeux(){
		global $c;
		if (!isset($this->jeu)) return false;
		$sql = 'UPDATE '.TABLE_ARCADE_JEUX.' 
				SET actif = true
				WHERE id_jeu IN ('.$this->jeu.')';
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,1300,__FILE__,__LINE__,$sql);
	}
	
	//
	// Desactive les jeux selectionnes
	function desactiver_jeux(){
		global $c;
		if (!isset($this->jeu)) return false;
		$sql = 'UPDATE '.TABLE_ARCADE_JEUX.' 
				SET actif = false
				WHERE id_jeu IN ('.$this->jeu.')';
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,1300,__FILE__,__LINE__,$sql);
	}
	
	//
	// Supprime tous les scores des jeux selectionnes
	function mettre_zero_scores(){
		global $c;
		if (!isset($this->jeu)) return false;
		$sql = 'DELETE FROM '.TABLE_ARCADE_SCORES.' 
				WHERE id_jeu IN ('.$this->jeu.')';
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,1300,__FILE__,__LINE__,$sql);
		$sql = 'UPDATE '.TABLE_ARCADE_JEUX.' 
				SET score_max = null, score_max_user_id = null
				WHERE id_jeu IN ('.$this->jeu.')';
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,1300,__FILE__,__LINE__,$sql);
	}
	
	//
	// Supprime tous les scores ultimes des jeux selectionnes
	function mettre_zero_scores_ultime(){
		global $c;
		if (!isset($this->jeu)) return false;
		$sql = 'UPDATE '.TABLE_ARCADE_JEUX.' 
				SET score_ultime = null, score_ultime_user_id = null
				WHERE id_jeu IN ('.$this->jeu.')';
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,1300,__FILE__,__LINE__,$sql);
	}

	//
	// Affecter le jeu dans des categories
	function affecter_jeu_dans_categories(){
		global $c;
		if (!isset($this->jeu) || !isset($this->id_cats)) return false;
		$sql = '';				
		foreach ($_POST['jeu'] as $id_jeu){
			foreach ($_POST['id_cats'] as $id_cat){
				if ($sql != '') $sql .= ',';
				$sql .= '('.$id_cat.','.$id_jeu.')';
			}
		}
		$sql = 'INSERT INTO '.TABLE_ARCADE_CATS_JEUX.' (id_cat,id_jeu) VALUES '.$sql;		
		// Pas de retour d'erreur pour eviter les alertes pour doublons
		$c->sql_query($sql);
		// On met a jour les indes de categorie
		$this->update_stats_cats();
	}
	
	//
	// Supprimer les rapports de triche selectionnes
	function supprimer_rapports_de_triche(){
		global $c;
		if (!isset($this->id_triche)) return false;
		$sql = 'DELETE FROM '.TABLE_ARCADE_TRICHES.' 
				WHERE id_triche IN ('.$this->id_triche.')';
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,1300,__FILE__,__LINE__,$sql);
	}
	
	//
	// Supprimer les rapports de triche selectionnes
	function supprimer_tous_les_rapports_de_triche(){
		global $c;
		$sql = 'TRUNCATE TABLE '.TABLE_ARCADE_TRICHES;
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,1300,__FILE__,__LINE__,$sql);
	}
	
	//
	// Retablit les scores selectionnes
	function retablir_scores(){
		global $c;
		if (!isset($this->id_triche)) return false;
		
		// 
		$submit = new submit();
		
		$sql = 'SELECT t.id_triche, t.user_id, t.id_jeu, t.score, t.date, t.temps_reel, j.score_sens, j.score_max, j.score_ultime
				FROM '.TABLE_ARCADE_TRICHES.' AS t
				LEFT JOIN '.TABLE_ARCADE_JEUX.' AS j
					ON t.id_jeu = j.id_jeu
				WHERE id_triche IN ('.$this->id_triche.')';

		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,1300,__FILE__,__LINE__,$sql);
		while($row = $c->sql_fetchrow($resultat)){
		$sql2 = 'SELECT score 
				 FROM '.TABLE_ARCADE_SCORES.' 
				 WHERE user_id='.$row['user_id'].' 
				 AND id_jeu= '.$row['id_jeu'];
				if (!$result = $c->sql_query($sql2))message_die(E_ERROR,1300,__FILE__,__LINE__,$sql2);
			if ($c->sql_numrows($result) == 0){	
			$score= '';}else{
			while($rowscore = $c->sql_fetchrow($result)){
			$score= $rowscore['score'];
			}
			}
			// Declaration des variables indispensables normalement fournies par le submit
			$submit->id_jeu = $row['id_jeu'];
			$submit->user_id = $row['user_id'];
			$submit->fin_session = $row['date'];
			$submit->gscore = $row['score'];
			$submit->score = $score;	
			$submit->score_max = $row['score_max'];
			$submit->score_ultime = $row['score_ultime'];
			$submit->tps_partie = $row['temps_reel'];
			$submit->gnbparties = 1;
			$submit->score_sens = $row['score_sens'];
			// Enregistrement du score
			$submit->enregistrement_score();
			$submit->maj_stats_jeu();
		}
		// Suppression du rapport de triche
		$sql = 'DELETE FROM '.TABLE_ARCADE_TRICHES.' WHERE id_triche IN ('.$this->id_triche.')';
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,1300,__FILE__,__LINE__,$sql);
	}
	
	//
	// Affiche un menu deroulant des differentes categories
	function affiche_menu_deroulant_categories(){
		global $c,$tpl;
		$sql = 'SELECT id_cat,titre_cat, module 
			FROM '.TABLE_ARCADE_CATS.' 
			ORDER BY module ASC, ordre ASC';
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,1300,__FILE__,__LINE__,$sql); 
		if ($c->sql_numrows($resultat) == 0){
			// Aucune categorie
			$tpl->assign_block_vars('aucune_categorie', array());
		}else{
			// Montage des options !
			$tpl->assign_block_vars('categories_ok', array());
			$liste_modules = array();
			$liste_options = '';
			while($row=$c->sql_fetchrow($resultat)){
				// On liste les modules
				if (!in_array($row['module'],$liste_modules)){
					if (sizeof($liste_modules) != 0) $liste_options .= "\n" .'</optgroup>';
					$liste_options .= "\n" . '<optgroup label="'.$row['module'].'">';
					$liste_modules[] = $row['module'];
				}
					// On liste les categories
					$liste_options .= "\n \t" . '<option value="'.$row['id_cat'].'">&nbsp;&nbsp;&nbsp;'.$row['titre_cat'].'</option>';	
				}
			$tpl->assign_vars(array(
				'SELECT_LISTE_ID_CAT'		=>	$liste_options
			));
		}
	}
	
	//
	// Liste les options d'action de masse
	function select_options_jeux(){
		global $lang;
		return	"\n".'<option value="activer">'.				$lang['L_ACTIVER_JEUX'].'</option>'.
				"\n".'<option value="desactiver">'.				$lang['L_DESACTIVER_JEUX'].'</option>'.
				"\n".'<option value="affecter">'.				$lang['L_AFFECTER_JEUX'].'</option>'.
				"\n".'<option value="mettre_zero">'.			$lang['L_METTRE_ZERO_JEUX'].'</option>'.
				"\n".'<option value="mettre_ultime_zero">'.		$lang['L_METTRE_ULTIME_ZERO_JEUX'].'</option>'.
				"\n".'<option value="supprimer">'.				$lang['L_SUPPRIMER_JEUX'].'</option>'.
				"\n".'<option value="supprimer_totalement">'.	$lang['L_SUPPRIMER_TOTALEMENT_JEUX'].'</option>';
	}
	
	//
	// Declare 1 seule fois toutes les clefs de langue de l'arcade
	function declarer_clefs_lang(){
		global $tpl,$lang,$root,$img;
		
		$tpl->assign_vars(array(
			'L_ARCADE_GESTION_SALLES'			=> $lang['L_ARCADE_GESTION_SALLES'],
			'L_ARCADE_GESTION_SALLES_EXPLAIN'	=> $lang['L_ARCADE_GESTION_SALLES_EXPLAIN'],
			'L_CONFIGURER'						=> $lang['L_CONFIGURER'],
			'L_TITRE_SALLE'						=> $lang['L_TITRE_SALLE'],
			'L_DESCRIPTION_SALLE'				=> $lang['L_DESCRIPTION_SALLE'],
			'L_AFFICHAGE_FICHE_JEUX'			=> $lang['L_AFFICHAGE_FICHE_JEUX'],
			'L_AFFICHAGE_JEU'					=> $lang['L_AFFICHAGE_JEU'],
			'L_AFFICHAGE_MOD'					=> $lang['L_AFFICHAGE_MOD'],
			'L_NBRE_JEUX_PAR_PAGE'				=> $lang['L_NBRE_JEUX_PAR_PAGE'],
			'L_NBRE_COLONNES'					=> $lang['L_NBRE_COLONNES'],
			'L_NBRE_COLONNES_JEUX'				=> $lang['L_NBRE_COLONNES_JEUX'],
			'L_GAUCHE'							=> $lang['L_GAUCHE'],
			'L_DROITE'							=> $lang['L_DROITE'],
			'L_DESSUS'							=> $lang['L_DESSUS'],
			'L_DESSOUS'							=> $lang['L_DESSOUS'],
			'L_AUCUN'							=> $lang['L_AUCUN'],
			'L_CLASSIQUE'						=> $lang['L_CLASSIQUE'],
			'L_YEPYOP'							=> $lang['L_YEPYOP'],
			'L_MUR'								=> $lang['L_MUR'],
			'L_AFFICHAGE_RECENTS'				=> $lang['L_AFFICHAGE_RECENTS'],
			'L_AFFICHAGE_POPULAIRES'			=> $lang['L_AFFICHAGE_POPULAIRES'],
			'L_AFFICHAGE_IMPOPULAIRES'			=> $lang['L_AFFICHAGE_IMPOPULAIRES'],
			'L_OUI'								=> $lang['L_OUI'],
			'L_NON'								=> $lang['L_NON'],
			'L_ACTIVER_CHAT'					=> $lang['L_ACTIVER_CHAT'],
			'L_ACTIVER_FAVORIS'					=> $lang['L_ACTIVER_FAVORIS'],
			'L_ENREGISTRER'						=> $lang['L_ENREGISTRER'],
			'L_OPTIONS_GENERALES'				=> $lang['L_OPTIONS_GENERALES'],
			'L_OPTIONS_SPECIALES'				=> $lang['L_OPTIONS_SPECIALES'],
			'L_GERER_CATEGORIES'				=> $lang['L_GERER_CATEGORIES'],
			'L_INSTALLER_JEUX'					=> $lang['L_INSTALLER_JEUX'],
			'L_METTRE_0_SCORES'					=> $lang['L_METTRE_0_SCORES'],
			'L_METTRE_0_SCORES_ULTIMES'			=> $lang['L_METTRE_0_SCORES_ULTIMES'],
			'L_METTRE_0_SCORES_ULTIMES'			=> $lang['L_METTRE_0_SCORES_ULTIMES'],
			'L_ACTIVER_JEUX'					=> $lang['L_ACTIVER_JEUX'],
			'L_DESACTIVER_JEUX'					=> $lang['L_DESACTIVER_JEUX'],
			'L_ARCADE_GESTION_CATEGORIES'			=> $lang['L_ARCADE_GESTION_CATEGORIES'],
			'L_ARCADE_GESTION_CATEGORIES_EXPLAIN'	=> $lang['L_ARCADE_GESTION_CATEGORIES_EXPLAIN'],
			'L_AUCUNE_CATEGORIE'				=> $lang['L_AUCUNE_CATEGORIE'],
			'L_CATEGORIES'						=> $lang['L_CATEGORIES'],
			'L_NBRE_JEUX'						=> $lang['L_NBRE_JEUX'],
			'L_GERER'							=> $lang['L_GERER'],
			'L_SELECT_ICONE'					=> $lang['L_SELECT_ICONE'],
			'L_ENREGISTRER'						=> $lang['L_ENREGISTRER'],
			'L_DEPLACER'						=> $lang['L_DEPLACER'],
			'L_TITRE_CAT'						=> $lang['L_TITRE_CAT'],
			'L_MODULE'							=> $lang['L_MODULE'],
			'L_ARCADE_INSTALLATION'				=> $lang['L_ARCADE_INSTALLATION'],
			'L_ARCADE_INSTALLATION_EXPLAIN'		=> sprintf($lang['L_ARCADE_INSTALLATION_EXPLAIN'],$this->path_games,$this->path_games),
			'L_LISTE_JEUX_ATTENTE'				=> $lang['L_LISTE_JEUX_ATTENTE'],
			'L_AUCUN_JEU_A_INSTALLER'			=> $lang['L_AUCUN_JEU_A_INSTALLER'],
			'L_ADAPTEUR'						=> $lang['L_ADAPTEUR'],
			'L_DECOCHER'						=> $lang['L_DECOCHER'],
			'L_COCHER'							=> $lang['L_COCHER'],
			'L_INSTALLER'						=> $lang['L_INSTALLER'],
			'L_SELECT_CATEGORIE'				=> $lang['L_SELECT_CATEGORIE'],
			'L_JEUX'							=> $lang['L_JEUX'],
			'L_AUCUNE_CATEGORIE_EXISTE'			=> $lang['L_AUCUNE_CATEGORIE_EXISTE'],
			'L_EDITER'							=> $lang['L_EDITER'],
			'L_SUPPRIMER'						=> $lang['L_SUPPRIMER'],
			'L_GERER_LES_JEUX'					=> $lang['L_GERER_LES_JEUX'],
			'L_DOSSIER_DATA_ICONES_ARCADE'		=> sprintf($lang['L_DOSSIER_DATA_ICONES_ARCADE'],$this->chemin_icones),
			'L_ARCADE_GESTION_JEUX'				=> $lang['L_ARCADE_GESTION_JEUX'],
			'L_ARCADE_GESTION_JEUX_EXPLAIN'		=> $lang['L_ARCADE_GESTION_JEUX_EXPLAIN'],
			'LISTE_JEUX'						=> $lang['LISTE_JEUX'],
			'L_APERCU'							=> $lang['L_APERCU'],
			'L_AUCUN_JEU_NE_CORRESPOND'			=> $lang['L_AUCUN_JEU_NE_CORRESPOND'],
			'L_VARIABLE'						=> $lang['L_VARIABLE'],
			'L_DESCRIPTION'						=> $lang['L_DESCRIPTION'],
			'L_TITRE_JEU'						=> $lang['L_TITRE_JEU'],
			'L_SWF'								=> $lang['L_SWF'],
			'L_IMAGE_PETITE'					=> $lang['L_IMAGE_PETITE'],
			'L_IMAGE_GRANDE'					=> $lang['L_IMAGE_GRANDE'],
			'L_LARGEUR'							=> $lang['L_LARGEUR'],
			'L_HAUTEUR'							=> $lang['L_HAUTEUR'],
			'L_FPS'								=> $lang['L_FPS'],
			'L_CONTROLES'						=> $lang['L_CONTROLES'],
			'L_OUI'								=> $lang['L_OUI'],
			'L_NON'								=> $lang['L_NON'],
			'L_ACTIF'							=> $lang['L_ACTIF'],
			'L_SCORE_SENS'						=> $lang['L_SCORE_SENS'],
			'L_ASCENDANT'						=> $lang['L_ASCENDANT'],
			'L_DESCENDANT'						=> $lang['L_DESCENDANT'],
			'L_JEUX_ORPHELINS'					=> $lang['L_JEUX_ORPHELINS'],
			'L_TITRE_CAT_ORPHELINS'				=> $lang['L_TITRE_CAT_ORPHELINS'],
			'L_CONFIRMER'						=> $lang['L_CONFIRMER'],
			'L_AUCUN_JEU'						=> $lang['L_AUCUN_JEU'],
			'L_ACTIVER_MAIL_CHAMPION'			=> $lang['L_ACTIVER_MAIL_CHAMPION'],
			'L_AUCUN_RAPPORT_TRICHE'			=> $lang['L_AUCUN_RAPPORT_TRICHE'],
			'L_PLAYER'							=> $lang['L_PLAYER'],
			'L_SCORE'							=> $lang['L_SCORE'],
			'L_DATE'							=> $lang['L_DATE'],
			'L_TIMEFLASH'						=> $lang['L_TIMEFLASH'],
			'L_REALTIME'						=> $lang['L_REALTIME'],
			'L_TYPE_TRICHE'						=> $lang['L_TYPE_TRICHE'],
			'L_ARCADE_GESTION_TRICHE'			=> $lang['L_ARCADE_GESTION_TRICHE'],
			'L_ARCADE_GESTION_TRICHE_EXPLAIN'	=> $lang['L_ARCADE_GESTION_TRICHE_EXPLAIN'],
			'L_LEGENDE'							=> $lang['L_LEGENDE'],
			'L_TRICHE_SCOREFAKE'				=> $lang['L_TRICHE_SCOREFAKE'],
			'L_TRICHE_SCOREFAKE_EXPLAIN'		=> $lang['L_TRICHE_SCOREFAKE_EXPLAIN'],
			'L_TRICHE_VARIABLE'					=> $lang['L_TRICHE_VARIABLE'],
			'L_TRICHE_VARIABLE_EXPLAIN'			=> $lang['L_TRICHE_VARIABLE_EXPLAIN'],
			'L_TRICHE_FLASHTIME'				=> $lang['L_TRICHE_FLASHTIME'],
			'L_TRICHE_FLASHTIME_EXPLAIN'		=> $lang['L_TRICHE_FLASHTIME_EXPLAIN'],
			'L_TRICHE_FPS'						=> $lang['L_TRICHE_FPS'],
			'L_TRICHE_FPS_EXPLAIN'				=> $lang['L_TRICHE_FPS_EXPLAIN'],
			'L_SUPPRIMER_LES_RAPPORTS'			=> $lang['L_SUPPRIMER_LES_RAPPORTS'],
			'L_SUPPRIMER_TOUS_LES_RAPPORTS'		=> $lang['L_SUPPRIMER_TOUS_LES_RAPPORTS'],
			'L_RETABLIR_SCORES'					=> $lang['L_RETABLIR_SCORES'],
			
			'I_EDITER'							=> $img['editer'],
			'I_EFFACER'							=> $img['effacer'],
			'I_UP'								=> $img['up'],
			'I_DOWN'							=> $img['down'],
			'I_NOUVEAU'							=> $img['nouveau'],
			'I_APERCU'							=> $img['apercu'],
			'I_EXPLORER'						=> $img['explorer'],
			
			'U_GESTION_CATEGORIES'				=> formate_url('admin.php?module=plugins/modules/arcade/admin_arcade_categories.php&salle='),
			'URL_MANAGE_JEUX'					=> formate_url('admin.php?module=plugins/modules/arcade/admin_arcade_jeux.php&id_cat='),
			'U_INSTALLER_JEUX'					=> formate_url('admin.php?module=plugins/modules/arcade/admin_arcade_installation.php&salle='),

		));
	}

	//
	// Cree ou repare l'arborescance dans data/
	function check_data_dirs(){
		if (!is_dir($this->path_games)) $this->creer_dossier($this->path_games);
		if (!is_dir($this->path_games. '_installer/')) $this->creer_dossier($this->path_games. '_installer/');
		if (!is_dir($this->path_games. '_supprimer/')) $this->creer_dossier($this->path_games. '_supprimer/');	
	}
	
	//
	// Copie les fichiers d'un dossier vers un autre en recreant l'arborescance. 
	// Cette fonction NE supprime PAS les anciens dossiers !!
	function copydir($fromDir,$toDir){
		$exceptions=array('.','..');
		$handle=opendir($fromDir);
		while (false!==($item=readdir($handle))){
			if (!in_array($item,$exceptions)){
				$from=str_replace('//','/',$fromDir.'/'.$item);
				$to=str_replace('//','/',$toDir.'/'.$item);
				if (is_file($from)){
					if (@copy($from,$to)){
						chmod($to,$this->umask);
						touch($to,filemtime($from));
					}
				}
				if (is_dir($from)){
					if (@mkdir($to)){
						chmod($to,$this->umask);
					}else
						$this->copydirr($from,$to);
				}
			}
		}
		closedir($handle);
		return true;
	}
	
	//
	// Retourne le libelle d'un type de triche
	function libelle_type_triche($id){
		global $lang;
		switch ($id){
			case 1 : return $lang['L_TRICHE_SCOREFAKE']; break; // score trafique
			case 2 : return $lang['L_TRICHE_VARIABLE']; break; // variables differentes
			case 3 : return $lang['L_TRICHE_FLASHTIME']; break; // temps flash
			case 4 : return $lang['L_TRICHE_FPS']; break; // fps
		}
		return $id;
	}
}


?>
