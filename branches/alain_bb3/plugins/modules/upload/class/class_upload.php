<?php
/*
|------------------------------------------------------------------------------------------------------------
| Software: Malleo ( CMS ) - Module Upload
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

class Upload
{
	var $nom;
	var $ctime;
	var $mtime;
	var $size;

	private $valid_data = array(
		array('mime'=>'application/ogg','trid'=>'audio-ogg-vorbis'),
		array('mime'=>'application/pdf','trid'=>'adobe_pdf'),
		array('mime'=>'application/x-shockwave-flash','trid'=>'video-flv'),
		array('mime'=>'application/zip','trid'=>'ark-zip'),
		array('mime'=>'application/x-gzip','trid'=>'ark-gz'),
		array('mime'=>'application/x-zip-compressed','trid'=>'ark-zip'),
		array('mime'=>'audio/x-ms-wma','trid'=>'audio-wma'),
		array('mime'=>'audio/vnd.rn-realaudio','trid'=>''),
		array('mime'=>'audio/x-wav','trid'=>'audio-wav'),
		array('mime'=>'image/gif','trid'=>'bitmap-gif'),
		array('mime'=>'image/jpeg','trid'=>'bitmap-jpeg'),
		array('mime'=>'image/png','trid'=>'bitmap-png'),
		array('mime'=>'image/vnd.microsoft.icon','trid'=>'bitmap-ico'),
		array('mime'=>'image/x-icon','trid'=>'bitmap-ico'),
		array('mime'=>'image/svg+xml','trid'=>'bitmap-svg'),
		array('mime'=>'video/mpeg','trid'=>'video-mpeg'),
		array('mime'=>'video/mp4','trid'=>'mp4'),
		array('mime'=>'video/quicktime','trid'=>'video-mov'),
		array('mime'=>'video/x-ms-wmv','trid'=>'video-wmv'),
		array('mime'=>'video/x-flv','trid'=>'video-flv'),
	);

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
				// Chaine de caracteres
				case 'nom':
					$this->$key = urldecode($val);break;
			}
		}
	}

	//
	// Supprimer un element
	function supprimer($dir)
	{
		if (empty($this->nom)) return false;

		$file = $dir . '/'. $this->nom;
		if (file_exists($file))
			@unlink($file);
	}

	//
	// Renvoie les informations de tous les elements
	function recuperer_tous($dir)
	{
		if (file_exists($dir))
		{
			$rows = array();
			$ch = @opendir($dir);
			while ($file = @readdir($ch))
			{
				if ($file[0] != '.')
				{
					$fn = $dir.'/'.$file;
					if (is_file($fn))
					{
						$x = new stdClass();
						$x->nom 	 = $file;
						$x->ctime = filectime($fn);
						$x->mtime = filemtime($fn);
						$x->size  = filesize($fn);
						$rows[] = $x;
					}
				}
			}
			@closedir($ch);
			return $rows;
		}
		return false;
	}

	function save_file($file, $dir)
	{
		$message = '';

		$form_message = $this->_get_upload_error_msg($file);
		if (!empty($form_message))
		{
            if (file_exists($file['tmp_name']))
                @unlink($file['tmp_name']);
			return array(FALSE, $form_message);
		}

        $ftype = '';
        $trid = '';
        foreach ($this->valid_data as $data)
        {
            if ($file['type'] == $data['mime'])
            {
                $ftype = $file['type'];
                $trid = $data['trid'];
                break;
            }
        }

		if (empty($ftype))
		{
            if (file_exists($file['tmp_name']))
                @unlink($file['tmp_name']);
			return array(FALSE, 'Type fichier non supporté '.$file['type']);
		}

        $ftrid = dirname(__FILE__).'/trid/'.$trid.'.trid.xml';
        $xml = simplexml_load_file($ftrid);
        if (empty($xml))
        {
            if (file_exists($file['tmp_name']))
                @unlink($file['tmp_name']);
			return array(FALSE, 'Type fichier non supporté');
        }

		if (is_object($xml->FrontBlock))
        {
			foreach($xml->FrontBlock->Pattern as $data)
            {
                $pos = (int)$data->Pos;
                if ($pos==0)
                {
                    $tmp = (string)$data->Bytes;
                    $trid_ref = str_split($tmp,2);
                    $trid_len = count($trid_ref);
                    break;
                }
            }
        }

        if(empty($trid_ref) OR empty($trid_len))
        {
            if (file_exists($file['tmp_name']))
                @unlink($file['tmp_name']);
			return array(FALSE, 'Type fichier non supporté');
        }
        
        $fs = @fopen($file['tmp_name'],"r");
        if (!$fs)
        {
            if (file_exists($file['tmp_name']))
                @unlink($file['tmp_name']);
			return array(FALSE, 'Erreur interne');
        }
        
        $str = fread($fs, $trid_len);
        @fclose($fs);
        $x = array();
        for ($i=0; $i<strlen($str); $i++)
            $x[] = sprintf("%02X", ord($str[$i]));

        if ($x != $trid_ref)
        {
            if (file_exists($file['tmp_name']))
                @unlink($file['tmp_name']);
            return array(false, 'Type fichier non supporté');
        }
        
		$file_name = $file['name'];
		$file_path_tmp = $dir.'/'.$file_name;
		if (!move_uploaded_file($file['tmp_name'], $file_path_tmp))
		{
            if (file_exists($file['tmp_name']))
                @unlink($file['tmp_name']);
			return array(FALSE, 'Problème avec move_uploaded_file');
		}

		if (file_exists($file['tmp_name']))
			@unlink($file['tmp_name']);

		return array(true, 'Succes');
	}

	function get_size($dir)
	{
		if (file_exists($dir))
		{
			$size = 0;
			$ch = @opendir($dir);
			while ($file = @readdir($ch))
			{
				if ($file[0] != '.')
				{
					$fn = $dir.'/'.$file;
					if (is_file($fn))
					{
						$size += filesize($fn);
					}
					if (is_dir($fn))
					{
						$size += $this->get_size($fn);
					}
				}
			}
			@closedir($ch);
			return $size;
		}
		return false;
	}

	private function _get_upload_error_msg($file)
	{
		$message = '';
		if ($file['error'])
		{
			switch ($file['error']){
				case 1: // UPLOAD_ERR_INI_SIZE
					$message = "Le fichier dépasse la limite autorisée par le serveur (fichier php.ini) !";
					break;
				case 2: // UPLOAD_ERR_FORM_SIZE
					$message =  "Le fichier dépasse la limite autorisée dans le formulaire HTML !";
					break;
				case 3: // UPLOAD_ERR_PARTIAL
					$message =  "L'envoi du fichier a été interrompu pendant le transfert !";
					break;
				case 4: // UPLOAD_ERR_NO_FILE
					$message =  "Aucun fichier n'a été téléchargé.";
					break;
				case 6: // UPLOAD_ERR_NO_TMP_DIR
					$message =  "Un dossier temporaire est manquant. ";
					break;
				case 7: // UPLOAD_ERR_CANT_WRITE
					$message =  "Échec de l'écriture du fichier sur le disque.";
					break;
				case 8: // UPLOAD_ERR_EXTENSION
					$message =  "L'envoi de fichier est arrêté par l'extension.";
					break;
				default:
					$message =  "Erreur inconnue : " . intval($file['error']);
					break;
			}
		}
		return $message;
	}

}

?>