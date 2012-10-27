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

class Action
{
	var $type_options;
	var $sort_options;
	var $mode_options;
	var $forgiven_tags;
	
	function __construct()
	{
		global $lang;
		
		$this->type_options = array(0 => $lang['sa_group_all']);
		for ($i = 1; $i <= 9; $i++) {
			if (!empty($lang['sa_group_'.$i]))
				$this->type_options[$i] = $lang['sa_group_'.$i];
			else
				break;
		}

		$this->sort_options = array(
			'title' => $lang['sa_sort_title'],
			'created_date' => $lang['sa_sort_date'],
			'price' => $lang['sa_sort_price']);
			
		$this->mode_options = array(
			'asc' => $lang['sa_mode_asc'],
			'desc' => $lang['sa_mode_desc']);

		$this->forgiven_tags = array('code', 'math', 'html');
		
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
	
	/**
	 * Creation jeton securite
	 *
	 * @return string jeton id
	 */
	function creer_jeton()
	{
		global $session;
		
		if (!session_id()) @session_start();
		$jeton = md5(uniqid(rand(), TRUE));
		$_SESSION['jeton'] = $jeton;
		$_SESSION['jeton_timestamp'] = $session->time;
		return $jeton;
	}
	
	/**
	 * Verifier jeton securite
	 *
	 * @return bool 
	 */
	function verifier_jeton($var)
	{
		if (!session_id()) @session_start();
		if (!array_key_exists('jeton',$var) 
			|| $var['jeton'] != $_SESSION['jeton'] 
			|| time() - $_SESSION['jeton_timestamp'] >= VALIDITE_JETON)
		{
			return false;
		}
		return true;
	}
	
	
		
}

?>