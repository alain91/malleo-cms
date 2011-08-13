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

defined('PROTECT') OR die("Tentative de Hacking");

class Citations
{
	var $id;
	var $id_cat;
	var $billet;
	var $auteur;
	var $date_add;
	var $date_upd;
	
	private function __construct()
	{
	}
	
	static function instance()
	{
		static $instance = null;
		if (empty($instance))
		{
			$x = get_class();
			$instance = new $x;
		}
		return $instance;
	}
	
	//
	// Nettoyage centralisé
	// entree : $vars = $_POST ou $_GET
	// sortie : $this->sortie tableau associatif des données saisies.
	
	function nettoyer($vars)
	{
		foreach($vars as $key=>$val)
		{
			switch($key)
			{
				// Entier
				case 'id':
				case 'id_cat':
					$this->$key = intval($val);break;
				// Chaine de caracteres
				case 'billet':
				case 'auteur':
					$this->$key = $val;break;
			}
		}	
	}	
	
	//
	// Renvoie le nombre
	function compter()
	{
		global $c;
		
		$sql = 'SELECT COUNT(1) AS NB FROM '.TABLE_CITATIONS;
		$resultat = $c->sql_query($sql) OR message_die(E_ERROR,702,__FILE__,__LINE__,$sql);
		$row = $c->sql_fetchrow($resultat);
		return (int)$row['NB'];
	}
	
	//
	// Insere une nouvelle citation
	function inserer()
	{
		global $c,$user;
		
		$sql = 'INSERT INTO '.TABLE_CITATIONS.' (user_id, contents, author, date_add) 
				VALUES 	(
				'.		intval($user['user_id']).',
				\''.	Helper::sql_escape($this->billet).'\',
				\''.	Helper::sql_escape($this->auteur).'\',
				'.		time().')';
		$resultat = $c->sql_query($sql) OR message_die(E_ERROR,702,__FILE__,__LINE__,$sql);
		$this->id = $c->sql_nextid($resultat);
		return $this;
	}
	
	//
	// Modifie une citation
	function modifier()
	{
		global $c,$module,$root;
		
		if(empty($this->id))
			return false;			
		$sql = 'UPDATE '.TABLE_CITATIONS.' SET 
					contents=\''.	Helper::sql_escape($this->billet).'\',
					author=\''.		Helper::sql_escape($this->auteur).'\',
					date_upd='.	time() .'
				WHERE id='.intval($this->id).'
				LIMIT 1';
		$resultat = $c->sql_query($sql) OR message_die(E_ERROR,702,__FILE__,__LINE__,$sql);
		return $resultat;
	}
	
	//
	// Supprime la citation demandée
	function supprimer()
	{
		global $c;
		
		if(empty($this->id))
			return false;
		$sql = 'DELETE FROM '.TABLE_CITATIONS.' WHERE id='.intval($this->id);
		$resultat = $c->sql_query($sql) OR message_die(E_ERROR,702,__FILE__,__LINE__,$sql);
		return ($resultat != false);
	}
	
	//
	// Renvoie les informations d'une citation
	function recuperer()
	{
		global $c;

		if(empty($this->id))
			return false;
		$sql = 'SELECT c.*
				FROM '.TABLE_CITATIONS.' as c
				WHERE id = '.intval($this->id).'
				LIMIT 1';
		$resultat = $c->sql_query($sql) OR message_die(E_ERROR,702,__FILE__,__LINE__,$sql);
		$row = $c->sql_fetchrow($resultat);
		if(!empty($row))
		{
			$this->id 		= empty($row['id']) ? null : $row['id'];
			$this->billet 	= empty($row['contents']) ? null : $row['contents'];
			$this->auteur 	= empty($row['author']) ? null : $row['author'];
			$this->date_add = empty($row['date_add']) ? null : $row['date_add'];
			$this->date_upd = empty($row['date_upd']) ? null : $row['date_upd'];
		}
		return $this;
	}
	
	//
	// Renvoie les informations de toutes les citations
	function recuperer_tous()
	{
		global $c;

		$sql = 'SELECT c.*
				FROM '.TABLE_CITATIONS.' as c';
		$resultat = $c->sql_query($sql) OR message_die(E_ERROR,702,__FILE__,__LINE__,$sql);
		$rows = array();
		while($row = $c->sql_fetchrow($resultat))
		{
			$x = new stdClass();
			$x->id 		= empty($row['id']) ? null : $row['id'];
			$x->billet 	= empty($row['contents']) ? null : $row['contents'];
			$x->auteur 	= empty($row['author']) ? null : $row['author'];
			$x->date_add = empty($row['date_add']) ? null : $row['date_add'];
			$x->date_upd = empty($row['date_upd']) ? null : $row['date_upd'];
			$rows[] = $x;
		}
		return $rows;
	}
	
}

?>