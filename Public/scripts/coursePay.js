/**
 * Created by Administrator on 2017/2/20.
 */
$(document).ready(function () {
    //选择付款方式
    $('.select').click(function(){
        $('.select').removeClass('selected');
        $(this).addClass('selected')
    })
    $('.back').click(function(){
        window.location.href="orderMange.html";
    })
//        报名成功
    $('.js-sub').click(function () {
        //ajax请求
        var id =$( ".js-order").val();
        if(id==''){
            alert("参数错误");
        }else{
            $.post("/index.php/Home/Course/vPay",{'id':id},function(data){
               if(data.status==1){
                   $('#succeed').css('display','block');
               }else{
                   if(data.status==2){
                       var flag = confirm(data.msg);
                       if(flag){
                           window.location.href="/index.php/Home/Vcoin/myWallet";
                       }else{
                           return false;
                       }
                   }else{
                       alert(data.msg);
                   }
               }
            });
        }
        //
        //$('#succeed').css('display','block')
    })
    $('.succeed-con a input').mouseover(function () {
        $(this).addClass('bgGreen');
    })
    $('.succeed-con a input').mouseout(function () {
        $(this).removeClass('bgGreen');
    })
})