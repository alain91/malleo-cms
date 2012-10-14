//
//	Configuration
//
var fileLoadingImage = "librairies/lightbox/images/loading.gif";		
var fileBottomNavCloseImage = "librairies/lightbox/images/closelabel.gif";

var overlayOpacity = 0.8;	// controls transparency of shadow overlay

var animate = true;			// toggles resizing animations
var resizeSpeed = 7;		// controls the speed of the image resizing animations (1=slowest and 10=fastest)

var borderSize = 10;		//if you adjust the padding in the CSS, you will need to update this variable

// -----------------------------------------------------------------------------------

//
//	Global Variables
//

if(animate == true){
	overlayDuration = 0.2;	// shadow fade in/out duration
	if(resizeSpeed > 10){ resizeSpeed = 10;}
	if(resizeSpeed < 1){ resizeSpeed = 1;}
	resizeDuration = (11 - resizeSpeed) * 0.15;
} else { 
	overlayDuration = 0;
	resizeDuration = 0;
}

// -----------------------------------------------------------------------------------

//
//	Additional methods for Element added by SU, Couloir
//	- further additions by Lokesh Dhakar (huddletogether.com)
//
Object.extend(Element, {
	getWidth: function(element) {
	   	element = $(element);
	   	return element.offsetWidth; 
	},
	setWidth: function(element,w) {
	   	element = $(element);
    	element.style.width = w +"px";
	},
	setHeight: function(element,h) {
   		element = $(element);
    	element.style.height = h +"px";
	},
	setTop: function(element,t) {
	   	element = $(element);
    	element.style.top = t +"px";
	},
	setLeft: function(element,l) {
	   	element = $(element);
    	element.style.left = l +"px";
	},
	setSrc: function(element,src) {
    	element = $(element);
    	element.src = src; 
	},
	setHref: function(element,href) {
    	element = $(element);
    	element.href = href; 
	},
	setInnerHTML: function(element,content) {
		element = $(element);
		element.innerHTML = content;
	}
});

// -----------------------------------------------------------------------------------

//
//	Extending built-in Array object
//	- array.removeDuplicates()
//	- array.empty()
//
Array.prototype.removeDuplicates = function () {
    for(i = 0; i < this.length; i++){
        for(j = this.length-1; j>i; j--){        
            if(this[i][0] == this[j][0]){
                this.splice(j,1);
            }
        }
    }
}

// -----------------------------------------------------------------------------------

Array.prototype.empty = function () {
	for(i = 0; i <= this.length; i++){
		this.shift();
	}
}

// -----------------------------------------------------------------------------------

//
//	Lightbox Class Declaration
//	- initialize()
//	- start()
//	- changeImage()
//	- resizeImageContainer()
//	- showImage()
//	- updateDetails()
//	- updateNav()
//	- enableKeyboardNav()
//	- disableKeyboardNav()
//	- keyboardNavAction()
//	- preloadNeighborImages()
//	- end()
//
//	Structuring of code inspired by Scott Upton (http://www.uptonic.com/)
//
var Arcade = Class.create();

