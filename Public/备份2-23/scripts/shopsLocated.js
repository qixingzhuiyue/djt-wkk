/**
 * Created by Administrator on 2017/2/13.
 */
$(document).ready(function () {
    $(".navbar-nav li:nth-child(3)").mouseout(function () {
        $(this).addClass('listBg') ;
    });
    // content
    $('.content ul li').click(function () {
        $(this).addClass('greenColor').siblings().removeClass('greenColor');
        $(this).find('a').addClass('greenBor').parents('li').siblings().find('a').removeClass('greenBor');
    });
    // $('.content>ul>li').mouseout(function () {
    //     $(this).removeClass('greenColor');
    //     $(this).find('a').removeClass('greenBor');
    // });
    //$('.sure').click(function () {
    //    $('#succeed').css('display','block')
    //})
})