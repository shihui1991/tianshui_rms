$(function(){
	$('.toggle').click(function(e) {
  var toggle = this;
  
  e.preventDefault();
         
  
$(toggle).toggleClass('toggle--on')
         .toggleClass('toggle--off')
         .toggleClass('toggleOnBg')
         .toggleClass('toggleOffBg')
         .addClass('toggle--moving');
  
  setTimeout(function() {
    $(toggle).removeClass('toggle--moving');
  }, 200)
});	
});
//$(function(){
//	$('.toggle').click(function(e) {
//var toggle = this;
//
//e.preventDefault();
//$(toggle).toggleClass('toggleOnBg')
//       .toggleClass('toggleOffBg')
//       .addClass('toggle--moving');
//
//setTimeout(function() {
//  $(toggle).removeClass('toggle--moving');
//}, 200)
//});	
//})