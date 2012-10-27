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
function InsererDsDIV( div , content )
{
	if ( document.getElementById ){
		document.getElementById( div ).innerHTML = content;
	}else{
		if ( document.layers ){
			document.div.innerHTML = content;
		}else{ 
			document.all.div.innerHTML = content;}
	}
	return true;
}
function ChangerValeurDiv( div , content )
{
	if ( document.getElementById ){
		document.getElementById( div ).value = content;
	}else{
		if ( document.layers ){
			document.div.value = content;
		}else{ 
			document.all.div.value = content;}
	}
	return true;
}
function ChangerOptions( div , content ){
	if ( document.getElementById ){
			var obj = document.getElementById( div );
	}else{
		if ( document.layers ){
			var obj = document.div;
		}else{ 
			var obj = document.all.div;}
	}
	var liste = content.split(',');
	for (no=0;no<10;no++){
		if (no<liste.length){
			obj.options[no] = new Option(liste[no],liste[no]);
		}else{
			obj.options[no] = null;
		}
	}
	return true;
}
function RequeteAjax(FILE, METHOD, DATA, div, type)
{
	if( METHOD == 'GET' && DATA != null )
	{
		FILE += '?' + DATA;
		DATA = null;
	}
	DATA = DATA.replace(/amp;/gi,"&");
	var httpRequestM = null;
	if( window.XMLHttpRequest )
	{ 
		httpRequestM = new XMLHttpRequest();
	}else if( window.ActiveXObject ){ 
		httpRequestM = new ActiveXObject( "Microsoft.XMLHTTP" );
	}else{ 
		return "Votre navigateur ne supporte pas les objets XMLHTTPRequest...";
	}
	httpRequestM.open( METHOD , FILE , true );
	httpRequestM.onreadystatechange = function()
										{
											if( httpRequestM.readyState == 4 )
											{
												switch (type)
												{
													case 'ChangerValeurDiv' : ChangerValeurDiv( div , httpRequestM.responseText ); break;
													case 'ChangerOptions':ChangerOptions( div , httpRequestM.responseText );break;
													case 'InsererDansDIV' : 
													default: InsererDsDIV( div , httpRequestM.responseText );
														break;
												}
											}
										}
	if( METHOD == 'POST' ){	httpRequestM.setRequestHeader( "Content-type" , "application/x-www-form-urlencoded" );}
	httpRequestM.send( DATA );
}

if ( document.getElementById ){
	var membres = document.getElementById("listing_membres");
	var tmp = document.getElementById("temp_liste");
}else{
	if ( document.layers ){
		var membres = document.listing_membres;
		var tmp = document.temp_liste;
	}else{
		var membres = document.all.listing_membres;
		var tmp = document.all.temp_liste;
	}
}

function ListerUsers(data)
{	
	membres.innerHTML = "";
	tmp.value = "";
	if (membres.childNodes.length>0){
		for (var i=0; i<membres.childNodes.length; i++) {
			membres.removeChild(membres.lastChild);
		}
	}
	if (data!=''){
		RequeteAjax('fonctions/includes/inc_liste_users.php', 'POST' , 'RequeteAjax='+escape(data), 'temp_liste',  'ChangerValeurDiv');
		setTimeout("afficher_liste_users()", 200);
	}
}
function afficher_liste_users(){
	var liste = tmp.value;
	if (liste.length>0){
		var elmts = liste.split(/##/gi);
		for (var i=0; i<elmts.length; i++) {
			var user = elmts[i];
			var tempDiv = document.createElement("div");
			tempDiv.innerHTML = user;
			tempDiv.onclick = makeChoice;
			tempDiv.className = "suggestions";
			membres.appendChild(tempDiv);
		}
	}
}
function makeChoice(evt) {
	var thisDiv = (evt) ? evt.target : window.event.srcElement;
	if ( document.getElementById ){
		document.getElementById("membre").value = thisDiv.innerHTML;
		document.getElementById("listing_membres").innerHTML = "";
	}else{
		if ( document.layers ){
			document.membre.value = thisDiv.innerHTML;
			document.listing_membres.innerHTML = "";
		}else{ 
			document.all.membre.value = thisDiv.innerHTML;
			document.all.listing_membres.innerHTML = "";}
	}
}