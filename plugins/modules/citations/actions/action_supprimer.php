<?php
defined('CITATIONS_PATH') OR die("Tentative de Hacking");

class action_supprimer extends Action
{
	function init()
	{
		global $droits,$module;
	}
	
	function run()
	{
		global $citations;
		
		if (!isset($_GET['confirme']) || $_GET['confirme'] != 1){
			error404(520);break;
		}
		if (!session_id()) @session_start();
		if (!array_key_exists('jeton',$_GET) 
			|| $_GET['jeton'] != $_SESSION['jeton'] 
			|| time() - $_SESSION['jeton_timestamp'] >= VALIDITE_JETON){
			error404(56);
		}else{
			$blog->clean($_GET);
			$blog->infos_billet();
			if (!$droits->check($module,0,'supprimer') && ($blog->auteur != $user['user_id'])){
				error404(520);
				exit;
			}
			$blog->supprime_billet();
		}

	}
}


?>