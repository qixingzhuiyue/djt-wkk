/**
 * Created by gsl on 2017/1/23.
 */
//适应iPhone5布局
//用户资料
    //填写输入框,并替换掉特殊字符
    //$(':text,textarea').blur(function(){
    //    if($(this).val()!=''){
    //        $(this).css('outline','none');
    //    }else $(this).css('outline','1px solid red');
    //    var str= new RegExp("[`~!@#$^&*()=|{}':;',\\[\\].<>/?~！@#￥……&*（）——|{}【】‘；：”“'。，、？]");
    //    var tem="";
    //    for (var i = 0; i < $(this).val().length; i++) {
    //        tem = tem+$(this).val().substr(i, 1).replace(str, '');
    //    }
    //    $(this).val(tem);
    //})
    // 上传图片
    var image = '';
    function selectImage(file) {
        if (!file.files || !file.files[0]) {
            return;
        }
        var reader = new FileReader();
        var name=file.id;
        console.log(name)
        reader.onload = function (evt) {
            image = evt.target.result;
            $('#'+name).prevAll('img').attr('src',image);
        }
        reader.readAsDataURL(file.files[0]);
    }
//    //选择地区
    $(".comAddress").distpicker({
        province: "省份",
        city: "城市",
        district: "地区"
    });
//    //性别选择
    $('.genderRep').click(function(){
        $('.genderRep').css({'background':'none','border':'1px solid #e6e6e6'});
        $(this).css({background:'url(../images/personal/select.png)','background-size':'cover'});
    })

////活动管理
//    // tab切换
    var i=0;
    $('.actLi').click(function(){
        i=$(this).index();
        //console.log(134)
        $('.actLi').removeClass('g9a').addClass('g9');
        $(this).removeClass('g9').addClass('g9a');
        $('.actMangeTab').addClass('dn').eq(i).removeClass('dn');
    })
//    //报名管理隐藏
//    $('.look').click(function(){
//        $(this).parents('li').find('table').toggle();
//    });
////    //报名管理
//    $('.signMange').click(function(){
//        $('.activity').addClass('dn');
//        $('.actMange').removeClass('dn');
//    })
    //删除收藏
    $('.delete').click(function(){
        var id = $(this).attr('collectid');
        if(id==''){
            alert('参数错误');
            return false;
        }
        var obj = $(this);
        var flag = confirm("确认删除收藏的活动吗?");
        if(flag){
            $.post("/index.php/Home/Activity/delCollect",{'id':id},function(data){
                if(data.status==1){
                    alert('删除成功');
                    obj.parents('li').remove();
                }else{
                    alert(data.msg);
                }
            });
        }
    })
//
////订单管理
//    //tab切换
//
//    // （适应手机端）
    if($(window).width()<768){
        //console.log(112321)
        $('.sinMoney').attr('colspan',"3").nextAll('td').wrap('<div class="wrap">');
    }
//    //立即付款 按钮
//    $('.pay').click(function(){
//        window.location.href="payMoney.html";
//    })
//    //付款页面 付款 表单验证
//
    //活动表单验证
    function checkAll(){
        var flag=true;
        var temp=$('input');
        var content = ue.hasContents();
        for(var i=0;i<temp.length;i++){
            console.log(temp.eq(i).val())
            if(temp.eq(i).val()==''){
                temp.eq(i).css('border-color','red');
                flag=false;
            }
            if(!content){
                $(".js-ue-content").css('border-color','red');
                flag=false;
            }
        }
        if(!flag){
            alert('请将信息和详情填写完整！');
            return false;
        }else{
            $(".js-act-form").submit();
        }
    }
    //商品订单提交
function checkGoodForm(){
    var flag=true;
    var temp=$(":input");
    for(var i=0;i<temp.length;i++){
        console.log(temp.eq(i).val())
        //隐藏域内容不能为空
        if(temp.eq(i).attr('type')=='hidden'){
            if(temp.eq(i).val()==''||temp.eq(i).val()==undefined){
                alert('参数异常');return false;
            }
        }else{
            if(temp.eq(i).hasClass("required")){
                if(temp.eq(i).val()==''||temp.eq(i).val()==undefined){
                    temp.eq(i).css('border-color','red');
                    flag=false;
                }else{
                    //验证手机格式
                    if(temp.eq(i).hasClass('js-phone')){
                        var preg = /^1\d{10}$/;
                        var phone = temp.eq(i).val();
                        if(!(preg.test(phone))){
                            temp.eq(i).css('border-color','red');
                            temp.eq(i).next('span').text('手机号码格式不正确');
                            flag=false;
                        }
                    }
                }
            }
        }
    }
    if(!flag){
        alert('请将信息和详情填写完整！');
        return false;
    }else{
      return true;
    }
}
    $('input').blur(function(){
        if($(this).val()!=''){
            $(this).css('border-color','#eeeeee');
        }else{
            if($(this).hasClass('required')){
                $(this).css('border-color','red');
            }
        }
    })
    //选择付款方式
    $('.select').click(function(){
        console.log(22)
        $('.select').removeClass('selected');
        $(this).addClass('selected')
    })
    $('.back').click(function(){
        window.location.href="orderMange.html";
    })
//    //查看订单详情
    $('.checkDetail').click(function(){
        window.location.href="actDetail_payMoney.html";
    })
////我的维币 立即支付
$('.oncePay').click(function(){
    window.location.href='myWalletSuccess.html';
})
////回到我的维币
$('.backWallet').click(function(){
    window.location.href='myWallet.html';
});
    // 活动管理
$('.lastBottom span').click(function () {
    $(this).css('color','#9ab126').siblings().css('color','#999');
    $(this).siblings('input').css('color','#fff')
});
// 订单管理
$('.leftNav a').hover(function () {
    $(this).css('color','#3c3c3c');
});

$('a').click(function () {
    $(this).css('text-decoration','none')
});
$('a').hover(function () {
    $(this).css('text-decoration','none')
});






