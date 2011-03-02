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
if ( !defined('PROTECT') )
{
	die("Tentative de Hacking");
}

require($root.'librairies/PHPMailer/class.phpmailer.php');
load_lang('emails');

class mail extends phpmailer {
    var $WordWrap = 75;
	var $config;
	var $message_explain='';
	var $titre_message='';
	
	function mail()
	{
		global $cf;
		// SMTP ou Mail()
		if($cf->config['mail_smtp'] == 1) $this->IsSMTP(); else $this->IsMail();
		// Connexion securisee?
		$this->SMTPSecure = ($cf->config['mail_secure'] == 1)? 'ssl':'';
		// Port d'ecoute
		$this->Port = $cf->config['mail_port_serveur'];
		// Serveur SMTP
		$this->Host = $cf->config['mail_url_serveur'];
		// Email Emetteur + pseudo
		$this->From     = $cf->config['mail_from'];
		$this->FromName = $cf->config['mail_fromname'];
		$this->SingleTo = true;
	}
	
	function formate_html($message){
		global $tpl,$lang,$root,$cf;
		$this->Subject = html_to_str($this->Subject);
		$tpl->set_filenames(array(
		  'EMAIL_HTML' =>  $root . 'html/email.html'
		));
		$tpl->buffer = '';
		$tpl->assign_vars(array(
			'CHARSET'				=>	$cf->config['charset'],
			'NOM_SITE'				=>	$cf->config['nom_site'],
			'ROOT_STYLE'			=>	'http://'.$cf->config['adresse_site'].$cf->config['path'],
			'STYLE'					=>	$cf->config['default_style'],
			'TITRE_MAIL'			=>	$this->protege_guillemets($this->Subject),
			'EXPLICATION_SITE'		=>	$this->protege_guillemets($lang['L_EXPLICATION_MAIL']),
			'EXPLICATION_MESSAGE'	=>	$this->protege_guillemets($this->protege_images($this->message_explain)),
			'TITRE_MESSAGE'			=>	$this->protege_guillemets($this->protege_images($this->titre_message)),
			'MESSAGE'				=>	$this->protege_guillemets($this->protege_images($message)),
			'L_URL_SITE'			=>	$lang['L_URL_SITE'],
			'URL_SITE'				=>	'http://'.$cf->config['adresse_site'].$cf->config['path'],
			'SIGNATURE'				=>	$cf->config['mail_fromname']
		));		
		$tpl->pparse('EMAIL_HTML',true);
		$this->Body  = $tpl->buffer;
		$this->AltBody  = $this->formate_texte($tpl->buffer);
	}
	
	function protege_guillemets($texte){
		return stripslashes(ereg_replace("''","'",$texte));
	}
	
	function protege_images($string){
		global $cf;
        $string = eregi_replace(' src="data/',' src="'.'http://'.$cf->config['adresse_site'].$cf->config['path'].'data/',$string);
        $string = eregi_replace(' src="/data/',' src="'.'http://'.$cf->config['adresse_site'].$cf->config['path'].'data/',$string);
        return $string;
	}
	
	function formate_texte($string){	
		$string = ereg_replace('<br />',"\n",$string);
		$string = trim(strip_tags($string));
		$string = ereg_replace("\n\n","",$string);
		$string = ereg_replace("\t",'',$string);
		$string = html_to_str($string);
		return $string;
	}
}
?>