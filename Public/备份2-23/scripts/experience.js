/**
 * Created by Administrator on 2017/2/9.
 */
$(document).ready(function () {
    // nav
    $(".navbar-nav li:nth-child(6)").mouseout(function () {
        $(this).addClass('listBg') ;
    });
    // tab
    $('.main-tab>li').click(function () {
        $(this).addClass('greenColor').siblings().removeClass('greenColor');
        var index=$(this).index();
        $("#content>li").eq(index).css("display","block").siblings().css("display","none");
    });
    // 筛选
    $('.screen>li').click(function () {
        $(this).addClass('greenColor').siblings().removeClass('greenColor')
    });
    // 模块一
    $('.model-one>li').mouseover(function () {
        $(this).addClass('berGreen')
    });
    $('.model-one>li').mouseout(function () {
        $(this).removeClass('berGreen')
    });
    //查看更多内容
    $("#more").click(function() {
        if($(this).text()!="查看更多内容>>"){
            $(this).text("查看更多内容>>");
            $("#modelMore").hide();
        }else{
            $(this).text("隐藏更多内容>>");
            $("#modelMore").show();
        }
    });
    $("#more-two").click(function() {
        if($(this).text()!="查看更多内容>>"){
            $(this).text("查看更多内容>>");
            $("#comment-two").hide();
        }else{
            $(this).text("隐藏更多内容>>");
            $("#comment-two").show();
        }
    });
    // 发帖
    $('.post input').mouseover(function () {
        $(this).addClass('postBg')
    });
    $('.post input').mouseout(function () {
        $(this).removeClass('postBg')
    });
})