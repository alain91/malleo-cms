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
class blog
{
	var $id_billet;
	var $id_com;
	var $commentaire;
	var $liste_categories;
	var $module;
	var $saisie = array();
	var $date_parution;
	
	function blog()
	{
		$this->date_parution = time();
	}
	
	//
	// Nettoyage centralisé
	// entree : $vars = $_POST ou $_GET
	// sortie : $this->sortie tableau associatif des données saisies.
	
	function clean($vars)
	{
		foreach($vars as $key=>$val)
		{
			switch($key)
			{
				// Entier + pointeur
				case 'id_com':			
				case 'id_billet':		$this->$key = $this->saisie[$key] = intval($val);break;
				// Entier
				case 'id_com':			
				case 'id_cat':			$this->saisie[$key] = intval($val); break;
				// Chaine de caracteres
				case 'blog_name':
				case 'billet':
				case 'excerpt':
				case 'url':
				case 'title':
				case 'commentaire':		
				case 'titre_billet':	
				case 'site':			$this->saisie[$key] = protection_chaine($val);	break;
				// Pseudo
				case 'pseudo':			$this->saisie['pseudo'] = nettoyage_nom($val); 	break;
				// Mail
				case 'mail':			$this->saisie['mail'] = nettoyage_mail($val); 	break;
				// Tags
				case 'tags':			$val = trim(ereg_replace("[^a-z0-9_-]",' ',strtolower(supprimer_accents($val)))); 	
										$val = ereg_replace("(^[a-z0-9]{1}[ ])|([ ][a-z0-9]{1}[ ])|([ ][a-z0-9]{1}$)",' ',$val); 	
										$this->saisie['tags'] = trim(ereg_replace("[ ]{2}",' ',$val)); 	
										break;
				// Temps
				case 'heure': case 'minute':  case 'mois':	case 'jour': case 'annee':
										if (isset($this->saisie['date_parution'])) break;
										$this->saisie['date_parution'] = mktime(intval($vars['heure']),intval($vars['minute']),0,intval($vars['mois']),intval($vars['jour']),intval($vars['annee']));
										break;
			}
		}	
	}	

	//
	// liste les informations d'un billet
	
