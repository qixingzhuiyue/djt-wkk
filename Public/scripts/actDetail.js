/**
 * Created by Administrator on 2017/2/21.
 */
//立即报名
$('.onSign').click(function(){
    var id = $(".js-uid").val();
    if(id==''){
        var flag = confirm("你还未登录，是否现在去登录");
        //alert(location.href);
        var url = location.href;
        if(flag){
            window.location.href="/index.php/Home/User/login?url="+url;
            return false;
        }else{
            return false;
        }
    }
    var vcoinNum = $('.js-vcoinNum').val();
    var flag1 = confirm("您需支付"+vcoinNum+"维币，确定报名吗？");
    if(flag1){
        $('.signWrap').removeClass('dn');
    }else{
        return false;
    }
})
//确定报名
$('.js-sub').click(function(){
    //window.location.href='signSuccess.html';
    var id = $(".js-acid").val();
    var name = $(".js-acname").val();
    var mobile = $(".js-mobile").val();
    var company = $(".js-company").val();
    if(id==''){
        alert("参数错误");
        return false;
    }
    if(name==''){
        alert("名字不能为空");
        return false;
    }
    if(mobile==''){
        alert("手机号不能为空");
        return false;
    }
    if(company==''){
        alert("公司不能空");
        return false;
    }
   $(".js-form").submit();
})
//关闭按钮
$('.close').click(function(){
    $('.signWrap').addClass('dn');
})
//点击关注

//点击收藏
$('.js-collect').click(function(){
    var status = $(this).attr("value");
    var id = $(".js-acid").val();
    var user = $(".js-info-name").text();
    if(user==''){
        var flag = confirm("您还未登录，是否现在去登录");
        var url = location.href;
        if(flag){
            window.location.href="/index.php/Home/User/login?url="+url;
        }
        return false;
    }
    if(id==0||id==undefined){
        alert("参数错误");
        return false;
    }
    if(status==0){
        //收藏
        $.post("/index.php/Home/Activity/addCollect",{'id':id},function(data){
           if(data.status==1){
               alert(data.msg);
               $(".js-collect").css('color','#9ab126');
               $(".js-collect-span").text('已收藏');
               $(".js-collect-p").css('color','#9ab126');
               $(".js-collect-p").html('已收藏&nbsp;');
               $(".js-collect").attr("value",1);
               $(".js-collect-img").attr('src','/Public/images/postDetails/collectGreen.png');
           }else{
               alert(data.msg);
           }
        });
    }
    if(status==1){
        //取消收藏
        $.post("/index.php/Home/Activity/delCollect",{'id':id},function(data){
            if(data.status==1){
                alert(data.msg);
                $(".js-collect").css("color",'#3c3c3c');
                $(".js-collect-span").text('收藏');
                $(".js-collect-p").css("color",'#3c3c3c');
                $(".js-collect-p").html('收藏&nbsp;');
                $(".js-collect").attr("value",0);
                $(".js-collect-img").attr('src','/Public/images/postDetails/collect.png');
            }
        });
    }
    //if(cc){
    //    $(this).css('color','#9ab126').find('img').attr('src','/Public/images/postDetails/collectGreen.png');
    //    cc=false;
    //}
    //else{
    //    $(this).css('color','#3c3c3c').find('img').attr('src','/Public/images/postDetails/collect.png');
    //    cc=true;
    //}
});
//点击分享
var num=0;
$('.bdsharebuttonbox a').mouseover(function(){
    num=$(this).index()+1;
    $(this).addClass('change'+num);
}).mouseleave(function(){
    num=$(this).index()+1;
    $(this).removeClass('change'+num);
})
//点击关注和告诉
var co=true;
var tt=true;
$('.visit').click(function(){
    if(co){
        co=changeColor(co,$(this));
    }else{
        co=reColor(co,$(this))
    }
})
$('.tell').click(function(){
    if(tt){
        tt= changeColor(tt,$(this));
    }else{
        tt= reColor(tt,$(this))
    }
});
//选择价格类型

$('.costType span').click(function(){
    $('.costType span').removeClass('bd9').addClass('bdd');
    $(this).addClass('bd9').removeClass('bdd');
})
//    改变颜色
function changeColor(num,obj){
    obj.addClass('b9a wh').removeClass('g9a');
    num=false;
    return num;
}
function reColor(num,obj){
    obj.addClass('g9a').removeClass('b9a wh');
    num=true;
    return num;
}
//发表评论
$('.pubContent').click(function(){
    var id = $(".js-acid").val();
    var name = $(".js-info-name").text();
    var content = $(".js-pinglun-content").val();
    if(name==''){
        var flag = confirm("您还未登录，是否现在去登录");
        var url = location.href;
        if(flag){
            window.location.href="/index.php/Home/User/login?url="+url;
        }
        return false;
    }
    if(content==''){
        alert("内容不能为空");
        return false;
    }
    if(id==''){
        alert("操作异常");
        return false;
    }
    var time=new Date();
    var y=time.getFullYear();
    var m=time.getMonth()+1;
    m=m>9?m:'0'+m;
    var d=time.getDate();
    d=d>9?d:'0'+d;
    var h=time.getHours();
    h=h>9?h:'0'+h;
    var mm=time.getMinutes();
    mm=mm>9?mm:'0'+mm;
    var s=time.getSeconds();
    s=s>9?s:'0'+s;
    var current=y+'-'+m+'-'+d+' '+h+':'+mm+':'+s;
    var str=$('textarea').val();
    if(str!=''&&str!=undefined){
        //ajax 请求
        $.post("/index.php/Home/Activity/addComment",{'id':id,'content':content},function(data){
           if(data.status==1){
               alert(data.msg);
               $(".js-pinglun-content").val('');
               $('.li:last').clone().prependTo($('.contentList'));
               $('.contentList li:first').find('.contentName').next('.contentTime').html(current);
               $('.contentList li:first').find('.content').html(str);
               $('.contentList li:first').show();
               //评论数加1
               var num = $(".js-pinglun-num").text();
               num = parseInt(num) + parseInt(1);
               $(".js-pinglun-num").text(num);
           }else{
               alert(data.msg);
           }
        });
    }
})