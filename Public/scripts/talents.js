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
        $("#sex>li input[name='sex']").removeAttr("checked");
        $(this).attr("checked","checked");
        $(this).find('span').toggleClass("sexY");
        $("#sex>li input[name='sex']").next("span").removeClass("sexY");
        $(this).next().addClass("sexY");
    })
    //收费选择
    $('#charge>li input').click(function () {
        $("#charge>li input[name='charge']").removeAttr("checked");
        $(this).attr("checked","checked");
        $(this).find('span').toggleClass("chargeY");
        $("#charge>li input[name='charge']").next("span").removeClass("chargeY");
        $(this).next().addClass("chargeY");
    })
    // 教育形式选择
    $('#shape>li input').click(function () {
        $("#shape>li input[name='shape']").removeAttr("checked");
        $(this).attr("checked","checked");
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
    //如果是必填的，则加红星标识.
    $("form :input.required").each(function(){
        var $required = $("<strong class='high'> *</strong>"); //创建元素
        $(this).parent().append($required); //然后将它追加到文档中
        $('strong').css('color','red')
    });
    $("form :input.notNull").each(function(){
        var $notNull = $("<strong class='high'> *</strong>"); //创建元素
        $(this).parent().append($notNull); //然后将它追加到文档中
        $('strong').css('color','red')
    });
    // function validateForm() {
    //     var x=$('.required').val();
    //     if(x==null || x==""){
    //         alert("请将信息填写完整");
    //         return false
    //     }
    // }

})
// 表单验证 校企
function verification(){
    var flag=true;
    $(".required").each(function(){
        if($(this).val()==""){
            alert($(this).attr('notNull')+"不能为空");
           flag = false;
            return false;
        }
    });
    if(flag){
        $("#myForm").submit();
    }
}
function  verificationT() {
    // 表单验证 合作企业
    var flag=true;
    $(".notNull").each(function(){
        if($(this).val()==""){
            alert($(this).attr('notNull')+"不能为空");
             flag = false;
            return false;
        }
    });
    if(flag){
        $("#teamwork").submit();
    }
}
