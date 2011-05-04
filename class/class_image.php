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
class image
{
	// qualite de l'image de sortie 
	// 0->100 PHP4
	// 0->10 PHP5
	var $qualite = 100;
	// Liste des extensions authorisees
	var	$ext_ok = array('gif','png','jpg','jpeg');
	// objet image
	var $image;
	// objet contenant les informations de l'image envoyee
	var $post;
	// Extension du fichier courant
	var $extension;
	// tableau des clefs de langue
	var $lang;
	// Tableau associatif de couleurs
	var $couleur;
	// Nom du champs file dans le template
	var $nom_champ='image';
	// Nom unique du fichier
	var $nom_unique='';
	// Masque a appliquer sur les fichiers et dossiers
	var $umask = 0777;
	// Positions
	var $position_x;
	var $position_y;
	// Taille max en bytes de l'image envoyee
	var $file_max_size=100000;
	// Taille max du dossier de stockage des images
	var $rep_taille_max=1000000;
	// Dimensions max
	var $file_max_largeur=100;
	var $file_max_hauteur=100;
	// Dossier ou copier l'image
	var $destination;
	// Contenu du .htaccess cree dans les dossiers cotnenant des images
	var $htaccess = 
'<Limit POST PUT DELETE>
	Order Allow,Deny
	Deny from All
</Limit>';
	
	function image()
	{
		global $lang;
		load_lang('image');
		$this->lang = $lang;
	}
	
	//
	// SAUVEGARDER image
	function save_image($image)
	{
		switch ($this->extension)
		{
			case 'png' :	imagepng($this->image,$image); break;
			case 'gif' : 	imagegif($this->image,$image); break;
			case 'jpg' :	imagejpeg($this->image,$image,$this->qualite); break;
			case 'jpeg' :	imagejpeg($this->image,$image,$this->qualite); break;
		}
		imageDestroy($this->image);
		@chmod($image,$this->umask);
	}
	
	//
	// generation de l'image finale en la renvoyant vers l'écran
	function afficher_image($image)
	{
		switch ($this->extension_source)
		{
			case 'png' :	header("Content-Type: image/png");	echo $image; break;
			case 'gif' : 	header("Content-Type: image/gif");	echo $image; break;
			case 'jpg' :	header("Content-Type: image/jpeg");	echo $image; break;
			case 'jpeg' :	header("Content-Type: image/jpeg");	echo $image; break;
		}
		return true; 
	}
	
	// 
	// TRANSFORME une couleur HEXA en RGB
	// entree : code HEXA
	// sortie : tableau de couleurs
	function html2rgb($color)
	{
	  if (substr($color,0,1)=="#") $color=substr($color,1,6);

	  $tablo[0] = hexdec(substr($color, 0, 2));
	  $tablo[1] = hexdec(substr($color, 2, 2));
	  $tablo[2] = hexdec(substr($color, 4, 2));
	  return $tablo;
	}
	
	// permet d'allouer une couleur hexa à une variable
	function assigner_couleur($libelle,$hexa)
	{
		$rgb = $this->html2rgb($hexa);
		$this->couleur[$libelle] = ImageColorAllocate ($this->image, $rgb[0],$rgb[1],$rgb[2]);
	}
	
	//
	// INCRUSTE une image dans une image
	function inserer_image($mini,$x,$y){
	
		$this->declaration_image($mini);
		// On recupere les dimensions de la nouvelle image
		$largeur_mini = imagesx($mini);
		$hauteur_mini = imagesy($mini);
		// calcul de position
		$this->position_x = ($x - $largeur_mini);
		$this->position_y = ($y - $hauteur_mini) ;
		// Incrustation de l'image
		imagecopymerge($this->image, $this->mini, $this->position_x, $this->position_y, 0, 0, $largeur_mini, $hauteur_mini, 100);
	}
	
	//
	// INCRUSTE du texte dans une image
	function  inserer_texte($texte,$taille,$couleur,$font=false)
	{
		global $root;
		$rgb = $this->html2rgb($couleur);
		$couleur = ImageColorAllocate ($this->image, $rgb[0],$rgb[1],$rgb[2]);
		if ($font != false && file_exists($font)){
			// FONT additionnelle fournie
			imagettftext($this->image, $taille, 0, $this->position_x, $this->position_y, $couleur, $font, $texte);
		}else{
			imagestring($this->image, $taille, $this->position_x, $this->position_y, $texte, $couleur);
		}
	}

