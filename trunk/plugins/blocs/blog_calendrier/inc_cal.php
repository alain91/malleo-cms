<?php
define('PROTECT',true);
$root = '../../../';
$lang=array();
require_once($root.'chargement.php');
load_lang('time');
$style_name=load_style();
require_once($root.'plugins/modules/blog/prerequis.php');
// Date demandee
// format : mm/AAAA
// Si non fournis on utilise la date du jour
if (isset($_POST['date'])||isset($_GET['date']))
{
	$date = (isset($_GET['date']))? $_GET['date']:$_POST['date'];
	$D = explode('/',$date);

	$dd = mktime(0,0,1,$D[1],$D[0],$D[2]);
	$dd = explode('/',date('j/n/Y/t',$dd));
}else{
	$dd = explode('/',date('j/n/Y/t',$session->time));
}
$module=(isset($_POST['module']))?$_POST['module']:((isset($_GET['module']))?$_GET['module']:'blog');
// 1er jour du mois demande
$DayOne = date('D',mktime(0,0,0,$dd[1],1,$dd[2]));
// jours anglais
$JA = array('Mon','Tue','Wed','Thu','Fri','Sat','Sun','Mon','Tue','Wed','Thu','Fri','Sat','Sun','Mon','Tue','Wed','Thu','Fri','Sat','Sun',			'Mon','Tue','Wed','Thu','Fri','Sat','Sun','Mon','Tue','Wed','Thu','Fri','Sat','Sun','Mon','Tue','Wed','Thu','Fri','Sat','Sun');
// Recherche des billets du mois demande
$sql = 'SELECT DISTINCT date_parution FROM '.TABLE_BLOG_BILLETS.' as b
		LEFT JOIN '.TABLE_BLOG_CATS.' as c 
		ON (b.id_cat=c.id_cat) 
		WHERE date_parution>'.mktime(0,0,0,$dd[1],1,$dd[2]).' 
		AND date_parution<'.mktime(0,0,0,$dd[1],$dd[3],$dd[2]).' 
		AND c.module="'.str_replace("\'","''",$module).'" 
		ORDER BY date_parution ASC';
if (!$resultat = $c->sql_query($sql)) message_die(E_ERROR,502,__FILE__,__LINE__,$sql);
$lb = array();
if ($c->sql_numrows($resultat) > 0)
{
	while($row = $c->sql_fetchrow($resultat))
	{
		$day = date('j',$row['date_parution']);
		$lb[$day] = 1;
	}
}
		
// Liste des cases dispos
$cases = array();
$la = false;
$day = 1;
for ($j=0;$j<42;$j++)
{
	$class = ($JA[$j] == 'Sat' || $JA[$j] == 'Sun')?'row3 center':'row2 center';
	// Si le 1er jour correspond à la case on démarre la lecture
	if ($JA[$j] == $DayOne || $la == true)
	{
		if (isset($lb[$day]))
		{
			$chiffre = '<a href="'.formate_url('index.php?module='.$module.'&mode=liste&date='.$day.'/'.$dd[1].'/'.$dd[2]).'" class="cat"><b>'.$day.'</b></a>';
			$class='row3 center';
		}else $chiffre = $day;
		
		// On a trouve le 1er jour du mois donc on active le listing du mois
		if ($JA[$j] == $DayOne) $la = true;
		// Le mois est finis donc on stop la numerotation
		if ($day == $dd[3]) $la = false;
		// On s'assure de ne pas boucler
		$DayOne = 99;
		$day++;
	}else{
		$chiffre = '';
	}
	$cases[$j]['class'] = $class;
	$cases[$j]['chiffre'] = $chiffre;
}
// Renvoie le numéro du jour
$m = 0;
function Cal_NumDuJour()
{
	global $m,$cases;
	$sortie = $m;
	$m++;
	return  $cases[$sortie];
}
?>
<div class="center">
<table>
<tr>
	<td class="center"><a href="javascript:;" class="cal" onclick="RequeteAjax('plugins/blocs/blog_calendrier/inc_cal.php', 'POST', 'module=<?php echo $module; ?>&date=<?php echo '1/'.($dd[1]-1).'/'.$dd[2] ; ?>', 'espace_calendrier_blog', 'InsererDansDIV');">&laquo;</a></td>
	<td colspan="5" class="center"><?php echo $lang['mois'][$dd[1]].' '.$dd[2]; ?></td>
	<td class="center"><a href="javascript:;" class="cal" onclick="RequeteAjax('plugins/blocs/blog_calendrier/inc_cal.php', 'POST', 'module=<?php echo $module; ?>&date=<?php echo '1/'.($dd[1]+1).'/'.$dd[2] ; ?>', 'espace_calendrier_blog', 'InsererDansDIV');">&raquo;</a></td>
</tr>
<tr>
	<th><?php echo $lang['Tjour'][1]; ?></th>
	<th><?php echo $lang['Tjour'][2]; ?></th>
	<th><?php echo $lang['Tjour'][3]; ?></th>
	<th><?php echo $lang['Tjour'][4]; ?></th>
	<th><?php echo $lang['Tjour'][5]; ?></th>
	<th><?php echo $lang['Tjour'][6]; ?></th>
	<th><?php echo $lang['Tjour'][7]; ?></th>
</tr>
<?php
$lignes= ($cases[35]['chiffre'] != '')?6:5;
for ($sem=1;$sem<=$lignes;$sem++)
{
	echo '<tr>';
	for ($j=1;$j<=7;$j++)
	{
		$case = Cal_NumDuJour();
		echo '<td class="'.$case['class'].'">'.$case['chiffre'].'</td>'; 
	}
	echo '</tr>';
}
?>
</table>
</div>