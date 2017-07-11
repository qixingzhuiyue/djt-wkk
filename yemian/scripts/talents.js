/**
 * Created by Administrator on 2017/2/8.
 */
$(document).ready(function () {
    // nav
    $(".navbar-nav li:nth-child(5)").mouseout(function () {
        $(this).addClass('listBg') ;
    });
    // tab选项卡
    $('#form-tab>li').click(function () {
        $(this).addClass('formTabBg').siblings().removeClass('formTabBg');
        var index=$(this).index();
        $("#form-con>li").eq(index).css("display","block").siblings().css("display","none");
    })
    // 性别选择
    $('#sex>li input').click(function () {
        $(this).find('span').toggleClass("sexY");
        $("#sex>li input[name='sex']").next("span").removeClass("sexY");
        $(this).next().addClass("sexY");
    })
    //收费选择
    $('#charge>li input').click(function () {
        $(this).find('span').toggleClass("chargeY");
        $("#charge>li input[name='charge']").next("span").removeClass("chargeY");
        $(this).next().addClass("chargeY");
    })
    // 教育形式选择
    $('#shape>li input').click(function () {
        $(this).find('span').toggleClass("chargeY");
        $("#shape>li input[name='shape']").next("span").removeClass("chargeY");
        $(this).next().addClass("chargeY");
    })
    // //        显示文件名
    // $(function () {
    //     $('input[id=up_img]').change(function () {
    //         $('.show').html($(this).val());
    //     });
    // });shape
    // 上传图片预览
    window.onload = function () {
        new uploadPreview({ UpBtn: "up_img", DivShow: "imgdiv", ImgShow: "imgShow" });
        //上传营业执照
        new uploadPreview({ UpBtn: "businessUp", DivShow: "businessDiv", ImgShow: "businessImg" });
        //上传公司环境图片
        new uploadPreview({ UpBtn: "environmentUp", DivShow: "environmentDiv", ImgShow: "environmentImg" });
    }
    // 表单验证
    function verification () {
        var x=document.forms["myForm"]
        ["fname"].value;
        if(x==null || x==""){
            alert("请将信息填写完整");
            return false
        }
    }
})