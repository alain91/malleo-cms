<?php
/*
 ROBIT BT plugins for tinyCME editor
 Image galery browser, image delete, image upload,thumblair generator.
 Accept only one image folder
 Required: PHP4 and gd.lib extension

 Install:
   1. edit this file config section
   2. copy this file into tinyCME/plugins/advimage folder
   3. replace the image.htm in tinyCME/plugins/advimage folder
   4. copy audio.jpg, video.jpg into tinyCME/plugins/advimage folder

 licence: GNU/GPL
 Authot:  Tibor Fogler   foglert@robitbt.hu
                         www.robitbt.hu
 2008.04.20
*/

define('PROTECT',true);
$root = '../../../../';
require_once($root.'chargement.php');
$lang=$erreur=array();
load_lang('tinymce');
$cf->config['activer_menuh'] = 0;

//
// CHARGEMENT des outils images

require_once($root.'class/class_image.php');
$image = new image();

$image->ext_ok = array('jpg','png','gif');
$image->file_max_size = 10000000;
$image->file_max_largeur = 800;
$image->file_max_hauteur = 1100;
$image->rep_taille_max = 9000000;

global $GDok,$IMGFOLDER,$IMGURL,$AUDIOICON,$VIDEOICON;
$GDok = TRUE;

$IMGFOLDER = $root.'data/files/'.$user['user_id'].'/';
$IMGURL = 'http://'.$cf->config['adresse_site'].$cf->config['path'].'data/files/'.$user['user_id'].'/';

if (!is_dir($IMGFOLDER)) $image->creer_dossier_image($IMGFOLDER);


function make_thumb($ext,$img_name,$filename,$new_w,$new_h) {
	// Securite taille
	$fsize = filesize($img_name);
	if (!$fsize || $fsize > 100000)   return false;
  
	//creates the new image using the appropriate function from gd library
	switch ($ext){
		case 'jpg':
		case 'jpeg':$src_img=ImageCreateFromJPEG($img_name);break;
		case 'gif':$src_img=ImageCreateFromGIF($img_name);break; 
		case 'png':$src_img=ImageCreateFromPng($img_name);break;
		default : return false;
	}
	//gets the dimmensions of the image
	$old_x=imageSX($src_img);
	$old_y=imageSY($src_img);
	if (($old_x > $new_w) | ($old_y > $new_h)){
		$ratio1=$old_x/$new_w;
		$ratio2=$old_y/$new_h;
		if($ratio1>$ratio2) {
			$thumb_w=$new_w;
			$thumb_h=$old_y/$ratio1;
		}else{
			$thumb_h=$new_h;
			$thumb_w=$old_x/$ratio2;
		}
		// we create a new image with the new dimmensions
		if (($old_x < 1000) and ($old_y < 1000)) {
			$dst_img=ImageCreateTrueColor($thumb_w,$thumb_h);
			// resize the big image to the new created one
			imagecopyresampled($dst_img,$src_img,0,0,0,0,$thumb_w,$thumb_h,$old_x,$old_y);
			// output the created image to the file. Now we will have the thumbnail into the
			// file named by $filename
			switch ($ext){
				case 'jpg':
				case 'jpeg':imagejpeg($dst_img,$filename);break;
				case 'gif':imagegif($dst_img,$filename);break; 
				case 'png':imagepng($dst_img,$filename);break;
			}
			imagedestroy($dst_img);
		}
	}
	imagedestroy($src_img);
}

// get size of image
function getimgsize($filename,&$x,&$y) {
  $x = -1;
  $y = -1;
  $result = FALSE;
  $imginfo = GetImageSize($filename);
  if (count($imginfo) > 2) {
    $x = $imginfo[0];
    $y = $imginfo[1];
    $result = TRUE;
  }
  return $result;
}

// read directory list,
// echo table with thumbmail images
// (generate thumbmail if not exists)
// onclick=parent.imgselect(i);
function maketable($dirname) {
	global $GDok,$IMGFOLDER,$IMGURL,$VIDEOICON,$AUDIOICON,$image,$img;

	// Listing des fichiers : $dirname
	$handle = opendir($dirname);
	$file_lista[]=array();
	while ($file = readdir($handle)) {
	 if ($file[0] != '.') {
			$file_lista[]=$file;
	 }
	};
	closedir($handle);
	
	// Pas d'images
	if (count($file_lista) == 0) return false;
	
	
	$kepdb= -1;
	$coldb = 0;
	print '<table border="0" cellspacin="0" cellpadding="0"><tr>'."\n";
	
	// Parcours des fichiers
	for ( $a=0; $a<sizeof($file_lista); $a++) {
		$fnev = $dirname.'/'.$file_lista[$a];
		// Extension du fichier en cours
		$ext = pathinfo($fnev);
		$ext = (array_key_exists('extension',$ext))? strtolower($ext['extension']):'';
		if (!is_dir($fnev) && $fnev!='.htaccess'){
				// Extension autorisee
				if (in_array($ext,$image->ext_ok) && ($file_lista[$a] != 'index.gif') && (!strpos($file_lista[$a],'_t.'))){
	                $kepdb++;
	                $coldb++;
	         	    $picname=substr($file_lista[$a], 0, -4);
	                $belyeg = $dirname.$picname.'_t'.substr($file_lista[$a],-4);
	                $belyegurl = $IMGURL.rawurlencode($picname).'_t'.substr($file_lista[$a],-4);
					
			        if (!file_exists($belyeg)) {
	                   if ($GDok) make_thumb($ext,$fnev,$belyeg,100,100);
		            };
					if (! file_exists($belyeg)) {
	                   $belyeg = $dirname.$file_lista[$a];
					   $belyegurl = $IMGURL.rawurlencode($file_lista[$a]);
	                }
	                $x = -1;
	                $y = -1;
	                getimgsize($belyeg,$x,$y);
	                print '<td width="110" height="110" onclick="parent.selectimg('.$kepdb.')" align="center" valign="center" style="border:solid 1px grey;padding:5px; cursor:pointer;">'."\n";
	                if ($x < 0) {
					  print '<img src="'.$belyegurl.'" alt="'.$file_lista[$a].'" width="100" height="100" id="'.$kepdb.'" />';
					}elseif($x > $y){
					  print '<img src="'.$belyegurl.'" alt="'.$file_lista[$a].'" width="100" id="'.$kepdb.'" />';
					}else{
					  print '<img src="'.$belyegurl.'" alt="'.$file_lista[$a].'" height="100" id="'.$kepdb.'" />';
					}
	                print '</td>'."\n";
					if ($coldb == 3) {
					  print '</tr><tr>'."\n";
					  $coldb = 0;
					}
				}
		}
    }
    print '</tr></table>'."\n";
} //function makejsdir

