/**
 * Created by Administrator on 2017/2/22.
 */
$(document).ready(function () {
    // 评分

    var index=0;
    $("#star li").click(function(){
        index=$(this).attr("index");
        $("#star li").each(function(){
            if($(this).attr("index")<=index){
                $(this).find("img").attr("src","/Public/images/trained/starY.png");
            }else{
                $(this).find("img").attr("src","/Public/images/trained/star.png");
            }
        });
      $(".js-star-num").val(index);
    });
})
// 评论
$('#publish').click(function(){
    //评分星数每颗星2分
    var num = $(".js-star-num").val();
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
    //内容
    var str=$('#text').val();
    //登录判断
    var name = $(".js-name").text();
    //参数id
    var id = $(".js-courseid").val();
    if(name==''){
        var flag = confirm("您还未登录，是否现在去登录");
        var url = location.href;
        if(flag){
            window.location.href="/index.php/Home/User/login?url="+url;
        }
        return false;
    }
    if(str==''){
        alert("内容不能为空");
        return false;
    }
    if(id==''){
        alert("参数错误");
        return false;
    }
    var score = num*2;
    //console.log(str)
    if(str!=''&&str!=undefined){
        //ajax 请求
        $.post("/index.php/Home/Course/comment",{'id':id,'score':score,'content':str},function(data){
            if(data.status==1){
                alert(data.msg);
                $('#text').val('');
                $('tr:first').clone().appendTo($('.pinghuifu'));
                //$('.pinghuifu tr:last').find('.pl-touxang')
                //    .next('h5').html('用户姓名');
                //$('.pinghuifu tr:first').find('.time').html(current);
                //$('.pinghuifu tr:first').find('.phPar').html(str);
                //$('tr .js-clone').clone().appendTo($('.pinghuifu')).show();
                ////$('.pinghuifu tr:first').find('.pl-touxang')
                ////    .next('h5').html('用户姓名');
                ////添加时间
                $('.pinghuifu tr:first').find('i').html(current);
                //添加内容
                $('.pinghuifu tr:first').find('.phPar').html(str);
                //更改星星图片
                $('.pinghuifu tr:first').find('.js-star-img li').each(function(){
                    if($(this).attr("index")<=num){
                        $(this).find("img").attr("src","/Public/images/trained/starY.png");
                    }else{
                        $(this).find("img").attr("src","/Public/images/trained/star.png");
                    }
                });
                //修改分数
                $('.pinghuifu tr:first').find('.star span strong').html(num*2);
                $('tr:first').show();
                var a = $(".js-pl-num").text();
                a = parseInt(a) + parseInt(1);
                $(".js-pl-num").text(a);
            }else{
                alert(data.msg);
            }
        });
    }
})

