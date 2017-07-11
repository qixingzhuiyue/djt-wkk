/**
 * Created by Administrator on 2017/3/13.
 */
$(document).ready(function () {
    $('input').focus(function () {
        $(this).css('border','1px solid #ff6c6c')
    });
    $('input').blur(function () {
        $(this).css('border','1px solid #e6e6e6')
    });
    $('#tabList>a').mouseover(function () {
        $(this).css({'background':'#9ab126','color':'#fff'});
    });
    $('#tabList>a').mouseout(function () {
        $(this).css({'background':'#fff','color':'#333333'});
    });
    $('#tabList>a:first-child').mouseout(function () {
        $(this).css({'background':'#9ab126','color':'#fff'});
    });
})