layui.define(['layer', 'form','jquery'], function (exports) {
    //var $ = jQuery = layui.$;
    var layer = layui.layer
        , form = layui.form;

    form.verify({
        
        account: function (value, item) { //value：表单的值、item：表单的DOM对象
            if (value == '') {
                return '请输入用户名';
            }

            if (!new RegExp("^[a-zA-Z0-9_\u4e00-\u9fa5\\s·]{6,20}$").test(value)) {
                return '用户名长度为6-20位，不能有特殊字符';
            }
            if (/(^\_)|(\__)|(\_+$)/.test(value)) {
                return '用户名首尾不能出现下划线\'_\'';
            }
            if (/^\d+\d+\d$/.test(value)) {
                return '用户名不能全为数字';
            }
        }

        //我们既支持上述函数式的方式，也支持下述数组的形式
        //数组的两个值分别代表：[正则匹配、匹配不符时的提示文字]
        , pass: [
            /^[\S]{6,12}$/
            , '密码必须6到12位，且不能出现空格'
        ],
        code: [
            /^[\S]{5,5}$/
            , '请输入5位验证码'
        ],
        prize:function(value,item){
            
            if( value > 10000 ){
                return '中奖几率数值在0-10000之间';
            }
        }
    });

    form.on('submit(form)', function (data) {
        //console.log( $("#username").val() ) ;
        //console.log(data.elem) //被执行事件的元素DOM对象，一般为button对象
        //console.log(data.form) //被执行提交的form对象，一般在存在form标签时才会返回
        console.log(data.field) //当前容器的全部表单字段，名值对形式：{name: value}
        //layer.msg('正在登录..') ;

        var loading = layer.load(2);

        $.post('/', data.field, function(data){
            console.log(data) ;
            layer.close(loading) ;
            if( data.status == 'success'){
                layer.msg('保存成功');
                window.location.href=data.url ;
            }else{
                var message = '保存失败';
                if( typeof data == 'Object' && data.message[0] ){
                    message = data.message[0];
                }
                layer.msg(message);
            }

        }).error(function(error){
            //console.log(error);
            if( error.status == 404 ){
                layer.msg('地址错误');
            }

            if( error.status == 500 ){
                layer.msg('服务器错误');
            }
            layer.close(loading) ;
        });

        return false; //阻止表单跳转。如果需要表单跳转，去掉这段即可。
    });
    exports('prize', {}); //注意，这里是模块输出的核心，模块名必须和use时的模块名一致
});  