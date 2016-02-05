/** Collapsible tables *********************************************************
 *
 *  Description: Allows tables to be collapsed - using $.makeCollapsible
 */

var autoCollapse = 2;
mw.hook( 'wikipage.content' ).add( function ( $content ) {
	mw.loader.using('jquery.makeCollapsible',function(){
		$content.find('table.collapsed').addClass('mw-collapsed').makeCollapsible();
		if(($content.find('table.collapsible').length + $content.find('table.mw-collapsible').length)>autoCollapse){
			$content.find('table.collapsible.autocollapse').addClass('mw-collapsed').makeCollapsible();
		}
		$content.find('table.collapsible').makeCollapsible();
	});
});