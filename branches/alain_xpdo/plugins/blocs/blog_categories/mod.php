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
require_once($root.'plugins/modules/blog/prerequis.php');
if (!isset($blog))
{
	include_once($root.'plugins/modules/blog/class_blog.php');
	$blog = new blog();
}
$blog->liste_categories = $cache->appel_cache('listing_blog_cat');

$tpl->set_filenames(array(
	  'blog_categories' => $root.'plugins/blocs/blog_categories/html/bloc_blog_categories.html'
));
if (!isset($module))$module='blog';
if (is_array($blog->liste_categories))
{
	foreach($blog->liste_categories as $key=>$val)
	{
		if ($val['module']==$module)
		{
			$tpl->assign_block_vars('blog_liste_cat', array(
				'IMAGE'	=> ($val['image_cat'] != '')? 'data/icones_cat_blog/'.$val['image_cat']:'',
				'TITRE'	=> $val['titre_cat'],
				'NBRE'	=> $val['nbre_billets'],
				'URL'	=> formate_url('mode=liste&categorie='.$val['id_cat'],true)
			));
		}
	}
}
?>