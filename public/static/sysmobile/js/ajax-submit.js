;
$(function () {
    /* 异步提交登录 */
    $('.js-ajax-login').on('click',function () {
        var btn=$(this),
            form=btn.parents('form:first'),
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
                    layer.open({
                        content:msg
                        ,skin: 'msg'
                        ,time:1.5
                    });
                }
            },
            success:function (data) {
                btn.data("loading",false).prop('disabled',false).removeClass('disabled');
                layer.open({
                    content:data.msg
                    ,skin: 'msg'
                    ,time:1.5
                });

                if(data.code){
                    window.location.href=data.url;
                }else{
                    $('input[name=username]').focus();
                }
            },
            error:function (xhr,e,statusText) {
                btn.data("loading",false).prop('disabled',false).removeClass('disabled');
                layer.open({
                    content:statusText
                    ,skin: 'msg'
                    ,time:1.5
                });

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
    var form=btn.data('form')?$('#'+btn.data('form')):btn.parents('form:first'),
        url=btn.data('action')?btn.data('action'):form.attr('action'),
        data=btn.data('formdata')?btn.data('formdata'):form.serialize(),
        notice=btn.data('notice'),
        is_layer=btn.data('layer'),
        msg='处理中……';

    if (btn.data("loading") || btn.prop('disabled')) {
        return false;
    }
    if(notice){
        layer.open({
            content: notice
            ,btn: ['确定', '取消']
            ,yes: function(index){
                layer.close(index);
                js_ajax_form_action(btn,url,data,msg,is_layer);
            }
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
                layer.open({
                    content:msg
                    ,skin: 'msg'
                    ,time:1.5
                });
            }
        },
        success:function (data) {
            btn.data("loading",false).prop('disabled',false).removeClass('disabled');
            layer.open({
                content:data.msg
                ,skin: 'msg'
                ,time:1.5
            });

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
            layer.open({
                content:'网络故障！请稍候重试……'
                ,skin: 'msg'
                ,time:1.5
            });
        }
    });
}


/* 异步提交查询 */
function js_ajax_search(obj) {
    var btn=$(obj),
        form=btn.data('form')?$('#'+btn.data('form')):btn.parents('form:first'),
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
                layer.open({
                    content:msg
                    ,skin: 'msg'
                    ,time:1.5
                });
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
            layer.open({
                content:statusText
                ,skin: 'msg'
                ,time:1.5
            });
        }
    });
}