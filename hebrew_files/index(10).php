$(document).ready(function() {

    var
//add this class to all elements created by the script. the reason is that we call the script again on
//window resize, and use the class to remove all the "artefacts" we created in the previous run.
		myClassName = 'imageMapHighlighterArtefacts'
		, liHighlightClass = 'liHighlighting'
// "2d context" attributes used for highlighting.
		, areaHighLighting = {fillStyle: 'rgba(0,0,0,0.35)', strokeStyle: 'yellow', lineJoin: 'round', lineWidth: 2}
//every imagemap that wants highlighting, should reside in a div of this 'class':
		, hilightDivMarker = '.imageMapHighlighter'
// specifically for wikis - redlinks tooltip adds this message
		, he = mw && mw.config && mw.config.get('wgUserLanguage') == 'he'
		, pageDoesntExistMessage = he ? ' (הדף אינו קיים)' : ' (page does not exist)'
		, expandLegend = he ? 'הצגת מקרא' : 'ּShow Legend'
		, collapseLegend = he ? 'הסתרת המקרא' : 'Hide Legend'
		;


	function drawMarker(context, areas) { // this is where the magic is done.

		function drawPoly(coords) {
			context.moveTo(coords.shift(), coords.shift());
			while (coords.length)
				context.lineTo(coords.shift(), coords.shift());
		}

		for (var i in areas) {
			var coords = areas[i].coords.split(',');
			context.beginPath();
			switch (areas[i].shape) {
				case 'rect': drawPoly([coords[0], coords[1], coords[0], coords[3], coords[2], coords[3], coords[2], coords[1]]); break;
				case 'circle': context.arc(coords[0],coords[1],coords[2],0,Math.PI*2);  break;//x,y,r,startAngle,endAngle
				case 'poly': drawPoly(coords); break;
			}
			context.closePath();
			context.stroke();
			context.fill();
		}
	}

	function mouseAction(e) {
		var $this = $(this),
			context = $this.data('context'),
			activate = e.type == 'mouseover',
			li = $this.prop('tagName') == 'LI';
		if (li && activate) { // in this case, we need to test visibility vis a vis scrolling
			var height = $this.height(),
				ol = $this.parent(),
				top = $this.position().top;
			if (top < 0 || top + height > ol.height()) 
				ol.animate({scrollTop: ol.scrollTop() + top - ol.height() / 2});
		}
		$this.toggleClass(liHighlightClass, activate);
		context.clearRect(0, 0, context.canvas.width, context.canvas.height);
		if (activate) {
			drawMarker(context, $this.data('areas'));
			if ($.client.profile().name === 'msie') {	// ie9: dimwit needs to be told twice.
				context.clearRect(0, 0, context.canvas.width, context.canvas.height);
				drawMarker(context, $this.data('areas'));
			}
		}
	}

	// massage the area "href" and create a human legible string to be used as the tooltip of "li"
	function pageOfHref(href, cssClass) {
		var page = href.replace(document.location.protocol + mw.config.get('wgServer') + "/wiki/", '').replace(/.*\/\//, '').replace(/_/g, ' ');
		page = page.replace(/#(.*)/, function(toReplace){return toReplace.replace(/\.([\dA-F]{2})/g, '%$1');});
		page = decodeURIComponent(page); // used for "title" of legends - just like "normal" wiki links.
		if (cssClass.indexOf('new') + 1)
			page += pageDoesntExistMessage;
		return page;
	}

	function init() {
		appendCSS('li.' + myClassName + '{white-space:nowrap;}\n' + //css for li element
					'li.' + liHighlightClass + '{background-color:yellow;}\n' + //css for highlighted li element.
					'.rtl li.' + myClassName + '{float: right; margin-left: 3em;}\n' +
					'.ltr li.' + myClassName + '{float: left; margin-right: 3em;}');
		$(hilightDivMarker+ ' img').each(function() {
			var img = $(this), map = img.siblings('map:first');
			if (!('area', map).length)
				return;	//not an imagemap. inside "each" anonymous function, 'return' means "continue".
			var w = img.width(), h = img.height();
			var dims = {position: 'absolute', width: w + 'px', height: h + 'px', border: 0, top:0, left:0};
			var jcanvas = $('<canvas>', {'class': myClassName})
				.css(dims)
				.attr({width: w, height: h});
			var bgimg = $('<img>', {'class': myClassName, src: img.attr('src')})
				.css(dims);//completely inert image. this is what we see.
			var context = $.extend(jcanvas[0].getContext("2d"), areaHighLighting);
// this is where the magic is done: prepare a sandwich of the inert bgimg at the bottom,
// the canvas above it, and the original, image, on top.
// so canvas won't steal the mouse events.
// pack them all TIGHTLY in a newly minted "relative" div, so when page chnage
// (other scripts adding elements, window resize etc.), canvas and imagese remain aligned.
			var div = $('<div>').css({position: 'relative', width: w + 'px', height: h + 'px'});
			img.before(div);	// put the div just above the image, and ...
			div.append(bgimg)	// place the background image in the div
				.append(jcanvas)// and the canvas. both are "absolute", so they don't occupy space in the div
				.append(img);	// now yank the original image from the window and place it on top.
			img.fadeTo(1, 0);	// make the image transparent - we see canvas and bgimg through it.
			var ol = $('<ol>', {'class': myClassName})
				.css({clear: 'both', margin: 0, listStyle: 'none', maxWidth: w + 'px', float: 'left', position: 'relative'})
				.attr({'data-expandtext' : expandLegend, 'data-collapsetext': collapseLegend});
			// ol below image, hr below ol. original caption pushed below hr.
			div.after($('<hr>', {'class': myClassName}).css('clear', 'both')).after(ol);
			var lis = {};	//collapse areas with same caption to one list item
			$('area', map).each(function() {
				var $this = $(this), text = this.title;
				var li = lis[text];	// title already met? use the same li
				if (!li) {			//no? create a new one.
					var href = this.href, cssClass = this['class'] || '';
					lis[text] = li = $('<li>', {'class': myClassName})
						.append($('<a>', {href: href, title: pageOfHref(href, cssClass), text: text, 'class': cssClass + ' internal'})) 
						.bind('mouseover mouseout', mouseAction)
						.data('areas', [])
						.data('context', context)
						.appendTo(ol);
				}
				li.data('areas').push(this);	//add the area to the li
				$(this).bind('mouseover mouseout', function(e) {li.trigger(e);});
			});
			ol.addClass('mw-collapsed')
			.makeCollapsible();
		});
	}

	//has at least one "imagehighlight" div, and canvas-capable browser:
	if ($(hilightDivMarker).length && $('<canvas>')[0].getContext)
		mw.loader.using('jquery.makeCollapsible', init);
});