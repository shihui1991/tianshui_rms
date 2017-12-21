;
KindEditor.ready(function(K) {
    /* ======== 上传 ======== */
    var editor_uploads = K.editor({
        uploadJson : upload_url,
        fileManagerJson : filemanager_url,
        allowFileManager : true
    });
    $('.add_tu').on('click','.btn-upload',function () {
        var btn=$(this);
        var btn_type=btn.data('type');
        var hidename=btn.data('hidename');
        var preview='';

        /* ======== 单图 ======== */
        if(btn_type=='image'){
            editor_uploads.loadPlugin('image', function() {
                editor_uploads.plugin.imageDialog({
                    clickFn : function(url, title, width, height, border, align) {
                        preview ='<li><img title="'+title+'" src="'+url+'" class="w_100 h_100" onclick="bigerimg(this)"><span><span onclick="picremove(this);"><i class="iconfont icon-guanbi"></i></span></span>';
                        preview +='<input type="hidden" name="'+hidename+'" value="'+url+'"/></li>';

                        btn.siblings().remove();
                        btn.before(preview);
                        editor_uploads.hideDialog();
                    }
                });
            });
        }
        /* ======== 多图 ======== */
        else if(btn_type=='multiimage'){

        }

    });
});

function multiimageupl(obj) {
    var upl_file_obj=$(obj),
        upl_file=obj.files;
    var hidename=upl_file_obj.parent().data('hidename');
    var preview='';

    if(upl_file.length){
        for(var i=0;i<upl_file.length;i++){
            var form_data=new FormData();
            form_data.append('picture',upl_file[i]);
            $.ajax({
                url:upload_url,
                data:form_data,
                type:'POST',
                dataType:'JSON',
                cache:false,
                processData:false,
                contentType:false,
                async: false,
                success:function (resp) {
                    if(resp.error){
                        layer.open({
                            content:resp.message
                            ,skin: 'msg'
                            ,time:1.5
                        });
                    }else{
                        preview ='<li><img src="'+resp.url+'" class="w_100 h_100" onclick="bigerimg(this)"><span><span onclick="picremove(this);"><i class="iconfont icon-guanbi"></i></span></span>';
                        preview +='<input type="hidden" name="'+hidename+'" value="'+resp.url+'"/></li>';
                        upl_file_obj.parent().before(preview);
                    }
                }
            });
        }

        upl_file_obj.val('');
    }
}

/* ======== 删除 ======== */
function picremove(obj) {
    $(obj).parent().parent().remove();
}


//图片放大
function bigerimg(obj){
    var pageii = layer.open({
        type: 1
        ,content: '<img style="width: 99%" src="'+obj.src+'"/>'
        ,anim: 'up'
        // ,style: 'position:fixed; left:0; top:0; width:100%; height:100%; border: none; -webkit-animation-duration: .5s; animation-duration: .5s;'
        ,btn: '<i class="iconfont icon-guanbi"></i>'
        ,yes:function (index) {
            layer.close(index);
        }
    });
}