// This function reads the extension of the file.
// It is used to determine if the file is an image by checking the extension.
function getExtension($str) {
  $i = strrpos($str,".");
  if (!$i) { return ""; }
  $l = strlen($str) - $i;
  $ext = substr($str,$i+1,$l);
  return $ext;
}
// ----------------
// main program
// ----------------
if (!extension_loaded('gd')) {
   if (!dl('gd.so')) {
       $GDok = FALSE;
   }
}
$act =		(isset($_GET['act']))? $_GET['act']:((isset($_POST['act']))?$_POST['act']:'');
$fname =	(isset($_POST['fname']))? $_POST['fname']:'';

switch ($act){
	case 'upload':
		if (isset($HTTP_POST_FILES) && is_array($HTTP_POST_FILES)) 
		{
			$image->post = $HTTP_POST_FILES;
			$image->nom_champ = 'upload';
			$image->destination = $IMGFOLDER;
			$msg = $image->verification_upload();
			if($msg!=1){
				die($msg);
			}
			header('Location: galery.php');
		}
		$picname=substr($name, 0, -4);
		break;
	case 'delete':
		// do delete file
		@unlink($IMGFOLDER.'/'.$_POST['fname']);
		header('location: galery.php');
		break;
	case 'list':
		// generate table
		maketable($IMGFOLDER);
		print '</body></html>';
		exit();
	default : 
		// draw image manager window
		
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-2" />
<link href="<?php echo $root; ?>styles/<?php echo $style_name; ?>/style.css" rel="stylesheet" type="text/css" />
</head>
<body>
	<iframe id="frm1" name="frm1" width="100%" height="220" src="./galery.php?act=list" border="0"></iframe>
	<form name="imgupload" method="post" action="./galery.php" enctype="multipart/form-data">
	<center>
		<div class="bloc" style="width:90%;">
		<table class="standard" width="100%">
		<tr><th colspan="2"><?php echo $lang['L_ACTION_PHOTO']; ?></th></tr>
		<tr>
			<td class="row1"><div id="imgalt"><?php echo $lang['L_CLIQUER_PHOTO']; ?></div></td>
			<td class="row2" width="230">	
				<button type=button onclick=viewimg();><?php echo $lang['L_APERCU']; ?></button>
				<button type=button onclick=insertimg();><?php echo $lang['L_INSERER']; ?></button>
				<button type=button onclick=deleteimg();><?php echo $lang['L_EFFACER']; ?></button>
			</td>
		</tr>
		<tr><th colspan="2"><?php echo $lang['L_UPLOADER_PHOTO']; ?></th></tr>
		<tr>
			<td colspan="2">
				<input type=file size="30" name=upload>&nbsp;&nbsp;
				<input type=hidden name=act value="upload">
				<input type=hidden name=fname value="">
				<button type=button onclick="uploadimg();"><?php echo $lang['L_ENVOYER']; ?></button>
			</td>
		</tr>
		</table>
		</div>
	</center></form>

<script language="JavaScript">
function selectimg(i) {
  doc = frames['frm1'].document;
  if (selected >= 0) {
	img = doc.getElementById(selected);
	img.parentNode.style.border= "solid grey 1px";
  }
  selected = i;
  img = doc.getElementById(i);
  img.parentNode.style.border= "solid white 2px";
  document.getElementById('imgalt').innerHTML = img.alt;
}
function deleteimg() {
  if (selected >= 0) {
	doc = frames['frm1'].document;
	img = doc.getElementById(selected);
	document.forms.imgupload.act.value='delete';
	document.forms.imgupload.fname.value=img.alt;;
	document.forms.imgupload.submit();
  }
}
function uploadimg() {
  document.forms.imgupload.act.value='upload';
  document.forms.imgupload.submit()
}
function insertimg() {
  if (selected >= 0) {
	doc = frames['frm1'].document;
	img = doc.getElementById(selected);
	opener.document.forms[0].src.value = '<?php echo $IMGURL ?>'+img.alt;
	window.close();
  }
}
function viewimg() {
  if (selected >= 0) {
	doc = frames['frm1'].document;
	img = doc.getElementById(selected);
	fnev = '<?php echo $IMGURL ?>/'+img.alt;
	window.open(fnev,'','left=100,top=100,width=600,height=500,resizable=yes,scrollbars=yes');
  }
}
// js main program
selected = -1;
</script>
</body>
</html>	
<?php
		break;
}
?>