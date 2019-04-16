"use strict";
/**
 * table查询自定义组件
 */
layui.define(['form','table'],function(exports){
    var form = layui.form ;
    var table = layui.table ;

    // 表单的提交
    form.on('submit(query)',function(data){

        console.log('搜索关键字',data.field) ;
        //console.log(data) ;

        table.reload('idTable', {
            where:data.field
        });

        return false ;
    });



    exports('mQuery',{}) ;
});