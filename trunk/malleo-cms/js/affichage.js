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


if(chargement_unique != 'OK'){
	var chargement_unique = 'OK';
	function afficher_code(id,afficher,masquer) {
		if (document.getElementById(id).style.height != '100%'){
			document.getElementById(id).style.height = '100%';
			document.getElementById('zone_'+id).innerHTML = masquer;
		}else{
			document.getElementById(id).style.height = '80px';
			document.getElementById('zone_'+id).innerHTML = afficher;
		}
	}

	function afficher_masquer(id){
		if (document.getElementById(id).style.display == 'none'){
			document.getElementById(id).style.display = 'block';
		}else{
			document.getElementById(id).style.display = 'none';
		}
	}

	function cocher_checkbox(container_id) {
		var rows = document.getElementById(container_id).getElementsByTagName('tr');
		for ( var i = 0; i < rows.length; i++ ){
			checkbox = rows[i].getElementsByTagName( 'input' )[0];
			if ( checkbox && checkbox.type == 'checkbox' 
							&& checkbox.checked == false 
								&& checkbox.disabled == false ){
					checkbox.checked = true;
			}
		}
		return true;
	}

	function decocher_checkbox(container_id) {
		var rows = document.getElementById(container_id).getElementsByTagName('tr');
		for ( var i = 0; i < rows.length; i++ ){
			checkbox = rows[i].getElementsByTagName( 'input' )[0];
			if ( checkbox && checkbox.type == 'checkbox'){
				if(checkbox.checked == true ){
					checkbox.checked = false;
				}
			}
		}
		return true;
	}

	function redirect(page){
		window.location = page;
	}

	function pause_before_redirect(time,page){
		setTimeout('redirect("'+page+'")',(time*1000));
	}
	
	function confirmLink(question, url, fichier)
	{
		if (typeof(window.opera) != 'undefined') {
		return true;
		}
		var url_confirmee = confirm(question + '\n' + fichier);
		if (url_confirmee) {
			url.href += '&confirme=1';
		}
		return url_confirmee;
	}
	
	function AddObserver(element, name, observer){
	    if (element.addEventListener) {
	      element.addEventListener(name, observer, false);
	    } else if (element.attachEvent) {
	      element.attachEvent('on' + name, observer);
	    }
	}

	document.write('<script type="text/javascript" src="librairies/lightbox/prototype.js"></script>');
	document.write('<script type="text/javascript" src="librairies/lightbox/scriptaculous.js?load=effects,builder"></script>');
	document.write('<script type="text/javascript" src="librairies/lightbox/lightbox.js"></script>');
	document.write('<link rel="stylesheet" href="librairies/lightbox/css/lightbox.css" type="text/css" media="screen" />');
}