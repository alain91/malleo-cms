<?php
/*
|------------------------------------------------------------------------------------------------------------
| Software: Malleo ( CMS ) - Module Annonces
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

class Annonces
{
	var $id;
	var $id_cat;
	var $billet;
	var $auteur;
	var $date_add;
	var $date_upd;

	private function __construct()
	{
        // private pour obliger utilisation de instance
	}

	/**
	 * Constructeur du singleton
	 *
	 */
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

	/**
	 * Nettoyage centralis�
	 *
	 * @params $_POST ou $_GET
	 * @return $this->sortie tableau associatif des donn�es saisies.
	 */
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

	/**
	 * Calcule nombre enregistrements
	 *
	 */
	function compter()
	{
		global $c;

		$sql = 'SELECT COUNT(1) AS NB FROM '.TABLE_ANNONCES;
		$resultat = $c->sql_query($sql) OR message_die(E_ERROR,702,__FILE__,__LINE__,$sql);
		$row = $c->sql_fetchrow($resultat);
		return (int)$row['NB'];
	}

	/**
	 * Inserer un enregistrement
	 *
	 */
	function inserer()
	{
		global $c,$user;

		$sql = 'INSERT INTO '.TABLE_ANNONCES.' (id_creator, contents, date_created)
				VALUES 	(
				'.		intval($user['user_id']).',
				\''.	Helper::sql_escape($this->billet).'\',
				'.		time().')';
		$resultat = $c->sql_query($sql) OR message_die(E_ERROR,702,__FILE__,__LINE__,$sql);
		$this->id = $c->sql_nextid($resultat);
		return $this;
	}

	/**
	 * Modifier un enregistrement
	 *
	 */
	function modifier()
	{
		global $c,$module,$root;

		if(empty($this->id))
			return false;

		$sql = 'UPDATE '.TABLE_ANNONCES.' SET
					contents=\''.	Helper::sql_escape($this->billet).'\',
					author=\''.		Helper::sql_escape($this->auteur).'\',
					date_upd='.	time() .'
				WHERE id='.intval($this->id).'
				LIMIT 1';

		$resultat = $c->sql_query($sql) OR message_die(E_ERROR,702,__FILE__,__LINE__,$sql);
		return $resultat;
	}

	/**
	 * Supprimer un enregistrement
	 *
	 */
	function supprimer()
	{
		global $c;

		if(empty($this->id))
			return false;

		$sql = 'DELETE FROM '.TABLE_ANNONCES.'
				WHERE id='.intval($this->id).'
				LIMIT 1';

		$resultat = $c->sql_query($sql) OR message_die(E_ERROR,702,__FILE__,__LINE__,$sql);
		return ($resultat != false);
	}

	/**
	 * Recupere les infos d'un enregistrement
	 *
	 */
	function recuperer()
	{
		global $c;

		if(empty($this->id))
			return false;

		$sql = 'SELECT c.*
				FROM '.TABLE_ANNONCES.' as c
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

	/**
	 * Recupere les infos de tous les enregistrements
	 *
	 */
	function recuperer_tous($sort=null, $mode=null, $filter=null)
	{
		global $c;

		$sql = 'SELECT a.*
				FROM '.TABLE_ANNONCES.' as a';

		if (!empty($filter))
		{
			$sql .= ' WHERE ('.$filter.')';
		}
		if (!empty($sort) AND !empty($mode))
		{
			$sql .= ' ORDER BY '.$sort.' '.$mode;
		}

		$resultat = $c->sql_query($sql) OR message_die(E_ERROR,702,__FILE__,__LINE__,$sql);
		$rows = array();
		while($row = $c->sql_fetchrow($resultat))
		{
			$rows[] = $row;
		}
		return $rows;
	}

	/**
	 * Renvoie l'attribut selected si valide
	 *
	 * @param name
	 * @param value
	 * @return attribut selected ou vide
	 */
	function selected($name, $value)
	{
		return ($name == $value) ? 'selected="selected"' : '';
	}

}

?>