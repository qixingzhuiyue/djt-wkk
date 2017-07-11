/**
 * Created by Administrator on 2017/3/14.
 */
$(document).ready(function () {
    $('.signCost input').click(function () {
        $(this).addClass('freeAct').siblings().removeClass('freeAct')
    })
    // nav
    $(".navbar-nav li:nth-child(1)").mouseout(function () {
        $(this).addClass('listBg') ;
    });
    // 上传图片预览
    window.onload = function () {
        new uploadPreview({ UpBtn: "up_img", DivShow: "imgdiv", ImgShow: "imgShow" });
    }
})