/**
 * Created by Administrator on 2017/2/10.
 */
$(document).ready(function () {
    //分享
    window._bd_share_config={"common":{"bdSnsKey":{},"bdText":"分享到新浪微博","bdMini":"1","bdMiniList":["bdxc","tqf","douban","bdhome","sqq","thx","ibaidu","meilishuo","mogujie","diandian","huaban","duitang","hx","fx","youdao","sdo","qingbiji","people","xinhua","mail","isohu","yaolan","wealink","ty","iguba","fbook","twi","linkedin","h163","evernotecn","copy","print"],"bdPic":"","bdStyle":"1","bdSize":"32"},"share":{}};with(document)0[(getElementsByTagName('head')[0]||body).appendChild(createElement('script')).src='http://bdimg.share.baidu.com/static/api/js/share.js?v=89860593.js?cdnversion='+~(-new Date()/36e5)];
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
    $('.bds_sqq').mouseover(function () {
        $(this).addClass('bds_sqq_color')
    });
    $('.bds_sqq').mouseout(function () {
        $(this).removeClass('bds_sqq_color')
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
    $('.support').click(function () {
        $(this).toggleClass('support-green')
    });
//     收藏
    $('.collect').click(function () {
        $(this).toggleClass('collect-green')
    });
});