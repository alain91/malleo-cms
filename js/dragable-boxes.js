	/************************************************************************************************************
	(C) www.dhtmlgoodies.com, January 2006
	
	This is a script from www.dhtmlgoodies.com. You will find this and a lot of other scripts at our website.	
	
	Version:	1.0	: January 16th - 2006
				1.1 : January 31th - 2006 - Added cookie support - remember rss sources
				1.2 : July 13th - 2006 - Fixed a problem in the createRSSBoxesFromCookie function
				
	Terms of use:
	You are free to use this script as long as the copyright message is kept intact. However, you may not
	redistribute, sell or repost it without our permission.
	
	Thank you!
	
	www.dhtmlgoodies.com
	Alf Magne Kalleland
	
	************************************************************************************************************/		
	
	/* USER VARIABLES */
	
	var columnParentBoxId = 'floatingBoxParentContainer';	// Id of box that is parent of all your dragable boxes
	var transparencyWhenDragging = true;
	var txt_editLink = 'Edit';
	var txt_editLink_stop = 'End edit';
	var autoScrollSpeed = 1;	// Autoscroll speed	- Higher = faster	
	var dragObjectBorderWidth = 1;	// Border size of your RSS boxes - used to determine width of dotted rectangle
	

	/* END USER VARIABLES */
	
	
	
	var columnParentBox;
	var dragableBoxesObj;
	
	var ajaxObjects = new Array();
	
	var boxIndex = 0;	
	var autoScrollActive = false;
	var dragableBoxesArray = new Array();
	
	var dragDropCounter = -1;
	var dragObject = false;
	var dragObjectNextSibling = false;
	var dragObjectParent = false;
	var destinationObj = false;
	
	var mouse_x;
	var mouse_y;
	
	var el_x;
	var el_y;	
	
	var rectangleDiv;
	var okToMove = true;

	var documentHeight = false;
	var documentScrollHeight = false;
	var dragableAreaWidth = false;
		
	var opera = navigator.userAgent.toLowerCase().indexOf('opera')>=0?true:false;
	var cookieCounter=0;
	var cookieRSSSources = new Array();
	
	var staticObjectArray = new Array();
	var id_module;
	
	function autoScroll(direction,yPos)
	{
		if(document.documentElement.scrollHeight>documentScrollHeight && direction>0)return;
		if(opera)return;
		window.scrollBy(0,direction);
		if(!dragObject)return;
		
		if(direction<0){
			if(document.documentElement.scrollTop>0){
				dragObject.style.top = (el_y - mouse_y + yPos + document.documentElement.scrollTop) + 'px';		
			}else{
				autoScrollActive = false;
			}
		}else{
			if(yPos>(documentHeight-50)){	
				dragObject.style.top = (el_y - mouse_y + yPos + document.documentElement.scrollTop) + 'px';			
			}else{
				autoScrollActive = false;
			}
		}
		if(autoScrollActive)setTimeout('autoScroll('+direction+',' + yPos + ')',5);
	}
		
	function initDragDropBox(e)
	{
		
		
		dragDropCounter = 1;
		if(document.all)e = event;
		
		if (e.target) source = e.target;
			else if (e.srcElement) source = e.srcElement;
			if (source.nodeType == 3) // defeat Safari bug
				source = source.parentNode;
		
		if(source.tagName.toLowerCase()=='img' || source.tagName.toLowerCase()=='a' || source.tagName.toLowerCase()=='input' || source.tagName.toLowerCase()=='td' || source.tagName.toLowerCase()=='tr' || source.tagName.toLowerCase()=='table')return;
		
	
		mouse_x = e.clientX;
		mouse_y = e.clientY;	
		var numericId = this.id.replace(/[^0-9]/g,'');
		el_x = getLeftPos(this.parentNode.parentNode)/1;
		el_y = getTopPos(this.parentNode.parentNode)/1 - document.documentElement.scrollTop;
			
		dragObject = this.parentNode.parentNode;
		
		documentScrollHeight = document.documentElement.scrollHeight + 100 + dragObject.offsetHeight;
		
		
		if(dragObject.nextSibling){
			dragObjectNextSibling = dragObject.nextSibling;
			if(dragObjectNextSibling.tagName!='DIV')dragObjectNextSibling = dragObjectNextSibling.nextSibling;
		}
		dragObjectParent = dragableBoxesArray[numericId]['parentObj'];
			
		dragDropCounter = 0;
		initDragDropBoxTimer();	
		
		return false;
	}
	
	
	function initDragDropBoxTimer()
	{
		if(dragDropCounter>=0 && dragDropCounter<10){
			dragDropCounter++;
			setTimeout('initDragDropBoxTimer()',10);
			return;
		}
		if(dragDropCounter==10){
			mouseoutBoxHeader(false,dragObject);
		}
		
	}

	function moveDragableElement(e){
		if(document.all)e = event;
		if(dragDropCounter<10)return;
		
		if(document.all && e.button!=1 && !opera){
			stop_dragDropElement();
			return;
		}
		
		if(document.body!=dragObject.parentNode){
			dragObject.style.width = (dragObject.offsetWidth - (dragObjectBorderWidth*2)) + 'px';
			dragObject.style.position = 'absolute';	
			dragObject.style.textAlign = 'left';
			dragObject.style.width = '250px'; /* SP fixe la largeur du bloc flottant */
			if(transparencyWhenDragging){	
				dragObject.style.filter = 'alpha(opacity=50)';
				dragObject.style.opacity = '0.5';
			}	
			dragObject.parentNode.insertBefore(rectangleDiv,dragObject);
			rectangleDiv.style.display='block';
			document.body.appendChild(dragObject);

			rectangleDiv.style.width = dragObject.style.width;
			rectangleDiv.style.height = (dragObject.offsetHeight - (dragObjectBorderWidth*2)) + 'px'; 
			
		}
		
		if(e.clientY<50 || e.clientY>(documentHeight-50)){
			if(e.clientY<50 && !autoScrollActive){
				autoScrollActive = true;
				autoScroll((autoScrollSpeed*-1),e.clientY);
			}
			
			if(e.clientY>(documentHeight-50) && document.documentElement.scrollHeight<=documentScrollHeight && !autoScrollActive){
				autoScrollActive = true;
				autoScroll(autoScrollSpeed,e.clientY);
			}
		}else{
			autoScrollActive = false;
		}		

		
		var leftPos = e.clientX;
		var topPos = e.clientY + document.documentElement.scrollTop;
		
		dragObject.style.left = (e.clientX - mouse_x + el_x) + 'px';
		dragObject.style.top = (el_y - mouse_y + e.clientY + document.documentElement.scrollTop) + 'px';
								

		
		if(!okToMove)return;
		okToMove = false;

		destinationObj = false;	
		rectangleDiv.style.display = 'none'; 
		
		var objFound = false;
		var tmpParentArray = new Array();
		
		// RECHERCHER de la position des blocs
		if(!objFound){
			for(var no=1;no<dragableBoxesArray.length;no++){
				if(dragableBoxesArray[no]['obj']==dragObject)continue;
				tmpParentArray[dragableBoxesArray[no]['obj'].parentNode.id] = true;
				if(!objFound){
					var tmpX = getLeftPos(dragableBoxesArray[no]['obj']);
					var tmpY = getTopPos(dragableBoxesArray[no]['obj']);

					// collision avec une autre cellule
					if(				leftPos>tmpX 
									&& leftPos<(tmpX + dragableBoxesArray[no]['obj'].offsetWidth) 
									&& topPos>(tmpY-20) 
									&& topPos<(tmpY + (dragableBoxesArray[no]['obj'].offsetHeight/2))){
						destinationObj = dragableBoxesArray[no]['obj'];
						destinationObj.parentNode.insertBefore(rectangleDiv,dragableBoxesArray[no]['obj']);
						rectangleDiv.style.display = 'block';
						objFound = true;
						break;
					}
					
					if(				   leftPos>tmpX 
									&& leftPos<(tmpX + dragableBoxesArray[no]['obj'].offsetWidth) 
									&& topPos>=(tmpY + (dragableBoxesArray[no]['obj'].offsetHeight/2)) 
									&& topPos<(tmpY + dragableBoxesArray[no]['obj'].offsetHeight/2)){
						if(dragableBoxesArray[no]['obj'].nextSibling){
						// Juste avant  une cellulle existante
							
							destinationObj = dragableBoxesArray[no]['obj'].nextSibling;
							if(!destinationObj.tagName)destinationObj = destinationObj.nextSibling;
							if(destinationObj!=rectangleDiv)destinationObj.parentNode.insertBefore(rectangleDiv,destinationObj);
						}else{
							destinationObj = dragableBoxesArray[no]['obj'].parentNode;
							dragableBoxesArray[no]['obj'].parentNode.appendChild(rectangleDiv);
						}
						rectangleDiv.style.display = 'block';
						objFound = true;
						break;					
					}
					
					
					if(!dragableBoxesArray[no]['obj'].nextSibling 
						// Juste après une cellulle existante	
						&& leftPos>tmpX 
						&& leftPos<(tmpX + 20 ) //dragableBoxesArray[no]['obj'].offsetWidth)
						&& topPos>(tmpY + 20 )){ //(dragableBoxesArray[no]['obj'].offsetHeight))){
						destinationObj = dragableBoxesArray[no]['obj'].parentNode;
						dragableBoxesArray[no]['obj'].parentNode.appendChild(rectangleDiv);	
						rectangleDiv.style.display = 'block';	
						objFound = true;				
					}
				}
			}
		}
		// RECHERCHER de la position des colonnes
		if(!objFound){
			
			for(var no=1;no<=numberOfColumns;no++){
				if(!objFound){
					var obj = document.getElementById('dragableBoxesColumn' + no);			
					var left = getLeftPos(obj)/1;						
					var top = getTopPos(obj)/1;						
					var width = obj.offsetWidth;
					var height = obj.offsetheight;
					if((leftPos>left && leftPos<(left+width))){
						destinationObj = obj;
						obj.appendChild(rectangleDiv);
						rectangleDiv.style.display='block';
						objFound=true;		
					}				
				}
			}		
			
		}
	

		setTimeout('okToMove=true',5);
		
	}
	
	function stop_dragDropElement()
	{
		
		if(dragDropCounter<10){
			dragDropCounter = -1
			return;
		}
		dragDropCounter = -1;
		if(transparencyWhenDragging){
			dragObject.style.filter = null;
			dragObject.style.opacity = null;
		}		
		dragObject.style.position = 'static';
		dragObject.style.width = null;
		var numericId = dragObject.id.replace(/[^0-9]/g,'');
		if(destinationObj && destinationObj.id!=dragObject.id){
			
			if(destinationObj.id.indexOf('dragableBoxesColumn')>=0){
				destinationObj.appendChild(dragObject);
				dragableBoxesArray[numericId]['parentObj'] = destinationObj;
			}else{
				destinationObj.parentNode.insertBefore(dragObject,destinationObj);
				dragableBoxesArray[numericId]['parentObj'] = destinationObj.parentNode;
			}
		}else{
			if(dragObjectNextSibling){
				dragObjectParent.insertBefore(dragObject,dragObjectNextSibling);	
			}else{
				dragObjectParent.appendChild(dragObject);
			}	
		}
	

		
		autoScrollActive = false;
		rectangleDiv.style.display = 'none'; 
		dragObject = false;
		dragObjectNextSibling = false;
		destinationObj = false;
		
		// A décommenter si on veut un enregistrement auto à chaque mouvement de blocs
		//SauverMAP();
		documentHeight = document.documentElement.clientHeight;	
	}

	function SauverMAP()
	{
		cookieCounter = 0;
		var chaine_sauvee = '';
		var tmpUrlArray = new Array();
		for(var no=1;no<=numberOfColumns;no++)
		{
			var parentObj = document.getElementById('dragableBoxesColumn' + no);
			
			var items = parentObj.getElementsByTagName('DIV');
			if(items.length==0)continue;
			
			var item = items[0];
			
			var tmpItemArray = new Array();
			while(item){
				var boxIndex = item.id.replace(/[^0-9]/g,'');
				if(item.id!='rectangleDiv'){
					tmpItemArray[tmpItemArray.length] = boxIndex;
				}	
				item = item.nextSibling;			
			}
			
			var columnIndex = no;
			
			for(var no2=tmpItemArray.length-1;no2>=0;no2--){
				var boxIndex = tmpItemArray[no2];
				var uniqueIdentifier = dragableBoxesArray[boxIndex]['uniqueIdentifier'];
				if (document.getElementById('dragableBox' + boxIndex).style.display !='none')
				{
					chaine_sauvee = chaine_sauvee + columnIndex + ":" + uniqueIdentifier + "|";
				}
			}
		}
		document.getElementById('debug').innerHTML = '<span class="alerte">Enregistrement en cours</span>';
		Goto('admin.php', 'POST' , 'IRQ=nouveau_modele&SaveMAP='+chaine_sauvee+'&id_modele='+document.getElementById('id_modele').value, 2,  'debug' );
	}
	
	function clear_debug()
	{
		document.getElementById('debug').innerHTML = '';
	}
	
	function getTopPos(inputObj)
	{		
	  var returnValue = inputObj.offsetTop;
	  while((inputObj = inputObj.offsetParent) != null){
	  	if(inputObj.tagName!='HTML')returnValue += inputObj.offsetTop;
	  }
	  return returnValue;
	}
	
	function getLeftPos(inputObj)
	{
	  var returnValue = inputObj.offsetLeft;
	  while((inputObj = inputObj.offsetParent) != null){
	  	if(inputObj.tagName!='HTML')returnValue += inputObj.offsetLeft;
	  }
	  return returnValue;
	}

	function mouseoverBoxHeader()
	{
		if(dragDropCounter==10)return;
		var id = this.id.replace(/[^0-9]/g,'');
		//document.getElementById('dragableBoxExpand' + id).style.visibility = 'visible';		
		document.getElementById('dragableBoxCloseLink' + id).style.visibility = 'visible';
	}
	function mouseoutBoxHeader(e,obj)
	{
		if(!obj)obj=this;
		
		var id = obj.id.replace(/[^0-9]/g,'');
		//document.getElementById('dragableBoxExpand' + id).style.visibility = 'hidden';		
		document.getElementById('dragableBoxCloseLink' + id).style.visibility = 'hidden';		
	}
	
	function showHideBoxContent(e,inputObj)
	{
		if(document.all)e = event;
		if(!inputObj)inputObj=this;
		
		var numericId = inputObj.id.replace(/[^0-9]/g,'');
		var obj = document.getElementById('dragableBoxContent' + numericId);
		
		obj.style.display = inputObj.src.indexOf(src_rightImage)>=0?'none':'block';
		inputObj.src = inputObj.src.indexOf(src_rightImage)>=0?src_downImage:src_rightImage
		
		dragableBoxesArray[numericId]['boxState'] = obj.style.display=='block'?1:0;
		//SauverMAP();
		setTimeout('dragDropCounter=-5',5);
	}
	
	function mouseover_CloseButton()
	{
		this.className = 'closeButton_over';	
		setTimeout('dragDropCounter=-5',5);
	}
	
	function highlightCloseButton()
	{
		this.className = 'closeButton_over';	
	}
	
	function mouseout_CloseButton()
	{
		this.className = 'closeButton';	
	}
	
	function closeDragableBox(e,inputObj)
	{
		if(!inputObj)inputObj = this;
		var numericId = inputObj.id.replace(/[^0-9]/g,'');
		document.getElementById('dragableBox' + numericId).style.display='none';	
		setTimeout('dragDropCounter=-5',5);
	}
		
	function addBoxHeader(parentObj,externalUrl,notDrabable)
	{
		var div = document.createElement('DIV');
		div.className = 'dragableBoxHeader';
		
		div.id = 'dragableBoxHeader' + boxIndex;
		div.onmouseover = mouseoverBoxHeader;
		div.onmouseout = mouseoutBoxHeader;
		if(!notDrabable){
			div.onmousedown = initDragDropBox;
			div.style.cursor = 'move';
		}
		
/* 		var image = document.createElement('IMG');
		image.id = 'dragableBoxExpand' + boxIndex;
		image.src = 'styles/BlueLight/images/webdesign_bullet.png';
		image.style.visibility = 'hidden';	
		image.style.cursor = 'pointer';
		image.onmousedown = showHideBoxContent;	
		div.appendChild(image); */
		
		var textSpan = document.createElement('SPAN');
		textSpan.id = 'dragableBoxHeader_txt' + boxIndex;
		div.appendChild(textSpan); 
				
		parentObj.appendChild(div);	

		var closeLink = document.createElement('A');
		closeLink.style.cssText = 'float:right';
		closeLink.style.styleFloat = 'right';
		closeLink.id = 'dragableBoxCloseLink' + boxIndex;
		closeLink.innerHTML = 'x';
		closeLink.className = 'closeButton';
		closeLink.onmouseover = mouseover_CloseButton;
		closeLink.onmouseout = mouseout_CloseButton;
		closeLink.style.cursor = 'pointer';
		closeLink.style.visibility = 'hidden';
		closeLink.onmousedown = closeDragableBox;
		div.appendChild(closeLink);

	}
	
	function addBoxContentContainer(parentObj,heightOfBox)
	{
		var div = document.createElement('DIV');
		div.className = 'dragableBoxContent';
		if(opera)div.style.clear='none';
		div.id = 'dragableBoxContent' + boxIndex;
		parentObj.appendChild(div);			
		if(heightOfBox && heightOfBox/1>40){
			div.style.height = heightOfBox + 'px';
			div.setAttribute('heightOfBox',heightOfBox);
			div.heightOfBox = heightOfBox;	
			if(document.all)div.style.overflowY = 'auto';else div.style.overflow='-moz-scrollbars-vertical;';
			if(opera)div.style.overflow='auto';
		}		
	}
	

	function createABox(columnIndex,heightOfBox,externalUrl,uniqueIdentifier,notDragable)
	{
		boxIndex++;
		
		var maindiv = document.createElement('DIV');
		maindiv.className = 'dragableBox';
		maindiv.id = 'dragableBox' + boxIndex;
		
		var div = document.createElement('DIV');
		div.className='dragableBoxInner';
		maindiv.appendChild(div);
		
		
		addBoxHeader(div,externalUrl,notDragable);
		addBoxContentContainer(div,heightOfBox);
		
		var obj = document.getElementById('dragableBoxesColumn' + columnIndex);		
		var subs = obj.getElementsByTagName('DIV');
		if(subs.length>0){
			obj.insertBefore(maindiv,subs[0]);
		}else{
			obj.appendChild(maindiv);
		}
		
		// SP : on met de coté l'ID du module principal
		if (uniqueIdentifier == 'module') id_module = boxIndex;
		
		dragableBoxesArray[boxIndex] = new Array();
		dragableBoxesArray[boxIndex]['obj'] = maindiv;
		dragableBoxesArray[boxIndex]['parentObj'] = maindiv.parentNode;
		dragableBoxesArray[boxIndex]['uniqueIdentifier'] = uniqueIdentifier;
		dragableBoxesArray[boxIndex]['heightOfBox'] = heightOfBox;
		dragableBoxesArray[boxIndex]['boxState'] = 0;	// Expanded
		
		staticObjectArray[uniqueIdentifier] = boxIndex;
		
		return boxIndex;
		
	}
	
	function createHelpObjects()
	{
		/* Creating rectangle div */
		rectangleDiv = document.createElement('DIV');
		rectangleDiv.id='rectangleDiv';
		rectangleDiv.style.display='none';
		document.body.appendChild(rectangleDiv);		 
	}
	
	function cancelSelectionEvent(e)
	{
		if(document.all)e = event;
		
		if (e.target) source = e.target;
			else if (e.srcElement) source = e.srcElement;
			if (source.nodeType == 3) // defeat Safari bug
				source = source.parentNode;
		if(source.tagName.toLowerCase()=='input')return true;
						
		if(dragDropCounter>=0)return false; else return true;	
		
	}
	
	function cancelEvent()
	{
		return false;
	}
	
	function initEvents()
	{
		document.body.onmousemove = moveDragableElement;
		document.body.onmouseup = stop_dragDropElement;
		document.body.onselectstart = cancelSelectionEvent;
		document.body.ondragstart = cancelEvent;	
		documentHeight = document.documentElement.clientHeight;	
		
	}
	
	/* Clear cookies */
	
	function clearCookiesForDragableBoxes()
	{
		var cookieValue = Get_Cookie(nameOfCookie);
		while(cookieValue && cookieValue!=''){
			Set_Cookie(nameOfCookie + cookieCounter,'',-500);
			cookieCounter++;
			var cookieValue = Get_Cookie(nameOfCookie + cookieCounter);
		}		
		
	}
	
	/* Delete all boxes */
	
	function deleteAllDragableBoxes()
	{
		var divs = document.getElementsByTagName('DIV');
		for(var no=0;no<divs.length;no++){
			if(divs[no].className=='dragableBox')closeDragableBox(false,divs[no]);	
		}
			
	}
	
	/* Reset back to default settings */
	
	function resetDragableBoxes()
	{
		cookieCounter = 0;
		clearCookiesForDragableBoxes();
		deleteAllDragableBoxes();
		cookieCounter = 0;
		cookieRSSSources = new Array();
		createDefaultBoxes();
	}
	
	function hideHeaderOptionsForStaticBoxes(boxIndex)
	{
		if(document.getElementById('dragableBoxCloseLink' + boxIndex))document.getElementById('dragableBoxCloseLink' + boxIndex).style.display='none';		
	}
	
	function createBox(id, titre, contenu, cible)
	{
		var htmlContentOfNewBox = '<DIV>'+contenu+'</div>';	// HTML content of new box
		var titleOfNewBox = titre;
		var idBox = id;
		var Zone = cible;
		if(!staticObjectArray[idBox]){	// The box is not stored in cookie - we need to create it.
			var newIndex = createABox(Zone,false,false,idBox,false);
			document.getElementById('dragableBoxContent' + newIndex).innerHTML = htmlContentOfNewBox;		
			document.getElementById('dragableBoxHeader_txt' + newIndex).innerHTML = titleOfNewBox;		
		}else{	// Box is stored in cookie - all we have to do is to move content into it.
			document.getElementById('dragableBoxContent' + staticObjectArray[idBox]).innerHTML = htmlContentOfNewBox;	
			document.getElementById('dragableBoxHeader_txt' + staticObjectArray[idBox]).innerHTML = titleOfNewBox;
		}
		//	disableBoxDrag(	staticObjectArray['staticObject2']);	// Not dragable
	}
	
		
	/* Disable drag for a box */
	function disableBoxDrag(boxIndex)
	{
		document.getElementById('dragableBoxHeader' + boxIndex).onmousedown = '';
		document.getElementById('dragableBoxHeader' + boxIndex).style.cursor = 'default';
	}
	
	
	function initDragableBoxesScript()
	{
		createHelpObjects();	// Always the second line of this function
		initEvents();	// Always the third line of this function
		createDefaultBoxes();	// Create default boxes.
	}
	
	//
// On place dans l'element définie par son ID le contenu passé en parametre.

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

//
// Fonction AJAX permettant de récupérer du contenu et de le pousser dans le DIV spécifier
// type =  1 => appel d'un bloc
// type = 2 => enregistrement agencement

function Goto( FILE , METHOD , DATA, type, data )
{
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
												switch (type)
												{
													case 1: createBox(data, '', httpRequestM.responseText ,1); break;
													case 2: 
														InsererDansDIV( data , '<span class="alerte">'+httpRequestM.responseText+'</div>' ); 
														window.setTimeout("clear_debug()",3000); 
														break;
													case 3: InsererDansDIV( data , httpRequestM.responseText); break;
												}
											}
										}
	if( METHOD == 'POST' ){	httpRequestM.setRequestHeader( "Content-type" , "application/x-www-form-urlencoded" );}
	httpRequestM.send( DATA );
}

function AppelBloc(bloc)
{
	Goto('admin.php', 'POST' , 'IRQ=nouveau_modele&RequeteAjax='+bloc, 1,  bloc );
}
