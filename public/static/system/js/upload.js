;

$('.upload-btn').on('click',function () {
    $(this).find('input.upload_files').get(0).click();
});

$('input.upload_files').on('change',function (e) {
    var _this=$(this),
        img_box=_this.parent().siblings(),
        _multiple=_this.prop('multiple'),
        upl_url=_this.data('url'),
        hidden_name=_this.data('hiddenname'),
        upl_files=this.files,
        form_data=new FormData(),
        img_preview,
        img_url;
    if(upl_files.length){
        for(var i=0;i<upl_files.length;i++){
            form_data.append('picture',upl_files[i]);
            $.ajax({
                url:upl_url,
                data:form_data,
                type:'POST',
                dataType:'JSON',
                processData:false,
                contentType:false,
                async: false,
                success:function (info) {
                    if(info.code==1){
                        img_url=info.data;
                        img_preview ='<div class="img"><img src="'+img_url+'" class="w_100 h_100" onclick="bigimg(this)"><p><span onclick="picremove(this);">删除</span></p>';
                        img_preview +='<input type="hidden" name="'+hidden_name+'" value="'+img_url+'"/></div>';

                        if(!_multiple){
                            img_box.remove();
                        }
                        _this.parent().before(img_preview);
                    }else{
                        alert('【'+upl_files[i].name+'】上传失败');
                    }
                }
            });
        }
    }
    _this.val('');
});

function picremove(obj) {
    var img_box=$(obj).parent().parent();
    img_box.css('display','none');
    img_box.find('img').attr('src','');
    img_box.find('input[type=hidden]').attr('value','');
}

