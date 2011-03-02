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
class Wiki
{
	var $t=null;
	var $id_version=0;
	var $id_tag=0;
	var $titre='';
	var $texte='';
	var $infos='';
	var $verrouiller='false';
	var $terminer='false';
	
	//
	// Nettoie les valeurs passees en parametre
	function clean($vars)
	{
		foreach($vars as $key=>$val)
		{
			switch($key)
			{
				case 't':	$val = supprimer_accents($val);
							$val = eregi_replace('[^a-z0-9]','_',$val);
							$val = ereg_replace('[_]{2,}','_',$val);				
							$this->t = $val;
							break;
				case 'id_version':	$this->id_version = intval($val); break;				
				case 'titre':		$this->titre = protection_chaine($val); break;				
				case 'texte':		$this->texte = protection_chaine($val); break;
				case 'verrouiller':	$this->verrouiller = ($val==1)?'true':'false'; break;
				case 'terminer':	$this->terminer = ($val==1)?'true':'false'; break;
			}
		}
	}
	
	//
	// Verifie l'existance d'une page dans le wiki
	// Renvoie vrai ou faux et l'id de la page si vrai
	function page_existe(){
		global $c,$module;
		$sql = 'SELECT id_tag FROM '.TABLE_WIKI.' WHERE module="'.$module.'" AND tag="'.$this->t.'" LIMIT 1';
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,702,__FILE__,__LINE__,$sql);
		if ($c->sql_numrows($resultat)>0){
			$row = $c->sql_fetchrow($resultat);
			$this->id_tag = $row['id_tag'];
			return true;
		}else{
			return false;
		}
	}

	
	//
	// AFFICHE les champs de saisie/edition
	function saisie_texte(){
		global $tpl,$lang;
		$mode = 'saisie_enregistrer';
		if (!is_array($this->infos)){
			$this->infos_page();
		}
		$tpl->assign_vars(array(
			'TITRE'				=> $this->infos['titre'],
			'TEXTE'				=> $this->infos['texte'],
			'TAG'				=> $this->t,
			'L_SAISIE_PAGE_WIKI'=> sprintf($lang['L_SAISIE_PAGE_WIKI'],$this->t),
			'MODE'				=> $mode
		));
	}
	
	//
	// Enregistre en base la page
	function enregistrer_page(){
		global $c,$module,$user,$root;
		
		// La page existe déjà ?
		if ($this->id_tag==0){
			$sql = 'INSERT INTO '.TABLE_WIKI.' (module, tag) 
					VALUES 
					(\''.$module.'\',\''.$this->t.'\')';
			if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,702,__FILE__,__LINE__,$sql);
			$this->id_tag = $c->sql_nextid($resultat);
		}
		$sql = 'INSERT INTO '.TABLE_WIKI_TEXTE.' (id_tag, titre, texte, user_id, date, taille) 
				VALUES 
				('.$this->id_tag.',
				\''.str_replace("\'","''",$this->titre).'\',
				\''.str_replace("\'","''",$this->texte).'\',
				'.$user['user_id'].',
				'.time().',
				'.(strlen($this->texte)*8 + strlen($this->titre)*8).')';
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,702,__FILE__,__LINE__,$sql);
		$this->id_version = $c->sql_nextid($resultat);
		$sql = 'UPDATE '.TABLE_WIKI.' SET
					id_version_actuelle='.$this->id_version.'
				WHERE id_tag='.$this->id_tag;
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,702,__FILE__,__LINE__,$sql);
		// Enregistrement en local des images
		require_once($root.'class/class_image.php');
		$image = new image();
		$image->copie_locale_images(str_replace("\'","''",$this->texte),'UPDATE '.TABLE_WIKI_TEXTE.' SET texte=\'%s\' WHERE id_version='.$this->id_version);
	}
	
	//
	// Met a jour 
	function update_etat_wiki(){
		global $c,$module,$droits;
		$sql = '';
		if ($droits->check($module,0,'proteger')){
			$sql .= 'protege='.$this->verrouiller;
		}
		if ($droits->check($module,0,'moderer')){
			if ($sql != '') $sql .= ', ';
			$sql .= 'termine='.$this->terminer;
		}
		if ($sql != ''){
			$sql = 'UPDATE '.TABLE_WIKI.' SET '.$sql.' WHERE tag="'.$this->t.'"';
			if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,702,__FILE__,__LINE__,$sql);
		}
	}
	
	//
	// Informations sur le tag
	function infos_page($id_version=null){
		global $c,$module;
		$sql = 'SELECT w.id_tag, id_version_actuelle, id_version, tag, 
						titre, texte, nbre_lectures, protege, termine 
				FROM '.TABLE_WIKI_TEXTE.' as t
				LEFT JOIN '.TABLE_WIKI.' as w ';
		if ($id_version!=null){
			$sql .= 'ON (t.id_tag=w.id_tag) WHERE t.id_version='.$id_version ;
		}else{
			$sql .= 'ON (t.id_version=w.id_version_actuelle)  WHERE tag="'.$this->t.'"';
		}
		$sql .= ' AND module="'.$module.'" ORDER BY date DESC LIMIT 1';
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,702,__FILE__,__LINE__,$sql);
 		$row = $c->sql_fetchrow($resultat);
		$this->infos = $row;
	}
	
	//
	// AFFICHE la page demandee
	function afficher_page(){
		global $c,$tpl,$post;
		
		// Meta Description
		if (empty($tpl->meta_description)) $tpl->meta_description = $post->bbcode2html($this->infos['texte'].' '.$this->infos['titre']);
		
		$tpl->assign_vars(array(
			'TEXTE' => $post->bbcode2html($this->infos['texte']),
			'TITRE' => $this->infos['titre']
		));
	}
	
	//
	// Retourne le nombre de contributions personnelles totales
	function nombre_contributions(){
		global $c,$module,$user;
		$sql = 'SELECT id_version 
				FROM '.TABLE_WIKI_TEXTE.' as t
				LEFT JOIN '.TABLE_WIKI.' as w 
				ON (t.id_tag=w.id_tag)
				WHERE module="'.$module.'" AND user_id='.$user['user_id'];
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,702,__FILE__,__LINE__,$sql);
		return $c->sql_numrows($resultat);
	}
	
	//
	// Supprime la version demandee
	function supprimer_version(){
		global $c,$module;
		// infos sur le tag
		$this->infos_page($this->id_version);
		
		// suppression version
		$sql = 'DELETE FROM '.TABLE_WIKI_TEXTE.' WHERE id_version='.$this->id_version;
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,702,__FILE__,__LINE__,$sql);		
		
		// si Version a supprimer = version courante
		if ($this->id_version == $this->infos['id_version_actuelle']){
			// La derniere version repasse en version courante
			$sql = 'SELECT id_version FROM '.TABLE_WIKI_TEXTE.' 
						WHERE id_tag='.$this->infos['id_tag'].' ORDER BY date DESC LIMIT 1';
			if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,702,__FILE__,__LINE__,$sql);
			if ($c->sql_numrows($resultat) > 0){
				// update version courante
				$sql = 'UPDATE '.TABLE_WIKI.' SET id_version_actuelle = 
						(	SELECT id_version 
							FROM '.TABLE_WIKI_TEXTE.' 
							WHERE id_tag='.$this->infos['id_tag'].' ORDER BY date DESC LIMIT 1 )
						WHERE id_tag='.$this->infos['id_tag'];
				if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,702,__FILE__,__LINE__,$sql);
			}else{
				// On efface ce tag
				$sql = 'DELETE FROM '.TABLE_WIKI.' WHERE id_tag='.$this->infos['id_tag'];
				if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,702,__FILE__,__LINE__,$sql);
			}
		}
	}
	
	//
	// Restaure la version indiquee comme version actuelle
	function restaurer_version(){
		global $c,$module;
		// infos sur le tag
		$this->infos_page($this->id_version);
		$sql = 'UPDATE '.TABLE_WIKI.' SET id_version_actuelle = '.$this->id_version.'
				WHERE id_tag='.$this->infos['id_tag'];
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,702,__FILE__,__LINE__,$sql);
	}
	
	//
	// AFFICHE les pages auquel le visiteur a contribue
	function afficher_contributions(){
		global $c,$module,$user,$tpl,$lang,$start,$cf;
		$sql = 'SELECT id_version, tag, titre, date, nbre_lectures, protege, termine 
				FROM '.TABLE_WIKI_TEXTE.' as t
				LEFT JOIN '.TABLE_WIKI.' as w
				ON (t.id_tag=w.id_tag)
				WHERE module="'.$module.'" AND user_id='.$user['user_id'].' 
				ORDER BY date DESC
				LIMIT '.$start.','.$cf->config['wiki_ligne_par_page'];
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,702,__FILE__,__LINE__,$sql);
		if ($c->sql_numrows($resultat) == 0 ){
			$tpl->assign_block_vars('aucune_page',array());
		}else{
	 		while($row = $c->sql_fetchrow($resultat)){
				$tpl->assign_block_vars('liste_pages',array(
					'DATE'				=> formate_date($row['date'],'d m Y H i','FORMAT_DATE',$user['fuseau']),
					'TITRE'				=> $row['titre'],
					'CPT'				=> $row['nbre_lectures'],
					'URL_PAGE'			=> formate_url('t='.$row['tag'],true),
					'URL_PAGE_VERSION'	=> formate_url('mode=version&id_version='.$row['id_version'],true),
					'VERSION'			=> $row['id_version']
				));
			}
		}
		$tpl->assign_vars(array(
			'PAGINATION'	=> create_pagination($start, 'mode=mes_contributions&start=', $this->nombre_contributions() , $cf->config['wiki_ligne_par_page'],$lang['L_CONTRIBUTION'])
		));
	}
	
	
	//
	// Retourne le nombre de versions dans l'historique
	function nombre_versions_historiques(){
		global $c,$module,$user;
		$sql = 'SELECT id_version 
				FROM '.TABLE_WIKI_TEXTE.' as t
				LEFT JOIN '.TABLE_WIKI.' as w
				ON (t.id_tag=w.id_tag)
				WHERE module="'.$module.'" AND tag="'.$this->t.'"';
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,702,__FILE__,__LINE__,$sql);
		return $c->sql_numrows($resultat);
	}
	//
	// AFFICHE l'historique du document en cours
	function afficher_historique(){
		global $c,$module,$user,$tpl,$lang,$start,$cf,$img,$droits,$session;
		
		// Creation du jeton de securite
		if (!session_id()) @session_start();
		$jeton = md5(uniqid(rand(), TRUE));
		$_SESSION['jeton'] = $jeton;
		$_SESSION['jeton_timestamp'] = $session->time;


		$sql = 'SELECT id_version, titre, date, nbre_lectures, protege, termine, t.user_id, u.pseudo 
				FROM '.TABLE_WIKI_TEXTE.' as t
				LEFT JOIN '.TABLE_WIKI.' as w
				ON (t.id_tag=w.id_tag)
				LEFT JOIN '.TABLE_USERS.' as u
				ON (t.user_id=u.user_id) 
				WHERE module="'.$module.'" AND tag="'.$this->t.'" 
				ORDER BY date DESC
				LIMIT '.$start.','.$cf->config['wiki_ligne_par_page'];
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,702,__FILE__,__LINE__,$sql);
		$cpt = $s_verrouiller_oui = $s_verrouiller_non = $s_terminer_oui = $s_terminer_non = '';
		if ($c->sql_numrows($resultat) == 0 ){
			$tpl->assign_block_vars('aucune_page',array());
		}else{
			while($row = $c->sql_fetchrow($resultat)){
				$tpl->assign_block_vars('liste_pages',array(
					'DATE'				=> formate_date($row['date'],'d m Y H i','FORMAT_DATE',$user['fuseau']),
					'TITRE'				=> $row['titre'],
					'AUTEUR'			=> formate_pseudo($row['user_id'],$row['pseudo']),
					'URL_PAGE_VERSION'	=> formate_url('mode=version&id_version='.$row['id_version'],true),
					'S_RESTAURER'		=> formate_url('mode=restaurer_version&id_version='.$row['id_version'].'&jeton='.$jeton,true),
					'S_SUPPRIMER'		=> formate_url('mode=supprimer_version&id_version='.$row['id_version'].'&jeton='.$jeton,true),
					'VERSION'			=> $row['id_version']
				));
				// Moderation de version
				if ($droits->check($module,0,'moderer')){
					$tpl->assign_block_vars('liste_pages.moderer',array());
				}else{
					$tpl->assign_block_vars('liste_pages.nomoderer',array());
				}
				
				// Droits sur la page
				if ($s_verrouiller_oui=='' && $s_verrouiller_non==''){
					$s_verrouiller_oui = ($row['protege']==true)?' checked="checked"':'';
					$s_verrouiller_non = ($row['protege']==false)?' checked="checked"':'';
					$s_terminer_oui = ($row['termine']==true)?' checked="checked"':'';
					$s_terminer_non = ($row['termine']==false)?' checked="checked"':'';
					$cpt = $row['nbre_lectures'];
				}
			}
		}
		$tpl->assign_vars(array(
			'L_PAGE_CPT_VUE'	=> sprintf($lang['L_PAGE_CPT_VUE'],$cpt),
			'TAG' 				=> $this->t,
			'JETON' 			=> $jeton,
			'I_SUPPRIMER' 		=> $img['effacer'],
			'I_RESTAURER' 		=> $img['valide'],
			'S_VERROUILLER_OUI'	=> $s_verrouiller_oui,
			'S_VERROUILLER_NON'	=> $s_verrouiller_non,			
			'S_TERMINER_OUI'	=> $s_terminer_oui,
			'S_TERMINER_NON'	=> $s_terminer_non,
			'PAGINATION'		=> create_pagination($start, 'mode=historique&start=', $this->nombre_versions_historiques() , $cf->config['wiki_ligne_par_page'],$lang['L_VERSION'])
		));
	}
	//
	// Incremente le compteur de visualisations
	function incremente(){
		global $c;
		$sql = 'UPDATE '.TABLE_WIKI.' SET nbre_lectures=nbre_lectures+1 WHERE tag="'.$this->t.'"';
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,702,__FILE__,__LINE__,$sql);
	}
	
	//
	// Avertissement
	function avertissement($msg){
		global $tpl,$root;
		$tpl->set_filenames(array('avertissement' => $root.'html/error_404.html'));
		$tpl->assign_vars(array('MSG'=>$msg));
		$tpl->assign_var_from_handle('AVERTISSEMENT','avertissement');
	}
}
?>