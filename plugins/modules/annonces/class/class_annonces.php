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
	var $title;
	var $contents;
	var $picture;
	var $created_by;
	var $created_date;
	var $type;
	var $price;
	var $approved_by;
	var $approved_date;
	var $updated_by;
	var $updated_date;
	var $max_weeks;

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
				case 'id':
				case 'id_cat':
				case 'created_by':
				case 'updated_by':
				case 'approved_by':
				case 'type':
				case 'max_weeks':
					$this->$key = intval($val);break;
				// Chaine de caracteres
				case 'title':
				case 'contents':
				case 'picture':
					$this->$key = $val;break;
				case 'price':
					$this->$key = (float)$val;break;
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
		
		$time = time();

		$sql = 'INSERT INTO '.TABLE_ANNONCES.'
			SET id_cat = '.$this->id_cat.',
				title = '.$this->title.',
				contents = '.$this->contents.',
				picture = '.$this->picture.',
				created_by = '.$user['user_id'].',
				created_date = '.$time.',';
		if ($user['user_id']==1)
		{
			$sql .= 'approved_by = '.$user['user_id'].',
				approved_date = '.$time.',';
		}
		$sql .= 'type = '.$this->type.',
				price = '.$this->price.',
				max_weeks = '.$this->max_weeks;
				
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
		global $c,$module,$root,$user;

		if(empty($this->id))
			return false;

		$time = time();

		$sql = 'UPDATE '.TABLE_ANNONCES.'
			SET id_cat = '.$this->id_cat.',
				title = '.$this->title.',
				contents = '.$this->contents.',
				picture = '.$this->picture.',
				updated_by = '.$user['user_id'].',
				updated_date = '.$time.',';
		if ($user['user_id']==1)
		{
			$sql .= 'approved_by = '.$user['user_id'].',
				approved_date = '.$time.',';
		}
		$sql .= 'type = '.$this->type.',
				price = '.$this->price.',
				max_weeks = '.$this->max_weeks;

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

		if(empty($this->id))
			return false;

		$sql = 'SELECT c.*
				FROM '.TABLE_ANNONCES.' as c
				WHERE id = '.intval($this->id).'
				LIMIT 1';

		$resultat = $c->sql_query($sql) OR message_die(E_ERROR,702,__FILE__,__LINE__,$sql);
		$row = $this->fetchObject($resultat);
		if(!empty($row)) foreach ($row as $k => $v)
		{
			$this->{$k}	= $v;
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
		while($row = $this->fetchObject($resultat))
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