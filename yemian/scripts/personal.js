/**
 * Created by gsl on 2017/1/23.
 */

//用户资料
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
    //选择地区
    $(".comAddress").distpicker({
        province: "省份",
        city: "城市",
        district: "地区"
    });
    //性别选择
    $('.genderRep').click(function(){
        $('.genderRep').css({'background':'none','border':'1px solid #e6e6e6'});
        $(this).css({background:'url(../images/personal/select.png)','background-size':'cover'});
    })

//活动管理
    // tab切换
    var i=0;
    $('.actLi').click(function(){
        i=$(this).index();
        console.log(134)
        $('.actLi').removeClass('g9a').addClass('g9');
        $(this).removeClass('g9').addClass('g9a');
        $('.actMangeTab').addClass('dn').eq(i).removeClass('dn');
    })
    //基本信息 之 查看
    $('.look').click(function(){
        $(this).parents('li').find('table').toggle();
    });
    //报名管理
    $('.signMange').click(function(){
        $('.activity').addClass('dn');
        $('.actMange').removeClass('dn');
    })
    //删除收藏
    $('.delete').click(function(){
        $(this).parents('li').remove();
    })

//订单管理
    //tab切换

    // （适应手机端）
    if($(window).width()<768){
        console.log(112321)
        $('.sinMoney').attr('colspan',"3").nextAll('td').wrap('<div class="wrap">');
    }
    //立即付款 按钮
    $('.pay').click(function(){
        window.location.href="payMoney.html";
    })
    //付款页面 付款 表单验证
    var flag=true;
    function checkAll(){
        var temp=$('input');
        for(var i=0;i<temp.length;i++){
            console.log(temp.eq(i).val())
            if(temp.eq(i).val()==''){
                temp.eq(i).css('border-color','red');
                flag=false;
            }
        }
        if(!flag){ alert('请将信息填写完整！');}
        return flag;
    }
    $('input').blur(function(){
        if($(this).val()!=''){
            $(this).css('border-color','#eeeeee');
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
//我的维币 立即支付
// $('.oncePay').click(function(){
//     window.location.href='walletSuccess.html';
// })
//回到我的维币
$('.backWallet').click(function(){
    window.location.href='myWallet.html';
})



