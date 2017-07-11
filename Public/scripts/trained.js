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
    var heightImg=$('#business li').width();
    var right=parseInt($("#business li").css("margin-right"));
    var quantity=($('#business li').length)-4;
     console.log(quantity);
    $('#left').click(function () {
        tan++;
        console.log(tan);
            var clone=$('.business li').first().clone();
            $('.business').append(clone);
            $('.business li').first().remove();
        $(this).find('img').attr('src','/Public/images/trained/leftG.png').parents('li').siblings().find('img').attr('src','/Public/images/trained/rightG.png');
    });
    $('#right').click(function () {
        tan--;
        console.log(tan);
            var clone=$('.business li').last().clone();
            $('.business').prepend(clone);
            $('.business li').last().remove();
        $(this).find('img').attr('src','/Public/images/trained/right.png').parents('li').siblings().find('img').attr('src','/Public/images/trained/left.png')
    });

    // 弹框
    $('.apple-click').click(function () {
        var id = $(".js-uid").val();
        if(id==''){
            var flag = confirm("你还未登陆，是否现在去登陆");
            var url = location.href;
            if(flag){
                window.location.href="/index.php/Home/User/login?url="+url;
            }
            return false;
        }
        $('#apple').css('display','block')
    });
    $('.customization').click(function () {
        $('#customization').css('display','block')
    })
    $('.formClose').click(function () {
        $('.apple').css('display','none')
    });
    // customization

});