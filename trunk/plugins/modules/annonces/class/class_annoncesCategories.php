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

class AnnoncesCategories
{
	var $id_cat;
	var $title_cat;
	var $picture_cat;
	var $order;

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
	 * Nettoyage centralisé
	 *
	 * @params $_POST ou $_GET
	 * @return $this->sortie tableau associatif des données saisies.
	 */
	function nettoyer($vars)
	{
		foreach($vars as $key=>$val)
		{
			switch($key)
			{
				// Entier
				case 'id_cat':
				case 'order':
					$this->$key = intval($val);break;
				// Chaine de caracteres
				case 'title_cat':
				case 'picture_cat':
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

		$sql = 'SELECT COUNT(1) AS NB FROM '.TABLE_ANNONCES_CATS;

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

		$sql = 'INSERT INTO '.TABLE_ANNONCES_CATS.'
			SET title_cat = '.Helper::sql_escape($this->title_cat).',
				picture_cat = '.Helper::sql_escape($this->picture_cat).',
				`order` = '.intval($this->order);

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

		if(empty($this->id_cat))
			return false;

		$sql = 'UPDATE '.TABLE_ANNONCES_CATS.'
			SET title_cat = '.Helper::sql_escape($this->title_cat).',
				picture_cat = '.Helper::sql_escape($this->picture_cat).',
				`order` = '.intval($this->order).'
				WHERE id_cat='.intval($this->id_cat).'
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

		if(empty($this->id_cat))
			return false;

		$sql = 'DELETE FROM '.TABLE_ANNONCES_CATS.'
				WHERE id_cat='.intval($this->id_cat).'
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

		if(empty($this->id_cat))
			return false;

		$sql = 'SELECT c.*
				FROM '.TABLE_ANNONCES_CATS.' as c
				WHERE id_cat = '.intval($this->id_cat).'
				LIMIT 1';

		$resultat = $c->sql_query($sql) OR message_die(E_ERROR,702,__FILE__,__LINE__,$sql);
		$row = $c->sql_fetchrow($resultat);
		if(!empty($row))
		{
			$this->id_cat 		= empty($row['id_cat']) ? null : $row['id_cat'];
			$this->title_cat 	= empty($row['title_cat']) ? null : $row['title_cat'];
			$this->picture_cat 	= empty($row['picture_cat']) ? null : $row['picture_cat'];
			$this->order 		= empty($row['order']) ? null : $row['order'];
		}
		return $this;
	}

	/**
	 * Recupere les infos de tous les enregistrements
	 *
	 */
	function recuperer_tous()
	{
		global $c;

		$sql = 'SELECT a.*
				FROM '.TABLE_ANNONCES_CATS.' as a';

		$resultat = $c->sql_query($sql) OR message_die(E_ERROR,702,__FILE__,__LINE__,$sql);
		$rows = array();
		while($row = $c->sql_fetchrow($resultat))
		{
			$item = new stdClass();
			$item->id_cat = $row['id_cat'];
			$item->title_cat = $row['title_cat'];
			$item->picture_cat = $row['picture_cat'];
			$item->order = $row['order'];
			$rows[] = $item;
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