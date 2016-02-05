// add link to Special:mytalk for anons next to the "log in/create account" links at top of page
// if usermessage wa left to the anon user within last 3 days.

mw.loader.using(['mediawiki.user', 'mediawiki.util'], function() {
	if (mw.user.isAnon()) 
		$(function() {
			var cookieName = 'anon-has-talkpage';
			
			if ($('.usermessage').length)
				$.cookie(cookieName, 'exists', {path: '/', expires: 3});

			if ($.cookie(cookieName)) 
				$('li#pt-createaccount').after(
					$('<li>', {id: 'my-anon-talkpage'}).append(
						$('<a>', {href: mw.util.getUrl('Special:Mytalk'), title: 'דף השיחה מכיל הודעות שהשאירו לך משתמשים אחרים בנוגע לעריכותיך בוויקיפדיה'}).text('דף השיחה שלי')
					)
				);
		});
});