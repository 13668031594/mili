"use strict";

// 自定义表格组件
layui.define(['table', 'form'], function (exports) {

    var table = layui.table;
    var form = layui.form;

    const tableParams = {

        params: {},

        init: function (params) {
            this.params = params;
            this.render();
        },

        render: function () {
            table.render({
                // 有筛选的地方高度变化一下
                height: 'height' in this.params ? this.params.height : 'full-130',
                elem: '#idTable',
                even: true,
                // 禁用前台自动排序
                autoSort: false,
                page: this.params.isPage == undefined ? true : this.params.isPage,
                loading: true,
                url: this.params.url.table,
                parseData: function (res) { //res 即为原始返回的数据
                    //console.log(res) ;
                    return {
                        "code": res.status == 'success' ? 0 : 1, //解析接口状态
                        //"msg": res.message, //解析提示文本
                        "count": res.total, //解析数据长度
                        "data": res.message //解析数据列表
                    };
                },
                cols: this.params.cols,
                done: this.params.callback,
                limit: 30,
            });
        },
    };

    // 表格搜索
    form.on('submit(query)', function (data) {

        console.log('搜索关键字', data.field);
        //console.log(data) ;

        table.reload('idTable', {
            where: data.field
        });

        return false;
    });

    table.on('sort(table)', function (obj) {
        //console.log(table) ;
        //console.log(obj.field); //当前排序的字段名
        //console.log(obj.type); //当前排序类型：desc（降序）、asc（升序）、null（空对象，默认排序）
        //console.log(this); //当前排序的 th 对象

        table.reload('idTable', {
            initSort: obj
            , where: {
                field: obj.field //排序字段
                , order: obj.type //排序方式
            }
        });

        //layer.msg('服务端排序。order by '+ obj.field + ' ' + obj.type);
    });

    table.on('tool(table)', function (obj) {
        //console.log(obj.tr);
        var data = obj.data;

        if (obj.event === 'detail') {
            // 编辑的操作
            window.location.href = tableParams.params.url.edit + data.id
            //layer.msg('ID：' + data.id + ' 的查看操作');
        } else if (obj.event === 'del') {
            layer.confirm('确定要删除吗？', function (index) {
                layer.close(index);
                var dmsg = layer.msg('删除中...', {time: false});
                // 批量删除
                $.getJSON(tableParams.params.url.del, {id: data.id}, function (data) {
                    layer.close(dmsg);
                    if (data.status == 'success') {
                        obj.del();
                        layer.msg('删除成功');
                    } else {
                        layer.msg(data.message);
                    }
                    //table.reload("idTable");
                });

            });
        } else if (obj.event == 'edit') {

            window.location.href = tableParams.params.url.edit + '?id=' + data.id

        } else if (obj.event.indexOf('|') > 0) {

            var pms = obj.event.split("|");

            if (pms[0] == 'custom') {
                if (pms[3] == data[pms[2]]) {
                    layer.msg('该项已' + pms[1]);
                    return false;
                }
                layer.confirm('确定要' + pms[1] + '吗？', function (index) {

                    layer.close(index);

                    var dmsg = layer.msg('请稍候...', {time: false});
                    // 自定义操作
                    $.getJSON(tUrl[pms[4]], {id: data.id, field: pms[2], value: pms[3]}, function (data) {

                        layer.close(dmsg);

                        if (data.status == 'success') {
                            obj.update({
                                [pms[2]]: pms[3],
                                title: '操作'
                            });
                            layer.msg('操作成功');
                        } else {
                            layer.msg('操作失败：' + data.message);
                        }
                        //table.reload("idTable");
                    });

                });
            }
        }
    });

    var $ = layui.$, active = {
        // 批量删除
        delData: function () { //获取选中数据
            var checkStatus = table.checkStatus('idTable')
                , data = checkStatus.data;

            if (data.length == 0) {
                layer.msg('请至少选择一列');
                return false;
            }

            var ids = checkStatus.data.reduce((pre, curr) => {
                return pre + curr.id + ',';
            }, '');

            ids = ids.substr(0, ids.length - 1);
            layer.confirm('确定要删除这' + data.length + '项吗？', function (index) {
                //obj.del();
                layer.close(index);
                var dmsg = layer.msg('删除中...', {time: false});
                // 批量删除
                $.getJSON(tableParams.params.url.del, {id: ids}, function (data) {
                    layer.close(dmsg);
                    if (data.status == 'success') {
                        layer.msg('删除成功');
                    } else {
                        layer.msg(data.message);
                    }
                    table.reload("idTable");
                });

            });
        }
        , searchData: function () {
            return false;
        }
        , clearData: function () {

        }
        , addData: function () {
            window.location.href = tableParams.params.url.add;
        }
    };

    $('.toolTable .layui-btn').on('click', function () {
        var type = $(this).data('type');
        active[type] ? active[type].call(this) : '';
    });

    exports('mTable', tableParams);
});
/*
function mTable(params, table) {

    table.render({
        // 有筛选的地方高度变化一下
        height: 'height' in params ? params.height : 'full-150',
        elem: '#idTable',
        page: true,
        loading: true,
        url: URL.table,
        parseData: function (res) { //res 即为原始返回的数据
            return {
                "code": res.status == 'success' ? 0 : 1, //解析接口状态
                "msg": res.message, //解析提示文本
                "count": res.total, //解析数据长度
                "data": res.message //解析数据列表
            };
        },
        cols: params.cols,
        done: params.callback,
        limit: 30,
    });


    table.on('tool(table)', function (obj) {

        var data = obj.data;
        if (obj.event === 'detail') {
            // 编辑的操作
            window.location.href = URL.edit + data.id
            //layer.msg('ID：' + data.id + ' 的查看操作');
        } else if (obj.event === 'del') {
            layer.confirm('确定要删除吗？', function (index) {
                layer.close(index);
                var dmsg = layer.msg('删除中...', { time: false });
                // 批量删除
                $.getJSON(URL.del, { id: data.id }, function (data) {
                    layer.close(dmsg);
                    if (data.status == 'success') {
                        obj.del();
                        layer.msg('删除成功');
                    } else {
                        layer.msg('删除失败');
                    }
                    //table.reload("idTable");
                });

            });
        } else if (obj.event === 'prize') {
            layer.confirm('确定要派奖吗？', function (index) {
                //layer.alert('编辑行：<br>' + JSON.stringify(data));
                var index = layer.msg('派奖中..', { time: 0 });
                $.getJSON(URL.prize, {
                    id: data.id
                }, function (data) {
                    layer.close(index);
                    if (data.status == 'success') {
                        layer.msg('派奖成功');
                        table.reload('idTable');
                    } else {
                        layer.msg('派奖失败');
                    }
                });
            });
        } else if (obj.event == 'edit') {
            window.location.href = URL.edit + data.id
        }
    });

    var $ = layui.$, active = {
        // 批量删除
        getCheckData: function () { //获取选中数据
            var checkStatus = table.checkStatus('idTable')
                , data = checkStatus.data;

            if (data.length == 0) {
                layer.msg('请至少选择一列');
                return false;
            }

            var ids = checkStatus.data.reduce((pre, curr) => {
                return pre + curr.id + ',';
            }, '');

            ids = ids.substr(0, ids.length - 1);
            layer.confirm('确定要删除这' + data.length + '项吗？', function (index) {
                //obj.del();
                layer.close(index);
                var dmsg = layer.msg('删除中...', { time: false });

                // 批量删除
                $.getJSON(URL.del, { id: ids }, function (data) {
                    layer.close(dmsg);
                    if (data.status == 'success') {
                        layer.msg('删除成功');
                    } else {
                        layer.msg('删除失败');
                    }
                    table.reload("idTable");
                });

            });
        }
        , searchData: function () {

            // 账户名
            var value = $("#searchValue").val();
            // 抽奖人
            var prizetor = $("#prizetor").val();
            console.log(prizetor);
            // 时间筛选 1 抽奖时间 2 派奖时间
            var timeType = $("#timeType").val();
            // 时间段
            var timeStart = $("#start").val();
            var timeEnd = $("#end").val();
            //console.log(timeType) ;
            if ((timeStart != '' || timeEnd != '') && !timeType) {
                layer.msg('请选择时间段筛选类型');
                return false;
            }

            var index = layer.msg('请稍候..', { time: false });
            table.reload("idTable", { //表格的id
                url: 'http://test.test/',
                where: {
                    'account': $.trim(value),
                    'timeType': $.trim(timeType),
                    'prizetor': $.trim(prizetor),
                    'timeEnd': $.trim(timeEnd),
                    'timeStart': $.trim(timeStart),
                }
            });
            layer.close(index);
            return false;
        }
        , clearData: function () {
            console.log('dd');
            tHeaderTypeValue = { 1: "", 2: "" };
            $(".layui-form input").prop('value', "");
            table.reload("idTable", {
                where: {
                    'account': "",
                    'timeType': "",
                    'prizetor': "",
                    'timeEnd': "",
                    'timeStart': "",
                    'hDevice':"",
                    'hPrize':"",
                }
            });
        }
        , addData: function () {
            //console.log(URL.add);
            window.location.href = URL.add;
        }
    };

    $('.toolTable .layui-btn').on('click', function () {
        var type = $(this).data('type');
        active[type] ? active[type].call(this) : '';
    });



}*/
