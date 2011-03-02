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
if (!defined('PROTECT'))
{
	die("Tentative de Hacking");
}
class messagerie{

	var $user_id;
	// chaine de pseudos separes par des virgules
	var $a;
	var $sujet;
	var $message;
	var $sujet_initial=null;
	var $hidden='nouveau';
	var $champs_de=false;
	var $id_mp;
	var $retour='inbox';
	// Liste des options de la messagerie
	var $options;
	
	function messagerie(){
		global $root,$post;
		include_once($root.'class/class_posting.php');
		$post = new posting();
	}
	
	//
	// Nettoie les entrees
	function clean($vars)
	{
		foreach($vars as $key=>$val)
		{
			switch($key)
			{
				// Liste chiffres separes par des virgules
				case 'id_mp':	if(is_array($val))$val = implode(',',$val);
								$this->id_mp = ereg_replace('[^0-9,]','',$val);	break;
				case 'sujet_initial':	if(intval($val)!=0) $this->sujet_initial = intval($val);	break;
				// Chaine encodee
				case 'a': $this->$key = utf8_encode(html_to_str(utf8_decode($val)));break;
				// Chaine
				case 'sujet':
				case 'message':	
				case 'retour': $this->$key = protection_chaine($val); 	break;
				// Booleen
				case 'messagerie_copie_mail':
				case 'messagerie_accepter_mp':	
				case 'messagerie_accepter_mail':
				case 'messagerie_absent_site':		$this->$key = ($val==1)?'true':'false';	break;
				// Booleen ou chaine
				case 'messagerie_absent_site_msg':	$this->$key = ($val==null || $val =='')? 'null':protection_chaine($val);	break;
			}
		}	
	}
	
	//
	// Protege les elements d'un tableau avec des quotes
	function protect_liste_users($chaine){
		$listing='';
		$temp = array();
		// Conversion en tableau
		$chaine = explode(',',$chaine);
		foreach ($chaine as $user){
			// suppression des doublons
			if (!in_array($user,$temp)){
				$temp[] = $user;
				if ($listing!='')$listing.=',';
				$listing.='"'.$user.'"';
			}		
		}
		$listing = htmlentities($listing,ENT_NOQUOTES,'UTF-8');
		return $listing;
	}
	
