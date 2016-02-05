/**
 * based on [[fr:MediaWiki:Common.js]]
 * expanded by [[he:user:Mikimik]]
 */
mw.hook( 'wikipage.content' ).add( function ( $content ) {
    function toggleTitles(div, title1, title2)
    {
        div=div.firstChild;
        title1=title1.find('.imgtoggleboxTitle').text();
        title2=title2.find('.imgtoggleboxTitle').text();
        div.innerHTML = title1;
        div.nextSibling.innerHTML = title2;

        if ( title1 && title2 ) div.appendChild( document.createElement("br") );
    }

  try {
    var divWrapper = $content.find('div.img_toggle');
    if ( !divWrapper.length ) return;

    for ( var i = 0 ; i < divWrapper.length ; i++ )
    {
        var boxes = $(divWrapper[i]).find('div.imgtogglebox');
        if ( boxes.length < 2 ) continue;                    // there must be at least 2 toggle boxes in the wrapper
       var startToggle=0;
        for ( var n = 0 ; n < boxes.length ; n++ )
            if ( /\btogglestart\b/.test(boxes[n].className) )
            {
                startToggle = n;
                break;
            }

        for ( var n = 0 ; n < boxes.length ; n++ ) if ( n != startToggle ) boxes[n].style.display = "none";

        var toggleRight = document.createElement("a");
        toggleRight.href = "#";
        toggleRight.className = "a_toggle";
        toggleRight.onclick = function()
            {
              try {
                if ( this.parentNode.status == 0 ) return false;
                var boxes = $ ( this.parentNode.parentNode).find("div.imgtogglebox" );

                boxes[this.parentNode.status].style.display = "none";
                this.parentNode.status--;
                boxes[this.parentNode.status].style.display = "";

                toggleTitles(this.parentNode, this.parentNode.status == 0 ? $() : $(boxes[this.parentNode.status-1]), $(boxes[this.parentNode.status+1]));

                return false;
              }
              catch (e)
              { return; }
            }

        var toggleLeft = document.createElement("a");
        toggleLeft.href = "#";
        toggleLeft.className = "a_toggle";
        toggleLeft.onclick = function()
            {
              try {
                if ( this.parentNode.status == this.parentNode.maxtoggle ) return false;
                var boxes = $ ( this.parentNode.parentNode).find("div.imgtogglebox" );;

                boxes[this.parentNode.status].style.display = "none";
                this.parentNode.status++;
                boxes[this.parentNode.status].style.display = "";

                toggleTitles(this.parentNode, $(boxes[this.parentNode.status-1]), this.parentNode.status == this.parentNode.maxtoggle ? $() : $(boxes[this.parentNode.status+1]));

                return false;
              }
              catch (e)
              { return; }
            }
        var div = document.createElement("div");
        div.maxtoggle = boxes.length - 1;
        div.status = startToggle;
        div.className="aTogglesContainer";
        div.appendChild ( toggleRight );
        div.appendChild ( toggleLeft );
        toggleTitles ( div, div.status == 0 ? $() : $(boxes[div.status-1]), div.status == div.maxtoggle ? $() : $(boxes[div.status+1]) );

        divWrapper[i].insertBefore ( div, boxes[boxes.length-1].nextSibling );
    }
  }
  catch (e)
  {
    return;        // lets just ignore what's happened
  }
});