	function infos_billet()
	{
		global $c;
		$sql = 'SELECT titre_billet, billet, date_parution, id_cat, tags, auteur   
				FROM '.TABLE_BLOG_BILLETS.' WHERE id_billet='.$this->id_billet.' LIMIT 1';
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,507,__FILE__,__LINE__,$sql); 
		if ($c->sql_numrows($resultat) == 0){
			return false;
		}else{
			$row = $c->sql_fetchrow($resultat);
			foreach($row as $key=>$value){
				$this->$key=$value;
			}
			return true;
		}
	}
	
	//
	// Enregistre un billet
	
	function ajouter_billet()
	{
		global $user,$c,$cf,$module,$root;
		// Protection contre les messages vides
		if (empty($this->saisie['titre_billet']) || empty($this->saisie['billet'])){
			message_die(E_WARNING,523,'','');
		}
		$sql = 'INSERT INTO '.TABLE_BLOG_BILLETS.' 
					(titre_billet, billet, auteur, date_redaction, date_parution, id_cat, tags) 
				VALUES 
					(\''.$this->saisie['titre_billet'].'\',
						\''.$this->saisie['billet'].'\',
						'.$user['user_id'].',
						\''.time().'\',
						\''.$this->saisie['date_parution'].'\',
						'.$this->saisie['id_cat'].',
						\''.$this->saisie['tags'].'\')';
		if (!$resultat = $c->sql_query($sql)) message_die(E_ERROR,501,__FILE__,__LINE__,$sql);
		$this->id_billet = $c->sql_nextid($resultat);

		// On met à jour le nbre de billets
		$this->update_nbre_billets();
		// On met à jour le nbre de messages de l'utilisateur
		$this->update_nbre_messages($user['user_id'],'+');
		// Enregistrement en local des images
		require_once($root.'class/class_image.php');
		$image = new image();
		$image->copie_locale_images($this->saisie['billet'],'UPDATE '.TABLE_BLOG_BILLETS.' SET billet=\'%s\' WHERE id_billet='.$this->id_billet);
		// On affiche une fenetre de confirmation
		affiche_message('blog','L_BILLET_ENREGISTRE',formate_url('mode=billet&id_billet='.$this->id_billet,true));
		return true;
	}

	//
	// Mise a jour des donnees
	
	function update_billet()
	{
		global $c,$cf,$user,$root;
		// on verifie que le user est bien l'auteur si il n'est pas l'admin
		$this->id_billet=$this->saisie['id_billet'];
		$this->infos_billet();

		// Protection contre les messages vides
		if (empty($this->saisie['titre_billet']) || empty($this->saisie['billet'])){
			message_die(E_WARNING,523,'','');
		}
		
		// Enregistrement
		$sql = 'UPDATE '.TABLE_BLOG_BILLETS.' SET
					titre_billet=\''.$this->saisie['titre_billet'].'\',
					billet=\''.$this->saisie['billet'].'\',
					date_parution=\''.$this->saisie['date_parution'].'\',
					tags=\''.$this->saisie['tags'].'\',
					id_cat='.$this->saisie['id_cat'].' 
				WHERE id_billet='.$this->saisie['id_billet'];
		if (!$resultat = $c->sql_query($sql)) message_die(E_ERROR,501,__FILE__,__LINE__,$sql);
		// On met à jour le nbre de billets
		$this->update_nbre_billets(); 
		// Enregistrement en local des images
		require_once($root.'class/class_image.php');
		$image = new image();
		$image->copie_locale_images($this->saisie['billet'],'UPDATE '.TABLE_BLOG_BILLETS.' SET billet=\'%s\' WHERE id_billet='.$this->saisie['id_billet']);
		// On affiche une fenetre de confirmation
		affiche_message('blog','L_BILLET_ENREGISTRE',formate_url('mode=billet&id_billet='.$this->saisie['id_billet'],true));
		return true;
	}
	
	//
	// Supprime un billet et qui tout ce qui s'y rapporte 
	
	function supprime_billet()
	{
		global $c;
		$sql = 'DELETE FROM '.TABLE_BLOG_BILLETS.' WHERE id_billet='.$this->id_billet.' LIMIT 1;';
		if (!$resultat = $c->sql_query($sql)) message_die(E_ERROR,506,__FILE__,__LINE__,$sql);
		// SUPPRESSION des coms associés
		$sql = 'DELETE FROM '.TABLE_BLOG_COMS.' WHERE id_billet='.$this->id_billet.';';
		if (!$resultat = $c->sql_query($sql)) message_die(E_ERROR,506,__FILE__,__LINE__,$sql);
		// On met à jour le nbre de billets
		$this->update_nbre_billets();
		// On affiche une fenetre de confirmation
		affiche_message('blog','L_BILLET_SUPPRIME',formate_url('',true));
		return true;
	}
	
	//
	// Ajoute un commentaire
	
	function ajouter_commentaire()
	{
		global $user,$c;
		// Protection contre les messages vides
		if (empty($this->saisie['commentaire']) ||($user['user_id']==1 && empty($this->saisie['pseudo']))){
			message_die(E_WARNING,523,'','');
		}
		
		$sql = 'INSERT INTO '.TABLE_BLOG_COMS.' ( id_billet, user_id, date, msg, email, pseudo, site) 
				VALUES ('.$this->saisie['id_billet'].','.$user['user_id'].',\''.time().'\',\''.$this->saisie['commentaire'].'\',\''.$this->saisie['mail'].'\',
				\''.$this->saisie['pseudo'].'\',\''.$this->saisie['site'].'\')';
		if (!$resultat = $c->sql_query($sql)) message_die(E_ERROR,505,__FILE__,__LINE__,$sql);
		// On incremente le compteur de coms 
		$this->update_nbre_commentaires();	
		// On met a jour le nbre de messages de l'utilisateur
		$this->update_nbre_messages($user['user_id'],'+');		
		// On previent les autres de cet email
		$this->mail_avertissement($this->saisie['id_billet'],$user['user_id'],$this->saisie['commentaire']);
		// On affiche une fenetre de confirmation
		affiche_message('blog','L_COM_ENREGISTRE',formate_url('mode=billet&id_billet='.$this->saisie['id_billet'].'#commentaires',true));
		return true;
	}
	
	//
	// Supprime un commentaire
	
	function supprime_commentaire()
	{
		global $c;
		$sql = 'DELETE FROM '.TABLE_BLOG_COMS.' WHERE id_com='.$this->saisie['id_com'].' LIMIT 1';
		if (!$resultat = $c->sql_query($sql)) message_die(E_ERROR,515,__FILE__,__LINE__,$sql);
		// On incremente le compteur de coms 
		$this->update_nbre_commentaires();
		affiche_message('blog','L_COM_SUPPRIME',formate_url('mode=billet&id_billet='.$this->commentaire['id_billet'] .'#commentaires',true));
		return true;
	}
	
	//
	// liste les categories pour les utiliser dans un menu deroulant
	
	function lister_options_categories()
	{
		global $c;
		$liste_cat = '';
		$sql = 'SELECT id_cat, titre_cat FROM '.TABLE_BLOG_CATS.' WHERE module="'.$this->module.'" ORDER BY ordre ASC';
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,509,__FILE__,__LINE__,$sql); 
		while($row = $c->sql_fetchrow($resultat))
		{
			$selected = (isset($this->id_cat) && $this->id_cat==$row['id_cat'])?' selected="selected"':'';
			$liste_cat .= "\n ".'<option value="'.$row['id_cat'].'"'.$selected.'>'.$row['titre_cat'].'</option>';
		}
		return $liste_cat;
	}
		
	//
	// liste les categories pour les utiliser dans un menu deroulant
	
	function lister_blog_cat()
	{
		global $c;
		$liste_cat = array();
		$sql = 'SELECT id_cat, titre_cat, image_cat, nbre_billets, module FROM '.TABLE_BLOG_CATS.' ORDER BY ordre ASC';
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,509,__FILE__,__LINE__,$sql); 
		while($row = $c->sql_fetchrow($resultat))
		{
			$liste_cat[] = $row;
		}
		return $liste_cat;
	}

	//
	// In/De cremente le compteur de billets
	
	function update_nbre_billets()
	{
		global $c;
		$sql_update = 'UPDATE '.TABLE_BLOG_CATS.' SET nbre_billets=0';
		if (!$c->sql_query($sql_update)) message_die(E_ERROR,514,__FILE__,__LINE__,$sql_update);
		$sql = 'SELECT id_cat, count(id_billet) as count FROM '.TABLE_BLOG_BILLETS.' GROUP BY id_cat;';
		if (!$resultat = $c->sql_query($sql)) message_die(E_ERROR,514,__FILE__,__LINE__,$sql);
		while($row = $c->sql_fetchrow($resultat))
		{
			$sql_update = 'UPDATE '.TABLE_BLOG_CATS.' SET nbre_billets='.$row['count'].' WHERE id_cat='.$row['id_cat'];
			if (!$c->sql_query($sql_update)) message_die(E_ERROR,514,__FILE__,__LINE__,$sql_update);
		}
		return true;
	}
	
	//
	// In/De cremente le compteur de commentaires
	
	function update_nbre_commentaires()
	{
		global $c;
		$sql_update = 'UPDATE '.TABLE_BLOG_BILLETS.' SET nbre_coms=0';
		if (!$c->sql_query($sql_update)) message_die(E_ERROR,501,__FILE__,__LINE__,$sql_update);
		$sql = 'SELECT id_billet, count(id_com) as count FROM '.TABLE_BLOG_COMS.' GROUP BY id_billet;';
		if (!$resultat = $c->sql_query($sql)) message_die(E_ERROR,501,__FILE__,__LINE__,$sql);
		while($row = $c->sql_fetchrow($resultat))
		{
			$sql_update = 'UPDATE '.TABLE_BLOG_BILLETS.' SET nbre_coms='.$row['count'].' WHERE id_billet='.$row['id_billet'];
			if (!$c->sql_query($sql_update)) message_die(E_ERROR,501,__FILE__,__LINE__,$sql_update);
		}
		return true;
	}
	
	function incremente_lectures()
	{
		global $c;
		$sql = 'UPDATE '.TABLE_BLOG_BILLETS.' SET nbre_vues=nbre_vues+1 WHERE id_billet='.$this->id_billet;
		if (!$resultat = $c->sql_query($sql)) message_die(E_ERROR,501,__FILE__,__LINE__,$sql);
	}
	
	//
	// On envoit un mail pour prevenir des reponses
	function mail_avertissement($id_billet,$except_uid,$message)
	{
		global $c,$cf,$root,$module,$lang,$user,$post;
		$liste_user_id = array();
		require_once($root.'class/class_mail.php');
		$email = new mail();
		
		$url_billet = 'http://'.$cf->config['adresse_site'].$cf->config['path'].'index.php?module='.$module.'&mode=billet&id_billet='.$id_billet.'#commentaires';
		
		$email->Subject = $lang['L_MAIL_SUJET'];
		$email->message_explain = sprintf($lang['L_MAIL_BODY_HTML'],$url_billet,$url_billet);
		$email->titre_message = $lang['L_MAIL_SUJET'];
		$email->formate_html(html_entity_decode(stripslashes($post->bbcode2html($message))));

		// Parcours des commentaires à la recherche des adresses mails
		$sql = 'SELECT DISTINCT c.id_com,c.user_id, c.email AS com_email, c.pseudo AS com_pseudo, c.prevenu, u.email, u.pseudo
			FROM '.TABLE_BLOG_COMS.' AS c LEFT JOIN '.TABLE_USERS.' AS u
			ON 	(c.user_id=u.user_id) 
			WHERE id_billet='.$id_billet.' AND u.user_id!='.$except_uid.' AND prevenu=0';
		if (!$resultat = $c->sql_query($sql)) message_die(E_ERROR,504,__FILE__,__LINE__,$sql); 
		$i=0;
		while ($row = $c->sql_fetchrow($resultat))
		{
			if ($user['user_id']==1 && $row['com_pseudo']!=null && $row['com_email']!=null)
			{
				$email->AddAddress( $row['com_email'],$row['com_pseudo']);
				$i++;
			}elseif($user['user_id']>1){
				$email->AddAddress( $row['email'],$row['pseudo']);
				$liste_user_id[] = $row['user_id'];
				$i++;
			}
		}
		if ($i>0){
 			if(!$email->Send()){
				message_die(E_WARNING,35,__FILE__,__LINE__);
			} 
			// Mise a jour du champs "Prevenu" a true pour eviter qu'ils se fassent spammer a chaque nouveau mail
			$liste_user_id = implode(',',$liste_user_id);
			if ($liste_user_id=='')$liste_user_id='""';
			$sql = 'UPDATE '.TABLE_BLOG_COMS.' SET prevenu=true 
					WHERE id_billet='.$id_billet.' AND user_id IN ('.$liste_user_id.')';
			$c->sql_query($sql);
		}
	}
	
	function Get_Coms()
	{
		global $c;
		$sql = 'SELECT c.*, b.* 
				FROM '.TABLE_BLOG_COMS.' AS c 
				LEFT JOIN '.TABLE_BLOG_BILLETS.' as b
				ON (c.id_billet=b.id_billet)
				WHERE c.id_com='.$this->id_com;
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,524,__FILE__,__LINE__,$sql);
		$this->commentaire = $c->sql_fetchrow($resultat);
	}
	
	// 
	// Incremente le compteur de messages de l'utilisateur
	function update_nbre_messages($user_id,$sens='+'){
		global $c;
		$sql = 'UPDATE '.TABLE_USERS.' AS u
				SET msg=msg'.$sens.'1 
				WHERE user_id='.$user_id;
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,524,__FILE__,__LINE__,$sql);
	}
}


?>