	//
	// Envoie un MP automatique en cas d'absence du site
	function avertir_absence_site($exp,$msg_perso,$dest_pseudo,$dest_id){
		global $c,$lang;
		$sujet = $lang['L_MSG_ABSENCE_TITRE'].$this->sujet;
		$message = ($msg_perso!='' && $msg_perso != null)? $msg_perso:$lang['L_MSG_ABSENCE'];
		$sql = 'INSERT INTO '.TABLE_MESSAGERIE.'(userid_from,sujet,message,date,destinataires,sujet_initial) VALUES
			('.$exp.', \''.str_replace("\'","''",$sujet).'\', \''.str_replace("\'","''",$message).'\', '.time().',\''.$dest_pseudo.'\','.$this->id_mp.')';
		if ($result=!$c->sql_query($sql)) message_die(E_ERROR,1203,__FILE__,__LINE__,$sql);
		$id_mp = $c->sql_nextid($result);
		$sql = 'INSERT INTO '.TABLE_MESSAGERIE_ETAT.'(id_mp,userid_dest,etat,cat) VALUES
			('.$id_mp.', \''.$dest_id.'\',0,0)';
		if (!$c->sql_query($sql)) message_die(E_ERROR,1203,__FILE__,__LINE__,$sql);
	}

	//
	// Envoie un MP
	function send_mp(){
		global $c,$user,$tpl,$email;
		
		// Tous les champs sont biens remplis ?
		if (empty($this->a) || empty($this->sujet) || empty($this->message)){
			message_die(E_WARNING,1207,'','');
		}	
		
		// RECHERCHE de l'existance des destinataires
		$sql = 'SELECT DISTINCT u.user_id, u.email, u.pseudo, u.messagerie_copie_mail, messagerie_absent_site, messagerie_absent_site_msg  
				FROM '.TABLE_USERS.' AS u
				LEFT JOIN '.TABLE_MESSAGERIE_CONTACTS.' AS c
				ON (u.user_id=c.user_id) 
				WHERE u.pseudo IN ('.$this->protect_liste_users($this->a).')
				AND (u.messagerie_accepter_mp=1
					OR (u.messagerie_accepter_mp=0 AND c.id_contact='.$user['user_id'].'))';
		if (!$resultat = $c->sql_query($sql)) message_die(E_ERROR,1202,__FILE__,__LINE__,$sql);
		if ($c->sql_numrows($resultat)==0){
			return false;
		}else{
			if ($this->sujet_initial == null)$this->sujet_initial='null';
			$sql = 'INSERT INTO '.TABLE_MESSAGERIE.'(userid_from,sujet,message,date,destinataires,sujet_initial) VALUES
					('.$user['user_id'].', \''.str_replace("\'","''",$this->sujet).'\', \''.str_replace("\'","''",$this->message).'\', '.time().',\''.str_replace('"','',$this->protect_liste_users($this->a)).'\','.$this->sujet_initial.')';
			if ($result=!$c->sql_query($sql)) message_die(E_ERROR,1203,__FILE__,__LINE__,$sql);
			$this->id_mp = $c->sql_nextid($result);
			// Enregistrement du MP pour chaque destinataire
			while($row = $c->sql_fetchrow($resultat)){
				$sql = 'INSERT INTO '.TABLE_MESSAGERIE_ETAT.'(id_mp,userid_dest,etat,cat) VALUES
					('.$this->id_mp.', \''.$row['user_id'].'\',0,0)';
				if (!$c->sql_query($sql)) message_die(E_ERROR,1203,__FILE__,__LINE__,$sql);
				// Si l'utilisateur veut une alerte email
				if ($row['messagerie_copie_mail'] == 1){
					$email->AddAddress($row['email'],$row['pseudo']);
				}
				if ($row['messagerie_absent_site'] == 1){
					// L'utilisateur est absent du site
					$this->avertir_absence_site($row['user_id'],$row['messagerie_absent_site_msg'],$user['pseudo'],$user['user_id']);				
				}
			}
			return true;
		}
	}
	
	//
	// Envoie un Mail
	function send_mail(){
		global $c,$user,$tpl,$email;
		
		// Tous les champs sont biens remplis ?
		if (empty($this->a) || empty($this->sujet) || empty($this->message)){
			message_die(E_WARNING,1207,'','');
		}	
		// RECHERCHE de l'existance des destinataires
		$sql = 'SELECT u.user_id, u.email, u.pseudo, u.messagerie_copie_mail  
				FROM '.TABLE_USERS.' AS u
				LEFT JOIN '.TABLE_MESSAGERIE_CONTACTS.' AS c
				ON (u.user_id=c.user_id) 
				WHERE u.pseudo IN ('.$this->protect_liste_users($this->a).')
				AND (u.messagerie_accepter_mail=1
					OR (u.messagerie_accepter_mail=0 AND c.id_contact='.$user['user_id'].'))';
		if (!$resultat = $c->sql_query($sql)) message_die(E_ERROR,1202,__FILE__,__LINE__,$sql);
		if ($c->sql_numrows($resultat)==0){
			return false;
		}else{
			// Enregistrement du MP pour chaque destinataire
			while($row = $c->sql_fetchrow($resultat)){
				// Si l'utilisateur veut une alerte email
				if ($row['messagerie_copie_mail'] == 1){
					$email->AddAddress( $row['email'],$row['pseudo']);
				}
			}
			return true;
		}
	}	
	//
	// Marque un MP comme lu (etat 1)
	function marquer_lu(){
		global $c,$user;
		$sql = 'UPDATE '.TABLE_MESSAGERIE_ETAT.' SET
				etat=1
				WHERE id_mp IN ('.$this->id_mp.') AND userid_dest='.$user['user_id'];
		if (!$resultat = $c->sql_query($sql)) message_die(E_ERROR,1205,__FILE__,__LINE__,$sql);
	}
	
	//
	// Marque un MP comme non lu (etat 0)
	function marquer_nonlu(){
		global $c,$user;
		$sql = 'UPDATE '.TABLE_MESSAGERIE_ETAT.' SET
				etat=0
				WHERE id_mp IN ('.$this->id_mp.') AND userid_dest='.$user['user_id'];
		if (!$resultat = $c->sql_query($sql)) message_die(E_ERROR,1205,__FILE__,__LINE__,$sql);
	}
	
	//
	// Supprimer un MP
	function supprimer_mp(){
		global $c,$user;
		$sql = 'SELECT id_mp, count(id_mp) as cpt  
				FROM '.TABLE_MESSAGERIE_ETAT.' as e
				WHERE e.id_mp IN ('.$this->id_mp.') 
				GROUP BY e.id_mp';
		if (!$resultat = $c->sql_query($sql)) message_die(E_ERROR,1209,__FILE__,__LINE__,$sql);
		while($row = $c->sql_fetchrow($resultat))
		{
			// Plusieurs destinataires Alors on ne nettoie que la table ETAT
			if ($row['cpt']>1){
				$sql_delete = 'DELETE FROM '.TABLE_MESSAGERIE_ETAT.' 
						WHERE id_mp='.$row['id_mp'].' AND userid_dest='.$user['user_id'];
			
			}else{
			// C'est le seul ou le dernier MP de cet auteur, on peut supprimer les 2 tables.
			// Ou je suis l'auteur du MP et le destinataire ne l'a pas encore lu
				$sql_delete = 'DELETE m,e 
						FROM '.TABLE_MESSAGERIE.' as m, '.TABLE_MESSAGERIE_ETAT.' as e
						WHERE m.id_mp=e.id_mp 
						AND e.id_mp='.$row['id_mp'].' 
						AND		(e.userid_dest='.$user['user_id'].' 
							OR 	(m.userid_from='.$user['user_id'].' AND etat=0))';
			}
			if (!$res = $c->sql_query($sql_delete)) message_die(E_ERROR,1209,__FILE__,__LINE__,$sql_delete);	
		}
	}
	
	//
	// archiver
	function archiver(){
		global $c,$user;
		$sql = 'UPDATE '.TABLE_MESSAGERIE_ETAT.' SET
				cat=1
				WHERE id_mp IN ('.$this->id_mp.') AND userid_dest='.$user['user_id'];
		if (!$resultat = $c->sql_query($sql)) message_die(E_ERROR,1205,__FILE__,__LINE__,$sql);
	}
	
	//
	// Options personnelles de la messagerie privee
	function parametrer_mp(){
		global $tpl,$root,$img,$lang,$user;
		$tpl->set_filenames(array('liste_options'=>$root.'plugins/modules/messagerie/html/liste_options.html'));
		
		foreach ($this->options as $k=>$v){
			// En fonction du type
			$tpl->assign_block_vars('options', array(
				'LIBELLE'=>$v[1]
			));
			switch($v[0]){
				case 'booleen':
					$tpl->assign_block_vars('options.booleen', array(
						'LIBELLE1' 	=> $v[2][1],
						'LIBELLE2' 	=> $v[2][0],
						'CHECK1'	=> ($user[$k]==true)?' checked="checked"':'',
						'CHECK2'	=> ($user[$k]==false)?' checked="checked"':'',
						'NOM' 		=> $k
					));
					break;
				case 'texte':
					$tpl->assign_block_vars('options.texte', array(
						'NOM'		=> $k,
						'VALEUR'	=> $v[2]				
					));
					break;			
			}		
		}
		$tpl->assign_var_from_handle('ZONE_CENTRALE','liste_options');	
	}
	
	//
	// Met a jour les preferences de l'utilisateur
	function update_element($element){
		global $c,$cache;
		$sql = 'UPDATE '.TABLE_USERS.' SET 
				'.$element.'
				WHERE user_id='.$this->user_id;
		if (!$resultat = $c->sql_query($sql)) message_die(E_ERROR,1210,__FILE__,__LINE__,$sql);
		$user=$cache->appel_cache('infos_user',true);
	}

	//
	// Renvoie le messages non lu de l'utilisateur
	function nbre_messages_nonlu(){
		global $c,$tpl,$user;
		$sql = 'SELECT e.id_mp
				FROM '.TABLE_MESSAGERIE_ETAT.' AS e
				WHERE etat=0  AND cat=0 AND userid_dest='.$user['user_id'];
		if (!$resultat = $c->sql_query($sql)) message_die(E_ERROR,1201,__FILE__,__LINE__,$sql);
		return $c->sql_numrows($resultat);
	}
	//
	// Charge le menu lateral
	function afficher_menu_lateral(){
		global $tpl,$lang,$root,$img;
		$tpl->set_filenames(array('menu'=>$root.'plugins/modules/messagerie/html/menu_lateral.html'));
		$tpl->assign_vars(array(
			'I_NEWMP'	=> $img['messagerie_menu_nouveau_mp'],
			'I_NEWMAIL'	=> $img['messagerie_menu_nouveau_mail'],
			'I_OPTIONS'	=> $img['messagerie_menu_options'],
			'I_CONTACTS'=> $img['messagerie_menu_contacts'],
			'I_DOSSIER'	=> $img['messagerie_menu_sauvegardes'],
			'I_INBOX'	=> $img['messagerie_menu_elements_recus'],
			'I_SENTBOX'	=> $img['messagerie_menu_elements_envoyes'],
			'I_OUTBOX'	=> $img['messagerie_menu_elements_en_cours'],
			
			'S_NEWMP'	=> formate_url('mode=newmp',true),
			'S_NEWMAIL'	=> formate_url('mode=newmail',true),
			'S_INBOX'	=> formate_url('mode=inbox',true),
			'S_OUTBOX'	=> formate_url('mode=outbox',true),
			'S_SENTBOX'	=> formate_url('mode=sentbox',true),
			'S_OPTIONS'	=> formate_url('mode=options',true),
			'S_CONTACTS'=> formate_url('mode=contacts',true),
			'S_SAVEBOX'	=> formate_url('mode=savebox',true),
		));
		$tpl->assign_var_from_handle('ZONE_MENU','menu');
	}
	
	//
	// Affiche la liste des contacts privilegies du membre
	function afficher_liste_contacts(){
		global $c,$tpl,$lang,$root,$user,$img,$_GET;
		$tpl->set_filenames(array('liste_contacts'=>$root.'plugins/modules/messagerie/html/liste_contacts.html'));
				$sql = 'SELECT id_contact, pseudo 
				FROM '.TABLE_MESSAGERIE_CONTACTS.' AS c
				LEFT JOIN '.TABLE_USERS.' AS u
				ON (c.id_contact=u.user_id)
				WHERE c.user_id='.$user['user_id'].'
				ORDER BY pseudo ASC';
		if (!$resultat = $c->sql_query($sql)) message_die(E_ERROR,1201,__FILE__,__LINE__,$sql);
		if ($c->sql_numrows($resultat)==0){
			$tpl->assign_block_vars('aucun_contact', array());
		}else{
			$class='row2';
			while($row = $c->sql_fetchrow($resultat))
			{
				$class=($class=='row2')?'row1':'row2';
				$tpl->assign_block_vars('liste_contacts', array(
					'CLASS'	=> $class,
					'PSEUDO'=> formate_pseudo($row['id_contact'],$row['pseudo']),
					'ECRIRE_MP'=> (isset($_GET['mode']) && $_GET['mode']=='newmp')? 'javascript:AjouterUser(\''.utf8_encode(html_entity_decode($row['pseudo'])).'\');':formate_url('mode=newmp&a='.utf8_encode(html_entity_decode($row['pseudo'])),true),
					'ECRIRE_MAIL'=> (isset($_GET['mode']) && $_GET['mode']=='newmail')? 'javascript:AjouterUser(\''.utf8_encode(html_entity_decode($row['pseudo'])).'\');':formate_url('mode=newmail&a='.utf8_encode(html_entity_decode($row['pseudo'])),true)
				));	
			}
		}
		$tpl->assign_vars(array(
			'I_MP'				=> $img['messagerie_envoyer_mp'],
			'I_MAIL'			=> $img['messagerie_envoyer_email'],
		));
		$tpl->assign_var_from_handle('ZONE_CONTACTS','liste_contacts');
	}
	
	//
	// lister boite de reception
	function lister_boite_reception(){
		global $c,$tpl,$user;
		$sql = 'SELECT e.id_mp, userid_from, sujet, message, date, etat, pseudo 
				FROM '.TABLE_MESSAGERIE_ETAT.' AS e
				LEFT JOIN '.TABLE_MESSAGERIE.' AS m
				ON (e.id_mp=m.id_mp)
				LEFT JOIN '.TABLE_USERS.' AS u
				ON (m.userid_from=u.user_id)
				WHERE cat=0 AND userid_dest='.$user['user_id'].'
				ORDER BY date DESC';
		if (!$resultat = $c->sql_query($sql)) message_die(E_ERROR,1201,__FILE__,__LINE__,$sql);
		$liste = array();
		while($row = $c->sql_fetchrow($resultat))
		{
			$liste[] = $row;
		}
		$this->champs_de = true;
		$tpl->assign_block_vars('champs_DE', array());
		if (sizeof($liste)>0)$tpl->assign_block_vars('options', array());
		$this->afficher_liste_messages($liste);
	}
	
	//
	// lister les archives
	function lister_archives(){
		global $c,$tpl,$user;
		$sql = 'SELECT e.id_mp, userid_from, sujet, message, date, etat, pseudo 
				FROM '.TABLE_MESSAGERIE_ETAT.' AS e
				LEFT JOIN '.TABLE_MESSAGERIE.' AS m
				ON (e.id_mp=m.id_mp)
				LEFT JOIN '.TABLE_USERS.' AS u
				ON (m.userid_from=u.user_id)
				WHERE cat=1 AND userid_dest='.$user['user_id'].'
				ORDER BY date DESC';
		if (!$resultat = $c->sql_query($sql)) message_die(E_ERROR,1201,__FILE__,__LINE__,$sql);
		$liste = array();
		while($row = $c->sql_fetchrow($resultat))
		{
			$liste[] = $row;
		}
		$this->champs_de = true;
		$tpl->assign_block_vars('champs_DE', array());
		if (sizeof($liste)>0)$tpl->assign_block_vars('options', array());
		$this->afficher_liste_messages($liste);
	}	
	//
	// Affichage de la boîte d'envoi
	function lister_boite_envoi(){
		global $c,$tpl,$user;
		$sql = 'SELECT e.id_mp, userid_dest, sujet, message, date, etat, pseudo
				FROM '.TABLE_MESSAGERIE_ETAT.' AS e
				LEFT JOIN '.TABLE_MESSAGERIE.' AS m
				ON (e.id_mp=m.id_mp)
				LEFT JOIN '.TABLE_USERS.' AS u
				ON (e.userid_dest=u.user_id)
				WHERE etat=0 AND userid_from='.$user['user_id'].'
				ORDER BY date DESC';
		if (!$resultat = $c->sql_query($sql)) message_die(E_ERROR,1201,__FILE__,__LINE__,$sql);
		$liste = array();
		while($row = $c->sql_fetchrow($resultat))
		{
			$liste[] = $row;
		}
		$tpl->assign_block_vars('champs_A', array());
		if (sizeof($liste)>0)$tpl->assign_block_vars('options', array());
		$this->afficher_liste_messages($liste);
	}
	
	//
	// Affichage de la boîte d'envoi
	function lister_boite_envoyes(){
		global $c,$tpl,$user;
		$sql = 'SELECT e.id_mp, userid_dest, sujet, message, date, etat, pseudo
				FROM '.TABLE_MESSAGERIE_ETAT.' AS e
				LEFT JOIN '.TABLE_MESSAGERIE.' AS m
				ON (e.id_mp=m.id_mp)
				LEFT JOIN '.TABLE_USERS.' AS u
				ON (e.userid_dest=u.user_id)
				WHERE etat=1 AND userid_from='.$user['user_id'].'
				ORDER BY date DESC';
		if (!$resultat = $c->sql_query($sql)) message_die(E_ERROR,1201,__FILE__,__LINE__,$sql);
		$liste = array();
		while($row = $c->sql_fetchrow($resultat))
		{
			$liste[] = $row;
		}
		$tpl->assign_block_vars('champs_A', array());
		$this->afficher_liste_messages($liste);
	}	
	//
	// AFFICHER la liste des messages
	function afficher_liste_messages($liste){
		global $tpl,$lang,$root,$img,$post,$mode;
		$tpl->set_filenames(array('liste_mps'=>$root.'plugins/modules/messagerie/html/liste_mps.html'));
		
		// Aucun MP
		if (sizeof($liste)==0){
			$tpl->assign_block_vars('aucun_message', array());
		}else{
			// AFFICHAGE des MPs
			foreach ($liste as $id=>$data){
				// Mode de visualisation A ou DE
				$auteur = ($this->champs_de==true)?  formate_pseudo($data['userid_from'],$data['pseudo']): formate_pseudo($data['userid_dest'],$data['pseudo']);
				$tpl->assign_block_vars('liste_messages', array(
					'ID_MP'			=> $data['id_mp'],
					'ICONE'			=> ($data['etat']==0)?$img['messagerie_message_non_lu']:$img['messagerie_message_lu'],
					'L_LU_NONLU'	=> ($data['etat']==0)?$lang['L_NONLU']:$lang['L_LU'],
					'AUTEUR'		=> $auteur,
					'SUJET'			=> $post->bbcode2html($data['sujet']),
					'S_LIRE'		=> formate_url('index.php?module=messagerie&mode=lecture&id_mp='.$data['id_mp']),
					'DATE'			=> date('d/m/Y G\hi',$data['date'])				
				));
				if (array_key_exists('options.',$tpl->_tpldata))$tpl->assign_block_vars('liste_messages.checkbox', array());
			}
		}
		$tpl->assign_vars(array(
			'FORMULAIRE'		=> formate_url('index.php?module=messagerie'),
			'RETOUR'			=> $mode
		));
		$tpl->assign_var_from_handle('ZONE_CENTRALE','liste_mps');
	}
	
	//
	// AFFICHER les champs de saisie d'un MP
	function afficher_saisie_mp(){
		global $tpl,$lang,$root,$img;
		$tpl->set_filenames(array('saisie_mp'=>$root.'plugins/modules/messagerie/html/saisie_mp.html'));
		
		if (isset($this->a)){
			$tpl->assign_vars(array(
				'A'=> stripslashes($this->a)
			));
		}
		$tpl->assign_vars(array(
			'I_DELETE'				=> $img['effacer'],
			'SUJET_INITIAL'			=> ($this->sujet_initial!=null)?$this->sujet_initial:'',
			'SUJET'					=> (isset($this->sujet))?$this->sujet:(($this->sujet_initial!=null)?$lang['L_RE']:''),
			'HIDDEN'				=> $this->hidden
		));
		$tpl->assign_var_from_handle('ZONE_CENTRALE','saisie_mp');	
	}
	
	//
	// AFFICHER les champs de saisie d'un Mail
	function afficher_saisie_mail(){
		global $tpl,$lang,$root,$img,$_GET,$cf,$user,$_SERVER,$session;
		$tpl->set_filenames(array('saisie_mail'=>$root.'plugins/modules/messagerie/html/saisie_mail.html'));
		
		if (isset($_GET['a'])){
			$tpl->assign_vars(array(
				'A'=> stripslashes($_GET['a'])
			));
		}
		$tpl->assign_vars(array(
			'L_ENTETE_EMAIL'		=> sprintf($lang['L_ENTETE_EMAIL'],'http://'.$cf->config['adresse_site'],$user['pseudo'],(isset($_SERVER['REMOTE_HOST']))?$_SERVER['REMOTE_HOST']:$session->ip),
			'I_DELETE'				=> $img['effacer'],
			'HIDDEN'				=> $this->hidden
		));
		$tpl->assign_var_from_handle('ZONE_CENTRALE','saisie_mail');	
	}	
	//
	// AFFICHAGE du MP
	function lecture_mp(){
		global $c,$tpl,$lang,$root,$img,$user,$session,$post;
		include_once($root.'fonctions/fct_profil.php');
		$tpl->set_filenames(array('lecture'=>$root.'plugins/modules/messagerie/html/lecture_mp.html'));
		
		$sql = 'SELECT e.id_mp,userid_dest, u.pseudo AS pseudo_dest, userid_from, u_from.pseudo AS pseudo_from, 
				sujet, message, date, destinataires, etat, sujet_initial 
				FROM '.TABLE_MESSAGERIE_ETAT.' AS e
				LEFT JOIN '.TABLE_MESSAGERIE.' AS m
					ON (e.id_mp=m.id_mp)
				LEFT JOIN '.TABLE_USERS.' AS u
					ON (e.userid_dest=u.user_id)
				LEFT JOIN '.TABLE_USERS.' AS u_from
					ON (m.userid_from=u_from.user_id)
				WHERE e.id_mp='.$this->id_mp.' 
				AND (m.userid_from='.$user['user_id'].' OR e.userid_dest='.$user['user_id'].')';
		if (!$resultat = $c->sql_query($sql)) message_die(E_ERROR,1206,__FILE__,__LINE__,$sql);

		// SECURITE
		if($c->sql_numrows($resultat)==0){
			error404(1204);
		}
		
		$row = $c->sql_fetchrow($resultat);		
		// Marquer comme lu
		if ($row['etat']==0) $this->marquer_lu();
		
		// TITRE Page + NavLinks
		$tpl->titre_navigateur = $tpl->titre_page = $row['sujet'];
		$session->make_navlinks(array($lang['L_LECTURE']	=> formate_url('mode=lecture&id_mp='.$this->id_mp,true)));
		
		$sujet_initial = ($row['sujet_initial']!=0)?$row['sujet_initial']:$row['id_mp'];
		$tpl->assign_vars(array(
			'TITRE'			=> $row['sujet'],
			'DE'			=> formate_pseudo($row['userid_from'],$row['pseudo_from']),
			'DATE'			=> date('d/m/Y G\hi',$row['date']),
			'A'				=> $row['destinataires'],
			'MESSAGE'		=> $post->bbcode2html($row['message']),
			'I_DELETE'		=> $img['effacer'],
			'I_REPONDRE'	=> $img['repondre'],
			'S_DELETE'		=> formate_url('mode=supprimer&id_mp='.$row['id_mp'],true),
			'S_REPONDRE'	=> formate_url('mode=newmp&a='.$row['pseudo_from'].'&sujet_initial='.$sujet_initial,true)
		));
		
		// Le MP n'est pas le sujet initial ??? alors on affiche tous les topics correspondants:
		$nbre_msg = 0;
		if ($row['sujet_initial']!=null){
			$sql = 'SELECT DISTINCT e.id_mp,userid_dest, u.pseudo AS pseudo_dest, userid_from, u_from.pseudo AS pseudo_from,u_from.avatar,  
				sujet, message, date, destinataires, etat  
				FROM '.TABLE_MESSAGERIE_ETAT.' AS e
				LEFT JOIN '.TABLE_MESSAGERIE.' AS m
					ON (e.id_mp=m.id_mp)
				LEFT JOIN '.TABLE_USERS.' AS u
					ON (e.userid_dest=u.user_id)
				LEFT JOIN '.TABLE_USERS.' AS u_from
					ON (m.userid_from=u_from.user_id)
				WHERE (sujet_initial='.$row['sujet_initial'].' 
					OR e.id_mp='.$row['sujet_initial'].')
				AND (m.userid_from='.$user['user_id'].' OR e.userid_dest='.$user['user_id'].')
				ORDER BY date ASC';
			if (!$resultat = $c->sql_query($sql)) message_die(E_ERROR,1206,__FILE__,__LINE__,$sql);
			while ($row = $c->sql_fetchrow($resultat)){
				$tpl->assign_block_vars('liste_mp', array(
					'DE'			=> formate_pseudo($row['userid_from'],$row['pseudo_from']),
					'TITRE'			=> $row['sujet'],
					'AVATAR'		=> $row['avatar'],
					'DATE'			=> date('d/m/Y G\hi',$row['date']),
					'A'				=> $row['destinataires'],
					'MESSAGE'		=> $post->bbcode2html($row['message'])
				));			
				$nbre_msg++;
			}
		}
		if ($nbre_msg>0) $tpl->assign_block_vars('reponse', array());
		
		$tpl->assign_var_from_handle('ZONE_CENTRALE','lecture');
	}
	
	//
	// Interface de gestion des contacts
	function lister_contacts(){
		global $tpl,$lang,$root,$img,$c,$user;
		$tpl->set_filenames(array('gestion_contacts'=>$root.'plugins/modules/messagerie/html/gerer_contacts.html'));
		
		$sql = 'SELECT id_contact, pseudo, rang, msg  
				FROM '.TABLE_MESSAGERIE_CONTACTS.' AS c
				LEFT JOIN '.TABLE_USERS.' AS u
				ON (c.id_contact=u.user_id)
				WHERE c.user_id='.$user['user_id'].'
				ORDER BY pseudo ASC';
		if (!$resultat = $c->sql_query($sql)) message_die(E_ERROR,1208,__FILE__,__LINE__,$sql);
		if ($c->sql_numrows($resultat)==0){
			$tpl->assign_block_vars('aucun_contacts', array());
		}else{
			if (!function_exists('formate_sexe')) include_once($root.'fonctions/fct_profil.php');
			while($row = $c->sql_fetchrow($resultat))
			{
				$tpl->assign_block_vars('contacts', array(
					'ID'		=> $row['id_contact'],
					'PSEUDO'	=> formate_pseudo($row['id_contact'],$row['pseudo']),
					'RANG'		=> formate_rang($row['rang'],$row['msg']),
					'ECRIRE_MP'	=> formate_url('mode=newmp&a='.utf8_encode(html_entity_decode($row['pseudo'])),true),
					'ECRIRE_MAIL'=> formate_url('mode=newmail&a='.utf8_encode(html_entity_decode($row['pseudo'])),true),
					'S_SUPPR'	=> formate_url('mode=contacts&action=delete&id='.$row['id_contact'],true)
				));
			}
		}
		$tpl->assign_vars(array(
			'I_MP'				=> $img['messagerie_envoyer_mp'],
			'I_DELETE'			=> $img['effacer'],
		));
		$tpl->assign_var_from_handle('ZONE_CENTRALE','gestion_contacts');	
	}
	
	//
	// Supprimer un contact
	function contact_delete($id_contact){
		global $c,$user;
		$sql = 'DELETE FROM '.TABLE_MESSAGERIE_CONTACTS.' 
				WHERE id_contact='.$id_contact.' AND user_id='.$user['user_id'];
		if (!$resultat = $c->sql_query($sql)) message_die(E_ERROR,1208,__FILE__,__LINE__,$sql);
	}
	
	//
	// Ajotuer un contact
	function contact_ajouter($pseudo){
		global $c,$user;
		$sql = 'SELECT user_id FROM '.TABLE_USERS.' 
				WHERE pseudo = \''.str_replace("\'","''",$pseudo).'\'';
		if (!$resultat = $c->sql_query($sql)) message_die(E_ERROR,1208,__FILE__,__LINE__,$sql);
		if ($c->sql_numrows($resultat)>0){
			$row = $c->sql_fetchrow($resultat);
			$sql = 'INSERT INTO '.TABLE_MESSAGERIE_CONTACTS.'(id_contact, user_id) VALUES 
					('.$row['user_id'].','.$user['user_id'].')';
			$c->sql_query($sql);
			return true;
		}
		return false;
	}
}

?>