$(function(){
	//返回上一步
	$(".back").click(function(){
		window.history.back(-1);
	});

	//评估页面
	$("#fwpg").click(function(){
		$(this).addClass("fwpg_on");
		$(this).siblings().removeClass("fwpg_on");
		$(".fwpg").css("display","block");
		$(".fsw").css("display","none");
	});
	$("#fsw").click(function(){
		$(this).addClass("fwpg_on");
		$(this).siblings().removeClass("fwpg_on");
		$(".fsw").css("display","block");
		$(".fwpg").css("display","none");
	});
	$("#fcpg").click(function(){
		$(this).addClass("tabBtnOn").siblings().removeClass("tabBtnOn");
		$(".fcpgcon").css("display","block");
		$(".zcpgcon").css("display","none");
	});
	$("#zcpg").click(function(){
		$(this).addClass("tabBtnOn").siblings().removeClass("tabBtnOn");
		console.log(12);
		$(".fcpgcon").css("display","none");
		$(".zcpgcon").css("display","block");
	});

	
	$(".ysbc_list .ysbc_tit").click(function(){
		$(this).siblings(".ysbc_listcon").slideToggle("show");
	});
	$(".fcpgtab>div>p").click(function(){
		$(this).siblings(".fc_hide").slideToggle("show");
	});
    
    //查看更多
    $(".formore_fw").css("display","none");
    $(".formore_fsw").css("display","none");
    $(".more_fw").click(function(){
    	$(".formore_fw").css("display","block");
    });
     $(".more_fsw").click(function(){
    	$(".formore_fsw").css("display","block");
    });
    //选择方式
    $(".qtfs_xz>span").click(function(){
    	$(this).addClass("qtfs_on").siblings("span").removeClass("qtfs_on");
    });
    //房源信息下拉
    $(".fy_xz_tit ").click(function(){
    	$(this).addClass("yx_on").parent().siblings().children().removeClass("yx_on");
    });
    //位置
    $(".weizhi").click(function(){
    	$(".wz_xq").slideToggle("show");
    	$(".hx_xq").css("display","none");
    	$(".jg_xq").css("display","none");
    	
    });
    //户型
    $(".huxing").click(function(){
    	$(".hx_xq").slideToggle("show");
    	$(".wz_xq").css("display","none");
    	$(".jg_xq").css("display","none");
    });
    //价格
    $(".jiage").click(function(){
    	$(".jg_xq").slideToggle("show");
    	$(".hx_xq").css("display","none");
    	$(".wz_xq").css("display","none");
    });
    $(".wz_xq>p,.hx_xq>p").click(function(){
    	$(this).addClass("p_on").siblings("p").removeClass("p_on");
    });
    $(".jg_xq>span").click(function(){
    	$(this).addClass("span_on").siblings("span").removeClass("span_on");
    })
    

});

function layerDom(obj) {
	var that=$(obj);
	var that_id=that.data('id');
	var that_title=that.data('title');

	layer.open({
		title:that_title
		,type:1
		,content:document.getElementById(that_id).innerHTML
		,anim:'up'
        ,style: 'position:fixed; left:0; top:0; width:100%; height:100%; border: none; -webkit-animation-duration: .5s; animation-duration: .5s;overflow:auto;'
		,btn:'<i class="iconfont icon-guanbi"></i>'
        ,yes:function (index) {
            layer.close(index);
        }
	});
}

//图片放大
function bigerimg(obj){
    var pageii = layer.open({
        type: 1
        ,content: '<img style="width: 99%" src="'+obj.src+'"/>'
        ,anim: 'up'
        ,btn: '<i class="iconfont icon-guanbi"></i>'
        ,yes:function (index) {
            layer.close(index);
        }
    });
}

//评估公司投票
function vote(obj) {
	var _btn=$(obj);
	var btns=$('p.vonum');
	var company=_btn.data('company');
	var title=_btn.data('title');
	var url=_btn.data('url');

	if(btns.data('loading') || btns.hasClass('disabled')){
		return false;
	}
	layer.open({
        content: title
        ,btn: ['是的', '考虑一下']
        ,yes: function(index){
            btns.data('loading',true).addClass('disabled');
            $.ajax({
				url:url
				,data:{"company_id":company}
				,type:"get"
				,dataType:'json'
				,success:function (resp) {
                    layer.open({
                        content: resp.msg
                        ,skin: 'msg'
                        ,time: 1.5
                        ,end:function (index) {
                            layer.closeAll();
                        }
                    });
					if(resp.code){
                        _btn.addClass('vonum_on').find('span').text(parseInt(_btn.find('span').text())+1);
                        $('p.vonum').off('click').removeAttr('onclick data-title data-url data-company');
                        setTimeout(function () {
                            location.reload();
                        },1500);
					}else{
                        btns.data('loading',false).addClass('disabled');
                    }
                }
                ,error:function () {
                    layer.open({
                        content: '网络错误，请稍候重试'
                        ,skin: 'msg'
                        ,time: 1.5
						,end:function (index) {
							layer.closeAll();
                        }
                    });
                    btns.data('loading',false).addClass('disabled');
                }
			});
        }
        ,btn1:function (index) {
            layer.close(index);
        }
	});
}



