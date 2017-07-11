/**
 * Created by Administrator on 2017/1/16.
 */
$(document).ready(function () {
    //立即购买
    $('input[type="button"]').mouseover(function () {
        $(this).addClass('btnStyle')
    });
    $('input[type="button"]').mouseout(function () {
        $(this).removeClass('btnStyle')
    });
    $('.classifyCon>li').mouseover(function () {
        $(this).css({'border':'1px solid #dcdcdc'})
    });
    $('.classifyCon>li').mouseout(function () {
        $(this).css({'border':'1px solid #f0f0f0'})
    });
//        tab选项卡
    $('.classifyTab>li').mouseover(function () {
        $(this).addClass('btnClick').siblings().removeClass('btnClick');
        var index = $(this).index();
        $("#classifyList>li").eq(index).css("display","block").siblings().css("display","none");
    });
    //锚点显示
    $(".js-all").click(function(){
        //内容显示
        $('.js-all-content').css("display","block").siblings().css("display","none");
        //导航指示
        $(".js-all-nav").addClass('btnClick').siblings().removeClass("btnClick");
    });
    //锚点显示
    $(".js-new").click(function(){
        //内容显示
        $('.js-new-content').css("display","block").siblings().css("display","none");
        //导航指示
        $(".js-new-nav").addClass('btnClick').siblings().removeClass("btnClick");
    });
    //锚点显示
    $(".js-dis").click(function(){
        //内容显示
        $('.js-dis-content').css("display","block").siblings().css("display","none");
        //导航指示
        $(".js-dis-nav").addClass('btnClick').siblings().removeClass("btnClick");
    });
    //详情页跳转到店铺页面
    var url = location.href;
    var a = url.split('#');
    var b = a[1];
    if(b==''||undefined){
        return false;
    }
    if(b=='all'){
        //内容显示
        $('.js-all-content').css("display","block").siblings().css("display","none");
        //导航指示
        $(".js-all-nav").addClass('btnClick').siblings().removeClass("btnClick");
    }
    if(b=='new'){
        //内容显示
        $('.js-new-content').css("display","block").siblings().css("display","none");
        //导航指示
        $(".js-new-nav").addClass('btnClick').siblings().removeClass("btnClick");
    }
    if(b=='dis'){
        //内容显示
        $('.js-dis-content').css("display","block").siblings().css("display","none");
        //导航指示
        $(".js-dis-nav").addClass('btnClick').siblings().removeClass("btnClick");
    }
    // 店铺模板三
    $('.hover').mouseover(function () {
        $(this).addClass('threeBg');
        $(this).find('h3').addClass('threeColor');
        $(this).find('p').addClass('threeColor');
    });
    $('.hover').mouseout(function () {
        $(this).removeClass('threeBg');
        $(this).find('h3').removeClass('threeColor');
        $(this).find('p').removeClass('threeColor');
    });
    $('.js-collect').click(function(){
       var uid =$('.js-uid').val();
        var url = location.href;
        var id = $('.js-shopid').val();
        if(uid==''){
            var flag = confirm("您还未登录，是否现在去登录");
            if(flag){
                window.location.href = "/index.php/Home/User/login?url="+url;
            }
            return false;
        }
        if(id==''){
            alert('操作异常');
            return false;
        }
        $.post("/index.php/Home/Shop/addCollect",{'id':id},function(data){
           if(data.status==2){
               var flag = confirm("您还未登录，是否现在去登录");
               if(flag){
                   window.location.href = "/index.php/Home/User/login?url="+url;
               }
               return false;
           }else{
               alert(data.msg);
           }
        });
    });
})