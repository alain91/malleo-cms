tinyMCEPopup.requireLangPack();

var GoogleMapDialog = {
	init : function() {
	},

	insert : function() {
		// Insert the contents from the input into the document
		var embedCode = '[googlemap width='+document.forms[0].GoogleMapLargeur.value+' height='+document.forms[0].GoogleMapHauteur.value+' zoom='+document.forms[0].GoogleMapZoom.value+']'+document.forms[0].GoogleMapAdresse.value+'[/googlemap]';
		tinyMCEPopup.editor.execCommand('mceInsertRawHTML', false, embedCode);
		tinyMCEPopup.close();
	}
};

tinyMCEPopup.onInit.add(GoogleMapDialog.init, GoogleMapDialog);