	//
	// EXTRAIT l'extension d'un fichier
	function extension($image)
	{
		$ext = pathinfo($image);
		return $this->extension = strtolower($ext['extension']);
	}
	
	// on declare l'image
	function declaration_image($url)
	{
		$this->extension($url);
		switch ($this->extension)
		{
			case 'png' : $this->image = imagecreatefrompng($url);  break;
			case 'gif' : $this->image = imagecreatefromgif($url);  break;
			case 'jpg' : $this->image = imagecreatefromjpeg($url);  break;
			case 'jpeg': $this->image = imagecreatefromjpeg($url);  break;
		}	
	}
	
	//
	// Verifie l'unicite d'une image dans un dossier, et si elle existe deja on trouve un nom de fichier approchant disponible
	function nom_unique($nom_fichier,$destination,$nom_teste=false,$cpt=1){
		if ($nom_teste==false) $nom_teste = supprimer_accents(utf8_decode($nom_fichier));
		if (file_exists($destination.$nom_teste) && $cpt<10)
		{
			$ext = pathinfo($nom_teste);
			$nom_teste = eregi_replace('.'.$ext['extension'],'',$nom_fichier).'_'.$cpt.'.'.$ext['extension'];
			$cpt++;
			$nom_teste = $this->nom_unique($nom_fichier,$destination,$nom_teste,$cpt);			
		}
		$this->nom_unique = $nom_teste;
		return $this->nom_unique;
	}
	
	//
	// Copie une image temporaire dans un dossier specifie
	// source = $HTTP_POST_FILES['image']['tmp_name']
	function download_image($nom_fichier,$source,$destination)
	{
		$this->nom_unique($nom_fichier,$destination);
		if (is_uploaded_file($source))
		{
		 	@move_uploaded_file($source, $destination.$this->nom_unique);
			@chmod($destination.$this->nom_unique, $this->umask);
		}else return $this->lang['image_move_down'];
	}
	//
	// Creation d'une miniature
	function creer_miniature($img_name,$filename,$new_w,$new_h) {
	  
		//creates the new image using the appropriate function from gd library
		if (!$this->extension) $this->extension($img_name);
		switch ($this->extension){
			case 'jpg':
			case 'jpeg':$src_img=ImageCreateFromJPEG($img_name);break;
			case 'gif':$src_img=ImageCreateFromGIF($img_name);break; 
			case 'png':$src_img=ImageCreateFromPng($img_name);break;
			default : return false;
		}
		//gets the dimmensions of the image
		$old_x=imageSX($src_img);
		$old_y=imageSY($src_img);

		$ratio1=$old_x/$new_w;
		$ratio2=$old_y/$new_h;
		if($ratio1>$ratio2) {
			$thumb_w=$new_w;
			$thumb_h=$old_y/$ratio1;
		}else{
			$thumb_h=$new_h;
			$thumb_w=$old_x/$ratio2;
		}
		$dst_img=ImageCreateTrueColor($thumb_w,$thumb_h);
		imagecopyresampled($dst_img,$src_img,0,0,0,0,$thumb_w,$thumb_h,$old_x,$old_y);
		switch ($this->extension){
			case 'jpg':
			case 'jpeg':imagejpeg($dst_img,$filename);break;
			case 'gif':imagegif($dst_img,$filename);break; 
			case 'png':imagepng($dst_img,$filename);break;
		}
		imagedestroy($dst_img);
		imagedestroy($src_img);
	}
	
	//
	// RENVOIE la taille en octets d'un dossier passe en parametre recursif
	function dirsize($dir)
	{
		// Si le dossier n'existe pas on le cree
		if (!is_dir($dir)) $this->creer_dossier_image($dir);
		@chmod($dir, $this->umask);
		$size=0;
		$ch = @opendir($dir);
		while ($image = @readdir($ch))
		{
			if (substr($image,0,1) != "." && $image != '.htaccess') {
				if (is_dir($dir.$image)){
					$size += $this->dirsize($dir.$image.'/');
				}else{
					$size += filesize($dir.$image);
				}
			}
		}
		@closedir($ch);
		return $size;
	}
	
