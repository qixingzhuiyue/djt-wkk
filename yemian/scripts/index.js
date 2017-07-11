/**
 * Created by Administrator on 2017/1/18.
 */
$(document).ready(function () {
    // nav
    $(".navbar-nav li:first-child").mouseout(function () {
        $(this).addClass('listBg') ;
    });
    //    当前日期
    $(function(){
        var mydate = new Date();
//        var t=mydate.toLocaleString();
        var min=mydate.getMinutes();
        if(min<=9){
            var min=('0'+mydate.getMinutes());
        };
        var sec=mydate.getSeconds();
        if(sec<=9){
            var sec=('0'+mydate.getSeconds())
        }
        console.log(min);
        var t=mydate.getFullYear()+'.'+(mydate.getMonth()+1)+'.'+mydate.getDate()+'   '+mydate.getHours()+':'+min+':'+sec;
        $("#time").text(t);
    });
//        主要业务悬停样式
    $('.business>li').mouseover(function () {
        $(this).addClass('listBg')
    });
    $('.business>li').mouseout(function () {
        $(this).removeClass('listBg')
    });
    $('.enterprise').mouseover(function () {
        $(this).addClass('enterprise_wh');
    });
    $('.enterprise').mouseout(function () {
        $(this).removeClass('enterprise_wh');
    });
    $('.website').mouseover(function () {
        $(this).addClass('website_wh');
    });
    $('.website').mouseout(function () {
        $(this).removeClass('website_wh');
    });
    $('.activity').mouseover(function () {
        $(this).addClass('activity_wh');
    });
    $('.activity').mouseout(function () {
        $(this).removeClass('activity_wh');
    });
    $('.generalize').mouseover(function () {
        $(this).addClass('generalize_wh');
    });
    $('.generalize').mouseout(function () {
        $(this).removeClass('generalize_wh');
    });
    //优秀企业
    $('.excellent>ul>li').mouseover(function () {
        $(this).find('p').addClass('greenColor');
    });
    $('.excellent>ul>li').mouseout(function () {
        $(this).find('p').removeClass('greenColor');
    });
    // 组织活动
    $('.list>li').mouseover(function () {
        $(this).addClass('borGreen')
    });
    $('.list>li').mouseout(function () {
        $(this).removeClass('borGreen')
    });
    // 企业招聘
    $('.recruitL').mouseover(function () {
        $(this).addClass('greenColor')
    });
    $('.recruitL').mouseout(function () {
        $(this).removeClass('greenColor')
    });
    $('.recruitR>li').mouseover(function () {
        $(this).addClass('greenColor')
    });
    $('.recruitR>li').mouseout(function () {
        $(this).removeClass('greenColor')
    });
    // 维沃柯简介
    $('.introductionR ul li').mouseover(function () {
        $(this).find('h4').addClass('greenColor')
    });
    $('.introductionR ul li').mouseout(function () {
        $(this).find('h4').removeClass('greenColor')
    });
    // 企业类型
    $('.type .typeList li').mouseover(function () {
        $(this).addClass('borGreen')
    });
    $('.type .typeList li').mouseout(function () {
        $(this).removeClass('borGreen')
    });
    // 企业类型
    $('#type-tab>li').click(function () {
        $(this).addClass('greenColor').siblings().removeClass('greenColor');
        var index =$(this).index();
        $("#type-list>li").eq(index).css("display","block").siblings().css("display","none");
    })
    // 滑动banner
    $('#type-business li').mouseover(function () {
        $(this).addClass('greenColor').siblings().removeClass('greenColor');
    })
    var tan=0;
   $('.type-left').click(function () {
        // $('.type-over').css('margin-left','-124px');
       tan++;
        $('.type-business').css({'transform':'translateX('+(-152*tan )+'px)'});
        if(tan>=3){
            tan=0;
        }
    });
    $('.type-right').click(function () {
        // $('.type-over').css('margin-left','-124px');
        tan--;
        $('.type-business').css({'transform':'translateX('+(152*tan )+'px)'});
        if(tan<=-3){
            tan=0;
        }
    });
    // 新闻、资讯、客服
    $('.service p').mouseover(function () {
        $(this).addClass('greenColor')
    });
    $('.service p').mouseout(function () {
        $(this).removeClass('greenColor')
    });
    // 友情链接
    $('.link p').mouseover(function () {
        $(this).addClass('greenColor')
    });
    $('.link p').mouseout(function () {
        $(this).removeClass('greenColor')
    });

})