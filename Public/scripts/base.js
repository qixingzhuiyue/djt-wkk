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
    $(".serviceBtn").click(function () {    $("html,body").animate({        scrollTop: 5500    },        "slow")});
    //  微信号
    // $('#shareBtn').click(function () {
    //     $('#share').toggle();
    // });
    //返回顶部
    $(".topBtn").click(function () {    $("html,body").animate({        scrollTop: 0    },        "slow")});


    // var media=$('video');
    // media.attr('autoplay','autoplay').delay(1000).removeAttr('autoplay');
    // media.autoplay
    // 视频自动播放一秒
    // var media=$('video')[0];
    // $('video').attr('autoplay','autoplay')
    // setTimeout(function(){
    //     $('video')[0].pause();
    // },2000);

    //富文本编辑器图片
    /*当图片宽度大于容器时其等于容器宽度*/
    $('#edit img').each(function () {
        var title = $(this).attr('title');/*获取图片alt属性*/
        var alt=$(this).attr('alt');/*获取图片alt属性*/
        if(alt!=''&&alt!=undefined){
            alt = alt.replace(/(<a href='\/index.php\/Home\/Index\/index.html'>*)|(<\/a>*)/g,'');/*取出后台给某些关键字添加的a标签*/
            $(this).attr('alt',alt);/*重新赋值图片alt属性*/
        }
        if(title!=''&&title!=undefined){
            title = title.replace(/(<a href='\/index.php\/Home\/Index\/index.html'>*)|(<\/a>*$)/g,'');/*取出后台给某些关键字添加的a标签*/
            $(this).attr('title',title);/*重新赋值图片alt属性*/
        }
        // alert($(this).attr('alt'));
        //var jsImg=$(this).width();/*获取图片宽度*/
        //var heightImg=$(this).height();/*获取图片度高*/
        //var bili=heightImg/jsImg;/*比例*/
        //var contentL=$('.contentL').width();/*获取容器宽度*/
        //var jsPadding=parseInt($(".contentL").css("padding-left"));/*获取容器padding*/
        //var jsPadding=jsPadding*2;
        //var contentL=contentL-jsPadding;
        //if(jsImg>=contentL){
        //    $(this).width(contentL);
        //    var heightImg = contentL*bili;
        //    $(this).height(heightImg);
        //}
    })
    // a标签下划线
    $('a').click(function () {
        $(this).css('text-decoration','none')
    });
    $('a').hover(function () {
        $(this).css('text-decoration','none')
    });
});
