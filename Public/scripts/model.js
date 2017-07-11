
//console.log($(".color_p" ));

//产品类型移入移出事件
$(".color_p").hover(function(){
    $(this).addClass("red_p");
},function(){
    $(this).removeClass("red_p");

})

//console.log($(".ic_right"));
// 更多 移入移出事件
$(".ic_right").hover(function(){
    $(this).addClass("right_red");
},function(){
    $(this).removeClass("right_red");
})

//分享

//console.log($(".bds_weixin,.bds_tsina,.bds_sqq,.bds_qzone") );

$(".bds_weixin").hover(function(){

    $(this).addClass("bds_weixin_hover");
    $(this).removeClass("bds_weixin");

},function(){
    $(this).removeClass("bds_weixin_hover");
    $(this).addClass("bds_weixin");
})

$(".bds_tsina").hover(function(){

    $(this).addClass("bds_tsina_hover");
    $(this).removeClass("bds_tsina");

},function(){
    $(this).removeClass("bds_tsina_hover");
    $(this).addClass("bds_tsina");
})

$(".bds_sqq").hover(function(){

    $(this).addClass("bds_sqq_hover");
    $(this).removeClass("bds_sqq");

},function(){
    $(this).removeClass("bds_sqq_hover");
    $(this).addClass("bds_sqq");
})

$(".bds_qzone").hover(function(){

    $(this).addClass("bds_qzone_hover");
    $(this).removeClass("bds_qzone");

},function(){
    $(this).removeClass("bds_qzone_hover");
    $(this).addClass("bds_qzone");
})


//插件 分享 调用
window._bd_share_config={"common":{"bdSnsKey":{},"bdText":"分享","bdMini":"1","bdMiniList":["bdxc","tqf","douban","bdhome","sqq","thx","ibaidu","meilishuo","mogujie","diandian","huaban","duitang","hx","fx","youdao","sdo","qingbiji","people","xinhua","mail","isohu","yaolan","wealink","ty","iguba","fbook","twi","linkedin","h163","evernotecn","copy","print"],"bdPic":"","bdStyle":"1","bdSize":"32"},"share":{}};with(document)0[(getElementsByTagName('head')[0]||body).appendChild(createElement('script')).src='http://bdimg.share.baidu.com/static/api/js/share.js?v=89860593.js?cdnversion='+~(-new Date()/36e5)];