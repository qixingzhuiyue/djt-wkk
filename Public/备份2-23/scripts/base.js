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
    //跳转客服
    $(".serviceBtn").click(function () {    $("html,body").animate({        scrollTop: 6000    },        "slow")});
    //  微信号
    $('.weChatBtn').click(function () {
        $('.weChat').toggle()
    })
    //返回顶部
    $(".topBtn").click(function () {    $("html,body").animate({        scrollTop: 0    },        "slow")});
})