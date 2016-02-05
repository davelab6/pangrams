/* אפשרות קישור לריענון דף אוטומטי, גם על ידי אנונימיים, ללא מעבר דרך טופס, עם הפרמטר auto=1 באמצעות שליחה אוטומטית של הטופס */
$(function() {
    if( mw.config.get('wgAction') == "purge" && mw.util.getParamValue("auto") == 1 && document.forms[0] && document.forms[0].elements["submit"] ) {
        document.forms[0].elements["submit"].click();
    }
});

/* הוספת כפתור לריענון דף אוטומטי, גם לאנונימיים - אפשרות יעילה יותר מהקודם כיוון שדף הטופס לא נטען בכלל */
$(function() {
    var formDiv = document.getElementById("PurgeBtn");
    if (formDiv) {
        var formObj = document.createElement( "form" );
        formObj.setAttribute( "method" , "post");
        formObj.setAttribute( "action" , mw.config.get('wgServer')+"/w/index.php?title=" + encodeURIComponent( mw.config.get('wgPageName') ) + "&action=purge" );
        var btn = document.createElement( "input" );
        btn.setAttribute( "type", "submit" );
        btn.setAttribute( "value", formDiv.getAttribute("title") );
        formObj.appendChild(btn);
        formDiv.appendChild(formObj);
    }
});