Arcade.prototype = {
	
	// initialize()
	// Constructor runs on completion of the DOM loading. Calls updateImageList and then
	// the function inserts html at the bottom of the page which is used to display the shadow 
	// overlay and the image container.
	//
	initialize: function() {
	
		// Code inserts html at the bottom of the page that looks similar to this:
		//
		//	<div id="overlay"></div>
		//	<div id="lightbox">
		//		<div id="outerImageContainer">
		//			<div id="imageContainer">
		//				<img id="lightboxImage">
		//				<div style="" id="hoverNav">
		//					<a href="#" id="prevLink"></a>
		//					<a href="#" id="nextLink"></a>
		//				</div>
		//				<div id="loading">
		//					<a href="#" id="loadingLink">
		//						<img src="images/loading.gif">
		//					</a>
		//				</div>
		//			</div>
		//		</div>
		//		<div id="imageDataContainer">
		//			<div id="imageData">
		//				<div id="imageDetails">
		//					<span id="caption"></span>
		//					<span id="numberDisplay"></span>
		//				</div>
		//				<div id="bottomNav">
		//					<a href="#" id="bottomNavClose">
		//						<img src="images/close.gif">
		//					</a>
		//				</div>
		//			</div>
		//		</div>
		//	</div>


		var objBody = document.getElementsByTagName("body").item(0);
		
		var objOverlay = document.createElement("div");
		objOverlay.setAttribute('id','Arcadeoverlay');
		objOverlay.style.display = 'none';
		//objOverlay.onclick = function() { monArcade.end(); }
		objBody.appendChild(objOverlay);
		
		var objLightbox = document.createElement("div");
		objLightbox.setAttribute('id','Arcadelightbox');
		objLightbox.style.display = 'none';
/*   		objLightbox.onclick = function(e) {	// close Lightbox is user clicks shadow overlay
			if (!e) var e = window.event;
			var clickObj = Event.element(e).id;
			if ( clickObj == 'Arcadelightbox') {
				monArcade.end();
			}
		};  */
		objBody.appendChild(objLightbox);
			
		var objOuterImageContainer = document.createElement("div");
		objOuterImageContainer.setAttribute('id','ArcadeouterFlashContainer');
		objLightbox.appendChild(objOuterImageContainer);

		// When Lightbox starts it will resize itself from 250 by 250 to the current image dimension.
		// If animations are turned off, it will be hidden as to prevent a flicker of a
		// white 250 by 250 box.
		if(animate){
			Element.setWidth('ArcadeouterFlashContainer', 250);
			Element.setHeight('ArcadeouterFlashContainer', 250);			
		} else {
			Element.setWidth('ArcadeouterFlashContainer', 1);
			Element.setHeight('ArcadeouterFlashContainer', 1);			
		}

		var objImageContainer = document.createElement("div");
		objImageContainer.setAttribute('id','ArcadeFlashContainer');
		objOuterImageContainer.appendChild(objImageContainer);
	
		var objLightboxImage = document.createElement("div");
		objLightboxImage.setAttribute('id','ArcadeFlash');
		objImageContainer.appendChild(objLightboxImage);
	
		var objLoading = document.createElement("div");
		objLoading.setAttribute('id','Arcadeloading');
		objImageContainer.appendChild(objLoading);
	
		var objLoadingLink = document.createElement("a");
		objLoadingLink.setAttribute('id','ArcadeloadingLink');
		objLoadingLink.setAttribute('href','#');
		//objLoadingLink.onclick = function() { monArcade.end(); return false; }
		objLoading.appendChild(objLoadingLink);
	
		var objLoadingImage = document.createElement("img");
		objLoadingImage.setAttribute('src', fileLoadingImage);
		objLoadingLink.appendChild(objLoadingImage);

		var objImageDataContainer = document.createElement("div");
		objImageDataContainer.setAttribute('id','ArcadeDataContainer');
		objLightbox.appendChild(objImageDataContainer);

		var objImageData = document.createElement("div");
		objImageData.setAttribute('id','ArcadeFlashData');
		objImageDataContainer.appendChild(objImageData);
	
		var objBottomNav = document.createElement("div");
		objBottomNav.setAttribute('id','ArcadebottomNav');
		objImageData.appendChild(objBottomNav);
	
		var objBottomNavCloseLink = document.createElement("a");
		objBottomNavCloseLink.setAttribute('id','ArcadebottomNavClose');
		objBottomNavCloseLink.setAttribute('href','#');
		objBottomNavCloseLink.onclick = function() { monArcade.end(); return false; }
		objBottomNav.appendChild(objBottomNavCloseLink);
	
		var objBottomNavCloseImage = document.createElement("img");
		objBottomNavCloseImage.setAttribute('src', fileBottomNavCloseImage);
		objBottomNavCloseLink.appendChild(objBottomNavCloseImage);
	},


	//
	//	start()
	//	Display overlay and lightbox. If image is part of a set, add siblings to imageArray.
	//
	start: function(DATA,largeur,hauteur) {	

		hideSelectBoxes();
		hideFlash();

		// stretch overlay to fill page and fade in
		var arrayPageSize = getPageSize();
		Element.setWidth('Arcadeoverlay', arrayPageSize[0]);
		Element.setHeight('Arcadeoverlay', arrayPageSize[1]);

		new Effect.Appear('Arcadeoverlay', { duration: overlayDuration, from: 0.0, to: overlayOpacity });

		// calculate top and left offset for the lightbox 
		var arrayPageScroll = getPageScroll();
		var lightboxTop = arrayPageScroll[1] + (arrayPageSize[3] / 10);
		var lightboxLeft = arrayPageScroll[0];
		Element.setTop('Arcadelightbox', lightboxTop);
		Element.setLeft('Arcadelightbox', lightboxLeft);
		
		Element.show('Arcadelightbox');
	
		// hide elements during transition
		if(animate){ Element.show('Arcadeloading');}
		Element.hide('ArcadeFlash');
		Element.hide('ArcadeDataContainer');

		// Récupération Ajax
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
		httpRequestM.open( 'POST' , 'index.php?'+DATA , true );
		httpRequestM.onreadystatechange = function()
											{
												if( httpRequestM.readyState == 4 )
												{
													Element.setInnerHTML('ArcadeFlash', httpRequestM.responseText);
													monArcade.resizeImageContainer(largeur, hauteur);
												}
											}
		httpRequestM.setRequestHeader( "Content-type" , "application/x-www-form-urlencoded" );
		httpRequestM.send( DATA );
	},

	//
	//	resizeImageContainer()
	//
	resizeImageContainer: function( imgWidth, imgHeight) {

		// get curren width and height
		this.widthCurrent = Element.getWidth('ArcadeouterFlashContainer');
		this.heightCurrent = Element.getHeight('ArcadeouterFlashContainer');

		// get new width and height
		var widthNew = (imgWidth  + (borderSize * 2));
		var heightNew = (imgHeight  + (borderSize * 2));

		// scalars based on change from old to new
		this.xScale = ( widthNew / this.widthCurrent) * 100;
		this.yScale = ( heightNew / this.heightCurrent) * 100;

		// calculate size difference between new and old image, and resize if necessary
		wDiff = this.widthCurrent - widthNew;
		hDiff = this.heightCurrent - heightNew;

		if(!( hDiff == 0)){ new Effect.Scale('ArcadeouterFlashContainer', this.yScale, {scaleX: false, duration: resizeDuration, queue: 'front'}); }
		if(!( wDiff == 0)){ new Effect.Scale('ArcadeouterFlashContainer', this.xScale, {scaleY: false, delay: resizeDuration, duration: resizeDuration}); }

		// if new and old image are same size and no scaling transition is necessary, 
		// do a quick pause to prevent image flicker.
		if((hDiff == 0) && (wDiff == 0)){
			if (navigator.appVersion.indexOf("MSIE")!=-1){ pause(250); } else { pause(100);} 
		}

		Element.setWidth( 'ArcadeDataContainer', widthNew);

		this.showImage();
	},
	
	//
	//	showImage()
	//	Display image and begin preloading neighbors.
	//
	showImage: function(){
		Element.hide('Arcadeloading');
		new Effect.Appear('ArcadeFlash', { duration: resizeDuration, queue: 'end', afterFinish: function(){	monArcade.updateDetails(); } });
	},
	
	//
	//	updateDetails()
	//
	updateDetails: function() {
		new Effect.Parallel(
			[ new Effect.SlideDown( 'ArcadeDataContainer', { sync: true, duration: resizeDuration, from: 0.0, to: 1.0 }), 
			  new Effect.Appear('ArcadeDataContainer', { sync: true, duration: resizeDuration }) ], 
			{ duration: resizeDuration, afterFinish: function() {
				// update overlay size and update nav
				var arrayPageSize = getPageSize();
				Element.setHeight('Arcadeoverlay', arrayPageSize[1]);
				}
			} 
		);
	},


	//
	//	enableKeyboardNav()
	//
	enableKeyboardNav: function() {
		document.onkeydown = this.keyboardAction; 
	},

	//
	//	disableKeyboardNav()
	//
	disableKeyboardNav: function() {
		document.onkeydown = '';
	},

	//
	//	keyboardAction()
	//
	keyboardAction: function(e) {
		if (e == null) { // ie
			keycode = event.keyCode;
			escapeKey = 27;
		} else { // mozilla
			keycode = e.keyCode;
			escapeKey = e.DOM_VK_ESCAPE;
		}

		key = String.fromCharCode(keycode).toLowerCase();
		
		if((keycode == escapeKey)){	// close lightbox
			monArcade.end();
		} 

	},


	//
	//	end()
	//
	end: function() {
		this.disableKeyboardNav();
		Element.hide('Arcadelightbox');
		new Effect.Fade('Arcadeoverlay', { duration: overlayDuration});
		showSelectBoxes();
		showFlash();
	}
}



function initArcadeLightbox(DATA,largeur,hauteur) { 

	monArcade = new Arcade(); 
	monArcade.start(DATA,largeur,hauteur);
}