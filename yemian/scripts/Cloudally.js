/**
 * Created by Administrator on 2017/2/7.
 */
$(document).ready(function () {
    // nav
    $(".navbar-nav li:last-child").mouseout(function () {
        $(this).addClass('listBg') ;
    });
    // 入驻流程
    $('.processList>li').mouseover(function () {
        $(this).addClass('option');
    });
    $('.processList>li').mouseout(function () {
        $(this).removeClass('option');
    });
    $('.register').mouseover(function () {
        $(this).addClass('registerOption');
    });
    $('.register').mouseout(function () {
        $(this).addClass('registerOption');
    });
    $('.apply').mouseover(function () {
        $(this).addClass('applyOption');
    });
    $('.apply').mouseout(function () {
        $(this).removeClass('applyOption');
    });
    $('.from').mouseover(function () {
        $(this).addClass('fromOption');
    });
    $('.from').mouseout(function () {
        $(this).removeClass('fromOption');
    });
    $('.generalize').mouseover(function () {
        $(this).addClass('generalizeOption');
    });
    $('.generalize').mouseout(function () {
        $(this).removeClass('generalizeOption');
    });
})