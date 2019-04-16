"use strict";
/**
 * 表单自定义组件
 */
layui.define(['form', 'layedit'], function (exports) {

    var form = layui.form;
    var layedit = layui.layedit;

    layedit.set({
        uploadImage: {
            url: '/admin/goods/imageContent' //接口url
            ,type: '' //默认post
        }
    });

    // 集成下富文本
    var fwbcontent = layedit.build('fwb-content', {
        height: 380
    });

    // 表单的验证
    form.verify(formParams.verify);

    // 表单的提交
    form.on('submit(*)', function (data) {

        // 有富文本就传值进去
        if ('fwb-content' in data.field) {
            data.field['fwb-content'] = layedit.getContent(fwbcontent);
        }

        console.log(data.field);
        console.log(data);

        if (!data.form.action) {
            layer.msg('提交地址出错！');
            return false;
        }

        data.elem.disabled = true;
        data.elem.innerText = '正在提交..';
        data.elem.className += ' layui-btn-disabled';

        $.post(data.form.action, data.field, d => {

            data.elem.disabled = false;
            data.elem.innerText = '立即提交';
            var classes = data.elem.className.split(" ");
            classes.pop();
            data.elem.className = classes.join(" ");

            if (d.status == 'success') {
                if (data.field.id) {

                    var text = '保存成功';
                    if(d.url){

                        text += ',2秒后自动跳转';

                        setTimeout(function () {

                            window.location.href=d.url;
                        },2000);
                    }
                    layer.msg(text);


                } else {

                    // layer.msg('是添加');
                    window.location.href=d.url ;
                }
            } else {
                layer.msg(d.message);
            }

        }, 'json');

        return false;
    });

    exports('mForm', {});
});

// 表单的自定义属性
var formParams = {
    // 表单的验证
    verify: {

        // 输入了值就验证手机号
        nullPhone: function (value, item) {
            if (!(/^1[34578]\d{9}$/.test(value)) && value.length > 0) {
                return item.title + '必须为手机号码';
            }
        },

        // /^[+]{0,1}(\d+)$/; (包括0)
        numberZ: function (value, item) {
            if (!/^[+]{0,1}(\d+)$/.test(value)) {
                return item.title + '必须为正整数';
            }
        },

        // 帐号验证
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
        ]
    }
};