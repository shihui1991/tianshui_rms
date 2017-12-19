<script type="text/javascript">
	function bodyZoom(){
			winWidth = window.innerWidth;
			bfb = winWidth/1900;

			// 左右边广告
			var dual = $(".dual");
			var dual_close = $("a.dual_close");
			var screen_w = screen.width;
			if (screen_w > 1024) {
				dual.show()
			}
			$(window).scroll(function() {
				var scrollTop = $(window).scrollTop();
				console.log(scrollTop);
				dual.stop().animate({
					top: (scrollTop + 160)/bfb
				})
			});
			dual_close.click(function() {
				$(this).parent().hide();
				return false
			});

			if (winWidth<1900) {
				console.log(bfb);
				document.body.style.zoom=bfb;
			}
			// 轮播图
				$("#banner .slider").slide({
					mainCell: ".bd ul",
					titCell: ".hd li",
					trigger: "click",
					effect: "leftLoop",
					autoPlay: true,
					delayTime: 700,
					interTime: 5000,
					pnLoop: false,
					titOnClassName: "active"
				})

		}
		window.onresize = bodyZoom;
		(function(){
			bodyZoom()
		})();

// ======================================= 根据屏幕尺寸来设置页面大小  ===============================================//
(function (doc, win) {
    var docEl = doc.documentElement,
        resizeEvt = 'orientationchange' in window ? 'orientationchange' : 'resize',
        recalc = function () {
            var clientWidth = docEl.clientWidth;
            // var  devicePixelRatio = window.devicePixelRatio;
            var scale = 1 / devicePixelRatio;
            if (!clientWidth) return;
            docEl.style.fontSize = clientWidth / 75 + 'px';
            document.querySelector('meta[name="viewport"]').setAttribute('content','initial-scale=' + scale + ', maximum-scale=' + scale + ', minimum-scale=' + scale + ', user-scalable=no');
        };
    if (!doc.addEventListener) return;
    win.addEventListener(resizeEvt, recalc, false);
    doc.addEventListener('DOMContentLoaded', recalc, false);
})(document, window);

</script>