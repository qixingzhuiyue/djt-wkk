$(function(){
    function IsDate() {
        console.log(123);
        var str = $("#text").val();
        if (str.length != 0) {
            var reg = /^1[3-9]\d{9}$/;
            var r = str.match(reg);
            if (r == null){
                $("#tel").text("手机号码格式错误");
                console.log(12);

            } else {
                $("#tel").text("");
                console.log(1);

            }
        }
    }
    $("#text").change(function(){
        IsDate();
    });
    function PassWord() {
        var str = $("#code").val();
        if (str.length != 0) {
            var reg = /^[a-zA-Z0-9]{6,10}$/;
            var r = str.match(reg);
            if (r == null){
                $("#password").text("请输入密码（6-10位字母或数字）");

            } else {
                $("#password").text("");
            }
        }
    }
    $("#code").change(function(){
        PassWord();
    });
    function again() {
        var str = $("#again").val();
        if (str.length != 0) {
            var reg = /^[a-zA-Z0-9]{6,10}$/;
            var r = str.match(reg);
            if (r == null){
                $("#once").text("请再次输入密码）");

            } else {
                $("#once").text("");
            }
        }
    }


});