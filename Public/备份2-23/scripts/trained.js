/**
 * Created by Administrator on 2017/2/11.
 */
$(document).ready(function () {
    // nav
    $(".navbar-nav li:nth-child(4)").mouseout(function () {
        $(this).addClass('listBg') ;
    });
    var myVideo = document.getElementById('video');//获取video元素
    var myVideoT = document.getElementById('videoT');//获取video元素
    var myVideoS = document.getElementById('videoS');//获取video元素
    var player=-1;
    $('#play').click(function () {
        player++;
        if(player%2==0){
            myVideo.play();
            $('#play').css('display','none');
        }else {
            myVideo.pause();
        }
    });
    //controls显示隐藏
    $('.videoO').mouseover(function () {
        video.controls = true;
//           video.load();
    });
    $('.videoO').mouseout(function () {
        video.controls = false;
//           video.load();
    });
    $('#playT').click(function () {
        player++;
        if(player%2==0){
            myVideoT.play();
            $('#playT').css('display','none');
        }else {
            myVideoT.pause();
        }
    });
    //controls显示隐藏
    $('.videoT').mouseover(function () {
        videoT.controls = true;
//           video.load();
    });
    $('.videoT').mouseout(function () {
        videoT.controls = false;
//           video.load();
    });
    $('#playS').click(function () {
        player++;
        if(player%2==0){
            myVideoS.play();
            $('#playS').css('display','none');
        }else {
            myVideoS.pause();
        }
    });
    //controls显示隐藏
    $('.videoS').mouseover(function () {
        videoS.controls = true;
//           video.load();
    });
    $('.videoS').mouseout(function () {
        videoS.controls = false;
//           video.load();
    });
    //滑动banner
    var tan=0;
    $('#left').click(function () {
        // $('.type-over').css('margin-left','-124px');
        tan++;
        $('.business').css({'transform':'translateX('+(-222*tan )+'px)'});
        if(tan>=3){
            tan=0;
        }
    });
    $('#right').click(function () {
        // $('.type-over').css('margin-left','-124px');
        tan--;
        $('.business').css({'transform':'translateX('+(222*tan )+'px)'});
        if(tan<=-3){
            tan=0;
        }
    });
    // // 弹框
    // $('.apple-click').click(function () {
    //     $('#apple').css('display','block')
    // });
    // $('.customization').click(function () {
    //     $('#customization').css('display','block')
    // })
    // $('.formClose').click(function () {
    //     $('.apple').css('display','none')
    // });
    // customization
})