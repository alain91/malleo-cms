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
				ordre = '.intval($this->ordre);

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
	
	function fetchObject($query_id = 0)
	{
		global $c;
		
		if( !$query_id )
		{
			$query_id = $c->query_result;
		}

		if( $query_id )
		{
			$c->row[$query_id] = mysql_fetch_object($query_id);
			return $c->row[$query_id];
		}
		else
		{
			return false;
		}
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
		if(empty($row))
			return false;
		foreach ($row as $k => $v)
		{
			$this->{$k}	= $v;
		}
		return true;
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
			$rows[] = $row;
		}
		return $rows;
	}

}
?>