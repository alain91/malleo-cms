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
function InsererDansDIV( div , content )
{
	if ( document.getElementById ){
		document.getElementById( div ).innerHTML = content;
	}else{
		if ( document.layers ){document.div.innerHTML = content;
		}else{ document.all.div.innerHTML = content;}
	}
	return true;
}

function Goto( DATA, div)
{
	FILE = 'fonctions/fct_register.php';
	METHOD = 'POST';
	
	if( METHOD == 'GET' && DATA != null )
	{
		FILE += '?' + DATA;
		DATA = null;
	}
	var httpRequestM = null;
	if( window.XMLHttpRequest )
	{ 
		// Firefox
		httpRequestM = new XMLHttpRequest();
	}else if( window.ActiveXObject ){ 
		// Internet Explorer
		httpRequestM = new ActiveXObject( "Microsoft.XMLHTTP" );
	}else{ 
		// XMLHttpRequest non supporté par le navigateur
		return "Votre navigateur ne supporte pas les objets XMLHTTPRequest...";
	}
	httpRequestM.open( METHOD , FILE , true );
	httpRequestM.onreadystatechange = function()
										{
											if( httpRequestM.readyState == 4 )
											{
												reponse = httpRequestM.responseText;
												if (reponse == 'OK'){ 
													ok(div);
												}else{ 
													//alert(reponse);
													nok(div,reponse); 
												}
											}
										}
	if( METHOD == 'POST' ){	httpRequestM.setRequestHeader( "Content-type" , "application/x-www-form-urlencoded" );}
	httpRequestM.send( DATA );
}

function ok(div)
{
	InsererDansDIV(div,'<img src="'+ image_ok +'" border="0" />');
	InsererDansDIV('alerte','');
}
function nok(div,text)
{
	InsererDansDIV(div,'<img src="'+ image_nok +'" border="0" />');
	InsererDansDIV('alerte','<span style="color:red;">'+ text +'</span>');
}

function teste_pseudo()
{
	Goto('pseudo='+document.getElementById('pseudo').value,'rep_pseudo');
	return true;
}
function teste_mail()
{
	Goto('email='+document.getElementById('email').value,'rep_mail');
	return true;
}
function teste_pass1()
{
	Goto('pass1='+document.getElementById('pass1').value,'rep_pass1');
	return true;
}
function teste_pass2()
{
	Goto('pass1='+document.getElementById('pass1').value+'&pass2='+document.getElementById('pass2').value,'rep_pass2');
	return true;
}