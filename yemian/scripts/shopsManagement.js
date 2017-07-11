/**
 * Created by Administrator on 2017/2/14.
 */
$(document).ready(function () {
    // 上传图片预览
    window.onload = function () {
        new uploadPreview({ UpBtn: "up_img", DivShow: "imgdiv", ImgShow: "imgShow" });
        new uploadPreview({ UpBtn: "logo_up", DivShow: "logoDiv", ImgShow: "logoImg" });
    }
    // tab
    $('#tab-icon>li').click(function () {
        $(this).addClass('balckBg').siblings().removeClass('balckBg');
        var index=$(this).index();
        $('#content>li').eq(index).css("display","block").siblings().css("display","none");
    })
    // 编辑删除
    $('.edit').mouseover(function () {
        $(this).addClass('editR')
    });
    $('.edit').mouseout(function () {
        $(this).removeClass('editR')
    });
    $('.delete').mouseover(function () {
        $(this).addClass('deleteR')
    });
    $('.delete').mouseout(function () {
        $(this).removeClass('deleteR')
    });
    // 选择
    $('#recommend>li input').click(function () {
        $(this).find('span').toggleClass("recommendY");
        $("#recommend>li input[name='recommend']").next("span").removeClass("recommendY");
        $(this).next().addClass("recommendY");
    });
    $('.state>li input').click(function () {
        $(this).find('span').toggleClass("recommendY");
        $(".state>li input[name='state']").next("span").removeClass("recommendY");
        $(this).next().addClass("recommendY");
    });
    // 编辑店铺
    $('.close').click(function () {
        $('.popping').css('display','none')
    })
    $('.edit').click(function () {
        $('#popping').css('display','block')
    });
    // 添加
    $('#add-icon').click(function () {
        $('#addition').css('display','block')
    })
})