<?php

defined('CITATIONS_PATH') OR die("Tentative de Hacking");

class action_editer extends Action
{
	function run()
	{
		global $session,$tpl,$droits,$module,$img,$lang,$root,$cf,$cache;
		
		$tpl->set_filenames(array(
			'citations' => CITATIONS_PATH.'/html/form.html',
		));

	}
}


?>