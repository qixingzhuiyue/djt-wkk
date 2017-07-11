/**
 * Created by Administrator on 2017/2/7.
 */
$(document).ready(function () {
    //nav
    $('.navbar-nav li').mouseover(function () {
        $(this).addClass('listBg') ;
    });
    $('.navbar-nav li').mouseout(function () {
        $(this).removeClass('listBg') ;
    });
    $(".navbar-nav li:nth-child(2)").mouseout(function () {
        $(this).addClass('listBg') ;
    });
    // $('.type-tab>li').mouseover(function () {
    //     $(this).addClass('greenColor')
    // });
    // $('.type-tab>li').mouseout(function () {
    //     $(this).removeClass('greenColor')
    // });
    $('.typeList>li').mouseover(function () {
        $(this).addClass('borGreen')
    });
    $('.typeList>li').mouseout(function () {
        $(this).removeClass('borGreen')
    });
    $('.seeIcon').mouseover(function () {
        $(this).addClass('seeIconG')
    });
    $('.seeIcon').mouseout(function () {
        $(this).removeClass('seeIconG')
    });
    $('.commentIcon').mouseover(function () {
        $(this).addClass('commentIconG')
    });
    $('.commentIcon').mouseout(function () {
        $(this).removeClass('commentIconG')
    });
    // tab
    $('#type-tab>li').click(function () {
        $(this).addClass('greenColor').siblings().removeClass('greenColor');
        var index =$(this).index();
        $("#type-list>li").eq(index).css("display","block").siblings().css("display","none");
    })
    // 分页
    $('.paging>li').mouseover(function () {
        $(this).addClass('green')
    });
    $('.paging>li').mouseout(function () {
        $(this).removeClass('green')
    });
    $('#xifenye').mouseover(function () {
        $(this).removeClass('green')
    });
    // $('.xiye').mouseover(function () {
    //     $(this).addClass('whiteness')
    // });
    // $('.xiye').mouseout(function () {
    //     $(this).removeClass('whiteness')
    // });
    // $('.mo').mouseover(function () {
    //     $(this).addClass('whiteness')
    // });
    // $('.mo').mouseout(function () {
    //     $(this).removeClass('whiteness')
    // });
    // 企业店铺
    $('.shopsIcon').mouseover(function () {
        $(this).addClass('shopsIconG')
    });
    $('.shopsIcon').mouseout(function () {
        $(this).removeClass('shopsIconG')
    });
    $('.gprsIcon').mouseover(function () {
        $(this).addClass('gprsIconG')
    });
    $('.gprsIcon').mouseout(function () {
        $(this).removeClass('gprsIconG')
    });
})