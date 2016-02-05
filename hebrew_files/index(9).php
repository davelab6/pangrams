// used by [[תבנית:תוכן נטען]] and [[תבנית:קישור לתוכן נטען]]
// written by [[user:yonidebest]] and [[user:ערן]]
$(document).ready(function(){
	var lastLoadedId;

	// The content element is not actually available on every page,
	// so just don't anything if it's not there.
	if (mw.util.$content === null) {
		return;
	}

	function showLoadingContect(target, id) {
		if(lastLoadedId==id) {
			return;
		}

		lastLoadedId=id;
		// indicate in target that we are loading info in backgrounds
		var text = '<div class="floatright"><img alt="אנא המתינו" ';
		text += 'src="//upload.wikimedia.org/wikipedia/commons/thumb/d/de/Ajax-loader.gif/22px-Ajax-loader.gif" ';
		text += 'width="22" height="22" /></div>טעינת תוכן חדש, אנא המתינו...<br /><br />';
		var targetDiv = document.getElementById(target);
		targetDiv.innerHTML = text + targetDiv.innerHTML;

		// get link info and update content
		var selectedLink = document.getElementById(id);
		updateContentTarget(target, $(selectedLink).attr('linkto'));
	}

	function updateContentTarget(target, url) {
		$('#'+target).load(mw.util.getUrl(url, {action: 'render'}));
	}

	var targets = mw.util.$content.find('div.loadingContent');
	if (!targets.length) {
		return;
	}

	var links = mw.util.$content.find('span.loadingContentLink');

	if (!links.length) {
		return; // no links
	}

	targets.each(function(i,e) {
		var target = this.id;

		var linksOfTarget=links.filter(function() {
			var info = this.title.split("***"); // where [0] = title, [1] = target, [2] = linkto, [3] = default;
			return (info[1] == target);
		});

		// for each link that links to target
		linksOfTarget.each(function(linkCounter) {
			var info = this.title.split("***");
			var link = $('<a>');
			link.attr('id',target + linkCounter);
			link.attr('targetName',target);
			link.attr('linkto',info[2]);
			link.css('textDecoration','none');
			if (info[3] == "no‏"){ // not default
				link.attr('href', mw.util.getUrl(info[2]));
				link.click(function(){showLoadingContect(info[1],link.attr('id'));return false;});
			}
			else
			{
				link.attr('disabled','true');
				//link.removeAttribute("href");
			}

			link.append(info[0]);
			this.innerHTML = "";
			this.style.display = "none";
			link.insertBefore($(this));
		});

		this.maxlinks = linksOfTarget.length;

		// load default text / random text
		var info2 = this.title.split("***"); // where [0] = default link, [1] = is random
		if (info2[1] == "yes") { // random
			var randomNum = Math.floor(Math.random()*linksOfTarget.length);
			var randomLink = document.getElementById(target + randomNum);
			showLoadingContect(target, randomLink.id);
		}
		else if (info2[0] != "") {
			updateContentTarget(target, info2[0]);
		}
		this.title = "";
	});
});