	//
	// Effectue les verification d'usage lors d'un UPLOAD
	// Si tout est OK enregistre l'image
	function verification_upload()
	{
		// TRANSFERT
		if ($this->post[$this->nom_champ]['error'] != UPLOAD_ERR_OK){
			switch ($this->post[$this->nom_champ]['error'])
			{
			   case 1: 
			   case 2: return sprintf($this->lang['image_taille_max'],$this->file_max_size); break;
			   case 3: return $this->lang['image_upload_stop']; break;
			   case 4: return $this->lang['image_upload_nul']; break;
			}		
		}
		// TAILLE 
		if ($this->post[$this->nom_champ]['size'] > $this->file_max_size){
			return sprintf($this->lang['image_taille_max'],round($this->file_max_size/1024,2));
		}
		// TAILLE DISQUE rep_taille_max
		$t_disk = $this->dirsize($this->destination);
		if (($t_disk + $this->post[$this->nom_champ]['size']) > $this->rep_taille_max){
			return sprintf($this->lang['rep_taille_max'],round($t_disk/1024,2),round($this->post[$this->nom_champ]['size']/1024,2),round($this->rep_taille_max/1024,2));
		}
		// DIMENSIONS
		$taille = getimagesize($this->post[$this->nom_champ]['tmp_name']);
		if ($taille['0'] > $this->file_max_largeur || $taille['1'] > $this->file_max_hauteur){
			return sprintf($this->lang['image_dimensions_max'],$this->file_max_largeur,$this->file_max_hauteur);
		}
		// EXTENSION 
		$type = strtolower(substr(strrchr($this->post[$this->nom_champ]['name'], '.'), 1));	
		if (!in_array($type,$this->ext_ok)){ 
			return sprintf($this->lang['image_bad_type'],implode(', ',$this->ext_ok));
		}
		// Tout est OK on enregistre
		$this->download_image($this->post[$this->nom_champ]['name'],$this->post[$this->nom_champ]['tmp_name'],$this->destination);
		return true;
	}
	
	//
	// Copie une image distante en local
	function download_image_distante($url){
	
		$this->extension($url);
		$width = $height = 0;
		$image_data=$type = '';

		// Recuperation des fragments de l'URL de l'image
		if ( preg_match('/^(http:\/\/)?([\w\-\.]+)\:?([0-9]*)\/([^ \?&=\#\"\n\r\t<]*?(\.(jpg|jpeg|gif|png)))$/', $url, $url_ary) )
		{
			if ( empty($url_ary[4]) )	return $url ;
			$base_get = '/' . $url_ary[4];
			$port = ( !empty($url_ary[3]) ) ? $url_ary[3] : 80;
			if (!function_exists('fsockopen') || 
				$url_ary[2] == gethostbyname($url_ary[2]) ||
				!($fsock = @fsockopen($url_ary[2], $port, $errno, $errstr)) )	return $url ;
			stream_set_blocking($fsock,0);
			@fputs($fsock, "GET $base_get HTTP/1.1\r\n");
			@fputs($fsock, "HOST: " . $url_ary[2] . "\r\n");
			@fputs($fsock, "Connection: close\r\n\r\n");
			while( !@feof($fsock) )
			{
				$image_data .= @fread($fsock, 200000);
			}
			@fclose($fsock);
			if (!preg_match('#Content-Length\: ([0-9]+)[^ /][\s]+#i', $image_data, $file_data1) || !preg_match('#Content-Type\: image/[x\-]*([a-z]+)[\s]+#i', $image_data, $file_data2)) return $url ;
			
			$image_filesize = $file_data1[1]; 
			$image_filetype = $file_data2[1]; 

			if ($image_filesize == 0 )	return $url;
			// recuperation du troncon appartenant a l'image dans le flux
			$image_data = substr($image_data, strlen($image_data) - $image_filesize, $image_filesize);
			// dossier de stockage temporaire de l'image
			$tmp_path = 'cache/tmp/';
			if (!is_dir($tmp_path)) $this->creer_dossier_image($tmp_path);
			$tmp_filename = tempnam($tmp_path, uniqid(rand()));
			$fptr = @fopen($tmp_filename, 'wb');
			$bytes_written = @fwrite($fptr, $image_data, $image_filesize);
			@fclose($fptr);
			if ( $bytes_written != $image_filesize )
			{
				@unlink($tmp_filename);
				return $url;
			}
			list($width, $height, $type) = @getimagesize($tmp_filename);
			// on controle l'image
			// TAILLE 
			if (filesize($tmp_filename) > $this->file_max_size){
				return sprintf($this->lang['image_taille_max'],round($this->file_max_size/1024,2));
			}
			// DIMENSIONS
			if ($width > $this->file_max_largeur || $height > $this->file_max_hauteur){
				return sprintf($this->lang['image_dimensions_max'],$this->file_max_largeur,$this->file_max_hauteur);
			}
			//EXTENSION
			if (!in_array($this->extension,$this->ext_ok)){ 
				return sprintf($this->lang['image_bad_type'],implode(', ',$this->ext_ok));
			}
			// Suppression de l'arborescance
			$nom_fichier = eregi_replace('(.*)[/]','',$url_ary[4]);
			$this->nom_unique($nom_fichier,$this->destination);
			@copy($tmp_filename, $this->destination.$this->nom_unique);
			@unlink($tmp_filename);
			@chmod($this->destination.$this->nom_unique, $this->umask);
			return $this->destination.$this->nom_unique;
		}else
			return $url;
	}
	
