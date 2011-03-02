/**
 * $Id: editor_plugin_src.js 201 2007-02-12 15:56:56Z spocke $
 *
 * @author Moxiecode
 * @copyright Copyright © 2004-2008, Moxiecode Systems AB, All rights reserved.
 */

(function() {
	tinymce.create('tinymce.plugins.BBCodePlugin', {
		init : function(ed, url) {
			var t = this, dialect = ed.getParam('bbcode_dialect', 'punbb').toLowerCase();

			ed.onBeforeSetContent.add(function(ed, o) {
				o.content = t['_' + dialect + '_bbcode2html'](o.content);
			});

			ed.onPostProcess.add(function(ed, o) {
				if (o.set)
					o.content = t['_' + dialect + '_bbcode2html'](o.content);

				if (o.get)
					o.content = t['_' + dialect + '_html2bbcode'](o.content);
			});
		},

		getInfo : function() {
			return {
				longname : 'BBCode Plugin',
				author : 'Moxiecode Systems AB',
				authorurl : 'http://tinymce.moxiecode.com',
				infourl : 'http://wiki.moxiecode.com/index.php/TinyMCE:Plugins/bbcode',
				version : tinymce.majorVersion + "." + tinymce.minorVersion
			};
		},

		// Private methods

		// HTML -> BBCode in PunBB dialect
		_punbb_html2bbcode : function(s) {
			s = tinymce.trim(s);

			function rep(re, str) {
				s = s.replace(re, str);
			};
			rep(/<a.*?href=\"(.*?)\".*?>(.*?)<\/a>/gi,"[url=$1]$2[/url]");
			rep(/<\/(em|i)>/gi,"[/i]");
			rep(/<(em|i)>/gi,"[i]");			
			rep(/<span style=\"text-decoration: ?underline;\">(.*?)<\/span>/gi,"[u]$1[/u]");
			rep(/<span style=\"text-decoration: line-through;\">(.*?)<\/span>/gi,"[strike]$1[/strike]");
			rep(/<\/address>/gi,"[/address]");
			rep(/<address>/gi,"[address]");	
			rep(/<\/strike>/gi,"[/strike]");
			rep(/<strike>/gi,"[strike]");
			rep(/<blockquote>(.*?)::/gi,"[quote=$1]");
			rep(/<blockquote>/gi,"[quote]");
			rep(/<\/blockquote>/gi,"[/quote]");
			rep(/<span class=\"quoteStyle\">(.*?)::(.*?)<\/span>/gi,"[quote=$1]$2[/quote]");
			rep(/<span class=\"quoteStyle\">(.*?)<\/span>/gi,"[quote]$1[/quote]");
			rep(/<span class=\"wiki\">(.*?)<\/span>/gi,"[wiki]$1[/wiki]");
			//rep(/<span class=\"video\">(youtube|dailymotion)::[a-z0-9-_]{1,15}::([0-9]{1,4})::([0-9]{1,4})<\/span>/gi,"[video=$1]$2::$3::$4[/video]");			
			//rep(/<span class=\"flash\">(.*).swf::([0-9]{1,4})::([0-9]{1,4})<\/span>/gi,"[flash]$1.swf::$2::$3[/flash]");			
			//rep(/<span class=\"flash\">(.*).swf<\/span>/gi,"[flash]$1.swf[/flash]");			
			rep(/<span class=\"codeStyle\">([a-z0-9_-]{1,25})::(.*?)<\/span>/gi,"[code=$1]$2[/code]");
			rep(/<span class=\"codeStyle\">(.*?)<\/span>/gi,"[code=html4strict]$1[/code]");
			rep(/<pre>(.*?)<\/pre>/gi,"[code=html4strict]$1[/code]");
			rep(/<hr \/>/gi,"[hr]");
			rep(/<ul>(.*?)<\/ul>/gi,"[list]$1[/list]");
			rep(/<ol>(.*?)<\/ol>/gi,"[list1]$1[/list1]");
			rep(/<li>(.*?)<\/li>/gi,"[*]$1[/*]");
			rep(/<img style="float: (left|right|center|justify);" src=\"(.*?)\".*?\/>/gi,"[img=$1]$2[/img]");
			rep(/<img.*?src=\"(.*?)\".*?\/>/gi,"[img]$1[/img]");
			rep(/<h([0-9]+)>(.*?)<\/h([0-9]+)>/gi,"[h$1]$2[/h$3]");
			rep(/<h([0-9]+) style="text-align: (left|right|center|justify);">(.*?)<\/h([0-9]+)>/gi,"[h$1][align=$2]$3[/align][/h$1]");
			rep(/<h([0-9]+) style=\"background-color: (#[a-z0-9]{6}|[a-z]+);\">(.*?)<\/h([0-9]+)>/gi,"[h$1][bgcolor=$2]$3[/bgcolor][/h$1]");
			// Alignement
			rep(/<div style=\"?padding-left: ([0-9]+)px;\">(.*?)<\/div>/gi,"[align=$1]$2[/align]");
			rep(/<div style=\"?text-align: (left|right|center|justify);\">(.*?)<\/div>/gi,"[align=$1]$2[/align]");
			// Background Color
			rep(/<div style=\"background-color: (#[a-z0-9]{6}|[a-z]+); text-align: (left|right|center|justify);\">(.*?)<\/div>/gi,"[align=$2][bgcolor=$1]$3[/bgcolor][/align]");
			rep(/<div style=\"text-align: (left|right|center|justify); background-color: (#[a-z0-9]{6}|[a-z]+);\">(.*?)<\/div>/gi,"[align=$1][bgcolor=$2]$3[/bgcolor][/align]");
			rep(/<div style=\"background-color: (#[a-z0-9]{6}|[a-z]+);\">(.*?)<\/div>/gi,"[bgcolor=$1]$2[/bgcolor]");
			// Background Color + Strong/b
			rep(/<strong style=\"?background-color: (#[a-z0-9]{6}|[a-z]+);\">(.*?)<\/strong>/gi,"[b][bgcolor=$1]$2[/bgcolor][/b]");
			rep(/<b style=\"?background-color: (#[a-z0-9]{6}|[a-z]+);\">(.*?)<\/b>/gi,"[b][bgcolor=$1]$2[/bgcolor][/b]");
			// Cumul BGcolor + color + size
			rep(/<span style=\"?background-color: (#[a-z0-9]{6}|[a-z]+); color: (#[a-z0-9]{6}|[a-z]+); font-size: ([0-9]+)px;\">(.*?)<\/span>/gi,"[color=$2][size=$3][bgcolor=$1]$4[/bgcolor][/size][/color]");
			rep(/<span style=\"?font-size: ([0-9]+)px; color: (#[a-z0-9]{6}|[a-z]+); background-color: (#[a-z0-9]{6}|[a-z]+);\">(.*?)<\/span>/gi,"[color=$2][size=$1][bgcolor=$3]$4[/bgcolor][/size][/color]");
			rep(/<span style=\"?color: (#[a-z0-9]{6}|[a-z]+); font-size: ([0-9]+)px; background-color: (#[a-z0-9]{6}|[a-z]+);\">(.*?)<\/span>/gi,"[color=$1][size=$2][bgcolor=$3]$4[/bgcolor][/size][/color]");
			rep(/<span style=\"?background-color: (#[a-z0-9]{6}|[a-z]+); font-size: ([0-9]+)px; color: (#[a-z0-9]{6}|[a-z]+);\">(.*?)<\/span>/gi,"[color=$3][size=$2][bgcolor=$1]$4[/bgcolor][/size][/color]");
			rep(/<span style=\"?color: (#[a-z0-9]{6}|[a-z]+); background-color: (#[a-z0-9]{6}|[a-z]+); font-size: ([0-9]+)px;\">(.*?)<\/span>/gi,"[color=$1][size=$3][bgcolor=$2]$4[/bgcolor][/size][/color]");
			rep(/<span style=\"?font-size: ([0-9]+)px; background-color: (#[a-z0-9]{6}|[a-z]+); color: (#[a-z0-9]{6}|[a-z]+);\">(.*?)<\/span>/gi,"[color=$3][size=$1][bgcolor=$2]$4[/bgcolor][/size][/color]");
			// Cumul couleur + size
			rep(/<span style=\"?color: (#[a-z0-9]{6}|[a-z]+); font-size: ([0-9]+)px;\">(.*?)<\/span>/gi,"[color=$1][size=$2]$3[/size][/color]");
			rep(/<span style=\"?font-size: ([0-9]+)px;?color: (#[a-z0-9]{6}|[a-z]+);\">(.*?)<\/span>/gi,"[color=$2][size=$1]$3[/size][/color]");
			// Cumul BGcolor + size
			rep(/<span style=\"?background-color: (#[a-z0-9]{6}|[a-z]+); font-size: ([0-9]+)px;\">(.*?)<\/span>/gi,"[size=$2][bgcolor=$1]$3[/bgcolor][/size]");
			rep(/<span style=\"?font-size: ([0-9]+)px;?background-color: (#[a-z0-9]{6}|[a-z]+);\">(.*?)<\/span>/gi,"[size=$1][bgcolor=$2]$3[/bgcolor][/size]");
			// Cumul couleur + bgcolor
			rep(/<span style=\"?color: (#[a-z0-9]{6}|[a-z]+); background-color: (#[a-z0-9]{6}|[a-z]+);\">(.*?)<\/span>/gi,"[color=$1][bgcolor=$2]$3[/bgcolor][/color]");
			rep(/<span style=\"?background-color: (#[a-z0-9]{6}|[a-z]+); color: (#[a-z0-9]{6}|[a-z]+);\">(.*?)<\/span>/gi,"[color=$2][bgcolor=$1]$3[/bgcolor][/color]");
			//  bgcolor
			rep(/<span style=\"?background-color: (#[a-z0-9]{6}|[a-z]+);\">(.*?)<\/span>/gi,"[bgcolor=$1]$2[/bgcolor]");
			// couleur
			rep(/<span style=\"?color: (#[a-z0-9]{6}|[a-z]+);\">(.*?)<\/span>/gi,"[color=$1]$2[/color]");
			// Taille
			rep(/<span style=\"font-size: ([0-9]+)px;\">(.*?)<\/span>/gi,"[size=$1]$2[/size]");
			// Exposants
			rep(/<sup>(.*?)<\/sup>/gi,"[sup]$1[/sup]");
			rep(/<sub>(.*?)<\/sub>/gi,"[sub]$1[/sub]");
			// Gras
			rep(/<\/(strong|b)>/gi,"[/b]");
			rep(/<(strong|b)>/gi,"[b]");
			// TOUJOURS A LA FIN
			rep(/<div.*?>/gi,"");
			rep(/<\/div>/gi,"");
			rep(/<span.*?>/gi,"");
			rep(/<\/span>/gi,"");
			rep(/<p.*?>/gi,"");
			rep(/<\/p>/gi,"\r\n");
			rep(/<br \/>/gi,"\r\n");
			rep(/&amp;/gi,"&");
			return s;
		},

		// BBCode -> HTML from PunBB dialect
		_punbb_bbcode2html : function(s) {
			s = tinymce.trim(s);

			function rep(re, str) {
				s = s.replace(re, str);
			};
			//alert('bbcode=>html \n\n'+s);
			// example: [b] to <strong>
			rep(/\r\n/gi,"<br \/>");
			rep(/\n/gi,"<br \/>");
	/* 		rep(/<p.*?>/gi,"");
			rep(/<\/p>/gi,""); */
			rep(/\[align=(left|right|center|justify)\](.*?)\[\/align\]/gi,"<div style=\"text-align: $1;\">$2</div>");
			rep(/\[align=([0-9]+)\](.*?)\[\/align\]/gi,"<div style=\"padding-left: $1px;\">$2</div>");
			// URL
			rep(/\[url=([^\]]+)\](.*?)\[\/url\]/gi,"<a href=\"$1\">$2</a>");
			rep(/\[url\](.*?)\[\/url\]/gi,"<a href=\"$1\">$1</a>");
			// GRAS
			rep(/\[b\]/gi,"<strong>");
			rep(/\[\/b\]/gi,"</strong>");
			// Italique
			rep(/\[i\]/gi,"<i>");
			rep(/\[\/i\]/gi,"</i>");
			// Souligne
			rep(/\[u\](.*?)\[\/u\]/gi,"<span style=\"text-decoration: underline;\">$1</span>");
			// barre
			rep(/\[strike\](.*?)\[\/strike\]/gi,"<span style=\"text-decoration: line-through;\">$1</span>");
			// Addresse
			rep(/\[address\](.*?)\[\/address\]/gi,"<address>$1</address>");
			// Code
			rep(/\[code=(.*?)\](.*?)\[\/code\]/gi,"<span class=\"codeStyle\">$1::$2</span>");
			// Video
			//rep(/\[video=(youtube|dailymotion)\](.*)::([0-9]{1,4})::([0-9]{1,4})\[\/video\]/gi,"<span class=\"video\">$1::$2::$3::$4</span>");
			// Flash
			//rep(/\[flash\](.*).swf::([0-9]{1,4})::([0-9]{1,4})\[\/flash\]/gi,"<span class=\"flash\">$1.swf::$2::$3</span>");
			//rep(/\[flash\](.*).swf\[\/flash\]/gi,"<span class=\"flash\">$1.swf</span>");
			// Citation
			rep(/\[quote=(.*?)\]/gi,"<blockquote>$1::");
			rep(/\[quote\]/gi,"<blockquote>");
			rep(/\[\/quote\]/gi,"</blockquote>");
			rep(/\[wiki\](.*?)\[\/wiki\]/gi,"<span class=\"wiki\">$1</span>");
			// ligne horizontale
			rep(/\[hr\]/gi,"<hr />");
			// Taille de texte
			rep(/\[size=(.*?)\](.*?)\[\/size\]/gi,"<span style=\"font-size: $1px;\">$2</span>");
			// Listes
			rep(/\[list1\](.*?)\[\/list1\]/gi,"<ol>$1</ol>");
			rep(/\[list\](.*?)\[\/list\]/gi,"<ul>$1</ul>");
			rep(/\[\*\](.*?)\[\/\*\]/gi,"<li>$1</li>");
			// Images
			rep(/\[img=(left|right|center|justify)\](.*?)\[\/img\]/gi,"<img style=\"float: $1;\" src=\"$2\" />");
			rep(/\[img\](.*?)\[\/img\]/gi,"<img src=\"$1\" />");
			// Titres
			rep(/\[h(.*?)\](.*?)\[\/h(.*?)\]/gi,"<h$1>$2</h$3>");
			// Couleurs
			rep(/\[bgcolor=(#[a-z0-9]{6}|[a-z]+)\](.*?)\[\/bgcolor\]/gi,"<span style=\"background-color: $1;\">$2</span>");
			rep(/\[color=(#[a-z0-9]{6}|[a-z]+)\](.*?)\[\/color\]/gi,"<span style=\"color: $1;\">$2</span>");
			// Exposants
			rep(/\[sub\](.*?)\[\/sub\]/gi,"<sub>$1</sub>");
			rep(/\[sup\](.*?)\[\/sup\]/gi,"<sup>$1</sup>");
			return s; 
		}
	});

	// Register plugin
	tinymce.PluginManager.add('bbcode', tinymce.plugins.BBCodePlugin);
})();