/* שינוי קישורי ההעלאה: קישור אחד לדף הסבר (שממנו יש קישורים לדפים הרלוונטיים), וקישור ישיר לוויקישיתוף */
$(document).ready(function(){
  var commonsLink = "//commons.wikimedia.org/w/index.php?title=Special:UploadWizard&uselang=he";
  if (typeof(customCommonsLink) != 'undefined') commonsLink = customCommonsLink;
  try {
    if ( $('#t-upload').length )
        $('#t-upload').html('<a href="'+mw.util.getUrl('ויקיפדיה:העלאת_קובץ_לשרת')+'">' + ( mw.config.get('wgUserLanguage') == "he" || mw.config.get('wgUserLanguage') == "fairuse" ? 'העלאת קובץ לשרת' : 'Upload file to server' ) + '</a>' +
            ' / <a id="commonsLink" href="' + commonsLink + '">' + ( mw.config.get('wgUserLanguage') == "he" || mw.config.get('wgUserLanguage') == "fairuse" ? 'לוויקישיתוף' : "to commons" ) + '</a>');
  }
  catch(e)
  {
    return;// lets just ignore what's happened
  }
});