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
class Pages
{
	var $module;
	var $titre_long;
	var $titre_court;
	var $texte;
	var $id_page;
	
	function clean($vars)
	{
		foreach($vars as $key=>$val)
		{
			switch($key)
			{
				// Entier
				case 'id_page':	$this->$key = intval($val);
								break;
				// Chaine de caracteres
				case 'titre_long':											
				case 'titre_court':
				case 'texte':	$this->$key = protection_chaine($val); 
								break;
			}
		}
	}
	
	//
	// Incremente le compteur de visualisations
	function incremente($id_page){
		global $c;
		$sql = 'UPDATE '.TABLE_PAGES.' SET cpt=cpt+1 WHERE id_page='.$id_page;
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,702,__FILE__,__LINE__,$sql);
	}

	//
	// AFFICHE la liste des pages
	function lister_pages($start=0){
		global $c,$tpl,$module,$lang,$img,$root,$cf,$user,$droits,$session;
		
		// Creation du jeton de securite
		if (!session_id()) @session_start();
		$jeton = md5(uniqid(rand(), TRUE));
		$_SESSION['jeton'] = $jeton;
		$_SESSION['jeton_timestamp'] = $session->time;
			
		$sql = 'SELECT id_page, u.user_id, u.pseudo, date, titre_court, titre_long, texte, cpt
				FROM '.TABLE_PAGES.' as p LEFT JOIN '.TABLE_USERS.' as u
				ON (p.user_id=u.user_id)
				WHERE module="'.$module.'"
				ORDER BY ordre ASC
				LIMIT '.$start.','.$cf->config['pages_nbre_pages_listing'];
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,702,__FILE__,__LINE__,$sql);
		$nbre_pages = $c->sql_numrows($resultat);
		$t=1;
		if ($nbre_pages == 0){
			$tpl->assign_block_vars('aucune_page', array());
		}else{
			while($row = $c->sql_fetchrow($resultat)){
				$tpl->assign_block_vars('liste', array(
					'URL_PAGE'		=> formate_url('p='.$row['id_page'],true),
					'TITRE_LONG'	=> $row['titre_long'],
					'CPT'			=> $row['cpt'],
					'AUTEUR'		=> formate_pseudo($row['user_id'],$row['pseudo']),
					'DATE'			=> formate_date($row['date'],'d m Y H i','FORMAT_DATE',$user['fuseau']),
					'S_EDITER'		=> formate_url('mode=editer&id_page='.$row['id_page'],true),
					'S_SUPPRIMER'	=> formate_url('mode=supprimer&id_page='.$row['id_page'].'&jeton='.$jeton,true),
					'S_UP'			=> formate_url('mode=move&sens=up&id_page='.$row['id_page'],true),
					'S_DOWN'		=> formate_url('mode=move&sens=down&id_page='.$row['id_page'],true),
				)); 
				
				// Autorisations
				if ($droits->check($module,0,'editer')) $tpl->assign_block_vars('liste.editer', array()); 
				if ($droits->check($module,0,'supprimer')) $tpl->assign_block_vars('liste.supprimer', array());
				
				// Monter / descendre
				if ($nbre_pages>1 && $t>1) $tpl->assign_block_vars('liste.monter',array());
				if ($nbre_pages>1 && $t<$nbre_pages) $tpl->assign_block_vars('liste.descendre',array());
				$t++;
			}
		}
		// Autorisations
		if ($droits->check($module,0,'ecrire')) $tpl->assign_block_vars('ecrire', array()); 
		
		// MENU lateral
		$this->afficher_menu($module);
		
		// PAGINATION (preparation)
		include($root.'fonctions/fct_affichage.php');
		
		$tpl->assign_vars(array(
			'I_DOWN'		=> $img['down'],
			'I_UP'			=> $img['up'],
			'I_EDITER'		=> $img['editer'],
			'I_NOUVEAU'		=> $img['nouveau'],
			'I_SUPPRIMER'	=> $img['effacer'],
			'S_NOUVEAU'		=> formate_url('mode=nouveau',true),
			'PAGINATION'	=> create_pagination($start, 'start=', $this->nbre_pages_web($module), $cf->config['pages_nbre_pages_listing'],$lang['L_PAGES_WEB'])
		));
	}
	
	//
	// Renvoie le nombre de pages web dans ce module
	function nbre_pages_web($module){
		global $c;
		$sql = 'SELECT id_page	FROM '.TABLE_PAGES.' WHERE module="'.$module.'"';
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,702,__FILE__,__LINE__,$sql);
		return $c->sql_numrows($resultat);
	}

	//
	// AFFICHE la page demandee
	function afficher_page($id_page){
		global $c,$tpl,$root,$module,$post,$session,$droits;
		$sql = 'SELECT titre_court, titre_long, texte
				FROM '.TABLE_PAGES.' as p 
				WHERE module="'.$module.'" AND id_page = '.$id_page;
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,702,__FILE__,__LINE__,$sql);
 		$row = $c->sql_fetchrow($resultat);
		$tpl->assign_vars(array(
			'TEXTE' => $post->bbcode2html($row['texte'])
		));
		$tpl->titre_navigateur = $tpl->titre_page = $row['titre_long'];
		
		// Meta Description
		if (empty($tpl->meta_description)) $tpl->meta_description = $post->bbcode2html($row['texte']);
		
		// Navlinks
		$session->make_navlinks(array(
			$row['titre_long']	=> formate_url('p='.$id_page,true),
		));
		
		// Menu lateral
		if ($row['titre_court']!=null){
			$tpl->assign_block_vars('menu_pages', array());
			$this->afficher_menu($module);
		}
		
		// Incrementation du compteur de visualisations
		$this->incremente($id_page);
	}
	
	//
	// Affiche le menu lateral du module
	function afficher_menu($module){
		global $tpl,$root,$c,$lang;
		$tpl->set_filenames(array(
			'menu_page' => $root.'plugins/modules/pages/html/menu.html'
		));
		$sql = 'SELECT titre_court, id_page 
				FROM '.TABLE_PAGES.' as p 
				WHERE module="'.$module.'" AND titre_court!=""
				ORDER BY ordre ASC';
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,702,__FILE__,__LINE__,$sql);
		if ($c->sql_numrows($resultat) ==0){
			$tpl->assign_block_vars('nomenu', array());
		}else{
			while($row = $c->sql_fetchrow($resultat)){
				$tpl->assign_block_vars('menu', array());
				if ($this->id_page==$row['id_page']){
					$tpl->assign_block_vars('menu.nolien', array(
						'TITRE_COURT'	=> $row['titre_court']
					));
				}else{
					$tpl->assign_block_vars('menu.lien', array(
						'URL'			=> formate_url('p='.$row['id_page'],true),
						'TITRE_COURT'	=> $row['titre_court']
					));
				}
			}
		}
		$tpl->assign_var_from_handle('MENU','menu_page');	
	}
	
	//
	// AFFICHE les champs de saisie/edition
	function saisie_texte($id_page=null){
		global $tpl,$lang;
		// Init var
		$this->titre_long = $this->titre_court = $this->texte = $this->hidden = '';
		$mode = 'nouveau_enregistrer';
		if ($id_page!=null){
			$this->infos_page($id_page);
			$mode = 'editer_enregistrer';	
			$this->hidden = '<input type="hidden" name="id_page" value="'.$id_page.'" />';
		}
		$tpl->assign_vars(array(
			'TITRE_LONG'		=> $this->titre_long,
			'TITRE_COURT'		=> $this->titre_court,
			'TEXTE'				=> $this->texte,
			'HIDDEN'			=> $this->hidden,
			'MODE'				=> $mode
		));
	}
	
	//
	// Enregistre en base la page
	function enregistrer_page(){
		global $c,$module,$user,$root;
		$sql = 'INSERT INTO '.TABLE_PAGES.' (module, user_id, date, titre_court, titre_long, texte) 
				VALUES 	(
				\''.	$module.'\',
				'.		$user['user_id'].',
				'.		time().',
				\''.	$this->titre_court.'\',
				\''.	$this->titre_long.'\',
				\''.	$this->texte.'\')';
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,702,__FILE__,__LINE__,$sql);
		$this->id_page = $c->sql_nextid($resultat);
		// Enregistrement en local des images
		require_once($root.'class/class_image.php');
		$image = new image();
		$image->copie_locale_images($this->texte,'UPDATE '.TABLE_PAGES.' SET texte=\'%s\' WHERE id_page='.$this->id_page);
	}
	
	//
	// Enregistrement des modifications apportées à la page
	function enregistrer_modification_page(){
		global $c,$module,$root;
		$sql = 'UPDATE '.TABLE_PAGES.' SET 
					titre_court=\''.	$this->titre_court.'\',
					titre_long=\''.		$this->titre_long.'\',
					texte=\''.			$this->texte.'\' 
				WHERE id_page='.		$this->id_page.'
				AND module=\''.			$module.'\'
				LIMIT 1';
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,702,__FILE__,__LINE__,$sql);
		// Enregistrement en local des images
		require_once($root.'class/class_image.php');
		$image = new image();
		$image->copie_locale_images(str_replace("\'","''",$this->texte),'UPDATE '.TABLE_PAGES.' SET texte=\'%s\' WHERE id_page='.$this->id_page);
	}
	
	//
	// Supprime la page demandée
	function supprimer_page(){
		global $c,$module;
		$sql = 'DELETE FROM '.TABLE_PAGES.' WHERE module=\''.$module.'\' AND id_page='.$this->id_page;
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,702,__FILE__,__LINE__,$sql);
		return true;
	}
	
	//
	// Renvoie les informations sur la page si elle existe
	function infos_page($id_page){
		global $c,$module;
		$sql = 'SELECT titre_court, titre_long, texte
				FROM '.TABLE_PAGES.' as p 
				WHERE module="'.$module.'" AND id_page = '.$id_page;
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,702,__FILE__,__LINE__,$sql);
		if ($c->sql_numrows($resultat) > 0){
			$row = $c->sql_fetchrow($resultat);
			$this->titre_long = $row['titre_long'];
			$this->titre_court = $row['titre_court'];
			$this->texte = $row['texte'];
		}
	}
	
}


?>
