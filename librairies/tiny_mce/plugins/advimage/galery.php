<?php
/*
 ROBIT BT plugins for tinyCME editor
 Image galery browser, image delete, image upload,thumblair generator.
 Accept only one image folder
 Required: PHP4

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
$style_name=load_style();
load_lang('tinymce');
$cf->config['activer_menuh'] = 0;

//
// CHARGEMENT des outils images

require_once($root.'class/class_image.php');
$image = new image();

$image->ext_ok = array('jpg','jpeg','png','gif','ico','bmp');
$image->file_max_size = 10000000;
$image->file_max_largeur = 800;
$image->file_max_hauteur = 1100;
$image->rep_taille_max = 9000000;

$IMGFOLDER = $root.'data/files/'.$user['user_id'].'/';
$IMGURL = 'http://'.$cf->config['adresse_site'].$cf->config['path'].'data/files/'.$user['user_id'].'/';

// Creation du dossier data/files/ID du User/
if (!is_dir($IMGFOLDER)){
	$image->creer_dossier_image($IMGFOLDER);
	$image->creer_dossier_image($IMGFOLDER.'thumbs/');	
}

$act =		(isset($_GET['act']))? $_GET['act']:((isset($_POST['act']))?$_POST['act']:'');
$fname =	(isset($_POST['fname']))? $_POST['fname']:'';
$msg = false;

switch ($act){
	case 'upload':
		if (isset($_FILES) && is_array($_FILES)) 
		{
			$image->post = $_FILES;
			$image->nom_champ = 'upload';
			$image->destination = $IMGFOLDER;
			$msg = $image->verification_upload();
 			if(strlen($msg)<2){
				header('Location: ./galery.php');
			}
		}
		break;
	case 'delete':
		if (file_exists($IMGFOLDER.$_POST['fname']))@unlink($IMGFOLDER.$_POST['fname']);
		if (file_exists($IMGFOLDER.'thumbs/'.$_POST['fname']))@unlink($IMGFOLDER.'thumbs/'.$_POST['fname']);
		header('location: ./galery.php');
		break;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-2" />
<link href="<?php echo $root; ?>styles/<?php echo $style_name; ?>/style.css" rel="stylesheet" type="text/css" />
</head>
<body>
	<div id="contenu_blocs">
	<form name="imgupload" method="post" action="./galery.php" enctype="multipart/form-data">
	<center>
	<div class="bloc">
		<table class="standard" width="100%">
		<tr>
			<th colspan="2"><?php echo $lang['L_UPLOADER_PHOTO']; ?></th>
		</tr>
		<tr>
			<td class="row1" colspan="2">
				<input type=file size="30" name=upload>&nbsp;&nbsp;
				<input type=hidden name=act value="upload">
				<input type=hidden name=fname value="">
				<button type=button onclick="uploadimg();"><?php echo $lang['L_ENVOYER']; ?></button>
			</td>
		</tr>
		<tr>
			<td class="row2" colspan="2">
<div style="width:100%;height:340px;overflow:auto;align:center;">
<?php
if ($msg!=false){
?>
	<div class="admin">
		<table class="Alerte" width="80%" align="center">
			<tr>
				<td class="rowAlerte center" height="20"><b><?php echo $msg; ?></b></td>
			</tr>
		</table>
	</div>
	<br />
<?php
}
?>
<?php
// Listing des fichiers 
$handle = opendir($IMGFOLDER);
$file_lista=array();
while ($file = readdir($handle)) {
	// Extension du fichier en cours
	$ext = pathinfo($file);
	$ext = (array_key_exists('extension',$ext))? strtolower($ext['extension']):'';
	if ($file[0] != '.' && !is_dir($file) && $file!='.htaccess' 
		&& in_array($ext,$image->ext_ok) && ($file != 'index.html') ) {
		$file_lista[]=$file;
	}
};
closedir($handle);

// Pas d'images
if (count($file_lista)> 0) {		
	$kepdb= -1;
	$coldb = 0;
	print '<table border="0" cellspacin="0" cellpadding="0" align="center"><tr>'."\n";
	
	// Parcours des fichiers
	for ( $a=0; $a<sizeof($file_lista); $a++) {
	
		$fnev = $IMGFOLDER.'/'.$file_lista[$a];
		$kepdb++;
		$coldb++;
		print '<td width="110" height="110" onclick="parent.selectimg('.$kepdb.')" align="center" valign="center" style="border:solid 1px grey;padding:5px; cursor:pointer;">'."\n";

		// Taille relle de l'image
		$imginfo = GetImageSize($fnev);
		$x = $imginfo[0];
		$y = $imginfo[1];
		
		// Si l'image est trop grande on prend son thumbnail
		if (($x > 100)  || ($y > 100)){
			$thumb_url = $IMGFOLDER.'thumbs/'.$file_lista[$a];
			if (!file_exists($thumb_url))  $image->creer_miniature($fnev,$thumb_url,100,100);
			print '<img src="'.$thumb_url.'" alt="'.$file_lista[$a].'" id="'.$kepdb.'" />';
			
		// SINON on la prend directement
		}else{
			$belyeg = $IMGFOLDER.$file_lista[$a];
			$belyegurl = $IMGURL.rawurlencode($file_lista[$a]);
			print '<img src="'.$belyegurl.'" alt="'.$file_lista[$a].'" id="'.$kepdb.'" />';
		}
		print '</td>'."\n";
		if ($coldb == 4){
			print '</tr><tr>'."\n";
			$coldb = 0;
		}
	}
	print '</tr></table>'."\n";
}
?>
</div>
<br />
			</td>
		</tr>
		<tr>
			<th colspan="2"><?php echo $lang['L_ACTION_PHOTO']; ?></th>
		</tr>
		<tr>
			<td class="row1"><div id="imgalt"><?php echo $lang['L_CLIQUER_PHOTO']; ?></div></td>
			<td class="row2" width="230">	
				<button type=button onclick=viewimg();><?php echo $lang['L_APERCU']; ?></button>
				<button type=button onclick=insertimg();><?php echo $lang['L_INSERER']; ?></button>
				<button type=button onclick=deleteimg();><?php echo $lang['L_EFFACER']; ?></button>
			</td>
		</tr>
		</table>
		</div>
	</center>
	</div>
	</form>

<script language="JavaScript">
function selectimg(i) {
  if (selected >= 0) {
	img = document.getElementById(selected);
	img.parentNode.style.border= "solid grey 1px";
  }
  selected = i;
  img = document.getElementById(i);
  img.parentNode.style.border= "solid white 2px";
  document.getElementById('imgalt').innerHTML = img.alt;
}
function deleteimg() {
  if (selected >= 0) {
	img = document.getElementById(selected);
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
	img = document.getElementById(selected);
	opener.document.forms[0].src.value = '<?php echo $IMGURL ?>'+img.alt;
	window.close();
  }
}
function viewimg() {
  if (selected >= 0) {
	img = document.getElementById(selected);
	fnev = '<?php echo $IMGURL ?>'+img.alt;
	window.open(fnev,'','left=100,top=100,width=600,height=500,resizable=yes,scrollbars=yes');
  }
}
// js main program
selected = -1;
</script>
</body>
</html>