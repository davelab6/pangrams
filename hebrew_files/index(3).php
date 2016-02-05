/* פונקציה להוספת כוכב בקישורי בינויקי לערכים מומלצים*/
$(document).ready(function() {
	function addInterwikiAttr(spanClass,title) {
		$('span.'+spanClass).each(function() {
			var langName=this.id.replace('interwiki-'+spanClass+'-','');
			$('#p-lang').find('li').filter(function() {
				return $(this).hasClass('interwiki-'+langName) &&
					!$(this).hasClass('badge-featuredarticle' ) &&
					!$(this).hasClass('badge-goodarticle') &&
					!$(this).hasClass('badge-featuredlist');
			}).addClass(spanClass).attr('title',title)
		});
	}
addInterwikiAttr('FA','ערך מומלץ');
addInterwikiAttr('GA','ערך איכותי');
});