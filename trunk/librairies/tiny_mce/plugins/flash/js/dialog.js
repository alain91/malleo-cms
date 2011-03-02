tinyMCEPopup.requireLangPack();

var flashSPDialog = {
	init : function() {
	},

	insert : function() {
		// Insert the contents from the input into the document
		//var embedCode = '<object width="'+document.forms[0].youtubeWidth.value+'" height="'+document.forms[0].youtubeHeight.value+'"><param name="movie" value="http://www.youtube.com/v/'+document.forms[0].youtubeID.value+'&rel=1"></param><param name="wmode" value="transparent"></param><embed src="http://www.youtube.com/v/'+document.forms[0].youtubeID.value+'&rel=1" type="application/x-shockwave-flash" wmode="transparent" width="'+document.forms[0].youtubeWidth.value+'" height="'+document.forms[0].youtubeHeight.value+'"></embed></object>';
		var embedCode = '[flash width='+document.forms[0].flashWidth.value+' height='+document.forms[0].flashHeight.value+']'+document.forms[0].flashID.value+'[/flash]';
		tinyMCEPopup.editor.execCommand('mceInsertRawHTML', false, embedCode);
		tinyMCEPopup.close();
	}
};
tinyMCEPopup.onInit.add(flashDialog.init, flashSPDialog);