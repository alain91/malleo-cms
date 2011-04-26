<?php
/*
|------------------------------------------------------------------------------------------------------------
| Software: Malleo ( CMS ) - Module Citations
| Contact:  alain91 - http://www.malleo-cms.com
| Support: http://www.malleo-cms.com?module=forum
|------------------------------------------------------------------------------------------------------------
|  Author: Alain GANDON
|  Copyright (c) 2011, Alain GANDON All Rights Reserved
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

class Action
{
	function __construct()
	{
		$this->init();
	}
	
	function Action()
	{
		self::__construct();
	}
	
	function init()
	{}
	
	function run()
	{}
}
class Controller
{
	function __construct()
	{
		$this->init();
	}
	
	function Controller()
	{
		self::__construct();
	}
	
	function init()
	{}
	
	function dispatch()
	{
		$actions = $this->getActions();
		
		$code = 'action';
		$action = !empty($_REQUEST[$code]) ? $_REQUEST[$code] : 'index';
		$filename = !empty($actions[$action]) ? $actions[$action] : null;

		if (!empty($filename))
		{
			require_once($filename.'.php');
			$class = basename($filename);
			$action = new $class;
			$action->run();
			return;
		}
		header('index.php');
	}
	
	function getActions()
	{}

}
class Citations
{
	var $texte;
	var $auteur;
	var $date;
	
	function clean()
	{
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
		
		$limit = empty($cf->config['pages_nbre_pages_listing']) ? 20 : $cf->config['pages_nbre_pages_listing'];
			
		$sql = 'SELECT c.id_citation,c.texte, c.auteur, c.date, u.user_id, u.pseudo
				FROM '.TABLE_CITATIONS.' as c
				LEFT JOIN '.TABLE_USERS.' as u ON (c.user_id=u.user_id)
				ORDER BY c.date DESC
				LIMIT '.$start.','.$limit;
		$resultat = $c->sql_query($sql) OR message_die(E_ERROR,702,__FILE__,__LINE__,$sql);
		$nbre_pages = $c->sql_numrows($resultat);
		if ($nbre_pages == 0){
			$tpl->assign_block_vars('aucune_page', array());
		}else{
			while($row = $c->sql_fetchrow($resultat)){
				$tpl->assign_block_vars('liste', array(
					'URL_CITATION'	=> formate_url('id='.$row['id_citation'],true),
					'TEXTE'			=> $row['texte'],
					'AUTEUR'		=> formate_pseudo($row['user_id'],$row['pseudo']),
					'DATE'			=> formate_date($row['date'],'d m Y H i','FORMAT_DATE',$user['fuseau']),
					'S_EDITER'		=> formate_url('mode=editer&id='.$row['id_citation'].'&jeton='.$jeton,true),
					'S_SUPPRIMER'	=> formate_url('mode=supprimer&id='.$row['id_citation'].'&jeton='.$jeton,true),
				)); 
				
				// Autorisations
				if ($droits->check($module,0,'editer')) $tpl->assign_block_vars('liste.editer', array()); 
				if ($droits->check($module,0,'supprimer')) $tpl->assign_block_vars('liste.supprimer', array());
			}
		}
		// Autorisations
		if ($droits->check($module,0,'ecrire')) $tpl->assign_block_vars('ecrire', array()); 
		
		// MENU lateral
		$this->afficher_menu($module);
		
		// PAGINATION (preparation)
		include($root.'fonctions/fct_affichage.php');
		
		$tpl->assign_vars(array(
			'I_EDITER'		=> $img['editer'],
			'I_NOUVEAU'		=> $img['nouveau'],
			'I_SUPPRIMER'	=> $img['effacer'],
			'S_NOUVEAU'		=> formate_url('mode=nouveau',true),
			'PAGINATION'	=> create_pagination($start, 'start=', $this->get_nbre_citations(), $limit, $lang['L_PAGES_WEB'])
		));
	}
	
	//
	// Renvoie le nombre de citations
	function get_nbre()
	{
		global $c;
		
		$sql = 'SELECT COUNT(1) AS NB FROM '.TABLE_CITATIONS;
		$resultat = $c->sql_query($sql) OR message_die(E_ERROR,702,__FILE__,__LINE__,$sql);
		$row = $c->sql_fetchrow($resultat);
		return (int)$row['NB'];
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
	// Insere une nouvelle citation
	function inserer()
	{
		global $c,$module,$user,$root;
		
		$sql = 'INSERT INTO '.TABLE_CITATIONS.' (module, user_id, date, titre_court, titre_long, texte) 
				VALUES 	(
				\''.	$module.'\',
				'.		$user['user_id'].',
				'.		time().',
				\''.	$this->titre_court.'\',
				\''.	$this->titre_long.'\',
				\''.	$this->texte.'\')';
		$resultat = $c->sql_query($sql) OR message_die(E_ERROR,702,__FILE__,__LINE__,$sql);
		$this->id_page = $c->sql_nextid($resultat);
		// Enregistrement en local des images
		require_once($root.'class/class_image.php');
		$image = new image();
		$image->copie_locale_images($this->texte,'UPDATE '.TABLE_PAGES.' SET texte=\'%s\' WHERE id_page='.$this->id_page);
	}
	
	//
	// Modifie une citation
	function modifier($id)
	{
		global $c,$module,$root;
		
		$sql = 'UPDATE '.TABLE_CITATIONS.' SET 
					contents=\''.	$c->sql_escape($this->texte).'\',
					author=\''.		$c->sql_escape($this->auteur).'\'
				WHERE id='.$id.'
				LIMIT 1';
		var_dump($sql); exit;
		$resultat = $c->sql_query($sql) OR message_die(E_ERROR,702,__FILE__,__LINE__,$sql);
		var_dump($resultat); exit;
	}
	
	//
	// Supprime la citation demandée
	function supprimer($id)
	{
		global $c;
		
		$sql = 'DELETE FROM '.TABLE_CITATIONS.' WHERE id_citation='.$id;
		$resultat = $c->sql_query($sql) OR message_die(E_ERROR,702,__FILE__,__LINE__,$sql);
		var_dump($resultat); exit;
		return true;
	}
	
	//
	// Renvoie les informations
	function infos($id)
	{
		global $c;
		
		$sql = 'SELECT c.*
				FROM '.TABLE_CITATIONS.' as c
				WHERE id = '.$id.'
				LIMIT 1';
		$resultat = $c->sql_query($sql) OR message_die(E_ERROR,702,__FILE__,__LINE__,$sql);
		$row = $c->sql_fetchrow($resultat);
		if(!empty($row))
		{
			$this->texte 	= $row['contents'];
			$this->auteur 	= $row['author'];
			$this->date 	= $row['timestamp'];
		}
		return $this;
	}
	
}

?>