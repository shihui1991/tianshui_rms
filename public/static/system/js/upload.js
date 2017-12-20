;
KindEditor.ready(function(K) {
    /* ======== 上传 ======== */
    var editor_uploads = K.editor({
        uploadJson : upload_url,
        fileManagerJson : filemanager_url,
        allowFileManager : filemanager_url?true:false
    });
    $('.imgCon').on('click','.btn-upload',function () {
        var btn=$(this);
        var btn_type=btn.data('type');
        var hidename=btn.data('hidename');
        var preview='';

        /* ======== 单图 ======== */
        if(btn_type=='image'){
            editor_uploads.loadPlugin('image', function() {
                editor_uploads.plugin.imageDialog({
                    clickFn : function(url, title, width, height, border, align) {
                        preview ='<div class="img"><img title="'+title+'" src="'+url+'" class="w_100 h_100" onclick="bigerimg(this)"><p><span onclick="picremove(this);">删除</span></p>';
                        preview +='<input type="hidden" name="'+hidename+'" value="'+url+'"/></div>';

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
                            preview ='<div class="img"><img src="'+data.url+'" class="w_100 h_100" onclick="bigerimg(this)"><p><span onclick="picremove(this);">删除</span></p>';
                            preview +='<input type="hidden" name="'+hidename+'" value="'+data.url+'"/></div>';
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
    $(obj).parent().parent().remove();
}


