$(function(){
    $(".find_nav_list").css("left",0);

    $(".find_nav_list li").each(function(){
        $(".sideline").css({left:0});
        $(".find_nav_list li").eq(0).addClass("tab_on").siblings().removeClass("tab_on");
    });
    var nav_w=$(".find_nav_list li").first().width();
    $(".sideline").width(nav_w);
    $(".find_nav_list li").on('click', function(){
        nav_w=$(this).width();
        $(".sideline").stop(true);
        $(".sideline").animate({left:$(this).position().left},300);
        $(".sideline").animate({width:nav_w});
        $(this).addClass("tab_on").siblings().removeClass("tab_on");
        var fn_w = ($(".find_nav").width() - nav_w) / 2;
        var fnl_l;
        var fnl_x = parseInt($(this).position().left);
        if (fnl_x <= fn_w) {
            fnl_l = 0;
        } else if (fn_w - fnl_x <= flb_w - fl_w) {
            fnl_l = flb_w - fl_w;
        } else {
            fnl_l = fn_w - fnl_x;
        }
        $(".find_nav_list").animate({
            "left" : fnl_l
        }, 300);

    });
    var fl_w=$(".find_nav_list").width();
    var flb_w=$(".find_nav_left").width();
    $(".find_nav_list").on('touchstart', function (e) {
        var touch1 = e.originalEvent.targetTouches[0];
        x1 = touch1.pageX;
        y1 = touch1.pageY;
        ty_left = parseInt($(this).css("left"));
    });
    $(".find_nav_list").on('touchmove', function (e) {
        var touch2 = e.originalEvent.targetTouches[0];
        var x2 = touch2.pageX;
        var y2 = touch2.pageY;
        if(ty_left + x2 - x1>=0){
            $(this).css("left", 0);
        }else if(ty_left + x2 - x1<=flb_w-fl_w){
            $(this).css("left", flb_w-fl_w);
        }else{
            $(this).css("left", ty_left + x2 - x1);
        }
        if(Math.abs(y2-y1)>0){
            e.preventDefault();
        }
    });


    for(n=1;n<9;n++){
        var page='pagenavi'+n;
        var mslide='slider'+n;
        var mtitle='emtitle'+n;
        arrdiv = 'arrdiv' + n;
        if(document.getElementById(page)){
            var as=document.getElementById(page).getElementsByTagName("a");
            var tt=new TouchSlider({id:mslide,'auto':'-1',fx:'ease-out',direction:'left',speed:600,timeout:5000,'before':function(index){
                    var as=document.getElementById(this.page).getElementsByTagName('a');

                    as[this.p].className='';
                    this.p=index;

                    fnl_x =  parseInt($(".find_nav_list li").eq(this.p).position().left);

                    nav_w=$(".find_nav_list li").eq(this.p).width();
                    $(".sideline").stop(true);
                    $(".sideline").animate({left:$(".find_nav_list li").eq(this.p).position().left},300);
                    $(".sideline").animate({width:nav_w},100);
                    $(".find_nav_list li").eq(this.p).addClass("tab_on").siblings().removeClass("tab_on");
                    var fn_w = ($(".find_nav").width() - nav_w) / 2;
                    var fnl_l;
                    if (fnl_x <= fn_w) {
                        fnl_l = 0;
                    } else if (fn_w - fnl_x <= flb_w - fl_w) {
                        fnl_l = flb_w - fl_w;
                    } else {
                        fnl_l = fn_w - fnl_x;
                    }
                    $(".find_nav_list").animate({
                        "left" : fnl_l
                    }, 300);
                }});
            tt.page = page;
            tt.p = 0;


            for(var i=0;i<as.length;i++){
                (function(){
                    var j=i;
                    as[j].tt = tt;
                    as[j].onclick=function(){
                        this.tt.slide(j);
                        return false;
                    }
                })();
            }
        }

    }

});