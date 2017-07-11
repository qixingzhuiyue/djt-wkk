/**
 * Created by Administrator on 2017/1/18.
 */
$(document).ready(function () {
    // nav
    $(".navbar-nav li:first-child").mouseout(function () {
        $(this).addClass('listBg') ;
    });
    //    当前日期,每一秒执行一次函数获取当前时间并更新，达到钟表效果
    setInterval(function() {
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
        //console.log(min);
        var t=mydate.getFullYear()+'.'+(mydate.getMonth()+1)+'.'+mydate.getDate()+'   '+mydate.getHours()+':'+min+':'+sec;
        $("#time").text(t);
        //$('#current-time').text(now);
    }, 1000);

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
        $(this).find('a').css('border','1px solid #9ab126')
    });
    $('.excellent>ul>li').mouseout(function () {
        $(this).find('p').removeClass('greenColor');
        $(this).find('a').css('border','1px solid #dfdfdf')
    });
    // 组织活动
    $('.list>li').mouseover(function () {
        $(this).addClass('borGreen')
    });
    $('.list>li').mouseout(function () {
        $(this).removeClass('borGreen')
    });
    $('#planningTab>a').click(function () {
        var index =$(this).index();
        $("#planningList>div").eq(index).css("display","block").siblings().css("display","none");
        $(this).css({'color':'#9ab126','text-decoration':'none'}).siblings().css('color','#8c8c8c');
    })
    // 企业招聘
    $('.recruitL').mouseover(function () {
        $(this).addClass('greenColor');
        $(this).find('a').css('border','1px solid #9ab126')
    });
    $('.recruitL').mouseout(function () {
        $(this).removeClass('greenColor');
        $(this).find('a').css('border','1px solid #dfdfdf')
    });
    $('.recruitR>li').mouseover(function () {
        $(this).addClass('greenColor');
        $(this).find('img').css('border','1px solid #9ab126')
    });
    $('.recruitR>li').mouseout(function () {
        $(this).removeClass('greenColor');
        $(this).find('img').css('border','1px solid #dfdfdf')
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
        $(this).addClass('greenColor');
    });
    $('#type-business li').mouseout(function () {
        $(this).removeClass('greenColor');
    });
    var tan=0;
    var heightImg=$('#type-business li').width();
    var marginImg=parseInt($("#type-business li").css("margin-left"));
    var marginImg=marginImg*2;
    var quantity=($('#type-business li').length)-3;
   $('.type-left').click(function () {
        // $('.type-over').css('margin-left','-124px');
       tan++;
       //var heightImg=$('#type-business li a img').height;
       // console.log('#type-business li a img');
       $('.type-business').css({'transform':'translateX('+(-(heightImg+marginImg)*tan )+'px)'});
        if(tan>=quantity){
            tan=0;
        }
    });
    $('.type-right').click(function () {
        // $('.type-over').css('margin-left','-124px');
        tan--;
        $('.type-business').css({'transform':'translateX('+((heightImg+marginImg)*tan  )+'px)'});
        if(tan<=-quantity){
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
    // var myVideo = document.getElementById('video');//获取video元素
    // var player=-1;
    // $('#play').click(function () {
    //     player++;
    //     if(player%2==0){
    //         myVideo.play();
    //         $('#play').css('display','none');
    //     }else {
    //         myVideo.pause();
    //     }
    // });
//     //controls显示隐藏
//     $('.video').mouseover(function () {
//         video.controls = true;
// //           video.load();
//     });
//     $('.video').mouseout(function () {
//         video.controls = false;
// //           video.load();
//     });
    //分享
    $('#share .bds_weixin').mouseover(function(){
        $(this).addClass('bds_weixinY');
    });
    $('#share .bds_weixin').mouseout(function(){
        $(this).removeClass('bds_weixinY')
    });
    $('#share .bds_tsina').mouseover(function(){
        $(this).addClass('bds_tsinaY');
    });
    $('#share .bds_tsina').mouseout(function(){
        $(this).removeClass('bds_tsinaY')
    });
    $('#share .bds_qzone').mouseover(function(){
        $(this).addClass('bds_qzoneY');
    });
    $('#share .bds_qzone').mouseout(function(){
        $(this).removeClass('bds_qzoneY')
    });
    console.log('不懂得尊重二字，明显好不了。')
});