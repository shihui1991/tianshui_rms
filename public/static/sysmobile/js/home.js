//菜单栏收缩与展开
$(function() {
	$(".caidan").click(function() {
		$(".cd_list").stop().slideToggle();
	});
	$(".link").click(function() {
		$(this).addClass('open').parent().siblings().children('div').removeClass("open");
		$(this).siblings('ul').stop().slideToggle();
		$(this).parent().siblings().children('ul').stop().slideUp();
	});
	$(".two>li").click(function() {
		$(this).addClass('open_li').siblings().removeClass('open_li');
		$(this).parent().parent().siblings().children('ul').children('li').removeClass('open_li');
		$(this).css("border-left", "2px solid #4ec59d").siblings().css("border-left", "2px solid white");
		$(this).parent().parent().siblings().children('ul').children('li').css("border-left", "2px solid white");
	});
	$("a").removeClass("ui-link");

	//表个更多内容展示
	$('table').on('click',".more>i",function() {
		$(this).parent("td").parent("tr").next("tr").stop().slideToggle(0);
	});
	//操作
	$(".shezhi").click(function() {
		$(this).children("div").toggleClass("hide");
	});
	//	全选
	$("#checkall").click(function() {
		if($(this).prop("checked") == true) {
			$("input[name='ids']").each(function() {
				$(this).attr("checked", true);
			})
		}
		if($(this).prop("checked") == false) {
			$("input[name='ids']").each(function() {
				$(this).attr("checked", false);
			})
		}

	});
	$(".yh").click(function() {
		$(".gerenList").stop().slideToggle();
	});

	//查询
	$("#showQuery").click(function() {
		$(".showQuery").slideDown(300);
	});
	$(".close").click(function() {
		$(".showQuery,.forSearch").slideUp(300);
	});

	$(document).on("click", function(e) {
		//id为menu的是菜单，id为open的是打开菜单的按钮
		if($(e.target).closest(".shezhi").length == 0) {
			//点击id为menu之外且id不是不是open，则触发
			$(".shezhi>div").addClass("hide");
		}
		//          if($(e.target).closest(".cd_list").length == 0){
		//          //点击id为menu之外且id不是不是open，则触发
		//              $(".cd_list").removeClass("hide");
		//          }
		if($(e.target).closest(".yh").length == 0) {
			//点击id为menu之外且id不是不是open，则触发
			$(".gerenList").css("display", "none");
		}
		if($(e.target).closest(".caidan").length == 0 && $(e.target).closest(".cd_list>ul>li>div").length == 0) {
			//点击id为menu之外且id不是不是open，则触发
			$(".cd_list").stop().slideUp(300);
		}
	});
	var winW = $(window).width();
	var len_img = $(".imgDiv .bd ul>li").length;
	$(".imgDiv .bd ul>li").css("width", winW);
	$(".imgDiv .bd ul").css("width", winW * len_img + 'px');

	$(".tab1>a").click(function() {
		$(this).addClass('on_lan').siblings().removeClass('on_lan');
	});

    $(".anniu a:gt(2)").css("display", "none");
    $(".xiaMore em").click(function() {
        if($(".anniu a:gt(2)").is(":visible")) {
            $(".anniu a:gt(2)").css("display", "none");
            $(".xiaMore>i").addClass("icon-gongyongshuangjiantouxia").removeClass("icon-shuangjiantouxiangshang");
            $(".xiaMore>em").text("更多");
        } else {
            $(".anniu a:gt(2)").css("display", "inline-block");
            $(".xiaMore>i").addClass("icon-shuangjiantouxiangshang").removeClass("icon-gongyongshuangjiantouxia");
            $(".xiaMore>em").text("收起");
        }
    });

	//动态添加行
	$("#add_rowName").focus(function() {
		$(this).val('');
		$(this).css("color", "#333333")
	});
	$("#add_row").click(function() {
		var rowName = $("#add_rowName").val();
		var cols = '';
		$("#dong_table tr").find('td').each(function() {
			if($(this).index() == "0") {
				var txt = $(this).children("p").text();
				cols += txt + "|";
			}
		});
		if(rowName == '此处不能为空且不能重复') {
			return false;
		}
		if(rowName == '' || cols.indexOf(rowName) >= 0) {
			$("#add_rowName").val("此处不能为空且不能重复");
			$("#add_rowName").css("color", "red");
			return false;
		} else {
			var tr = "<tr><td colspan='2' style='height:15px;background:white;border:none'></td></tr><tr><td><p>" + rowName + "</p><span class='dele_row' onclick='deleRow(this);'>删除</span></td><td><ul class='hxt_ul add_tu'><li><input type='file' /><a>+</a></li><li><img src='img/4.jpg' /><em class='em' onclick='delepic(this);'>删除</em></li></ul></td></tr>"
			$("#dong_table").append(tr);
		}
	});

	$(".jbxx_tit").click(function() {
		$(this).addClass("on_p").siblings().removeClass("on_p");
		$(".jbxx_con").css("display", "block");
		$(".spwj_con").css("display", "none");
	});
	$(".spwj_tit").click(function() {
		$(this).addClass("on_p").siblings().removeClass("on_p");
		$(".jbxx_con").css("display", "none");
		$(".spwj_con").css("display", "block");
	});


    $("[las]").click(function() {
        $("[lasy=" + $(this).attr("las") + "]").stop().slideDown();
    });

    $(".btnp").find('span').on('click',function(){
        $(this).addClass("on").siblings("span").removeClass("on");
        $(".tabDiv>div").eq($(this).index()).css("display", "block").siblings("div").css("display", "none")
    });

    $(".toolBar").click(function() {
        $(".toolCon").stop().slideToggle(300);
    });
    $(".toolCon p").click(function() {
        $(this).addClass("on").siblings("p").removeClass("on");
    });
});


