<?php

//
// CLASS submit est une extension de la class arcade dediee aux fonctionnalites de soumission du score
// de test de triche, et autres actions declenchees par une modification d'un score ou d'un classement
//
class submit Extends arcade{
	var $chaine;
	var $fin_session;
	
	// inits
	function submit(){
		global $module,$user,$id_session;
		$this->module = $module;
		$this->fin_session = time();
		$this->id_session = $id_session;
		$this->user_id=$user['user_id'];
		if(!isset($this->config) || !is_array($this->config)) $this->Get_config();
	}

	//
	// Traite le nouveau score
	function nouveau_score($chaine_score,$session_id){
		$this->chaine = $chaine_score;
		$this->session_id = $session_id;
		$this->recherche_session();
		$this->femeture_session();
		if($this->tests_anti_triche()){
			$this->enregistrement_score();
			$this->maj_stats_jeu();
			$this->chargement_plugins();
			$this->redirect_jeu();
		}
	}
	
	//
	// Impossible de savoir quelle partie est celle submitee
	// On parcours les sessions pour trouver celle correspondant
	function recherche_session(){
		global $c,$user,$id_session;
		$sql = 'SELECT * FROM '.TABLE_ARCADE_SESSIONS.' 
				WHERE user_id='.$user['user_id'].' AND id_session='.$this->session_id.'
				ORDER BY debut_session DESC';
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,1302,__FILE__,__LINE__,$sql);
		if ($c->sql_numrows($resultat) == 0){
			//  ALERTER session perdue
			error404(1304);
		}else{
			while ($row = $c->sql_fetchrow($resultat)){
			if ($row['fin_session']!= null)
		{
		header('location: '.formate_url('index.php?module='.$this->module.'&mode=partie&id_jeu='.$row['id_jeu']));
		exit;
		}
			
				$chaine_decodee = $this->DecodeChaine($this->chaine,$row['clef_session']);
				// Nom du Jeu ; FPS ; Flashgametime ; score ; nbre parties ;  score fake
				$match = explode(';',$chaine_decodee);
				if (sizeof($match) == 6){
					$this->gname			= preg_replace('/[^a-z0-9_-]/i','',$match[0]);	// variable
					$this->gframerate		= intval($match[1]);							// FPS
					$this->flashgametime	= intval($match[2]);							// Temps flash
					$this->gscore			= floatval($match[3]);							// score
					$this->gnbparties		= intval($match[4]);							// Nombre de parties
					$this->gscorefake		= floatval($match[5]);							// score modifie
					// On enregistre l'identifiant de la session pour traitement
					$this->session = $row;
					break;
				}
			}
			// La session n'a pas ete trouvee
			if (!isset($this->session)){
				error404(1304);
			}
		}
	}
	
	//
	// Ferme la session en cours
	function femeture_session(){
		global $c,$user;
		$sql = 'UPDATE '.TABLE_ARCADE_SESSIONS.' SET fin_session='.$this->fin_session.'
				WHERE id_session='.$this->session['id_session'];
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,1302,__FILE__,__LINE__,$sql);
	}
	
	//
	// Effectue des tests sur les valeurs saisies
	function tests_anti_triche(){
		global $cf;
		$this->tps_partie = ($this->fin_session-$this->session['debut_session']);
		// Le joueur a fait au moins 1 partie
		$this->gnbparties = ($this->gnbparties>0)?$this->gnbparties:1;
		// On recupere les infos sur le jeu
		if (!$this->infos_jeu($this->session['id_jeu'])){ return error404(1305); }
		// Triche d'edition du score
		if (($this->gscorefake - $this->gscore) != $this->session['clef_session']  || $this->gscorefake == $this->gscore){
			return $this->declarer_triche(1);
		}
		// le jeu est bien active ?
		if (!$this->actif) return error404(1306);
		// On verifie que la variable du jeu est bien la bonne
		if ($this->variable != $this->gname){
			return $this->declarer_triche(2);
		}
		// la difference de temps entre le submit flash et le traitement du score est trop long
		$gamenotrealtime = array(-1, 0, 1, 2, 3);
	    if (!in_array($this->tps_partie, $gamenotrealtime, TRUE) 
			&& ( ($this->flashgametime > ($this->tps_partie + $cf->config['arcade_time_tolerance'])) || ($this->flashgametime < ($this->tps_partie - $cf->config['arcade_time_tolerance'])))){
			return $this->declarer_triche(3);
		}
		// On controle les FPS
		if (($cf->config['arcade_fps_tolerance'] >= 0) 
		&&  ($this->gframerate + ($cf->config['arcade_fps_tolerance'] * $this->fps * 0.01)) < $this->fps ){
			return $this->declarer_triche(4);
		}
		return true;
	}

	//
	// Enregistrement du score
	function enregistrement_score(){
		global $c;
		$sql = '';
		if ($this->score == null){
			// Premier score
			$sql = 'INSERT INTO '.TABLE_ARCADE_SCORES.' (id_jeu, user_id, date_score, score, temps_partie,nbre_parties)
					VALUES ('.$this->id_jeu.','.$this->user_id.','.$this->fin_session.','.$this->gscore.','.$this->tps_partie.','.$this->gnbparties.')';		
		}else{
			// Maj
			if (	($this->score_sens == 1 && $this->gscore > $this->score)
				|| 	($this->score_sens == 0 && $this->gscore < $this->score)){
				$sql = 'score = '.$this->gscore.', date_score='.$this->fin_session.', ';
			}
			$sql = 'UPDATE '.TABLE_ARCADE_SCORES.' 
					SET '.$sql.' temps_partie=temps_partie+'.$this->tps_partie.',nbre_parties=nbre_parties+'.$this->gnbparties.'
					WHERE id_jeu='.$this->id_jeu.'  AND user_id='.$this->user_id;
		}
		if ($sql!=''){
			if (!$c->sql_query($sql))message_die(E_ERROR,1300,__FILE__,__LINE__,$sql);
		}
	}
	
	//
	// Met a jour les stats nbre de partie, temps jeu, meilleur score et ultimate score
	function maj_stats_jeu(){
		global $c;
		$sql_score_max = $sql_score_ultime = '';
		if (	($this->score_sens == 1 && $this->gscore > $this->score_max)
			|| 	($this->score_sens == 0 && $this->gscore < $this->score_max)
			||	$this->score_max == null){
			$sql_score_max = 'score_max = '.$this->gscore.', score_max_user_id='.$this->user_id.', ';
			// Mail pour prevenir l'ancien champion
			if (	$this->score_max != null 
					&& $this->config['activer_mail_champion'] == 1
					&& $this->score_max_user_id != $this->user_id){
					$this->mail_ancien_champion();
			}
			// MP pour prevenir l'ancien champion
			if (	$this->score_max != null 
					&& $this->config['activer_mp_champion'] == 1
					&& $this->score_max_user_id != $this->user_id){
					$this->mp_ancien_champion();
			}
		}
		if (	($this->score_sens == 1 && $this->gscore > $this->score_ultime)
			|| 	($this->score_sens == 0 && $this->gscore < $this->score_ultime)
			||	$this->score_ultime == null){
			$sql_score_ultime = 'score_ultime = '.$this->gscore.', score_ultime_user_id='.$this->user_id.', ';
		}
		$sql = 'UPDATE '.TABLE_ARCADE_JEUX.' 
				SET '.$sql_score_max.' '.$sql_score_ultime.'
				temps_partie=temps_partie+'.$this->tps_partie.',nbre_parties=nbre_parties+'.$this->gnbparties.'
				WHERE id_jeu='.$this->id_jeu;	
		if (!$c->sql_query($sql))message_die(E_ERROR,1300,__FILE__,__LINE__,$sql);
	}
	
	//
	// Fonction bateau permettant d'être remplacee a souhait par des plugins
	function chargement_plugins(){
		return true;
	}
	
	//
	// Mail pour prevenir l'ancien champion
	function mail_ancien_champion(){
		global $root,$cf,$lang,$user;
		load_lang('emails');
		
		$url_image = 'http://'.$cf->config['adresse_site'].$cf->config['path'].$this->path_games.$this->dossier_jeu.'/'.$this->image_grande;
		$url_jeu = 'http://'.$cf->config['adresse_site'].$cf->config['path'].'index.php?module='.$this->module.'&mode=partie&id_jeu='.$this->id_jeu;
		
		require_once($root.'class/class_mail.php');
		$email = new mail();
		$email->Subject = $lang['L_MAIL_CHAMPION_SUJET'];
		$email->titre_message = $this->nom_jeu;
		$email->message_explain = sprintf($lang['L_MAIL_CHAMPION_EXPLAIN'],$this->pseudo);
		$email->formate_html(sprintf($lang['L_MAIL_CHAMPION_MSG'], $url_image, $user['pseudo'],$this->gscore, $url_jeu));
		$email->AddAddress($this->email,$this->pseudo);
		$email->Send();	
	}
	
	//
	// MP pour prévenir l'ancien champion
	function mp_ancien_champion(){
		global $c,$cf,$user,$tpl,$lang;

		$sql = 'SELECT user_id, pseudo 
				FROM '.TABLE_USERS.' 
				WHERE pseudo = \''.str_replace('"','',$this->pseudo).'\'';
		if (!$resultat = $c->sql_query($sql)) message_die(E_ERROR,1202,__FILE__,__LINE__,$sql);
				
		$url_image = 'http://'.$cf->config['adresse_site'].$cf->config['path'].$this->path_games.$this->dossier_jeu.'/'.$this->image_grande;
		$url_jeu = 'http://'.$cf->config['adresse_site'].$cf->config['path'].'index.php?module='.$this->module.'&mode=partie&id_jeu='.$this->id_jeu;
		
		$sql = 'INSERT INTO '.TABLE_MESSAGERIE.'(userid_from,sujet,message,date,destinataires) VALUES
				('.$user['user_id'].', \''.str_replace("\'","''",sprintf($lang['L_MP_CHAMPION_SUJET'],protection_chaine(stripslashes($this->nom_jeu)))).'\', \''.str_replace("\'","''",sprintf($lang['L_MP_CHAMPION_MSG'], $url_image, $user['pseudo'],$this->gscore, $url_jeu)).'\', '.time().',\''.str_replace('"','',$this->pseudo).'\')';
		if ($result=!$c->sql_query($sql)) message_die(E_ERROR,1203,__FILE__,__LINE__,$sql);
		$this->id_mp = $c->sql_nextid($result);
		
		while($row = $c->sql_fetchrow($resultat)){
				$sql = 'INSERT INTO '.TABLE_MESSAGERIE_ETAT.'(id_mp,userid_dest,etat,cat) VALUES
				('.$this->id_mp.', \''.$row['user_id'].'\',0,0)';
		if (!$c->sql_query($sql)) message_die(E_ERROR,1203,__FILE__,__LINE__,$sql);		
		}				
		return true;
	}
	
	//
	// Redirige le joueur sur le jeu ou il etait
	function redirect_jeu($erreur=null){
		header('location: '.formate_url('index.php?module='.$this->module.'&mode=partie&id_jeu='.$this->id_jeu.$erreur));
		exit;
	}
	
	//
	// Enregistre les informations de la partie dans le gestionnaire de triche
	// type_triche : 
	// 1 :  gamescorefake
	// 2:  variable
	// 3:  flashgametime
	// 4:  fps
	function declarer_triche($type_triche){
		global $c;
		$sql = 'INSERT INTO '.TABLE_ARCADE_TRICHES.' (user_id,id_jeu,score,date,flashtime,temps_reel,type_triche,fps) 
				VALUES ('.$this->user_id.','.$this->id_jeu.','.$this->gscore.','.$this->fin_session.',
				'.$this->flashgametime.','.$this->tps_partie.','.$type_triche.','.$this->gframerate.')';
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,1300,__FILE__,__LINE__,$sql);
	
		// Message d'Alerte
		switch ($type_triche){
			case 1: $this->redirect_jeu('&erreur_submit=1308');break;
			case 2: $this->redirect_jeu('&erreur_submit=1307');break;
			case 3: $this->redirect_jeu('&erreur_submit=1309');break;
			case 4: $this->redirect_jeu('&erreur_submit=1310');break;
		}
		exit;
	}
	
	//
	// Recupere toutes les infos connues sur le jeu
	function infos_jeu($id_jeu){
		global $c;
		$sql = 'SELECT j.id_jeu,j.dossier_jeu,j.nom_jeu,j.actif,j.variable,j.score_sens,
				j.score_max,j.score_max_user_id,j.score_ultime,j.score_ultime_user_id,j.fps,
				j.temps_partie,j.nbre_parties,j.image_grande,
				s.date_score, s.score, s.temps_partie,
				u.email,u.pseudo
				FROM '.TABLE_ARCADE_JEUX.' AS j
				LEFT JOIN '.TABLE_ARCADE_SCORES.' AS s
					ON (j.id_jeu = s.id_jeu AND s.user_id='.$this->user_id.')
				LEFT JOIN '.TABLE_USERS.' AS u
					ON (j.score_max_user_id = u.user_id)
				WHERE j.id_jeu='.$id_jeu.' LIMIT 1';
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,1300,__FILE__,__LINE__,$sql);
		if ($c->sql_numrows($resultat) == 0){
			return false;
		}else{
			$row = $c->sql_fetchrow($resultat);
			foreach ($row AS $key=>$val){
				$this->$key = $val;
			}
			return true;			
		}
	}
	
	//
	// Decode la chaine passee
	function DecodeChaine($chaine='', $clef='') {
		$retour = '';
		$i = $j = $char = 0;
		while ($i < STRLEN($chaine)) {
				if ($j >= STRLEN($clef)) $j = 0;
				$char = intval(SUBSTR($clef,$j,1)) ^ ord(SUBSTR($chaine, $i, 1));
				$retour .= CHR($char);
				$i++;
				$j++;
		}
		return($retour);
	}
	
	
}
?>
