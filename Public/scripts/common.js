/**
 * Created by nhy on 2016/6/28.
 */
(function (doc, win) {
    var docEl = doc.documentElement,
        resizeEvt = 'orientationchange' in window ? 'orientationchange' : 'resize',
        recalc = function () {
            var clientWidth = docEl.clientWidth;
            if (!clientWidth) return;
            docEl.style.fontSize = 100 * (clientWidth / 1920) + 'px';
            $(function(){
    $("body *").each(function(){
        if(parseFloat($(this).css("font-size"))<12){
            $(this).css("font-size",12);
        }
    });
});
        };
    if (!doc.addEventListener) return;
    win.addEventListener(resizeEvt, recalc, false);
    doc.addEventListener('DOMContentLoaded', recalc, false);
})(document, window);


// $(function(){
//     $("body *").each(function(){
//         if(parseFloat($(this).css("font-size"))<12){
//             $(this).css("font-size",12);
//         }
//     });
// });