/**
 * Created by Administrator on 2017/2/10.
 */
$(document).ready(function () {
    //分享
    $('.bds_weixin').mouseover(function () {
        $(this).addClass('bds_weixin_color')
    });
    $('.bds_weixin').mouseout(function () {
        $(this).removeClass('bds_weixin_color')
    });
    $('.bds_tsina').mouseover(function () {
        $(this).addClass('bds_tsina_color')
    });
    $('.bds_tsina').mouseout(function () {
        $(this).removeClass('bds_tsina_color')
    });
    $('.bds_qzone').mouseover(function () {
        $(this).addClass('bds_qzone_color')
    });
    $('.bds_qzone').mouseout(function () {
        $(this).removeClass('bds_qzone_color')
    });
    // 回复
    //回复
    $(".re-show").click(function(){
        $(this).hide();
        $(this).next(".re-hide").show();
        $(this).parents("div").next(".reply").show();
        $(this).parents("div").prev("table").show();//显示回复列表
    })
    $(".re-hide").click(function(){
        $(this).hide();
        $(this).prev(".re-show").show();
        $(this).parents("div").next(".reply").hide();
        $(this).parents("div").prev("table").hide();//隐藏回复列
    })
//  评论
    $('.huifu-s').click(function () {
        $(this).toggleClass('huifu-s-green')
    });
//     支持
//    $('.support').click(function () {
//        $(this).toggleClass('support-green')
//    });
//     收藏
//    $('.collect').click(function () {
//        $(this).toggleClass('collect-green')
//    });
});
// 评论
$('.submitPL').click(function(){
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
    var str=$('.text').val();
    //用户登录判断
    var name = $(".js-user").val();
    if(name==''){
       var flag = confirm("您还未登录，是否现在登录");
        var url = location.href;
        if(flag){
            window.location.href="/index.php/Home/User/login?url="+url;
        }
        return false;
    }
    //被评论的id
    var id = $(".js-arc-id").val();
    if(id==''){
        alert('操作异常');
        return false;
    }
    //内容判断
    if(str==''){
        alert('内容为空');
        return false;
    }
    if(str!=''&&str!=undefined){
        //ajax请求
        $.post("/index.php/Home/Article/comment",{'id':id,'content':str},function(data){
            if(data.status==1){
                alert(data.msg);
                $('.text').val('');
                $('tr:last').clone().appendTo($('.pinghuifu'));
                //$('.pinghuifu tr:last').find('.pl-touxang')
                //    .next('h5').html('用户姓名');
                $('.pinghuifu tr:last').find('.time').html(current);
                $('.pinghuifu tr:last').find('.phPar').html(str);
                $('tr:last').show();
            }else{
                alert(data.msg);
                return false;
            }
        });
    }
})
// 精彩活动评论
$('.js-ac-submitPL').click(function(){
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
    var str=$('.text').val();
    //被评论的id
    var id = $(".js-arc-id").val();
    if(id==''){
        alert('参数错误');
        return false;
    }
    //用户登录判断
    var name = $(".js-user").val();
    if(name==''){
        alert('参数错误');
        return false;
    }
    //内容判断
    if(str==''){
        alert('内容为空');
        return false;
    }
    if(str!=''&&str!=undefined){
        //ajax请求
        $.post("/index.php/Home/Activity/comment",{'id':id,'content':str},function(data){
            if(data.status==1){
                alert(data.msg);
                $('tr:last').clone().appendTo($('.pinghuifu'));
                //$('.pinghuifu tr:last').find('.pl-touxang')
                //    .next('h5').html('用户姓名');
                $('.pinghuifu tr:last').find('.time').html(current);
                $('.pinghuifu tr:last').find('.phPar').html(str);
                $('tr:last').show();
            }else{
                alert(data.msg);
                return false;
            }
        });
    }
})
// 商品评论
$('.js-good-submitPL').click(function(){
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
    var str=$('.js-good-comment').val();
    //被评论的id
    var id = $(".js-good-id").val();
    if(id==''||id==undefined){
        alert('参数错误');
        return false;
    }
    //用户登录判断
    var name = $(".js-user").val();
    if(name==''||name==undefined){
        var flag = confirm("您还没有登录，是否现在去登录？");
        var url = window.href;
        if(flag){
            window.location.href="/index.php/Home/User/login?url="+url;
        }
        return false;
    }
    //内容判断
    if(str==''){
        alert('内容为空');
        return false;
    }
    if(str!=''&&str!=undefined){
        //ajax请求
        $.post("/index.php/Home/Shop/comment",{'id':id,'content':str},function(data){
            if(data.status==1){
                alert(data.msg);
                $('tr:first').clone().appendTo($('.pinghuifu'));
                //$('.pinghuifu tr:last').find('.pl-touxang')
                //    .next('h5').html('用户姓名');
                $('.pinghuifu tr:first').find('.time').html(current);
                $('.pinghuifu tr:first').find('.phPar').html(str);
                $('tr:first').show();
                $('.js-good-comment').val('');
            }else{
                alert(data.msg);
                return false;
            }
        });
    }
})
// 回复
$('.submitHF').click(function () {
    var obj2=$(this).parents('td').find('.re-show');
    var obj3=$(this).parents('td').find('.re-hide');
    var obj = $(this).parents('tr').find('p:last');
    var str=$(this).parents('.reply').find('textarea').val();
    var commentid = $(this).parents('.reply').find("input").val();
    var name = $(".js-hufui-id").attr("user");
    if(commentid==''){
        alert("参数错误");
        return false;
    }
    if(name==''){
        alert("你还没有登录");
        return false;
    }
    if(str==''){
        alert("内容不能为空");
        return false;
    }
    var obj1 = $(this).parents('.reply');
    // var shuru=$('.hfCon').val();
    if(str!=''&&str!=undefined){
        str = name+ "回复说："+str;
        var str1="<p style=''>"+str+"</p>";
        //ajax请求
        $.post("/index.php/Home/Article/replyComment",{'commentid':commentid,'content':str},function(data){
           if(data.status==1){
               alert(data.msg);
               obj.append(str1);
               obj3.hide();
               obj3.parents("div").next(".reply").hide();
               obj3.parents("div").prev("table").hide();//隐藏回复列
               obj2.parents('div').removeClass('huifu-s-green');
               obj2.show();
           }else{
               alert(data.msg);
           }
        });
    }
});

