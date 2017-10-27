$(function(){

	//table鼠标悬浮是当前行变色
	$(".table tbody tr:gt(0)").mouseover(function(){
		$(this).children("td").css("background","#F5F5F5");
	}).mouseout(function(){
		$(this).children("td").css("background","#FFF");
		$(".table tbody tr td").eq(0).css("background","#F5F5F5");
	});
});
