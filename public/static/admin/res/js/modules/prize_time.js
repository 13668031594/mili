layui.define(['layer', 'form', 'jquery', 'laydate'], function (exports) {
    //var $ = jQuery = layui.$;
    var layer = layui.layer
        , form = layui.form
        , laydate = layui.laydate;

    laydate.render({
        elem: '#start' //指定元素
        ,type: 'datetime'
    });

    laydate.render({
        elem: '#end' //指定元素
        ,type: 'datetime'
    });

    form.on('submit(form)', function (data) {
        //console.log( $("#username").val() ) ;
        //console.log(data.elem) //被执行事件的元素DOM对象，一般为button对象
        //console.log(data.form) //被执行提交的form对象，一般在存在form标签时才会返回
        //console.log(data.field) //当前容器的全部表单字段，名值对形式：{name: value}
        //layer.msg('正在登录..') ;

        var loading = layer.load(2);

        $.post('/', data.field, function (data) {
            console.log(data);
            layer.close(loading);
            if (data.status == 'success') {
                layer.msg('保存成功');
                window.location.href = data.url;
            } else {
                var message = '保存失败';
                if (typeof data == 'Object' && data.message[0]) {
                    message = data.message[0];
                }
                layer.msg(message);
            }

        }).error(function (error) {
            //console.log(error);
            if (error.status == 404) {
                layer.msg('地址错误');
            }

            if (error.status == 500) {
                layer.msg('服务器错误');
            }

            layer.close(loading);
        });
        /* $.ajax({
            type: "post",
            url: url ,
            data: data.field,
            dataType: "json",
            success: function(result){
            if(result.code == 0){
                layer.msg(result.msg,{icon: 1});
                layer.closeAll('page');
                return done(result.data.src);
            }else if(result.code == -1){
                 layer.alert(result.msg,{icon: 2});
             }
        }
     }); */

        return false; //阻止表单跳转。如果需要表单跳转，去掉这段即可。
    });
    exports('prize_time', {}); //注意，这里是模块输出的核心，模块名必须和use时的模块名一致
});  