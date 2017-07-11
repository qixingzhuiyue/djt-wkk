/**
 * Created by Administrator on 2017/2/14.
 */
$(document).ready(function () {
    // 上传图片预览
    // window.onload = function () {
    //    new uploadPreview({UpBtn: "com_up", DivShow: "comDiv", ImgShow: "comImg"});
    //    new uploadPreview({UpBtn: "up_img", DivShow: "imgdiv", ImgShow: "imgShow"});
    //    new uploadPreview({UpBtn: "logo_up", DivShow: "logoDiv", ImgShow: "logoImg"});
    //    new uploadPreview({UpBtn: "license_up", DivShow: "licenseDiv", ImgShow: "licenseImg"});
    //    new uploadPreview({UpBtn: "up_edit", DivShow: "editDiv", ImgShow: "editShow"});
    // };
    // tab
    $('#tab-icon>li').click(function () {
        $(this).addClass('balckBg').siblings().removeClass('balckBg');
        var index = $(this).index();
        $('#content>li').eq(index).css("display", "block").siblings().css("display", "none");
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
        $("#recommend>li input[name='type']").next("span").removeClass("recommendY");
        $(this).next().addClass("recommendY");
        $(this).attr("checked","checked");
    });
    $('#state>li input').click(function () {
        $(this).find('span').toggleClass("recommendY");
        $(".state>li input[name='status']").next("span").removeClass("recommendY");
        $(this).next().addClass("recommendY");
    });
    // // 编辑店铺
    // $('.close').click(function () {
    //     $('.popping').css('display', 'none')
    // })
    // $('.edit').click(function () {
    //     $('#popping').css('display', 'block')
    // });
    // 添加
    $('#add-icon').click(function () {
        $('#addition').css('display', 'block')
    })
    // 维币换算
    $('#original').keyup(function () {
        var Yuan = $('#original').val();
        $("#originalW").html(Yuan * 10);
    });
    $('#favorable').keyup(function () {
        var Yuan = $('#favorable').val();
        $("#favorableY").html(Yuan * 10);
    });
    // 表单验证
    //如果是必填的，则加红星标识.
    $(".required").each(function () {
        var $required = $("<strong class='high'> *</strong>"); //创建元素
        $(this).parent().append($required); //然后将它追加到文档中
        $('strong').css('color', 'red')
    });
    // 删除
    //$(".delete").click(function () {
    //     var result=confirm("确认删除商品吗？");
    //     if(result){
    //         $(this).parents('.model-two-list>li').remove()
    //     }
    //    // $(this).parents('.model-two-list>li').remove()
    //});
    //    删除
    $(".js-news-delete").click(function () {
        var result=confirm("确认删除新闻吗？");
        if(result){
            var id = $(this).attr("value");
            if(id){
                $(this).parents('.news>li').remove();
                $.post("/index.php/Home/News/delNews",{'id':id},function(data){
                    alert(data.msg);
                });
            }else{
                alert("该消息信息不全");
            }
        }
    });
    $(".js-product-del").click(function () {
        var result=confirm("确认删除产品吗？");
        if(result){
            var id = $(this).attr("value");
            if(id){
                $(this).parents('.product>li').remove();
                $.post("/index.php/Home/Company/delProduct",{'id':id},function(data){
                    alert(data.msg);
                });
            }else{
                alert("该消息信息不全");
            }
        }
    });
    //删除帖子
    $(".js-article-del").click(function () {
        var result=confirm("确认删除文贴吗？");
        if(result){
            var id = $(this).attr("value");
            if(id){
                $(this).parents('.product>li').remove();
                $.post("/index.php/Home/Article/delArticle",{'id':id},function(data){
                    alert(data.msg);
                });
            }else{
                alert("该消息信息不全");
            }
        }
    });
    //删除商品
    $(".js-good-del").click(function () {
        var result=confirm("确认删除商品吗？");
        if(result){
            var id = $(this).attr("value");
            if(id){
                $(this).parents('.js-goods>li').remove();
                $.post("/index.php/Home/Shop/delGoods",{'id':id},function(data){
                    alert(data.msg);
                });
            }else{
                alert("该消息信息不全");
            }
        }
    });
    // a标签下划线
    $('a').click(function () {
        $(this).css('text-decoration','none')
    });
    $('a').hover(function () {
        $(this).css('text-decoration','none')
    });
});