	//
	// Copie locale des images postees
	function copie_locale_images($msg,$requete){
		global $cf,$c,$user;
		// Parametres
		$this->file_max_size = 1000000;
		$this->file_max_largeur = 1300;
		$this->file_max_hauteur = 1300;
		$this->rep_taille_max = 1000000;
		$this->destination = 'data/images/';
		$adresse_site = 'http://'.$cf->config['adresse_site'].$cf->config['path'];
		
		$pattern = "#\[img\]([^?](?:[^\[]+|\[(?!url))*?)\[/img\]#i";
		preg_match_all($pattern,$msg,$images);

		$nbre_images = sizeof($images[1]);
		// Parcours la liste des images pour les enregistrer
		if ($nbre_images > 0)
		{
			$images_modifiees = array();
			for ($i=0;$i<$nbre_images;$i++)
			{
				// toute image n'etant pas dans le dossier data/images/ est enregistree
				if (!eregi($this->destination,$images[1][$i]) 
						&& !eregi('data/smileys/',$images[1][$i]))
				{
					// Copie locale
					if (eregi('^(data\/)',$images[1][$i])){
						$nom_fichier = eregi_replace('(.*)[/]','',$images[1][$i]);
						$this->nom_unique($nom_fichier,$this->destination);
						if(rename($images[1][$i],$this->destination.$this->nom_unique)){
							// On enregistre la nouvelle url
							$images_modifiees['avant'][$i] = $images[1][$i];
							$images_modifiees['apres'][$i] = $this->destination.$this->nom_unique;
	 						// Suppression du fichier dans le dossier perso
							if (eregi('data/files/'.$user['user_id'].'/',$images[1][$i]))
							{
								$img_tmp = eregi_replace($adresse_site,'',$images[1][$i]);
								if (file_exists($img_tmp))@unlink($img_tmp);
								$img_tmp = eregi_replace('data/files/'.$user['user_id'].'/','data/files/'.$user['user_id'].'/thumbs/',$img_tmp);
								if (file_exists($img_tmp))@unlink($img_tmp);
							}
						}

					// copie distante
					}else{

						$img = $this->download_image_distante($images[1][$i]);
						if ($img != $images[1][$i] && $img != '')
						{
							// On enregistre la nouvelle url
							$images_modifiees['avant'][$i] = $images[1][$i];
							$images_modifiees['apres'][$i] = $img;
						}
					}
				}
			}
			if (array_key_exists('avant',$images_modifiees) && sizeof($images_modifiees)>0){
				// Remplacement des URLS des images par les nouvelles
				$msg = str_replace($images_modifiees['avant'],$images_modifiees['apres'],$msg);
				$c->sql_query(sprintf($requete,$msg));
			}
		}
		return $msg;		
	}
	
	
	//
	// LISTE un repertoire pour en extraire les images
	function lister_images_dir($dir){
		// Si le dossier n'existe pas on le cree
		if (!is_dir($dir)) $this->creer_dossier_image($dir);
		@chmod($dir, $this->umask);
	
		$ch = @opendir($dir);
		while ($image = @readdir($ch))
		{
			if ($image[0] != '.' && in_array($this->extension($image),$this->ext_ok)) {
				$this->miniature_image($image,$dir);
			}
		}
		@closedir($ch);
	}
		
	//
	// Cree le dossier demande et place dedans un .htaccess pour le proteger
	function creer_dossier_image($dir){
		@mkdir($dir,$this->umask);
		chmod($dir, $this->umask);
		$file = @fopen($dir.'.htaccess', 'w');
	    @fwrite($file,$this->htaccess);
	    @fclose($file);
		chmod($dir.'.htaccess', $this->umask);
	}
}
?>