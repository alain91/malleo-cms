<?php

if ( !defined('PROTECT') )
{
	die("Tentative de Hacking");
}
$tpl->set_filenames(array('PAGE_BAS' => $root . $style_path . $style_name.'/_page_bas.html'));

$tpl->pparse('PAGE_BAS');
?>