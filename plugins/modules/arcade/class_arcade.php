<?php


class arcade{
	// chemin ou sont stockes les dossiers de jeux
	var $path_games = 'data/games/';
	// depart de la pagination
	var $start = 0;
	// tableau contenant la configuration de ce module de jeu
	var $config;
	// nom du module
	var $module;
	// categorie courante
	var $id_cat;
	// nombre de jeux dans la categorie
	var $nbre_jeux_cat;
	// Clef complexe permettant d'identifier la session de jeu
	var $clef;
	// Contenu du .htaccess cree dans les dossiers cotnenant des images
	var $htaccess = 
'<Limit POST PUT DELETE>
	Order Allow,Deny
	Deny from All
</Limit>';
	// Masque a appliquer sur les fichiers et dossiers
	var $umask = 0777;
	// Dossier ou sont stockees les icones des categories
	var $chemin_icones = 'data/icones_arcade/';
	// Icone de la categorie des orphelins
	var $icone_orphelins = 'VIDE.png';
	// Mode de fonctionnement
	var $mode = null;
	
	//
	// initialisation de l'arcade
	function arcade(){
		global $_GET,$_POST;
		// Nettoyage des saisies
		$this->clean($_GET);
		$this->clean($_POST);
	}

	// nettoyage des donnees saisies par l'utilisateur
	function clean($vars)
	{
		foreach($vars as $key=>$val)
		{
			switch($key)
			{
				// Entier
				case 'id_session':
				case 'id_jeu': 
				case 'id_cat': 
				case 'user_id': 
				case 'erreur_submit': 
				case 'start': 			$this->$key = intval($val); break;
				// Aucun traitement
				case 'gsubmitscore': 	$this->$key = $val; break;
				// Chaine
				case 'mode':
				case 'action': 			$this->$key = protection_chaine($val); break;
			}
		}			
	}

	// 
	// La meme interface peut afficher differents mods d'affichage
	function select_mod_fonctionnement($mode){
		global $tpl,$root,$droits;

		switch ($mode){
			case 'submit': 
				if (!isset($this->gsubmitscore) || empty($this->gsubmitscore) || !isset($this->id_session) || empty($this->id_session)){
					error404(1311);
				}elseif(!$droits->check($this->module,0,'jouer')){
					$mode = 'interdiction';
					$this->interdiction_de_jouer('jouer');
				}else{
					$submit = new submit();
					$submit->nouveau_score($this->gsubmitscore,$this->id_session); 
				} break;
			case 'clef': 			$this->transmission_clef(); exit; break;
			case 'cat':				$this->liste_jeux(); break;
			case 'favoris':			if(!$droits->check($this->module,0,'favoris') || !$this->config['activer_favoris'])error404(1312);
									$this->liste_favoris(); break;
			case 'recents':			$this->liste_recents(); break;
			case 'populaires':		$this->liste_populaires(); break;
			case 'impopulaires':	$this->liste_impopulaires(); break;
			case 'stats_perso':		$this->liste_stats_perso(); break;
			case 'stats_globales':	$this->liste_stats_globales(); break;
			case 'interdiction':	$this->interdiction_de_jouer(); break;
			case 'partie':			
				if (!$droits->check($this->module,0,'jouer')){
					$mode = 'interdiction';
					$this->interdiction_de_jouer('jouer');
				}else{
					$this->affiche_jeu(); 
				}
				break;
			case 'partie_popup':			
				if (!$droits->check($this->module,0,'jouer')){
					$mode = 'interdiction';
					$this->interdiction_de_jouer('jouer');
				}else{
					$this->affiche_jeu_popup(); 
				}
				break;
			case 'cats':	
			default : 				$mode= 'cats'; $this->liste_cats(); break;
		}
		
		// Chargement du fichier HTML
		if(file_exists($root.'plugins/modules/arcade/html/'.$mode.'.html')){
			$tpl->set_filenames(array('arcade' => $root.'plugins/modules/arcade/html/'.$mode.'.html'));
		}
		
		// Chargement des options
		$this->afficher_options();
		
		// Declaration des clefs de langue
		$this->declarer_clefs_lang();
	}
	
