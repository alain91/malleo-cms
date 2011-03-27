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
defined('PROTECT') OR die("Tentative de Hacking");

require(dirname(__FILE__).'/phpbb_template.php');

class Template extends phpbb_template
{
	// Buffer de sortie
	var $buffer = '';
	
	// Contenu de la page compresse
	var $contenu_page = '';
	
	// titre apparaissant dans le navigateur
	var $titre_navigateur = '';
	
	// titre apparaissant dans la page
	var $titre_page = '';
	
	// Chaine de caracteres mise dans la description
	// Sera limite a 150 caracteres
	var $meta_description = '';
	
	// Url canonique utilisée par Google pour unifier les pages ayant la même url
	var $url_canonique = '';
	
	// mini icones d'options rapides
	var $options_page = array();
	
	var $filename;
	
	function Template($root = ".")
	{
		$this->set_rootdir($root);
	}
	
	function set_filenames($filename_array)
	{
		if (!is_array($filename_array))
		{
			return false;
		}

		reset($filename_array);
		while(list($handle, $filename) = each($filename_array))
		{
			$this->files[$handle] = $this->make_filename($this->alt_file_template($filename));
		}

		return true;
	}

	function compile($code, $do_not_echo = false, $retvar = '')
	{
		global $lang;
		
		$match=array();
		preg_match_all('#\{([a-z0-9\-_]*?)\}#is',$code,$match);
		foreach($match[1] as $clef){
			if (isset($lang[$clef]) && !$this->var_defined($clef))
			{
				$this->assign_var($clef,$lang[$clef]);
			}
		}
		return parent::compile($code, $do_not_echo, $retvar);
	}
	
	function var_defined($name)
	{
		return isset($this->_tpldata['.'][0][$name]) ? true : false; 
	}
	
	function make_filename($filename)
	{
		if (!file_exists($filename))
		{
			die("Template->make_filename(): Error - file $filename does not exist");
		}

		return $filename;
	}
	
	function teste_template_file($pattern,$dir)
	{
		global $style_name,$style_path,$root;
		$pattern = "/".str_replace("/","\/",$root.$pattern)."/i";
		if (preg_match($pattern,$this->filename)){
			$alt_file = $root.$style_path.$style_name.'/'.$dir.preg_replace($pattern,'',$this->filename);
			if (file_exists($alt_file)){
				$this->filename = $alt_file;
				return true;
			}
		}
		return false;
	}
	
	// Fichier alternatif
	function alt_file_template($filename)
	{
		$this->filename = $filename;
		if(!$this->teste_template_file('plugins/blocs/','blocs/')){
			if(!$this->teste_template_file('plugins/modules/','modules/')){
				$this->teste_template_file('html/','');
			}
		}		
		$filename = $this->filename;
		//echo '<br />'.$filename;
		return $filename;
	}
	
	/**
	 * Load the file for the handle, compile the file,
	 * and run the compiled code. This will print out
	 * the results of executing the template.
	 */
	function pparse($handle,$deporter=false)
	{
		if (!$this->loadfile($handle))
		{
			die("Template->pparse(): Couldn't load template file for handle $handle");
		}
		$gzip=false;
		if (ACTIVE_GZIP==true && $deporter==false){ $gzip=true;$deporter=true;}
		// actually compile the template now.
		if (!isset($this->compiled_code[$handle]) || empty($this->compiled_code[$handle]))
		{
			// Actually compile the code now.
			$this->buffer = '';
			$this->compiled_code[$handle] = $this->compile($this->uncompiled_code[$handle],$deporter,'this->buffer');
		}
		// Run the compiled code.
		eval($this->compiled_code[$handle]);
		
		if (ACTIVE_GZIP==true && $gzip==true) $this->monter_page();
		return true;
	}

	function monter_page()
	{
		$this->contenu_page.=$this->buffer;
	}
	
	function afficher_page()
	{
		if (ACTIVE_GZIP==true && extension_loaded('zlib'))
		{
			$phpver = phpversion();
			$useragent = (isset($_SERVER['HTTP_USER_AGENT'])) ? $_SERVER['HTTP_USER_AGENT'] : getenv('HTTP_USER_AGENT');
			if ( $phpver >= '4.0.4pl1' && ( strstr($useragent,'compatible') || strstr($useragent,'Gecko') ) )
			{
				ob_start('ob_gzhandler');
				echo $this->contenu_page;
			}elseif( $phpver > '4.0'){
				ob_start();
				ob_implicit_flush(0);
				header('Content-Encoding: gzip');
				echo $this->contenu_page;
				$contenu = ob_get_contents();
				ob_end_clean();
				$gzip_size = strlen($contenu);
				$gzip_crc = crc32($contenu);
				$contenu = gzcompress($contenu, 9);
				$contenu = substr($contenu, 0, strlen($contenu) - 4);
				echo "\x1f\x8b\x08\x00\x00\x00\x00\x00";
				echo $contenu;
				echo pack('V', $gzip_crc);
				echo pack('V', $gzip_size);
			}
		}
		return true;
	}
}

?>