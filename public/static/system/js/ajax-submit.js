;
$(function () {
    /* 异步提交登录 */
    $('.js-ajax-login').on('click',function () {
        var btn=$(this),
            form=btn.parents('form.js-ajax-form'),
            url=form.attr('action'),
            data=form.serialize(),
            msg='登录中……';

        if (btn.data("loading")) {
            return false;
        }

        $.ajax({
            url:url,
            data:data,
            type:'POST',
            dataType:'JSON',
            beforeSend:function () {
                btn.data("loading",true).prop('disabled',true).addClass('disabled');
                if(msg){
                    layer.msg(msg);
                }
            },
            success:function (data) {
                btn.data("loading",false).prop('disabled',false).removeClass('disabled');
                layer.msg(data.msg,function () {});
                if(data.code){
                    window.location.href=data.url;
                }else{
                    $('input[name=name]').focus();
                }
            },
            error:function (xhr,e,statusText) {
                btn.data("loading",false).prop('disabled',false).removeClass('disabled');
                layer.msg(statusText);
            }
        });
        return false;
    });

    /* 异步提交表单快捷操作-按钮点击 */
    $('.js-ajax-form-btn').on('click',function () {
        js_ajax_btn_action($(this));
    });

});

/* 异步提交表单快捷操作-数据整理 */
function js_ajax_btn_action(btn) {
    var form=btn.data('form')?$('#'+btn.data('form')):btn.parents('form.js-ajax-form'),
        url=btn.data('action')?btn.data('action'):form.attr('action'),
        data=btn.data('formdata')?btn.data('formdata'):form.serialize(),
        notice=btn.data('notice'),
        is_layer=btn.data('layer'),
        msg='处理中……';

    if (btn.data("loading") || btn.prop('disabled')) {
        return false;
    }
    if(notice){
        layer.confirm(notice, {
            skin: 'new-layer',
            title: '提示',
            btn: ['确定','取消'] //按钮
        }, function(index){
            layer.close(index);
            js_ajax_form_action(btn,url,data,msg,is_layer);
        }, function (index) {
            layer.close(index);
        });
    }else{
        js_ajax_form_action(btn,url,data,msg,is_layer);
    }

    return false;
}

/* 异步提交表单快捷操作-表单提交操作 */
function js_ajax_form_action(btn,url,data,msg,is_layer) {
    $.ajax({
        url:url,
        data:data,
        type:'POST',
        dataType:'JSON',
        beforeSend:function () {
            btn.data("loading",true).prop('disabled',true).addClass('disabled');
            if(msg){
                layer.msg(msg);
            }
        },
        success:function (data) {
            btn.data("loading",false).prop('disabled',false).removeClass('disabled');
            layer.msg(data.msg,function () {});
            $('input[name=name]').focus();
            if(data.url){
                if(is_layer){
                    window.parent.location.href=data.url;
                }else{
                    window.location.href=data.url;
                }
                return false;
            }
            if(data.code){
                if(is_layer){
                    window.parent.location.reload();
                }else{
                    window.location.reload();
                }
                return false;
            }else{
                $('input[name=name]').focus();
            }
        },
        error:function (xhr,e,statusText) {
            btn.data("loading",false).prop('disabled',false).removeClass('disabled');
            $('input[name=name]').focus();
            layer.msg('网络故障！请稍候重试……',function () {});
        }
    });
}


/* 异步提交查询 */
function js_ajax_search(obj) {
    var btn=$(obj),
        form=btn.data('form')?$('#'+btn.data('form')):btn.parents('form.js-ajax-form'),
        url=btn.data('action')?btn.data('action'):form.attr('action'),
        data=btn.data('formdata')?btn.data('formdata'):form.serialize(),
        msg='查询中……';

    if (btn.data("loading")) {
        return false;
    }
    return_data=null;
    $.ajax({
        url:url,
        data:data,
        type:'POST',
        dataType:'JSON',
        async: false,
        beforeSend:function () {
            btn.data("loading",true).prop('disabled',true).addClass('disabled');
            if(msg){
                layer.msg(msg);
            }
        },
        success:function (data) {
            btn.data("loading",false).prop('disabled',false).removeClass('disabled');
            if(data.code){
                return_data=data;
            }
        },
        error:function (xhr,e,statusText) {
            btn.data("loading",false).prop('disabled',false).removeClass('disabled');
            layer.msg(statusText);
        }
    });
}