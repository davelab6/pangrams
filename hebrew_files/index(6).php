/* תמיכה בלשוניות, נכתב על ידי Yonidebest */
$(function(){
var arrTabContent = new Array();
var arrTabStyle = new Array();
/* where:
   arrTabStyle['sX0'] = unselected background color
   arrTabStyle['sX1'] = border color
   where: X = id
*/

function getTabContent(selectedID) {
 // clear styling of all tabs
 var id = (selectedID.indexOf('0') == 7) ? selectedID.slice(6, 8) : selectedID.slice(6, 7);
 for (var i = 1; i <= 10; i++) {
  var td = document.getElementById('custom' + id + 'Tab' + i);
  if (!td) break;
  td.style.background = arrTabStyle['s' + id + '0'];
  td.style.borderBottom = '1px solid ' + arrTabStyle['s' + id + '1'];
  td.style.cursor = 'pointer';
 }

 // style the selected tab
 var tdSelected = document.getElementById(selectedID);
 tdSelected.style.background = 'white';
 tdSelected.style.borderBottom = 'none';
 tdSelected.style.cursor = 'default';

 // show the content
 var tdContent = document.getElementById('custom' + id + 'TabsContent');
 tdContent.innerHTML = arrTabContent['a' + id + ((id == 10) ? selectedID.slice(11) : selectedID.slice(10))];
}

function customTabsLoad(id) {
 // create main table
 try { // for IE
  var table = document.createElement('<TABLE ID="custom' + id + 'TabsTable"></TABLE>');
 } catch (e) { // for FF
  var table = document.createElement('TABLE');
  table.id = 'custom' + id + 'TabsTable';
 };
 table.cellSpacing = '0';
 var customTabAlign = document.getElementById('custom' + id + 'TabAlign');
 if (!customTabAlign)
  table.align = 'center';
 else
  table.align = customTabAlign.innerHTML;
 if (table.align == 'center')
  table.style.clear = 'both';
 var customTabWidth = document.getElementById('custom' + id + 'TabWidth');
 if (!customTabWidth)
  table.width = '90%';
 else
  table.width = customTabWidth.innerHTML;
 var tbody = document.createElement("tbody");

 // get style data into the array
 var customTabBackground = document.getElementById('custom' + id + 'TabBackground');
 if (!customTabBackground)
  arrTabStyle['s' + id + '0'] = '#DEFED6';
 else
  arrTabStyle['s' + id + '0'] = customTabBackground.innerHTML;
 var customTabBorder = document.getElementById('custom' + id + 'TabBorder');
 if (!customTabBorder)
  arrTabStyle['s' + id + '1'] = '#45A22F';
 else
  arrTabStyle['s' + id + '1'] = customTabBorder.innerHTML;

 var tr1 = document.createElement("TR");
 var i;
 for (i = 1; i <= 10; i++) {
  // create the first row
  var divTab = document.getElementById('custom' + id + 'Tab' + i);
  if (!divTab) break;

  try { // for IE
   var td = document.createElement('<TD ID="' + divTab.getAttribute("id") + '"></TD>');
  } catch (e) { // for FF
   var td = document.createElement('TD');
   td.id = divTab.getAttribute("id");
  };
  td.style.textAlign = 'center';
  td.style.fontWeight = 'bold';
  td.style.border = '1px solid ' + arrTabStyle['s' + id + '1'];
  td.style.background = arrTabStyle['s' + id + '0'];
  td.style.cursor = 'pointer';
  td.innerHTML = divTab.getAttribute("title");
  td.title = td.innerHTML;
  td.onclick = function() { javascript: getTabContent(this.id); }
  tr1.appendChild(td);

  // add a space td cell
  var tdSpace = document.createElement('TD');
  tdSpace.style.backgroundColor = 'transparent';
  tdSpace.style.borderBottom = '1px solid ' + arrTabStyle['s' + id + '1'];
  tdSpace.innerHTML = '&nbsp;';
  tr1.appendChild(tdSpace);

  // populate the array
  arrTabContent['a' + id + i] = divTab.innerHTML;
 }
 tbody.appendChild(tr1);

 // create the second row structure
 var tr2 = document.createElement("TR");
 try { // for IE
  var td = document.createElement('<TD ID="custom' + id + 'TabsContent"></TD>');
 } catch (e) { // for FF
  var td = document.createElement('TD');
  td.id = 'custom' + id + 'TabsContent';
 };
 td.style.padding = '7px';
 td.style.border = '1px solid ' + arrTabStyle['s' + id + '1'];
 td.style.borderTop = 'none';
 td.colSpan = (i - 1) * 2;
 td.innerHTML = "טוען...";
 tr2.appendChild(td);
 tbody.appendChild(tr2);

 // attach table
 table.appendChild(tbody);
 var mainDiv = document.getElementById('custom' + id + 'Tabs');
 mainDiv.parentNode.insertBefore(table, mainDiv);

 // load default tab
 var defaultTab = document.getElementById('custom' + id + 'TabDefault');
 if (!defaultTab)
  getTabContent('custom' + id + 'Tab1');
 else
  getTabContent('custom' + id + 'Tab' + defaultTab.innerHTML);

 // remove the div and wait notice
 mainDiv.parentNode.removeChild(mainDiv);
 var customTabsWait = document.getElementById('custom' + id + 'TabsWait');
 customTabsWait.parentNode.removeChild(customTabsWait);
}

 for (var i = 1; i <= 10; i++) {
  var customTabs = document.getElementById('custom' + i + 'Tabs');
  if (customTabs)
   customTabsLoad(i);
 }

});

//tabs2
mw.hook( 'wikipage.content' ).add( function ( $content ) {
	if($content.find('.tabWrapper').length === 0) return;
	mw.loader.using( 'jquery.ui.tabs', function () {
		$content.find('.tabWrapper').each(function () {
		    var options = $('.tabWrapperOptions', this).text();

		    var tabBackground = options.match(/tabBackground:(.*?);/)[1];
		    var tabBorder = options.match(/tabBorder:(.*?);/)[1];
		    var defaultTab = options.match(/defaultTab:(.*?);/)[1];

		    $(this).tabs({
		        selected: defaultTab
		    });
		    if (tabBackground) {
		        var tabs = $('.ui-tabs-nav li a', this);
		        tabs.css('background', tabBackground)
		        $(this).bind('tabsselect', function (e, ui) {
		            tabs.css('background', tabBackground);
		            $(ui.tab).css('background', '#ffffff');
		        });
		        $(tabs.get(defaultTab)).css('background', '#ffffff');
		    }
		    if (tabBorder) {
		        $(this).css('border-color', tabBorder);
		    }
		    $content.find('.tabWrapper .ui-widget-content,.tabWrapper.ui-widget-content').removeClass('ui-widget-content');
		})
	});
});