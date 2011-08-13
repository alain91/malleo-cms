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

defined('UPLOAD_PATH') OR die("Tentative de Hacking");

class action_lister extends Action
{
	function init()
	{
		global $droits,$module,$user;

		if (!$droits->check($module,0,'voir') || empty($user['user_id']))
		{
			error404(518);
			exit;
		}
	}

	function run()
	{
		global $session,$tpl,$droits,$module,$img,$lang,$user,$root,$cf;

		$upload = Upload::instance();

		$userdir = UPLOAD_ROOTDIR.'/user_'.$user['user_id'];
		$dir = $root.$userdir;
		if (!file_exists($dir) || !is_dir($dir))
			mkdir($dir);

		$ok = true;
		if (!empty($_FILES))
		{
			list($ok,$message) = $upload->save_file($_FILES['userfile'], $dir);
		}

		$tpl->set_filenames(array(
			'upload' => UPLOAD_PATH.'/html/liste.html',
		));

		$tpl->assign_vars(array(
			'I_DELETE' 	=> $img['effacer'],
			'L_DELETE'	=> htmlentities('Supprimer'),
			'L_CONFIRM_DELETE' => htmlentities('Confirmer la suppression'),
			'DIR' => $userdir,
			));

		if (empty($ok))
		{
			$tpl->assign_block_vars('error', array(
				'MESSAGE'=>utf8_encode($message),
			));
		}

		$maxsize1 = intval($cf->config['upload_filemaxsize']);
		$filemaxsize = $maxsize1*1024*1024;
		$maxsize2 = intval($cf->config['upload_dirmaxsize']);
		$dirmaxsize = $maxsize2*1024*1024;

		$tpl->assign_vars(array(
			'FILEMAXSIZE' => $maxsize1,
			));

		$freemax = 0;
		$dirsize = $upload->get_size($dir);
		if ($dirsize < $dirmaxsize)
		{
			$freemax = ceil(($dirmaxsize - $dirsize)/1000)*1000;
			$max = min($freemax, $filemaxsize);
			$tpl->assign_block_vars('notfull', array('MAXSIZE' => $max));
		}

		// Creation du jeton de securite
		if (!session_id()) @session_start();
		$jeton = md5(uniqid(rand(), TRUE));
		$_SESSION['jeton'] = $jeton;
		$_SESSION['jeton_timestamp'] = $session->time;

		$rows = $upload->recuperer_tous($dir);
		if (!empty($rows)) foreach ($rows as $row)
		{
			$tpl->assign_block_vars('quotes', array(
				'MTIME' => date('r',$row->mtime),
				'NOM' => htmlentities($row->nom),
				'URL' => htmlentities($userdir.'/'.$row->nom),
				'SIZE' => intval($row->size),
			));
			$tpl->assign_block_vars('quotes.delete', array(
				'U_DELETE' => formate_url('action=supprimer&nom='.urlencode($row->nom).'&jeton='.$jeton,true)
			));
		}

		// Titre de page
		$tpl->titre_navigateur = $module;
		$tpl->titre_page = htmlentities($module . ' (place disponible : '.($freemax/1000).' Ko)');
	}

}
?>