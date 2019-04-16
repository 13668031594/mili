layui.use(['layer', 'form'], function(){
    var layer = layui.layer;
    var form = layui.form;

    $('#forms').on('submit', function(){
        Ajax($(this));
        return false;
    });

    //表单提交
    function Ajax( form, fct, text, dataType, type ){
        var type_ = type || $(form).prop('method');
        var dataType_ = dataType || 'json';
        var fct_ = fct || callback;
        if( $(form).find('input:file').length <= 0 ){
            //console.log('序列化');
            var datas = $(form).serialize();
            //console.log('datas', datas);
            $.ajax({
                type: type_,
                url: $(form).prop('action'),
                dataType: dataType_,
                data: datas,
                success: function(resp){
                    console.log('data1', resp);
                    fct_(resp) ;
                },
                error: function (data) {
                    //console.log('error', data);
                    layer.msg('错误：'+ data.status);
                }
            });
        }else{
            //console.log('FormData');
            var datas = new FormData($(form)[0]);
            $.ajax({
                type: type_,
                url: $(form).attr('action'),
                dataType: dataType_,
                data: datas,
                cache: false,			         //设置为false不缓存此页面
                contentType: false,               //'multipart/form-data',        //不可缺参数  内容编码类型   默认值："application/x-www-form-urlencoded"
                processData: false,               //不可缺参数  将data传递的不是字符串的数据处理转化成一个查询字符串，以配合默认内容类型 "application/x-www-form-urlencoded"  默认值：true
                success: function(resp){
                    console.log('data2', resp);
                    fct_(resp) ;
                },
                error: function(data){
                    //console.log('error', data);
                    layer.msg('错误：'+ data.status);
                },
            });
        }
    }

    //成功请求后回调函数
    function callback(resp){
        if( resp && resp.status == 'success' ){
            layer.alert(resp.message, function(index){
                if( resp.url && resp.url != '' ){
                    window.location.href = resp.url;
                }
                layer.close(index);
            });
        }else{
            if( resp.code == '999' ){
                layer.alert('登录失效, 请重新登录', function(index){
                    window.location.href = '/login';
                    layer.close(index);
                });
            }else{
                layer.alert(resp.message);
            }
        }
    }
});