	//
	// Execute une requete SQL et renvoi le resultat sous forme de tableau
	function retour_tableau_sql($sql){
		global $c;
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,1300,__FILE__,__LINE__,$sql); 
		if (strpos($sql,'SELECT') === 0){
			if ($c->sql_numrows($resultat) == 0 ){
				return false;
			}else{
				$liste=array();
				while($row=$c->sql_fetchrow($resultat)){
					$liste[] = $row;
				}
				return $liste;
			}
	 	}else{
			return true;
		}
	}
	
	//
	// Execute une requete SQL et renvoi le resultat dans des objets
	function retour_objet_sql($sql){
		global $c;
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,1300,__FILE__,__LINE__,$sql); 
		if ($c->sql_numrows($resultat) == 0 ){
			return false;
		}else{
			$liste=array();
			$row = $c->sql_fetchrow($resultat);
			foreach($row as $k=>$v){
				$this->$k = $v;
			}
		}
	}
	
	//
	// Insere des donnees dans un fichier html en utilisant la class template
	// $handle = nom du noeud servant a creer les boucles dans le .html
	// $data = tableau de valeurs a affecter au noeud
	// $chariot = parametre optionnel de type entier non signe permettant de faire un retour chariot tous les X enregistrements
	function inserer_bloc_donnees_html($handle,$data=null,$chariot=null,$options=null){
		global $tpl,$lang,$user;
		if (is_array($data)){
			$cels=$nbre=0;
			foreach ($data as $k=>$v){
				// Effectue des traitements sur les donnees avant leur affichage
				foreach ($v as $key=>$value){
					switch($key){
						case 'pseudo': 				$v[$key]=	$this->formate_pseudo($v['user_id'],$v['pseudo']);break;					
						case 'titre_cat': 			$v[$key]=	ucfirst($v['titre_cat']);break;				
						case 'nom_jeu': 			$v[$key]=	ucfirst($v['nom_jeu']);break;				
						case 'nbre_jeux_cat': 		$v[$key]=	sprintf($lang['L_FORMATE_NBRE_JEUX'],$v['nbre_jeux_cat']);break;				
						case 'icone': 				$v[$key]=($v['icone']!='')?				'<img src="'.$v['icone'].'" alt="'.$v['titre_cat'].'" />':'';break;				
						case 'image_petite': 		$v[$key]=($v['image_petite']!='')?		$this->path_games. $v['dossier_jeu'] .'/'.$v['image_petite']:$v['image_petite'];break;				
						case 'image_grande': 		$v[$key]=($v['image_grande']!='')?		$this->path_games. $v['dossier_jeu'] .'/'.$v['image_grande']:$v['image_grande'];break;				
						case 'score_max': 			$v[$key]=($v['score_max']!='')?			'('.$v['score_max'].')':$lang['L_AUCUN_SCORE'];break;				
						case 'score_max_user_id':	$v[$key]=($v['score_max_user_id']!='')?	$this->formate_pseudo($v['score_max_user_id'],$v['score_max_pseudo']):'';break;				
						case 'score_ultime': 		$v[$key]=($v['score_ultime']!='')?		'('.$v['score_ultime'].')':$lang['L_AUCUN_SCORE'];break;				
						case 'score_ultime_user_id':$v[$key]=($v['score_ultime_user_id']!='')?$this->formate_pseudo($v['score_ultime_user_id'],$v['score_ultime_pseudo']):'';break;				
						case 'score_visiteur':		$v[$key]=($v['score_visiteur']!='')?	$v['score_visiteur']:$lang['L_AUCUN_SCORE'];break;	
						case 'nom_swf':				$v[$key]=$this->path_games.$v['dossier_jeu'].'/'.$v['nom_swf']; break;	
						case 'temps_partie':        $v[$key]=($v['temps_partie']!='')?		$this->afficher_duree_temps($v['temps_partie']):$lang['L_JAMAIS_JOUE'];break;
						case 'nbre_parties':		$v[$key]=($v['nbre_parties']!='')?		$v['nbre_parties']:$lang['L_JAMAIS_JOUE'];break;
						case 'date_ajout':			$v[$key]=	formate_date($v['date_ajout'],'d m Y H i','FORMAT_DATE',$user['fuseau']);break;

					}
				}
				$v['place'] = $nbre+1;
				$tpl->assign_block_vars($handle, $v);
				
				if ($options != null ) eval($options);
				
				// Nombre de celulles
				$nbre++;
				
				// TR de debut
				if (($chariot != null && $cels==0) || $chariot == null){
					$tpl->assign_block_vars($handle.'.tr_debut', array());
				}
			
				// TR de fin
				if (($chariot != null && $cels>=($chariot-1)) || (sizeof($data)==$nbre) || ($chariot == null)){
					$tpl->assign_block_vars($handle.'.tr_fin', array());
					$cels=0;
				}else{
					$cels++;
				}
			}
			
			// Switch de securite
			
		}else{
			// Simple switch
			$tpl->assign_block_vars($handle, array());
		}
	}
	
	//
	//  Insere des donnees dans le fichier html en utilisant la class template,
	// contrairement a la class precedente, les donnees ne se placent pas dans un bloc mais directement a la racine
	function inserer_donnees_html($array){
		global $tpl;
		$tpl->assign_vars($array);
	}
	
	//
	// Declare 1 seule fois toutes les clefs de langue de l'arcade + les URLS + les images
	function declarer_clefs_lang(){
		global $tpl,$lang,$root,$module,$img;

		// PAGINATION (preparation)
		include_once($root.'fonctions/fct_affichage.php');

		$tpl->assign_vars(array(
			'MODULE'					=> $module,
			
			'I_SUPPR'					=> $img['effacer'],
			'I_POPUP'					=> $img['arcade_popup'],
			'I_AUGMENTER'				=> $img['arcade_augmenter'],
			'I_DIMINUER'				=> $img['arcade_diminuer'],

			'URL_ACCUEIL'				=> formate_url('',true),
			'URL_CAT'					=> formate_url('mode=cat&id_cat=',true),
			'URL_JEU'					=> formate_url('mode=partie&id_jeu=',true),	
			'URL_SUPPRIMER_FAVORIS'		=> formate_url('mode=favoris&action=supprimer&id_jeu=',true),	
			'URL_FAVORIS'				=> formate_url('mode=favoris',true),	
			'URL_FAVORIS_COMPLET'		=> formate_url('mode=favoris&user_id=',true),	
			'URL_RECENTS'				=> formate_url('mode=recents',true),	
			'URL_POPULAIRES'			=> formate_url('mode=populaires',true),	
			'URL_IMPOPULAIRES'			=> formate_url('mode=impopulaires',true),	
			'PAGINATION_CAT'			=> create_pagination($this->start, 'mode=cat&id_cat='.$this->id_cat.'&start=', $this->nbre_jeux_cat, $this->config['nbre_jeux_page'],$lang['L_JEUX_PAG'])			
		));
	}
	
	//
	// Referencement et navigation
	function set_titre_navigateur($titre){
		global $tpl;
		$tpl->titre_navigateur = $titre;
	}	
	function set_titre_page($titre){
		global $tpl;
		$tpl->titre_page = $titre;
	}	
	function meta_description($description){
		global $tpl;
		$tpl->meta_description = $description;
	}
	function set_navlinks($titre,$lien){
		global $session;
		$session->make_navlinks($titre,$lien);
	}
	
	//
	// Liste les categories de l'arcade
	function liste_cats(){
		global $tpl,$root,$droits;
		$sql = 'SELECT id_cat,titre_cat,nbre_jeux_cat,icone 
				FROM '.TABLE_ARCADE_CATS.' 
				WHERE module=\''.$this->module.'\' ORDER BY ordre';
		// Aucun resultat		
		if (!is_array($retour = $this->retour_tableau_sql($sql))){
			$this->inserer_bloc_donnees_html('aucun_resultat');

		// Affichage des categories
		}else{
			$this->inserer_bloc_donnees_html('liste_cats',$retour,$this->config['nbre_colonnes']);
		}
		
		// Affichage des favoris activer_favoris
		if ($this->config['activer_favoris'] && $droits->check($this->module,0,'favoris')){
			$this->inserer_bloc_donnees_html('activer_favoris');
			$tpl->set_filenames(array('favoris' => $root.'plugins/modules/arcade/html/favoris.html'));
			$this->declarer_clefs_lang();
			$this->liste_favoris(5);
			$tpl->assign_var_from_handle('FAVORIS','favoris');
		}
		
		// Affichage des jeux directement en page d'accueil
		switch ($this->config['affichage_mod']){
			case 'impopulaires':	$tpl->set_filenames(array('jeux_accueil' => $root.'plugins/modules/arcade/html/impopulaires.html'));
									$this->inserer_bloc_donnees_html('titre_impopulaires');
									$this->liste_impopulaires($this->config['nbre_jeux_page']); break;
			case 'populaires':		$tpl->set_filenames(array('jeux_accueil' => $root.'plugins/modules/arcade/html/populaires.html'));
									$this->inserer_bloc_donnees_html('titre_populaires');
									$this->liste_populaires($this->config['nbre_jeux_page']); break;
			case 'recents':	
			default:				$tpl->set_filenames(array('jeux_accueil' => $root.'plugins/modules/arcade/html/recents.html'));
									$this->inserer_bloc_donnees_html('titre_recents');
									$this->liste_recents($this->config['nbre_jeux_page']); break;
		}
		
		$this->declarer_clefs_lang();
		$tpl->assign_var_from_handle('JEUX_ACCUEIL','jeux_accueil');
		
		// Referencement et Navigation
		$this->set_titre_navigateur(($this->config['titre_salle']!='')?$this->config['titre_salle']:$this->module);
		$this->set_titre_page(($this->config['titre_salle']!='')?$this->config['titre_salle']:$this->module);
		$this->meta_description($this->config['description_salle']);
		$this->set_navlinks(($this->config['titre_salle']!='')?$this->config['titre_salle']:$this->module,formate_url('',true));
	}
	
	//
	// Liste les jeux de la categorie selectionnee
	function liste_jeux(){
		global $user,$tpl,$root;
		
		// Informations sur la categorie
		$this->Get_infos_categorie();
		
		$sql = 'SELECT DISTINCT j.id_jeu,j.dossier_jeu,j.nom_jeu,j.description,j.controles,j.actif,
				j.largeur,j.hauteur,
				j.score_max,j.score_max_user_id,u1.pseudo AS score_max_pseudo,
				j.score_ultime,j.score_ultime_user_id,u2.pseudo AS score_ultime_pseudo,
				s.score AS score_visiteur, 
				j.image_petite,j.image_grande,j.largeur,j.hauteur,j.date_ajout 
				FROM '.TABLE_ARCADE_CATS_JEUX.' AS cj
				LEFT JOIN '.TABLE_ARCADE_JEUX.' AS j
					ON (cj.id_jeu=j.id_jeu)
				LEFT JOIN '.TABLE_ARCADE_CATS.' AS c
					ON (cj.id_cat=c.id_cat)
				LEFT JOIN '.TABLE_USERS.' AS u1
					ON (j.score_max_user_id=u1.user_id)
				LEFT JOIN '.TABLE_USERS.' AS u2
					ON (j.score_ultime_user_id=u2.user_id)
				LEFT JOIN '.TABLE_ARCADE_SCORES.' AS s
					ON (j.id_jeu=s.id_jeu AND s.user_id='.$user['user_id'].')
				WHERE j.actif=1 AND cj.id_cat='.$this->id_cat.' AND c.module=\''.$this->module.'\'
				ORDER BY date_ajout ASC
				LIMIT '.$this->start.','.$this->config['nbre_jeux_page'];

		switch($this->config['affichage_jeu']){
			// Mur de vignettes
			case '3':	$affichage_cat = 'cat_affichage_mur'; 
						$colonnes = $this->config['nbre_colonnes_jeux']; break;
			// Grandes vignettes
			case '2':	$affichage_cat = 'cat_affichage_grandes_vignettes';
						$colonnes = null; break;
			// Classique
			case '1': 
			default :	$affichage_cat = 'cat_affichage_classique';
						$colonnes = null; break;
		}
		$tpl->set_filenames(array('affichage_cat' => $root.'plugins/modules/arcade/html/'.$affichage_cat.'.html'));
		$this->declarer_clefs_lang();
		
		if (!is_array($retour = $this->retour_tableau_sql($sql))){
			// Aucun resultat		
			$this->inserer_bloc_donnees_html('aucun_resultat');
		}else{
			// Affichage popup
			global $droits;
			$options = ($droits->check($this->module,0,'jouer'))? '$tpl->assign_block_vars(\'liste_jeux.popup\', array());':false;
			// Affichage des jeux
			$this->inserer_bloc_donnees_html('liste_jeux',$retour,$colonnes,$options);
					
			// Referencement et Navigation
			$this->set_titre_navigateur($this->titre_cat. ' :: ' .$this->config['titre_salle']);
			$this->set_titre_page('<img src="'.$this->icone.'" alt="'.$this->titre_cat.'" /> '.$this->titre_cat);
			$this->meta_description($this->titre_cat. ' ' .$this->config['description_salle']);
			$this->set_navlinks($this->config['titre_salle'],formate_url('',true));
			$this->set_navlinks($this->titre_cat,formate_url('mode=cat&id_cat='. $this->id_cat ,true));
		}
		$tpl->assign_var_from_handle('AFFICHAGE_CAT','affichage_cat');	
	}
	
	//
	// Affiche le jeu et initialise une nouvelle partie
	function affiche_jeu(){
		global $user,$droits,$root,$tpl,$cf;
		
		$sql = 'SELECT DISTINCT j.id_jeu,j.dossier_jeu,j.nom_jeu,j.description,j.controles,j.actif,
				j.nom_swf,j.controles,j.score_sens,j.largeur,j.hauteur,
				j.score_max,j.score_max_user_id,u1.pseudo AS score_max_pseudo,
				j.score_ultime,j.score_ultime_user_id,u2.pseudo AS score_ultime_pseudo,
				s.score AS score_visiteur, s.temps_partie AS temps_partie_visiteur,s.nbre_parties AS nbre_parties_visiteur,
				j.image_petite,j.image_grande,j.largeur,j.hauteur,j.date_ajout,j.temps_partie,j.nbre_parties,
				c.id_cat,c.titre_cat,c.icone,
				f.id_jeu AS favoris
				FROM '.TABLE_ARCADE_CATS_JEUX.' AS cj
				LEFT JOIN '.TABLE_ARCADE_JEUX.' AS j
					ON (cj.id_jeu=j.id_jeu)
				LEFT JOIN '.TABLE_ARCADE_CATS.' AS c
					ON (cj.id_cat=c.id_cat)
				LEFT JOIN '.TABLE_USERS.' AS u1
					ON (j.score_max_user_id=u1.user_id)
				LEFT JOIN '.TABLE_USERS.' AS u2
					ON (j.score_ultime_user_id=u2.user_id)
				LEFT JOIN '.TABLE_ARCADE_SCORES.' AS s
					ON (j.id_jeu=s.id_jeu AND s.user_id='.$user['user_id'].')
				LEFT JOIN '.TABLE_ARCADE_FAVORIS.' AS f
					ON (j.id_jeu=f.id_jeu AND f.user_id='.$user['user_id'].')
				WHERE j.actif=1 AND cj.id_jeu='.$this->id_jeu.' AND c.module=\''.$this->module.'\'
				ORDER BY c.ordre ASC';

		// Aucun resultat		
		if (!is_array($retour = $this->retour_tableau_sql($sql))){
			$this->inserer_bloc_donnees_html('aucun_resultat');

		}else{
			global $tpl,$root,$lang,$erreur,$img;
			// Dimensions réelles
			$largeur = $retour[0]['largeur'];
			$hauteur = ($retour[0]['hauteur']);
			
			// Limite le jeu en largeur / hauteur
			if ($retour[0]['hauteur']>$retour[0]['largeur'] 
				&& $cf->config['arcade_hauteur_max'] < $retour[0]['hauteur']){
				// Plus haut que large
				$retour[0]['largeur'] = $retour[0]['largeur'] * $cf->config['arcade_hauteur_max'] / $retour[0]['hauteur'];
				$retour[0]['hauteur'] = ($cf->config['arcade_hauteur_max'])+32;
				
			}elseif($retour[0]['hauteur']<$retour[0]['largeur'] 
				&& $cf->config['arcade_largeur_max'] < $retour[0]['largeur']){
				// plus large que haut
				$retour[0]['hauteur'] = ($retour[0]['hauteur'] * $cf->config['arcade_largeur_max'] / $retour[0]['largeur'])+32;
				$retour[0]['largeur'] = $cf->config['arcade_largeur_max'];
			}
			
			$temp[] = $retour[0];
			$this->inserer_bloc_donnees_html('lancement_jeu',$temp);
			
			//Initialisation de la session de jeu
			$this->init_session_jeu($retour[0]['dossier_jeu']);

			// fiche de presentation
			$tpl->set_filenames(array('fiche_jeu' => $root.'plugins/modules/arcade/html/partie_fiche_jeu.html'));
			$this->declarer_clefs_lang();

			$this->inserer_donnees_html(array(
				'ID'				=>	$this->id_session,
				'DESCRIPTION_JEU'	=>	$retour[0]['description'],
				'ID_JEU'			=>	$retour[0]['id_jeu'],
				'LARGEUR'			=>	$largeur,
				'HAUTEUR'			=>	($hauteur+32),
				'LARGEURMAX'		=>	$cf->config['arcade_largeur_max'],
				'HAUTEURMAX'		=>	$cf->config['arcade_hauteur_max'],
				'IMAGE'				=>	$this->path_games. $retour[0]['dossier_jeu'] .'/'.$retour[0]['image_petite'],
				'CONTROLES'			=>	$this->formate_controles($retour[0]['controles']),
				'DATE_AJOUT'		=>	formate_date($retour[0]['date_ajout'],'d m Y H i','FORMAT_DATE',$user['fuseau']),
				'SCORE_MAX'			=>	($retour[0]['score_max']!='')?				'('.$retour[0]['score_max'].')':$lang['L_AUCUN_SCORE'],				
				'SCORE_MAX_USER_ID'	=>	($retour[0]['score_max_user_id']!='')?		$this->formate_pseudo($retour[0]['score_max_user_id'],$retour[0]['score_max_pseudo']):'',
				'SCORE_ULTIME'		=>	($retour[0]['score_ultime']!='')?			'('.$retour[0]['score_ultime'].')':$lang['L_AUCUN_SCORE'],
				'SCORE_ULTIME_USER_ID'=>($retour[0]['score_ultime_user_id']!='')?	$this->formate_pseudo($retour[0]['score_ultime_user_id'],$retour[0]['score_ultime_pseudo']):'',
				'SCORE_VISITEUR'	=>	($retour[0]['score_visiteur']!='')?			$retour[0]['score_visiteur']:$lang['L_AUCUN_SCORE'],
				'TEMPS_PARTIE'		=>	($retour[0]['temps_partie']!='')?			$this->afficher_duree_temps($retour[0]['temps_partie']):$lang['L_JAMAIS_JOUE'],
				'NBRE_PARTIES'		=>	($retour[0]['nbre_parties']!='')?			$retour[0]['nbre_parties']:$lang['L_JAMAIS_JOUE'],
				'TEMPS_PARTIE_VISITEUR'	=>	($retour[0]['temps_partie_visiteur']!='')?	$this->afficher_duree_temps($retour[0]['temps_partie_visiteur']):$lang['L_TU_JAMAIS_JOUE'],
				'NBRE_PARTIES_VISITEUR'	=>	($retour[0]['nbre_parties_visiteur']!='')?	$retour[0]['nbre_parties_visiteur']:$lang['L_TU_JAMAIS_JOUE'],
				'FAVORIS'			=>	($retour[0]['favoris']!='')?				$lang['L_DEJA_FAVORIS']:'<a href="'.formate_url('mode=favoris&id_jeu='.$this->id_jeu,true).'" title="'.$lang['L_METTRE_FAVORIS'].'"><img src="'.$img['arcade_ajouter_favoris'].'" alt="'.$lang['L_METTRE_FAVORIS'].'" /></a>',
			));
			
			// Categories auquelles appartient ce jeu
			$this->inserer_bloc_donnees_html('cats',$retour);
			
			// Jeu en favoris ?
			if ($this->config['activer_favoris'] && $droits->check($this->module,0,'favoris')){
				$this->inserer_bloc_donnees_html('activer_favoris');
			}
			
			// Position de la fiche de presentation du jeu
			switch($this->config['affichage_fiche_jeux']){
				// Dessous
				case '4':	$this->inserer_bloc_donnees_html('lancement_jeu.dessous');
							$tpl->assign_var_from_handle('FICHE','fiche_jeu'); break;
				// Dessus
				case '3':	$this->inserer_bloc_donnees_html('lancement_jeu.dessus');
							$tpl->assign_var_from_handle('FICHE','fiche_jeu'); break;
				// Gauche
				case '1':	$this->inserer_bloc_donnees_html('lancement_jeu.gauche');
							$tpl->assign_var_from_handle('FICHE','fiche_jeu'); break;
				// droite
				case '2':	$this->inserer_bloc_donnees_html('lancement_jeu.droite');
							$tpl->assign_var_from_handle('FICHE','fiche_jeu'); break;
				// Aucune fiche
				default :	case '0':	break;
			}
			
			// Message d'erreur au submit ?
			if (isset($this->erreur_submit) && $this->erreur_submit>=1300 && $this->erreur_submit<1400){
				erreur_saisie('erreur_saisie',$erreur[$this->erreur_submit]);
			}
			
			// Referencement et Navigation
			$this->set_titre_navigateur($retour[0]['nom_jeu']. ' :: ' .$this->config['titre_salle']);
			$this->set_titre_page('<img src="'.$this->path_games. $retour[0]['dossier_jeu'] .'/'.$retour[0]['image_petite'].'" alt="'.$retour[0]['nom_jeu'].'" /> '.$retour[0]['nom_jeu']);
			$this->meta_description($retour[0]['nom_jeu']. ' ' .$retour[0]['description']. ' :: ' .$this->config['description_salle']);
			$this->set_navlinks($this->config['titre_salle'],formate_url('',true));
			$this->set_navlinks($retour[0]['nom_jeu'],formate_url('mode=partie&id_jeu='.$this->id_jeu,true));
		}
	}
	
	
		//
	// Affiche le jeu et initialise une nouvelle partie
	function affiche_jeu_popup(){
		global $user,$droits,$root,$tpl;

		$tpl->set_filenames(array(
		  'arcade_partie' => $root.'plugins/modules/arcade/html/partie_popup.html'
		));
		
		$sql = 'SELECT DISTINCT j.id_jeu,j.dossier_jeu,j.nom_jeu,j.description,j.controles,j.actif,
				j.nom_swf,j.controles,j.score_sens,
				j.score_max,j.score_max_user_id,u1.pseudo AS score_max_pseudo,
				j.score_ultime,j.score_ultime_user_id,u2.pseudo AS score_ultime_pseudo,
				s.score AS score_visiteur, s.temps_partie AS temps_partie_visiteur,s.nbre_parties AS nbre_parties_visiteur,
				j.image_petite,j.image_grande,j.largeur,j.hauteur,j.date_ajout,j.temps_partie,j.nbre_parties,
				c.id_cat,c.titre_cat,c.icone,
				f.id_jeu AS favoris
				FROM '.TABLE_ARCADE_CATS_JEUX.' AS cj
				LEFT JOIN '.TABLE_ARCADE_JEUX.' AS j
					ON (cj.id_jeu=j.id_jeu)
				LEFT JOIN '.TABLE_ARCADE_CATS.' AS c
					ON (cj.id_cat=c.id_cat)
				LEFT JOIN '.TABLE_USERS.' AS u1
					ON (j.score_max_user_id=u1.user_id)
				LEFT JOIN '.TABLE_USERS.' AS u2
					ON (j.score_ultime_user_id=u2.user_id)
				LEFT JOIN '.TABLE_ARCADE_SCORES.' AS s
					ON (j.id_jeu=s.id_jeu AND s.user_id='.$user['user_id'].')
				LEFT JOIN '.TABLE_ARCADE_FAVORIS.' AS f
					ON (j.id_jeu=f.id_jeu AND f.user_id='.$user['user_id'].')
				WHERE j.actif=1 AND cj.id_jeu='.$this->id_jeu.' AND c.module=\''.$this->module.'\'
				ORDER BY c.ordre ASC';

		// Aucun resultat		
		if (!is_array($retour = $this->retour_tableau_sql($sql))){
			$this->inserer_bloc_donnees_html('aucun_resultat');

		}else{
			global $tpl,$root,$lang,$erreur,$img;
			$temp[] = $retour[0];
			$this->inserer_bloc_donnees_html('lancement_jeu',$temp);
			
			//Initialisation de la session de jeu
			$this->init_session_jeu($retour[0]['dossier_jeu']);

			// fiche de presentation
			$tpl->set_filenames(array('fiche_jeu' => $root.'plugins/modules/arcade/html/partie_fiche_jeu.html'));
			$this->declarer_clefs_lang();
			$this->inserer_donnees_html(array(
				'ID'				=>	$this->id_session,
				'DESCRIPTION_JEU'	=>	$retour[0]['description'],
				'IMAGE'				=>	$this->path_games. $retour[0]['dossier_jeu'] .'/'.$retour[0]['image_petite'],
				'CONTROLES'			=>	$this->formate_controles($retour[0]['controles']),
				'DATE_AJOUT'		=>	formate_date($retour[0]['date_ajout'],'d m Y H i','FORMAT_DATE',$user['fuseau']),
				'SCORE_MAX'			=>	($retour[0]['score_max']!='')?				'('.$retour[0]['score_max'].')':$lang['L_AUCUN_SCORE'],				
				'SCORE_MAX_USER_ID'	=>	($retour[0]['score_max_user_id']!='')?		$this->formate_pseudo($retour[0]['score_max_user_id'],$retour[0]['score_max_pseudo']):'',
				'SCORE_ULTIME'		=>	($retour[0]['score_ultime']!='')?			'('.$retour[0]['score_ultime'].')':$lang['L_AUCUN_SCORE'],
				'SCORE_ULTIME_USER_ID'=>($retour[0]['score_ultime_user_id']!='')?	$this->formate_pseudo($retour[0]['score_ultime_user_id'],$retour[0]['score_ultime_pseudo']):'',
				'SCORE_VISITEUR'	=>	($retour[0]['score_visiteur']!='')?			$retour[0]['score_visiteur']:$lang['L_AUCUN_SCORE'],
				'TEMPS_PARTIE'		=>	($retour[0]['temps_partie']!='')?			$this->afficher_duree_temps($retour[0]['temps_partie']):$lang['L_JAMAIS_JOUE'],
				'NBRE_PARTIES'		=>	($retour[0]['nbre_parties']!='')?			$retour[0]['nbre_parties']:$lang['L_JAMAIS_JOUE'],
				'TEMPS_PARTIE_VISITEUR'	=>	($retour[0]['temps_partie_visiteur']!='')?	$this->afficher_duree_temps($retour[0]['temps_partie_visiteur']):$lang['L_TU_JAMAIS_JOUE'],
				'NBRE_PARTIES_VISITEUR'	=>	($retour[0]['nbre_parties_visiteur']!='')?	$retour[0]['nbre_parties_visiteur']:$lang['L_TU_JAMAIS_JOUE'],
				'FAVORIS'			=>	($retour[0]['favoris']!='')?				$lang['L_DEJA_FAVORIS']:'<a href="'.formate_url('mode=favoris&id_jeu='.$this->id_jeu,true).'" title="'.$lang['L_METTRE_FAVORIS'].'"><img src="'.$img['arcade_ajouter_favoris'].'" alt="'.$lang['L_METTRE_FAVORIS'].'" /></a>',
			));
			
			// Categories auquelles appartient ce jeu
			$this->inserer_bloc_donnees_html('cats',$retour);
			
			// Jeu en favoris ?
			if ($this->config['activer_favoris'] && $droits->check($this->module,0,'favoris')){
				$this->inserer_bloc_donnees_html('activer_favoris');
			}
			
			// Position de la fiche de presentation du jeu
			switch($this->config['affichage_fiche_jeux']){
				// Dessous
				case '4':	$this->inserer_bloc_donnees_html('lancement_jeu.dessous');
							$tpl->assign_var_from_handle('FICHE','fiche_jeu'); break;
				// Dessus
				case '3':	$this->inserer_bloc_donnees_html('lancement_jeu.dessus');
							$tpl->assign_var_from_handle('FICHE','fiche_jeu'); break;
				// Gauche
				case '1':	$this->inserer_bloc_donnees_html('lancement_jeu.gauche');
							$tpl->assign_var_from_handle('FICHE','fiche_jeu'); break;
				// droite
				case '2':	$this->inserer_bloc_donnees_html('lancement_jeu.droite');
							$tpl->assign_var_from_handle('FICHE','fiche_jeu'); break;
				// Aucune fiche
				default :	case '0':	break;
			}
		}
		$tpl->pparse('arcade_partie');
		$tpl->afficher_page();
		exit;
	}
	
	//
	// Affichage des jeux mis en favoris
	function liste_favoris($limit=null){
		global $user,$tpl,$root,$lang;
		
		// Si un jeu est a supprimer
		if (isset($this->action) && ($this->action='supprimer') && isset($this->id_jeu)){
			$this->supprimer_favoris($this->id_jeu,$user['user_id']);
		// Si un jeu est present dans l'url on l'ajoute
		}elseif (isset($this->id_jeu)){
			$this->ajouter_favoris($this->id_jeu,$user['user_id']);
		}
		
		// Les favoris de quel joueur doivent etre affiches
		if (!isset($this->user_id)) $this->user_id = $user['user_id'];
		
		$sql = 'SELECT DISTINCT DISTINCT count(j.id_jeu) AS max 
				FROM '.TABLE_ARCADE_FAVORIS.' AS f
				LEFT JOIN '.TABLE_ARCADE_JEUX.' AS j
					ON (f.id_jeu=j.id_jeu)
				LEFT JOIN '.TABLE_ARCADE_CATS_JEUX.' AS cj
					ON (j.id_jeu=cj.id_jeu)
				LEFT JOIN '.TABLE_ARCADE_CATS.' AS c
					ON (cj.id_cat=c.id_cat)
				WHERE j.actif=1 AND f.user_id='.$this->user_id.' AND c.module=\''.$this->module.'\'
				ORDER BY j.nbre_parties DESC';
		$stats = $this->retour_tableau_sql($sql);
		
		$sql = 'SELECT DISTINCT j.id_jeu,j.dossier_jeu,j.nom_jeu,j.description,j.controles,j.actif,
				j.nom_swf,j.controles,j.score_sens,
				s.score , j.temps_partie,j.nbre_parties,
				j.image_petite,j.image_grande,j.largeur,j.hauteur,j.date_ajout,
				u.pseudo,u.user_id,
				j.score_max,j.score_max_user_id, u2.pseudo AS score_max_pseudo 
				FROM '.TABLE_ARCADE_FAVORIS.' AS f
				LEFT JOIN '.TABLE_ARCADE_JEUX.' AS j
					ON (f.id_jeu=j.id_jeu)
				LEFT JOIN '.TABLE_ARCADE_CATS_JEUX.' AS cj
					ON (j.id_jeu=cj.id_jeu)
				LEFT JOIN '.TABLE_ARCADE_CATS.' AS c
					ON (cj.id_cat=c.id_cat)
				LEFT JOIN '.TABLE_ARCADE_SCORES.' AS s
					ON (f.id_jeu=s.id_jeu AND s.user_id='.$this->user_id.')
				LEFT JOIN '.TABLE_USERS.' AS u
					ON (f.user_id=u.user_id)
				LEFT JOIN '.TABLE_USERS.' AS u2
					ON (j.score_max_user_id=u2.user_id)
				WHERE j.actif=1 AND f.user_id='.$this->user_id.' AND c.module=\''.$this->module.'\'
				ORDER BY j.nbre_parties DESC';
		if ($limit != null){
			$sql .= ' limit '.$limit;
		}else{
			if (isset($this->start)) $this->start = 0;
			$sql .= ' limit '.$this->start.','.$this->config['nbre_jeux_page'];
		}

		// Aucun resultat		
		if (!is_array($retour = $this->retour_tableau_sql($sql))){
			$this->inserer_bloc_donnees_html('aucun_favoris');
		}else{
			// Affichage popup
			global $droits;
			$options = ($droits->check($this->module,0,'jouer'))? '$tpl->assign_block_vars(\'liste_favoris.popup\', array());':false;
			// Affichage des jeux
			$this->inserer_bloc_donnees_html('liste_favoris',$retour,false,$options);
			if ($limit != null && $stats[0]['max']>$this->config['nbre_jeux_page']) $this->inserer_bloc_donnees_html('liste_complete');
			$this->inserer_donnees_html(array(
				'USER_ID'	=> $this->user_id
			));
		}
		if ($limit == null){
			// Referencement et Navigation
			$this->set_titre_navigateur(sprintf($lang['L_FAVORIS_DE'],$retour[0]['pseudo']));
			$this->set_titre_page(sprintf($lang['L_FAVORIS_DE'],$retour[0]['pseudo']));
			$this->meta_description($this->config['description_salle']);
			$this->set_navlinks($this->config['titre_salle'],formate_url('',true));
			$this->set_navlinks(sprintf($lang['L_FAVORIS_DE'],$retour[0]['pseudo']),formate_url('mode=favoris&user_id='. $this->user_id ,true));

			// pagination
			$this->inserer_bloc_donnees_html('pagination');
			include_once($root.'fonctions/fct_affichage.php');
			$tpl->assign_vars(array(
				'PAGINATION'	=> create_pagination($this->start, 'mode=recents&start=', $stats[0]['max'], $this->config['nbre_jeux_page'],$lang['L_JEUX_PAG'])
			));
		}
	}
	
	//
	// Affichage des jeux mis en favoris
	function liste_top_favoris($limit=null){
		global $user,$tpl,$root,$lang;
		
		$sql = 'SELECT j.id_jeu, COUNT(f.id_jeu) AS nbre_favoris,
				j.dossier_jeu,j.nom_jeu,j.description,j.controles,j.actif,
				j.nom_swf,j.controles,j.score_sens,
				j.image_petite,j.image_grande,j.largeur,j.hauteur,j.date_ajout,
				j.score_max,j.score_max_user_id, u2.pseudo AS score_max_pseudo,
				u.pseudo,u.user_id,
				s.score , j.temps_partie,j.nbre_parties 
				FROM '.TABLE_ARCADE_FAVORIS.' AS f
				LEFT JOIN '.TABLE_ARCADE_JEUX.' AS j
					ON (f.id_jeu=j.id_jeu)
				LEFT JOIN '.TABLE_ARCADE_SCORES.' AS s
					ON (f.id_jeu=s.id_jeu AND s.user_id='.$user['user_id'].')
				LEFT JOIN '.TABLE_USERS.' AS u
					ON (f.user_id=u.user_id)
				LEFT JOIN '.TABLE_USERS.' AS u2
					ON (j.score_max_user_id=u2.user_id)
				WHERE j.actif=1  AND  f.id_jeu IN ('.$this->liste_id_jeux().')
				GROUP BY f.id_jeu
				HAVING COUNT(f.id_jeu)
				ORDER BY nbre_favoris DESC';
		if ($limit != null){
			$sql .= ' limit '.$limit;
		}else{
			if (isset($this->start)) $this->start = 0;
			$sql .= ' limit '.$this->start.','.$this->config['nbre_jeux_page'];
		}
		
		// Aucun resultat		
		if (!is_array($retour = $this->retour_tableau_sql($sql))){
			$this->inserer_bloc_donnees_html('aucun_favoris');
		}else{
			// Affichage popup
			global $droits;
			$options = ($droits->check($this->module,0,'jouer'))? '$tpl->assign_block_vars(\'liste_favoris.popup\', array());':false;
			$this->inserer_bloc_donnees_html('liste_favoris',$retour,false,$options);
			// Affichage popup
			global $droits;
			if($droits->check($this->module,0,'jouer')) $this->inserer_bloc_donnees_html('liste_favoris.popup');
		}
	}
	
	//
	// Affichage des jeux recents
	function liste_recents($limit=null){
		global $user,$tpl,$root,$lang;
		if (!isset($this->start)) $this->start = 0;
		$sql = 'SELECT DISTINCT count(j.id_jeu) AS max
				FROM '.TABLE_ARCADE_JEUX.' AS j
				LEFT JOIN '.TABLE_ARCADE_CATS_JEUX.' AS cj
					ON (j.id_jeu=cj.id_jeu)
				LEFT JOIN '.TABLE_ARCADE_CATS.' AS c
					ON (cj.id_cat=c.id_cat)
				WHERE j.actif=1 AND c.module=\''.$this->module.'\'';
		$stats = $this->retour_tableau_sql($sql);
		
		
		$sql = 'SELECT DISTINCT j.id_jeu,j.dossier_jeu,j.nom_jeu,j.description,j.controles,j.actif,
				j.nom_swf,j.controles,j.score_sens,
				s.score , j.temps_partie,j.nbre_parties,
				j.image_petite,j.image_grande,j.largeur,j.hauteur,j.date_ajout,j.score_max,j.score_max_user_id,
				u.pseudo AS score_max_pseudo 
				FROM '.TABLE_ARCADE_JEUX.' AS j
				LEFT JOIN '.TABLE_ARCADE_SCORES.' AS s
					ON (j.id_jeu=s.id_jeu AND s.user_id='.$user['user_id'].')
				LEFT JOIN '.TABLE_USERS.' AS u
					ON (j.score_max_user_id=u.user_id)
				LEFT JOIN '.TABLE_ARCADE_CATS_JEUX.' AS cj
					ON (j.id_jeu=cj.id_jeu)
				LEFT JOIN '.TABLE_ARCADE_CATS.' AS c
					ON (cj.id_cat=c.id_cat)
				WHERE j.actif=1 AND c.module=\''.$this->module.'\'
				ORDER BY j.date_ajout DESC
				LIMIT '.$this->start.','.$this->config['nbre_jeux_page'];
		
		// Aucun resultat		
		if (!is_array($retour = $this->retour_tableau_sql($sql))){
			$this->inserer_bloc_donnees_html('aucun_recents');
		}else{
			// Affichage popup
			global $droits;
			$options = ($droits->check($this->module,0,'jouer'))? '$tpl->assign_block_vars(\'liste_recents.popup\', array());':false;
			$this->inserer_bloc_donnees_html('liste_recents',$retour,false,$options);
			if ($limit != null && $stats[0]['max']>$this->config['nbre_jeux_page'])$this->inserer_bloc_donnees_html('liste_complete_recents');
		}
		
		if ($limit == null){
			// Referencement et Navigation
			$this->set_titre_navigateur($lang['L_RECENTS']);
			$this->set_titre_page($lang['L_RECENTS']);
			$this->meta_description($this->config['description_salle']);
			$this->set_navlinks($this->config['titre_salle'],formate_url('',true));
			$this->set_navlinks($lang['L_RECENTS'],formate_url('mode=recents',true));
			// pagination
			$this->inserer_bloc_donnees_html('pagination');
			include_once($root.'fonctions/fct_affichage.php');
			$tpl->assign_vars(array(
				'PAGINATION'	=> create_pagination($this->start, 'mode=recents&start=', $stats[0]['max'], $this->config['nbre_jeux_page'],$lang['L_JEUX_PAG'])
			));
		}
	}

	//
	// Affichage des jeux populaires
	function liste_populaires($limit=null){
		global $user,$tpl,$root,$lang;
		if (!isset($this->start)) $this->start = 0;
		$sql = 'SELECT DISTINCT count(j.id_jeu) AS max
				FROM '.TABLE_ARCADE_JEUX.' AS j
				LEFT JOIN '.TABLE_ARCADE_CATS_JEUX.' AS cj
					ON (j.id_jeu=cj.id_jeu)
				LEFT JOIN '.TABLE_ARCADE_CATS.' AS c
					ON (cj.id_cat=c.id_cat)
				WHERE j.actif=1 AND c.module=\''.$this->module.'\'';
		$stats = $this->retour_tableau_sql($sql);
		
		$sql = 'SELECT DISTINCT j.id_jeu,j.dossier_jeu,j.nom_jeu,j.description,j.controles,j.actif,
				j.nom_swf,j.controles,j.score_sens,
				s.score , j.temps_partie,j.nbre_parties,
				j.image_petite,j.image_grande,j.largeur,j.hauteur,j.date_ajout,j.score_max,j.score_max_user_id,
				u.pseudo AS score_max_pseudo 
				FROM '.TABLE_ARCADE_JEUX.' AS j
				LEFT JOIN '.TABLE_ARCADE_SCORES.' AS s
					ON (j.id_jeu=s.id_jeu AND s.user_id='.$user['user_id'].')
				LEFT JOIN '.TABLE_USERS.' AS u
					ON (j.score_max_user_id=u.user_id)
				LEFT JOIN '.TABLE_ARCADE_CATS_JEUX.' AS cj
					ON (j.id_jeu=cj.id_jeu)
				LEFT JOIN '.TABLE_ARCADE_CATS.' AS c
					ON (cj.id_cat=c.id_cat)
				WHERE j.actif=1 AND c.module=\''.$this->module.'\'
				ORDER BY j.nbre_parties DESC
				LIMIT '.$this->start.','.$this->config['nbre_jeux_page'];
		
		// Aucun resultat		
		if (!is_array($retour = $this->retour_tableau_sql($sql))){
			$this->inserer_bloc_donnees_html('aucun_populaire');
		}else{
			// Affichage popup
			global $droits;
			$options = ($droits->check($this->module,0,'jouer'))? '$tpl->assign_block_vars(\'liste_populaires.popup\', array());':false;
			$this->inserer_bloc_donnees_html('liste_populaires',$retour,false,$options);
			if ($limit != null && $stats[0]['max']>$this->config['nbre_jeux_page'])	$this->inserer_bloc_donnees_html('liste_complete_populaires');
		}
		if ($limit == null){
			// Referencement et Navigation
			$this->set_titre_navigateur($lang['L_POPULAIRES']);
			$this->set_titre_page($lang['L_POPULAIRES']);
			$this->meta_description($this->config['description_salle']);
			$this->set_navlinks($this->config['titre_salle'],formate_url('',true));
			$this->set_navlinks($lang['L_POPULAIRES'],formate_url('mode=populaires',true));
			
			// pagination
			$this->inserer_bloc_donnees_html('pagination');
			include_once($root.'fonctions/fct_affichage.php');
			$tpl->assign_vars(array(
				'PAGINATION'	=> create_pagination($this->start, 'mode=populaires&start=', $stats[0]['max'], $this->config['nbre_jeux_page'],$lang['L_JEUX_PAG'])
			));
		}
	}

	//
	// Affichage des jeux impopulaires
	function liste_impopulaires($limit=null){
		global $user,$tpl,$root,$lang;
		if (!isset($this->start)) $this->start = 0;
		$sql = 'SELECT DISTINCT count(j.id_jeu) AS max
				FROM '.TABLE_ARCADE_JEUX.' AS j
				LEFT JOIN '.TABLE_ARCADE_CATS_JEUX.' AS cj
					ON (j.id_jeu=cj.id_jeu)
				LEFT JOIN '.TABLE_ARCADE_CATS.' AS c
					ON (cj.id_cat=c.id_cat)
				WHERE j.actif=1 AND c.module=\''.$this->module.'\'';
		$stats = $this->retour_tableau_sql($sql);
		
		$sql = 'SELECT DISTINCT j.id_jeu,j.dossier_jeu,j.nom_jeu,j.description,j.controles,j.actif,
				j.nom_swf,j.controles,j.score_sens,
				s.score , j.temps_partie,j.nbre_parties,
				j.image_petite,j.image_grande,j.largeur,j.hauteur,j.date_ajout,j.score_max,j.score_max_user_id,
				u.pseudo AS score_max_pseudo 
				FROM '.TABLE_ARCADE_JEUX.' AS j
				LEFT JOIN '.TABLE_ARCADE_SCORES.' AS s
					ON (j.id_jeu=s.id_jeu AND s.user_id='.$user['user_id'].')
				LEFT JOIN '.TABLE_USERS.' AS u
					ON (j.score_max_user_id=u.user_id)
				LEFT JOIN '.TABLE_ARCADE_CATS_JEUX.' AS cj
					ON (j.id_jeu=cj.id_jeu)
				LEFT JOIN '.TABLE_ARCADE_CATS.' AS c
					ON (cj.id_cat=c.id_cat)
				WHERE j.actif=1 AND c.module=\''.$this->module.'\'
				ORDER BY j.nbre_parties ASC
				LIMIT '.$this->start.','.$this->config['nbre_jeux_page'];
		
		// Aucun resultat		
		if (!is_array($retour = $this->retour_tableau_sql($sql))){
			$this->inserer_bloc_donnees_html('aucun_impopulaire');
		}else{
			// Affichage popup
			global $droits;
			$options = ($droits->check($this->module,0,'jouer'))? '$tpl->assign_block_vars(\'liste_impopulaires.popup\', array());':false;
			$this->inserer_bloc_donnees_html('liste_impopulaires',$retour,false,$options);
			if ($limit != null && $stats[0]['max']>$this->config['nbre_jeux_page'])	$this->inserer_bloc_donnees_html('liste_complete_impopulaires');
		}
		if ($limit == null){
			// Referencement et Navigation
			$this->set_titre_navigateur($lang['L_IMPOPULAIRES']);
			$this->set_titre_page($lang['L_IMPOPULAIRES']);
			$this->meta_description($this->config['description_salle']);
			$this->set_navlinks($this->config['titre_salle'],formate_url('',true));
			$this->set_navlinks($lang['L_IMPOPULAIRES'],formate_url('mode=impopulaires',true));
			
			// pagination
			$this->inserer_bloc_donnees_html('pagination');
			include_once($root.'fonctions/fct_affichage.php');
			$tpl->assign_vars(array(
				'PAGINATION'	=> create_pagination($this->start, 'mode=impopulaires&start=', $stats[0]['max'], $this->config['nbre_jeux_page'],$lang['L_JEUX_PAG'])
			));
		}
	}
	
	//
	// Affiche la page des statistiques globales
	function liste_stats_globales(){
		global $tpl,$root,$lang,$user;	
		
		if (!isset($this->user_id)) $this->user_id = $user['user_id'];
		
		// -------------------------------------------------------
		// GLOBALES
		// X jeux sont installes dans cette salle
		// Y parties ont ete jouees
		// xJyHzM de jeu ont ete enregistres
		$sql = 'SELECT count(DISTINCT(j.id_jeu)) AS Nbre_Jeux, 
					SUM(temps_partie) AS temps_partie, 
					SUM(nbre_parties) AS nbre_parties 
				FROM '.TABLE_ARCADE_JEUX.' AS j
				WHERE id_jeu IN ('.$this->liste_id_jeux().')';
		$retour = $this->retour_tableau_sql($sql);
		$this->inserer_donnees_html(array(
			'X_JEUX_INSTALLES'	=>	sprintf($lang['L_X_JEUX_INSTALLES'],$retour[0]['Nbre_Jeux']),
			'X_PARTIES_JOUEES'	=>	sprintf($lang['L_X_PARTIES_JOUEES'],$retour[0]['nbre_parties']),
			'X_TEMPS_DE_JEU'	=>	sprintf($lang['L_X_TEMPS_DE_JEU'],$this->afficher_duree_temps($retour[0]['temps_partie'])),
		));
		
		// Mr X a le record du nombre de parties avec Y parties	
		$sql = 'SELECT u.user_id,u.pseudo,SUM(nbre_parties) AS nbre_parties, u.sexe
				FROM '.TABLE_ARCADE_SCORES.' AS s
				LEFT JOIN '.TABLE_USERS.' AS u
					ON (s.user_id=u.user_id)
				WHERE s.id_jeu IN ('.$this->liste_id_jeux().')
				GROUP BY s.user_id
				HAVING SUM(nbre_parties)
				ORDER BY nbre_parties DESC
				LIMIT 1';
		$retour = $this->retour_tableau_sql($sql);
		if($retour[0]['sexe'] == 2)
		{
			$mrx_record_partie = sprintf($lang['L_MMEX_RECORD_PARTIES'],$this->formate_pseudo($retour[0]['user_id'],$retour[0]['pseudo']),$retour[0]['nbre_parties']);
		}else{
			$mrx_record_partie = sprintf($lang['L_MRX_RECORD_PARTIES'],$this->formate_pseudo($retour[0]['user_id'],$retour[0]['pseudo']),$retour[0]['nbre_parties']);
		}
			$this->inserer_donnees_html(array(
				'MRX_RECORD_PARTIES'	=>	$mrx_record_partie
			));
	
		// Mr X a le record du nombre de jeux differents essayes
		$sql = 'SELECT u.user_id,u.pseudo,count(id_jeu) AS id_jeu, u.sexe
				FROM '.TABLE_ARCADE_SCORES.' AS s
				LEFT JOIN '.TABLE_USERS.' AS u
					ON (s.user_id=u.user_id)
				WHERE s.id_jeu IN ('.$this->liste_id_jeux().')
				GROUP BY s.user_id
				HAVING count(id_jeu)
				ORDER BY id_jeu DESC
				LIMIT 1';

		$retour = $this->retour_tableau_sql($sql);
		if($retour[0]['sexe'] == 2)
		{
			$mrx_record_jeux = sprintf($lang['L_MMEX_RECORD_JEUX'],$this->formate_pseudo($retour[0]['user_id'],$retour[0]['pseudo']),$retour[0]['id_jeu']);
		}else{
			$mrx_record_jeux = sprintf($lang['L_MRX_RECORD_JEUX'],$this->formate_pseudo($retour[0]['user_id'],$retour[0]['pseudo']),$retour[0]['id_jeu']);
		}
		$this->inserer_donnees_html(array(
			'MRX_RECORD_JEUX'	=>	$mrx_record_jeux
		));
		// Mr X a le record du temps passe sur l'arcade avec Xj YH Zm
		$sql = 'SELECT u.user_id,u.pseudo,SUM(temps_partie) AS temps_partie, u.sexe
				FROM '.TABLE_ARCADE_SCORES.' AS s
				LEFT JOIN '.TABLE_USERS.' AS u
					ON (s.user_id=u.user_id)
				WHERE s.id_jeu IN ('.$this->liste_id_jeux().')
				GROUP BY s.user_id
				HAVING SUM(temps_partie)
				ORDER BY temps_partie DESC
				LIMIT 1';

		$retour = $this->retour_tableau_sql($sql);
		if($retour[0]['sexe'] == 2)
		{
			$mrx_record_temps = sprintf($lang['L_MMEX_RECORD_TEMPS'],$this->formate_pseudo($retour[0]['user_id'],$retour[0]['pseudo']),$this->afficher_duree_temps($retour[0]['temps_partie']));
		}else{
			$mrx_record_temps= sprintf($lang['L_MRX_RECORD_TEMPS'],$this->formate_pseudo($retour[0]['user_id'],$retour[0]['pseudo']),$this->afficher_duree_temps($retour[0]['temps_partie']));
		}
		$this->inserer_donnees_html(array(
			'MRX_RECORD_TEMPS'	=>	$mrx_record_temps
		));
		// Mr X a le record du nombre de victoires
		$sql = 'SELECT j.score_max_user_id,u.pseudo,COUNT(id_jeu) AS nbre_victoires, u.sexe
				FROM '.TABLE_ARCADE_JEUX.' AS j
				LEFT JOIN '.TABLE_USERS.' AS u
					ON (j.score_max_user_id=u.user_id)
				WHERE j.id_jeu IN ('.$this->liste_id_jeux().')
				AND j.score_max_user_id is not null
				GROUP BY j.score_max_user_id
				HAVING COUNT(id_jeu)
				ORDER BY nbre_victoires DESC
				LIMIT 1';

		$retour = $this->retour_tableau_sql($sql);
			
		if($retour[0]['sexe'] == 2)
		{
			$mrx_record_victoires = sprintf($lang['L_MMEX_RECORD_VICTOIRES'],$this->formate_pseudo($retour[0]['score_max_user_id'],$retour[0]['pseudo']),$retour[0]['nbre_victoires']);
		}else{
			$mrx_record_victoires = sprintf($lang['L_MRX_RECORD_VICTOIRES'],$this->formate_pseudo($retour[0]['score_max_user_id'],$retour[0]['pseudo']),$retour[0]['nbre_victoires']);
		}
		$this->inserer_donnees_html(array(
			'MRX_RECORD_VICTOIRES'	=>	$mrx_record_victoires
		));
		// Mr X a le record du nombre de scores Ultimes
		$sql = 'SELECT j.score_ultime_user_id,u.pseudo,COUNT(id_jeu) AS nbre_victoires, u.sexe
				FROM '.TABLE_ARCADE_JEUX.' AS j
				LEFT JOIN '.TABLE_USERS.' AS u
					ON (j.score_ultime_user_id=u.user_id)
				WHERE j.id_jeu IN ('.$this->liste_id_jeux().')
				AND j.score_ultime_user_id is not null
				GROUP BY j.score_ultime_user_id
				HAVING COUNT(id_jeu)
				ORDER BY nbre_victoires DESC
				LIMIT 1';

		$retour = $this->retour_tableau_sql($sql);
		
		if($retour[0]['sexe'] == 2)
		{
			$mrx_record_victoires_ultimes = sprintf($lang['L_MMEX_RECORD_VICTOIRES_ULTIMES'],$this->formate_pseudo($retour[0]['score_ultime_user_id'],$retour[0]['pseudo']),$retour[0]['nbre_victoires']);
		}else{
			$mrx_record_victoires_ultimes = sprintf($lang['L_MRX_RECORD_VICTOIRES_ULTIMES'],$this->formate_pseudo($retour[0]['score_ultime_user_id'],$retour[0]['pseudo']),$retour[0]['nbre_victoires']);
		}
		$this->inserer_donnees_html(array(
			'MRX_RECORD_VICTOIRES_ULTIMES'	=>	$mrx_record_victoires_ultimes
		));
		
		// Top 3 au classement par victoires
		$sql = 'SELECT j.score_max_user_id,u.pseudo AS score_max_pseudo, u.avatar, 
				COUNT(id_jeu) AS nbre_victoires
				FROM '.TABLE_ARCADE_JEUX.' AS j
				LEFT JOIN '.TABLE_USERS.' AS u
					ON (j.score_max_user_id=u.user_id)
				WHERE j.id_jeu IN ('.$this->liste_id_jeux().')
				AND j.score_max_user_id is not null 
				GROUP BY j.score_max_user_id
				ORDER BY nbre_victoires DESC
				LIMIT 3';

		if (is_array($retour = $this->retour_tableau_sql($sql))){
			$this->inserer_bloc_donnees_html('top_classement',$retour);
		}
		
		// Top 3 des favoris
		$tpl->set_filenames(array('top_favoris' => $root.'plugins/modules/arcade/html/top_favoris.html'));
		$this->declarer_clefs_lang();
		$this->liste_top_favoris(5);
		$tpl->assign_var_from_handle('TOP_FAVORIS','top_favoris');
		
		// Stats des categories
		
				
		// Referencement et Navigation
		$this->set_titre_navigateur($lang['L_STATS_GLOBALES']);
		$this->set_titre_page($lang['L_STATS_GLOBALES']);
		$this->meta_description($lang['L_STATS_GLOBALES']);
		$this->set_navlinks($this->config['titre_salle'],formate_url('',true));
		$this->set_navlinks($lang['L_STATS_GLOBALES'],formate_url('mode=stats&user_id='. $this->user_id ,true));

	
	}	
	
	//
	// Affiche la page des statistiques personnelles
	function liste_stats_perso(){
		global $tpl,$root,$lang,$user;	
		
		if (!isset($this->user_id)) $this->user_id = $user['user_id'];

		// Affichage des favoris
		$tpl->set_filenames(array('favoris' => $root.'plugins/modules/arcade/html/favoris.html'));
		$this->declarer_clefs_lang();
		$this->liste_favoris(5);
		$tpl->assign_var_from_handle('FAVORIS','favoris');
		
		// tu as joue xHys
		$sql = 'SELECT SUM(temps_partie) AS temps_partie
				FROM '.TABLE_ARCADE_SCORES.' AS s
				WHERE s.id_jeu IN ('.$this->liste_id_jeux().')
				AND s.user_id='.$this->user_id.'
				LIMIT 1';

		$retour = $this->retour_tableau_sql($sql);
		$this->inserer_donnees_html(array(
			'TON_TEMPS'	=>	sprintf($lang['L_TON_TEMPS'],$this->afficher_duree_temps(intval($retour[0]['temps_partie']))),
		));
		$temps_jeu = $retour[0]['temps_partie'];
		// Tu as joue a X jeux differents
		$sql = 'SELECT count(id_jeu) AS nbre_jeux
				FROM '.TABLE_ARCADE_SCORES.' AS s
				WHERE s.id_jeu IN ('.$this->liste_id_jeux().')
				AND  s.user_id='.$this->user_id.'
				LIMIT 1';
		$retour = $this->retour_tableau_sql($sql);
		$this->inserer_donnees_html(array(
			'TON_NBRE_JEUX'	=>	sprintf($lang['L_TON_NBRE_JEUX'],intval($retour[0]['nbre_jeux'])),
		));
		// Tu cumules X parties enregistrees
		$sql = 'SELECT SUM(nbre_parties) AS nbre_parties
				FROM '.TABLE_ARCADE_SCORES.' AS s
				WHERE s.id_jeu IN ('.$this->liste_id_jeux().')
				AND  s.user_id='.$this->user_id.'
				LIMIT 1';
		$retour = $this->retour_tableau_sql($sql);
		$this->inserer_donnees_html(array(
			'TON_NBRE_PARTIES'	=>	sprintf($lang['L_TON_NBRE_PARTIES'],intval($retour[0]['nbre_parties'])),
		));
		$nbre_parties = ($retour[0]['nbre_parties'] == 0)?1:intval($retour[0]['nbre_parties']);
		
		// En moyenne tu joues X min par jeu
		
		$this->inserer_donnees_html(array(
			'TON_TPS_MOYEN'	=>	sprintf($lang['L_TON_TPS_MOYEN'],$this->afficher_duree_temps(round($temps_jeu / $nbre_parties))),
		));
		
		// Le jeu ou tu as passe le plus de temps est 
		$sql = 'SELECT s.temps_partie, s.id_jeu, j.nom_jeu 
				FROM '.TABLE_ARCADE_SCORES.' AS s
				LEFT JOIN '.TABLE_ARCADE_JEUX.' AS j
					ON (s.id_jeu=j.id_jeu)
				WHERE s.id_jeu IN ('.$this->liste_id_jeux().')
				AND s.user_id='.$this->user_id.'
				ORDER BY s.temps_partie DESC
				LIMIT 1';

		$retour = $this->retour_tableau_sql($sql);
		$this->inserer_donnees_html(array(
			'TON_JEU_PREFERRE'	=>	sprintf($lang['L_TON_JEU_PREFERRE'],formate_url('mode=partie&id_jeu='.$retour[0]['id_jeu'],true),$retour[0]['nom_jeu'],$this->afficher_duree_temps(intval($retour[0]['temps_partie']))),
		));
		
		
		// Top 5 des jeux les plus mis en favoris
		
		
		
		// Referencement et Navigation
		$this->set_titre_navigateur($lang['L_STATS_PERSO']);
		$this->set_titre_page($lang['L_STATS_PERSO']);
		$this->meta_description($lang['L_STATS_PERSO']);
		$this->set_navlinks($this->config['titre_salle'],formate_url('',true));
		$this->set_navlinks($lang['L_STATS_PERSO'],formate_url('mode=stats&user_id='. $this->user_id ,true));
	
	}
	
	//
	// Affichage d'une page indiquant que l'utilisateur n'a pas le droit de jouer
	function interdiction_de_jouer($zone='voir'){
		global $tpl,$root,$lang,$user,$droits;
		load_lang('register');
		load_lang('login');
		
		$reglement = (file_exists(PATH_REGLEMENT))? @file_get_contents(PATH_REGLEMENT):$lang['REGLEMENT'];
		$reglement = preg_replace("/\n /",'<br />',$reglement);
		
		if($zone == 'voir')
			$this->inserer_bloc_donnees_html('interdiction_voir');
		else
			$this->inserer_bloc_donnees_html('interdiction_jouer');

		$this->inserer_donnees_html(array(
			'L_COMMENT_DEVENIR_MEMBRE'		=>	$lang['L_COMMENT_DEVENIR_MEMBRE'],
			'L_REGISTER_SITE'				=>	$lang['L_REGISTER_SITE'],
			'L_INSCRIPTION'					=>	$lang['L_INSCRIPTION'],
			'L_CHECK'						=>	$lang['L_CHECK'],
			'ETAPE'							=>	1,
			'REGLEMENT'						=>	$reglement,
			'TITRE_LOGIN'					=> $lang['TITRE_LOGIN'],
			'LOGIN_LOGIN'					=> $lang['LOGIN_LOGIN'],
			'LOGIN_MDP'						=> $lang['LOGIN_MDP'],
			'LOGIN_COOKIE_BLOC'				=> $lang['LOGIN_COOKIE_BLOC'],
			'LOGIN_AUTHENTIFIER'			=> $lang['LOGIN_AUTHENTIFIER'],
			'L_INTERDICTION_JOUER_DETAILS'	=> $lang['L_INTERDICTION_JOUER_DETAILS'],
			'L_INTERDICTION_VOIR_ALERTE'	=> $lang['L_INTERDICTION_VOIR_ALERTE'],
			'L_INTERDICTION_JOUER_ALERTE'	=> $lang['L_INTERDICTION_JOUER_ALERTE']
		));
		
		// Referencement et Navigation
		$this->set_titre_navigateur($lang['L_INTERDICTION_JOUER_TITRE']);
		$this->set_titre_page($lang['L_INTERDICTION_JOUER_TITRE']);
		$this->meta_description($this->meta_description($this->config['description_salle']));
		$this->set_navlinks($lang['L_INTERDICTION_JOUER_TITRE'],formate_url('mode=interdiction',true));
	}
	
	//
	// Ajoute le jeu aux favoris 
	function ajouter_favoris($id_jeu,$user_id){
		global $c;
		$sql = 'INSERT INTO '.TABLE_ARCADE_FAVORIS.' (id_jeu, user_id) VALUES
				('.$id_jeu.','.$user_id.')';
		$c->sql_query($sql); 
	}
	
	//
	// Supprime le jeu des favoris 
	function supprimer_favoris($id_jeu,$user_id){
		global $c;
		$sql = 'DELETE FROM '.TABLE_ARCADE_FAVORIS.' WHERE id_jeu='.$id_jeu.' AND user_id='.$user_id;
		$c->sql_query($sql); 
	}
	
	//
	// pour chaque nouvelle partie on cree une session unique
	function init_session_jeu($dossier){
		global $c,$user,$session;
		//  generation d'une clef numerique de 20 a 30 caracteres
		$this->clef = mt_rand();
		$sql = 'INSERT INTO '.TABLE_ARCADE_SESSIONS.' (clef_session,id_jeu,dossier_jeu,user_id,debut_session) 
				VALUES (\''.$this->clef.'\','.$this->id_jeu.',\''.$dossier.'\','.$user['user_id'].','.$session->time.')';
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,1302,__FILE__,__LINE__,$sql);
		$this->id_session = $c->sql_nextid($resultat);
	}
	
	//
	// Transmission de la clef
	function transmission_clef()
	{
		global $c,$user;
		$sql = 'SELECT clef_session FROM '.TABLE_ARCADE_SESSIONS.' 
				WHERE user_id='.$user['user_id'].' AND fin_session is null  AND id_session='.$this->id_session.' LIMIT 1';
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,1302,__FILE__,__LINE__,$sql);
		if ($c->sql_numrows($resultat) == 1){
			$row = $c->sql_fetchrow($resultat);
			die(utf8_encode('myscorekey='.$row['clef_session'].'&MalleoOK=1'));
		}else{
			print('Aucune session trouvee');
		}
	}
	
	//
	// Installe un  nouveau module
	function installer_nouveau_module(){
	if ($this->module != "plugins/modules/arcade/admin_arcade_triche.php"){
		$sql = 'INSERT INTO '.TABLE_ARCADE_MODULES.' (`module`) VALUES (\''.$this->module.'\');';
		if (!$this->retour_tableau_sql($sql)){
			message_die(E_ERROR,1301,__FILE__,__LINE__,$sql);
		}
		$this->Get_config();
		}
	}
	
	//
	// Cree un ensemble de pointeurs contenant la configuration de cette salle d'arcade
	function Get_config(){
		$sql = 'SELECT * FROM '.TABLE_ARCADE_MODULES.' WHERE module=\''.$this->module.'\'';
		if (!($this->config = $this->retour_tableau_sql($sql))){
			$this->installer_nouveau_module();
		}else{
			$this->config = $this->config[0];
		}
	}
	//
	// Cree un ensemble de pointeurs contenant les informations sur la categorie en cours
	function Get_infos_categorie(){
		if (!isset($this->id_cat)) return false;
		$this->retour_objet_sql('SELECT * FROM '.TABLE_ARCADE_CATS.' WHERE module=\''.$this->module.'\' AND id_cat='.$this->id_cat);	
	}
	
	//
	// Cree un ensemble de pointeurs contenant les informations sur la categorie en cours
	function Get_infos_jeu(){
		if (!isset($this->id_jeu)) return false;
		$this->retour_objet_sql('SELECT * FROM '.TABLE_ARCADE_JEUX.' WHERE id_jeu='.$this->id_jeu);	
	}
	
	//
	// Renvoie la liste des jeux appartenant au module courant
	function liste_id_jeux(){
		if (!isset($this->liste_id_jeux)){
			$this->liste_id_jeux = $this->Get_liste_id_jeux();
		}
		return $this->liste_id_jeux;
	}

	//
	// Cree une liste des id de jeux d'un module
	function Get_liste_id_jeux(){
		$sql = 'SELECT DISTINCT(j.id_jeu)
					FROM '.TABLE_ARCADE_CATS.' AS c
					LEFT JOIN '.TABLE_ARCADE_CATS_JEUX.' AS cj
						ON (c.id_cat=cj.id_cat)
					LEFT JOIN '.TABLE_ARCADE_JEUX.' AS j
						ON (cj.id_jeu=j.id_jeu)										
					WHERE c.module=\''.$this->module.'\' AND j.actif is true';
		$retour = $this->retour_tableau_sql($sql);
		$liste_id_jeux = '';
		if (is_array($retour)){
			foreach($retour as $d){
				foreach($d as $id){
					$id = intval($id);
					if ($id>0) $liste_id_jeux[]  = '\''.$id.'\'';
				}
			}
		}
		return (is_array($liste_id_jeux))? implode(',',$liste_id_jeux):'\'\'';
	}
	
	//
	// Modifie l'apparance du controle
	function formate_controles($id_controle){
		global $lang;
		switch ($id_controle){
			default : case 0: return $lang['L_CONTROLES_INCONNUS']; break;
			case 1: return $lang['L_CONTROLES_CLAVIER']; break;
			case 2: return $lang['L_CONTROLES_SOURIS']; break;
			case 3: return $lang['L_CONTROLES_CLAVIER_SOURIS']; break;
			case 4: return $lang['L_CONTROLES_MULTI']; break;
		}	
	}
	//
	// Cree le dossier demande et place dedans un .htaccess pour le proteger
	function creer_dossier($dir){
		@mkdir($dir,$this->umask);
		chmod($dir, $this->umask); // normalement inutile mais certains hebergeurs modifient les droits par defaut a la creation
		$file = @fopen($dir.'.htaccess', 'w');
	    @fwrite($file,$this->htaccess);
	    @fclose($file);
		chmod($dir.'.htaccess', $this->umask);
	}
	
	//
	// Transforme un nombre de secondes, en temps lisible pour l'humain
	function afficher_duree_temps($tps){
		global $lang;
		load_lang('time');
		// SECONDES
		if ($tps < 60 ){
			return $tps.$lang['L_SECONDE'];		
			
		// MINUTES ET SECONDES
		}elseif ($tps < 3600){
			$min = floor($tps/60);
			$sec = $tps - ($min*60);
			return $min.$lang['L_MINUTE'].' '.$sec.$lang['L_SECONDE'];	
			
		// HEURES, MINUTES et SECONDES
		}elseif($tps < 86400){
			$h = floor($tps/3600);
			$min = floor(($tps - ($h*3600))/60);
			$sec = $tps - ($h*3600) - ($min*60);
			return $h.$lang['L_HEURE'].' '.$min.$lang['L_MINUTE'].' '.$sec.$lang['L_SECONDE'];	
		
		// JOURS, HEURES, MINUTES et SECONDES
		}else{
			$j = floor($tps/86400);
			$h = floor(($tps - ($j*86400))/3600);
			$min = floor(($tps - ($j*86400) - ($h*3600))/60);
			$sec = floor(($tps - ($j*86400) - ($h*3600) - ($min*60))/60);		
			return $j.$lang['L_JOUR'].' '. $h.$lang['L_HEURE'].' '.$min.$lang['L_MINUTE'].' '.$sec.$lang['L_SECONDE'];	
		}
	}
	
	//
	// permet d'enrichir le pseudo des users avant leur affichage
	function formate_pseudo($user_id,$pseudo){
		global $img,$lang;
		$stats = '<a href="'.formate_url('mode=stats_perso&user_id='.$user_id,true).'">&nbsp;<img src="'.$img['arcade_stats_user'].'" alt="'.sprintf($lang['L_STATS_DE'],$pseudo).'" /></a>';
		return formate_pseudo($user_id,$pseudo).$stats;
	}
	
	//
	// affiche les options
	function afficher_options(){
		global $tpl,$lang,$img,$droits;
		$options = array();		
		$options[] = array(
			'ICONE'		=> $img['arcade_recents'],
			'LIBELLE'	=> $lang['L_RECENTS'],
			'LIEN'		=> formate_url('mode=recents',true));
		$options[] = array(
			'ICONE'		=> $img['arcade_populaires'],
			'LIBELLE'	=> $lang['L_POPULAIRES'],
			'LIEN'		=> formate_url('mode=populaires',true));
		$options[] = array(
			'ICONE'		=> $img['arcade_impopulaires'],
			'LIBELLE'	=> $lang['L_IMPOPULAIRES'],
			'LIEN'		=> formate_url('mode=impopulaires',true));
		// Les favoris sont actives ?
		if ($this->config['activer_favoris'] && $droits->check($this->module,0,'favoris')){
			$options[] = array(
				'ICONE'		=> $img['arcade_favoris'],
				'LIBELLE'	=> $lang['L_FAVORIS'],
				'LIEN'		=> formate_url('mode=favoris',true));
		}
		$options[] = array(
			'ICONE'		=> $img['arcade_stats_globales'],
			'LIBELLE'	=> $lang['L_STATS_GLOBALES'],
			'LIEN'		=> formate_url('mode=stats_globales',true));	

		$tpl->options_page = $options;	
	}
}

?>
