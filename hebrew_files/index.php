/* פונקציות כלליות */

/* פונקציה להוספת כפתור לאחד מסרגלי הכלים בממשק, מתוך [[:en:User:Omegatron/monobook.js/addlink.js]] */
function addLink(where, url, name, id, title, key, after) {
    // addLink() accepts either an id or a DOM node, addPortletLink() only takes a node
    if (after && !after.cloneNode)
        after = document.getElementById(after);

    return mw.util.addPortletLink(where, url, name, id, title, key, after);
}