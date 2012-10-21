<?php

require (dirname(__file__).'/template_phpBB3.php');

class Template extends template_phpBB3
{
	var $classname = "Template";

	// Hash of filenames for each template handle.
	var $files = array();

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

	// This will hold the uncompiled code for that handle.
	var $uncompiled_code = array();

	var $filename;

	/**
	 * Constructor. Simply sets the root dir.
	 *
	 */
	function Template($root = ".", $cache='cache')
	{
    	global $phpEx,$phpbb_root_path;
        $phpEx='php';
        $phpbb_root_path='toto';
		$this->set_rootdir($root);
        $this->set_cachepath($cache);
	}

	/**
	 * Sets the template root directory for this Template object.
	 */
	function set_rootdir($dir)
	{
		if (!is_dir($dir))
		{
            trigger_error('set_rootdir : argument is not a directory', E_USER_ERROR);
            return false;
		}

		$this->root = $dir;
		return true;
	}
    
	/**
	 * Sets the template root directory for this Template object.
	 */
	function set_cachepath($cache)
	{
		if (!is_dir($cache))
		{
            trigger_error('set_cachepath : argument is not a directory', E_USER_ERROR);
            return false;
		}

		$this->cachepath = $cache;
		return true;
	}

    /**
    *
    *
    */
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

    /**
	* Fichier alternatif
    *
    */
	function alt_file_template($filename)
	{
		$this->filename = $filename;
		if(!$this->teste_template_file('plugins/blocs/','blocs/')){
			if(!$this->teste_template_file('plugins/modules/','modules/')){
				$this->teste_template_file('html/','');
			}
		}
		$filename = $this->filename;
		return $filename;
	}

	/**
	 * Load the file for the handle, compile the file,
	 * and run the compiled code. This will print out
	 * the results of executing the template.
	 */
	function pparse($handle,$deporter=false)
	{
		if (!$this->loadfile($handle)){
			die("Template->pparse(): Couldn't load template file for handle $handle");
		}
		$gzip=false;
		if (ACTIVE_GZIP==true && $deporter==false){ $gzip=true;$deporter=true;}
		// actually compile the template now.
		if (!isset($this->compiled_code[$handle]) || empty($this->compiled_code[$handle])){
			// Actually compile the code now.
			$this->buffer = '';
			$this->compiled_code[$handle] = $this->compile($this->uncompiled_code[$handle],$deporter,'this->buffer');
		}
		// Run the compiled code.
		eval($this->compiled_code[$handle]);

		if (ACTIVE_GZIP==true && $gzip==true) $this->monter_page();
		return true;
	}


	/**
	 * Inserts the uncompiled code for $handle as the
	 * value of $varname in the root-level. This can be used
	 * to effectively include a template in the middle of another
	 * template.
	 * Note that all desired assignments to the variables in $handle should be done
	 * BEFORE calling this function.
	 */
	function assign_var_from_handle($varname, $handle)
	{
		if (!$this->loadfile($handle)){
			die("Template->assign_var_from_handle(): Couldn't load template file for handle $handle");
		}

		// Compile it, with the "no echo statements" option on.
		$_str = "";
		$code = $this->compile($this->uncompiled_code[$handle], true, '_str');

		// evaluate the variable assignment.
		eval($code);
		// assign the value of the generated variable to the given varname.
		$this->assign_var($varname, $_str);

		return true;
	}

	/**
	 * Generates a full path+filename for the given filename, which can either
	 * be an absolute name, or a name relative to the rootdir for this Template
	 * object.
	 */
	function make_filename($filename)
	{
		if (!file_exists($filename)){
			die("Template->make_filename(): Error - file $filename does not exist");
		}
		return $filename;
	}

	/**
	 * If not already done, load the file for the given handle and populate
	 * the uncompiled_code[] hash with its code. Do not compile.
	 */
	function loadfile($handle)
	{
		// If the file for this handle is already loaded and compiled, do nothing.
		if (isset($this->uncompiled_code[$handle]) && !empty($this->uncompiled_code[$handle])){
			return true;
		}

		// If we don't have a file assigned to this handle, die.
		if (!isset($this->files[$handle])){
			die("Template->loadfile(): No file specified for handle $handle");
		}

		$filename = $this->files[$handle];

		$str = implode("", @file($filename));
		if (empty($str)){
			die("Template->loadfile(): File $filename for handle $handle is empty");
		}

		$this->uncompiled_code[$handle] = $str;

		return true;
	}

	/**
	* The all seeing all doing compile method. Parts are inspired by or directly from Smarty
	*
	*/
	function compile($code, $no_echo = false, $echo_var = '')
	{
        return $compile->compile($code, $no_echo, $echo_var);
	}

	/**
	* Generates a reference to the given variable inside the given (possibly nested)
	* block namespace. This is a string of the form:
	* ' . $this->_tpldata['parent'][$_parent_i]['$child1'][$_child1_i]['$child2'][$_child2_i]...['varname'] . '
	* It's ready to be inserted into an "echo" line in one of the templates.
	* NOTE: expects a trailing "." on the namespace.
	*
	*/
	function generate_block_varref($namespace, $varname, $echo = true, $defop = false)
	{
        return $compile->generate_block_varref($namespace, $varname, $echo, $defop);
	}

	/**
	* Generates a reference to the array of data values for the given
	* (possibly nested) block namespace. This is a string of the form:
	* $this->_tpldata['parent'][$_parent_i]['$child1'][$_child1_i]['$child2'][$_child2_i]...['$childN']
	*
	* If $include_last_iterator is true, then [$_childN_i] will be appended to the form shown above.
	* NOTE: does not expect a trailing "." on the blockname.
	* @access private
	*/
	function generate_block_data_ref($blockname, $include_last_iterator, $defop = false)
	{
        // template_compile;
        return $compile->generate_block_data_ref($blockname, $include_last_iterator, $defop);
	}

	/**
    * Monter une page
    *
    */
	function monter_page()
	{
		$this->contenu_page.=$this->buffer;
	}

	/**
    * Afficher une page
    *
    */
	function afficher_page()
	{
		if (ACTIVE_GZIP==true && extension_loaded('zlib')){
			$phpver = phpversion();
			$useragent = (isset($_SERVER['HTTP_USER_AGENT'])) ? $_SERVER['HTTP_USER_AGENT'] : getenv('HTTP_USER_AGENT');
			if ( $phpver >= '4.0.4pl1'
                && ( strstr($useragent,'compatible') || strstr($useragent,'Gecko') ) )
            {
				ob_start('ob_gzhandler');
				echo $this->contenu_page;
			} elseif( $phpver > '4.0') {
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
