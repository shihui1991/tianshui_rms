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
	$("#pgjg").click(function(){
		$(this).addClass("spanon").parent().siblings().children().removeClass("spanon");
		$(".pgjgcon").css("display","block");
		$(".ysbccon").css("display","none");
	});
	$("#ysbc").click(function(){
		$(this).addClass("spanon").parent().siblings().children().removeClass("spanon");
		$(".pgjgcon").css("display","none");
		$(".ysbccon").css("display","block");
	});
	
	$(".ysbc_list .ysbc_tit").click(function(){
		$(this).siblings(".ysbc_listcon").slideToggle("show");
	});
	$(".fcpgtab>div>p").click(function(){
		$(this).siblings(".fc_hide").slideToggle("show");
	});
	//toupiao
	$(".vonum").click(function(){
		if($(this).hasClass("vonum_on")){
			$(this).removeClass("vonum_on");
		}
		else{
			$(this).addClass("vonum_on");
		}
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
    
//  $(".xl_xq").css("height",document.body.clientHeight+"px");
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



