/** NavTip functions *****
  * Adds a "tip" box for anon users.
  * By: [[User:Yonidebest]]
  */
function getRandomTip() {
 var navTipContent = new Array();
 navTipContent[0] = '<a href="/wiki/%D7%A4%D7%95%D7%A8%D7%98%D7%9C:%D7%A4%D7%95%D7%A8%D7%98%D7%9C%D7%99%D7%9D">פורטלים</a> משמשים שערים לנושאים מרכזיים ומרתקים כאחד, <a href="/wiki/%D7%A4%D7%95%D7%A8%D7%98%D7%9C:%D7%A4%D7%95%D7%A8%D7%98%D7%9C%D7%99%D7%9D">נסו אותם</a>!';
 navTipContent[1] = 'גם אתם יכולים לערוך ערכים בוויקיפדיה! <a href="/wiki/%D7%95%D7%99%D7%A7%D7%99%D7%A4%D7%93%D7%99%D7%94:%D7%95%D7%99%D7%A7%D7%99%D7%A4%D7%93%D7%99%D7%94_%D7%A6%D7%A2%D7%93_%D7%90%D7%97%D7%A8_%D7%A6%D7%A2%D7%93">למידע נוסף ראו כאן</a>.';
 navTipContent[2] = 'כדי להגיע מערך מסוים לערך המקביל בוויקיפדיה בשפה אחרת, לחצו על שם השפה הרצויה בתיבת "שפות אחרות" בצד הימני התחתון של המסך.';
 navTipContent[3] = 'אם מצאתם טעות או שיש לכם הערה לגבי ערך כלשהו, תוכלו לציין זאת ב<a href="/wiki/%D7%95%D7%99%D7%A7%D7%99%D7%A4%D7%93%D7%99%D7%94:%D7%93%D7%A3_%D7%A9%D7%99%D7%97%D7%94">דף השיחה</a> של הערך.';
 navTipContent[4] = 'אם חיפשתם ערך ולא מצאתם את המידע שחיפשתם, השאירו שאלה בדף <a href="/wiki/%D7%95%D7%99%D7%A7%D7%99%D7%A4%D7%93%D7%99%D7%94:%D7%94%D7%9B%D7%94_%D7%90%D7%AA_%D7%94%D7%9E%D7%95%D7%9E%D7%97%D7%94">הכה את המומחה</a> וחכו בסבלנות לתשובה.';
 navTipContent[5] = 'תוהים כיצד אפשר ליצור ערך חדש? דף <a href="/wiki/%D7%95%D7%99%D7%A7%D7%99%D7%A4%D7%93%D7%99%D7%94:%D7%90%D7%99%D7%9A_%D7%9C%D7%99%D7%A6%D7%95%D7%A8_%D7%93%D7%A3_%D7%97%D7%93%D7%A9">זה</a> יסביר לכם כיצד לעשות זאת.';
 navTipContent[6] = '<a href="/wiki/%D7%95%D7%99%D7%A7%D7%99%D7%A4%D7%93%D7%99%D7%94:%D7%A0%D7%99%D7%95%D7%95%D7%98" title="ויקיפדיה:ניווט">בדף הזה</a> תוכלו לראות מגוון של דרכים בהן תוכלו למצוא את המידע שאתם מחפשים.';
 navTipContent[7] = 'ביכולתכם להירשם לוויקיפדיה בחינם וליהנות ממגוון <a href="/wiki/%D7%95%D7%99%D7%A7%D7%99%D7%A4%D7%93%D7%99%D7%94:%D7%9C%D7%9E%D7%94_%D7%9C%D7%99%D7%A6%D7%95%D7%A8_%D7%97%D7%A9%D7%91%D7%95%D7%9F%3F">יתרונות</a>. להרשמה לחצו <a href="/wiki/%D7%A2%D7%96%D7%A8%D7%94:%D7%97%D7%A9%D7%91%D7%95%D7%9F_%D7%97%D7%93%D7%A9">כאן</a>.';
 navTipContent[8] = 'ב<a href="/wiki/%D7%95%D7%99%D7%A7%D7%99%D7%A4%D7%93%D7%99%D7%94:%D7%90%D7%A8%D7%92%D7%96_%D7%97%D7%95%D7%9C" title="ויקיפדיה:ארגז חול">ארגז החול</a> ניתן לבצע ניסויי עריכה. גשו לארגז החול, לחצו על הלשונית "עריכה" ונסו בעצמכם!';
 navTipContent[9] = 'בערכי יישובים ואתרים שונים ברחבי העולם, דוגמת <a href="/wiki/%D7%A8%D7%A2%D7%A0%D7%A0%D7%94">רעננה</a> ו<a href="/wiki/%D7%90%D7%91%D7%95_%D7%9E%D7%A0%D7%94">אבו מנה</a>, ניתן למצוא בראש הערך קואורדינטות הכוללות קישור למפות שונות.';
 navTipContent[10] = 'לכל ערך מוצמד "דף שיחה" שמאפשר לכם להעביר משוב על הערך. עיברו לדף השיחה באמצעות הלשונית "שיחה" שבראש הערך.';
 navTipContent[11] = 'מעוניינים לכתוב בוויקיפדיה? ה<a href="/wiki/%D7%A2%D7%96%D7%A8%D7%94:%D7%97%D7%A9%D7%91%D7%95%D7%9F_%D7%97%D7%93%D7%A9">הרשמה</a> חינם.';
 navTipContent[12] = 'ניתן להדפיס כל דף בוויקיפדיה על ידי לחיצה על "גרסת הדפסה" מתוך תיבת הכלים בצד ימין.';

 return navTipContent[Math.floor(Math.random()*navTipContent.length)];
}

function addNavTip() {
    // create the content
    var navTipMain = document.createElement('DIV');
    navTipMain.className = (skin == "vector") ? "portal" : "portlet";
    navTipMain.id = 'p-navTip';
    var navTipTitle = document.createElement('H3');
    navTipTitle.appendChild(document.createTextNode('טיפ'));
    navTipMain.appendChild(navTipTitle);
    var navTipBody = document.createElement('DIV');
    navTipBody.className = (skin == "vector") ? "body" : "pBody";
    navTipMain.appendChild(navTipBody);
    var ulWrapper = document.createElement('UL');
    navTipBody.appendChild ( ulWrapper );
    var listItem = document.createElement('LI');
    listItem.innerHTML = getRandomTip();
    ulWrapper.appendChild ( listItem );
    if ( skin == "vector" ) listItem.style.cssText = "margin-left:0.75em;";
     else
    {
        ulWrapper.style.cssText = "margin-right:0; margin-left:0;";
        listItem.style.cssText = "list-style: none outside none;";
    }

    var pCommunity = document.getElementById('p-community');
    pCommunity.parentNode.insertBefore(navTipMain, pCommunity);
}

if ( !wgUserName ) $(addNavTip);  // for anon only