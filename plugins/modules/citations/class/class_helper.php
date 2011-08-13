<?php
/*
|------------------------------------------------------------------------------------------------------------
| Software: Malleo ( CMS )
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

class Helper
{
	static function sql_escape($str)
	{
		global $c;
		
		$link = $c->db_connect_id;
		if (!empty($link))
			$str = mysql_real_escape_string($str, $link);
		return $str;
	}
	
	static function cleanSlashes($arg)
	{
		if (is_array($arg))
		{
			$new_array = array();
			foreach ($arg as $key => $val)
			{
				$new_array[$key] = self::cleanSlashes($val);
			}
			return $new_array;
		}

		$str = stripslashes($arg);
		return $str;
	}
	
    static function cleanGPC()
    {
		// Check PCRE support for Unicode properties such as \p and \X.
		$ER = error_reporting(0);
		define('PCRE_UNICODE_PROPERTIES', (bool) preg_match('/^\pL$/u', 'ñ'));
		error_reporting($ER);

        if (is_array($_GET) AND count($_GET) > 0)
        {
            foreach ($_GET as $key => $val)
            {
                $_GET[self::clean_input_keys($key)] = self::clean_input_data($val);
            }
        }
        else
        {
            $_GET = array();
        }
        
        if (is_array($_POST) AND count($_POST) > 0)
        {
            foreach ($_POST as $key => $val)
            {
                $_POST[self::clean_input_keys($key)] = self::clean_input_data($val);
            }
        }
        else
        {
            $_POST = array();
        }
        
        if (is_array($_COOKIE) AND count($_COOKIE) > 0)
        {
            foreach ($_COOKIE as $key => $val)
            {
                // Sanitize $_COOKIE
                $_COOKIE[self::clean_input_keys($key)] = self::clean_input_data($val);
            }
        }
        else
        {
            $_COOKIE = array();
        }
        
    }
    
	/**
	 * This is a helper function. It escapes data and standardizes newline characters to '\n'.
	 *
	 * @param   unknown_type  string to clean
	 * @return  string
	 */
	private static function clean_input_data($str)
	{
		if (is_array($str))
		{
			$new_array = array();
			foreach ($str as $key => $val)
			{
				$new_array[self::clean_input_keys($key)] = self::clean_input_data($val);
			}
			return $new_array;
		}

		if (get_magic_quotes_gpc())
		{
			$str = stripslashes($str);
		}

		// Standardize newlines
		return str_replace(array("\r\n", "\r"), "\n", $str);
	}
    
	/**
	 * This is a helper function. To prevent malicious users
	 * from trying to exploit keys we make sure that keys are
	 * only named with alpha-numeric text and a few other items.
	 *
	 * @param   string  string to clean
	 * @return  string
	 */
	private static function clean_input_keys($str)
	{
		$chars = (PCRE_UNICODE_PROPERTIES) ? '\pL' : 'a-zA-Z';

		if ( ! preg_match('#^['.$chars.'0-9:_/-]+$#uD', $str))
		{
			exit('Disallowed key characters in global data.');
		}

		return $str;
	}

	
}

?>