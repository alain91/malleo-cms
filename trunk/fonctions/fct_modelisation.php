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
if (isset($_POST['date']))
{
	$params = explode('|',$_POST['date']);
	$d['Y'] = 2008; $d['n'] = 1; $d['j'] =1; 
	$d['H'] = $d['i'] = $d['s'] = 0;
	foreach($params as $key=>$val)
	{	
		$v = explode(':',$val);
		switch ($v[0])
		{
			case 'Y': $d['Y'] = $v[1]; break;
			case 'n': $d['n'] = $v[1]; break;
			case 'j': $d['j'] = $v[1]; break;
			case 'H': $d['H'] = $v[1]; break;
			case 'i': $d['i'] = $v[1]; break;
			case 's': $d['s'] = $v[1]; break;
		}
	}
	//  H i s n j Y
	echo mktime($d['H'],$d['i'],$d['s'],$d['n'],$d['j'],$d['Y']);
}


?>