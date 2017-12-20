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
                        preview ='<li><img title="'+title+'" src="'+url+'" class="w_100 h_100" onclick="bigerimg(this)"><span onclick="picremove(this);"><i class="iconfont icon-guanbi"></i></span>';
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
            editor_uploads.loadPlugin('multiimage', function() {
                editor_uploads.plugin.multiImageDialog({
                    clickFn : function(urlList) {
                        K.each(urlList, function(i, data) {
                            preview ='<li><img src="'+data.url+'" class="w_100 h_100" onclick="bigerimg(this)"><span onclick="picremove(this);"><i class="iconfont icon-guanbi"></i></span>';
                            preview +='<input type="hidden" name="'+hidename+'" value="'+data.url+'"/></li>';
                            btn.before(preview);
                        });
                        editor_uploads.hideDialog();
                    }
                });
            });
        }

    });
});

/* ======== 删除 ======== */
function picremove(obj) {
    $(obj).parent().remove();
}


//图片放大
function bigerimg(obj){
    var pageii = layer.open({
        type: 1
        ,content: '<img src="'+obj.src+'"/>'
        ,anim: 'up'
        // ,style: 'position:fixed; left:0; top:0; width:100%; height:100%; border: none; -webkit-animation-duration: .5s; animation-duration: .5s;'
        ,btn: '<i class="iconfont icon-guanbi"></i>'
        ,yes:function (index) {
            layer.close(index);
        }
    });
}


