/**
 * Created by Administrator on 2017/1/16.
 */
$(document).ready(function () {
    //立即购买
    $('input[type="button"]').mouseover(function () {
        $(this).addClass('btnStyle')
    });
    $('input[type="button"]').mouseout(function () {
        $(this).removeClass('btnStyle')
    });
    $('.classifyCon>li').mouseover(function () {
        $(this).css({'border':'1px solid #dcdcdc'})
    });
    $('.classifyCon>li').mouseout(function () {
        $(this).css({'border':'1px solid #f0f0f0'})
    });
//        tab选项卡
    $('.classifyTab>li').mouseover(function () {
        $(this).addClass('btnClick').siblings().removeClass('btnClick');
        var index = $(this).index();
        $("#classifyList>li").eq(index).css("display","block").siblings().css("display","none");
    });
    // 店铺模板三
    $('.hover').mouseover(function () {
        $(this).addClass('threeBg');
        $(this).find('h3').addClass('threeColor');
        $(this).find('p').addClass('threeColor');
    });
    $('.hover').mouseout(function () {
        $(this).removeClass('threeBg');
        $(this).find('h3').removeClass('threeColor');
        $(this).find('p').removeClass('threeColor');
    });
})