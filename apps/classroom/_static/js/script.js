// JavaScript Document


	
$(document).ready(function(){
	
	jQuery(".fullSlide").slide({ titCell:".hd ul", mainCell:".bd ul", effect:"fold",  autoPlay:true, autoPage:true, trigger:"click" });
	jQuery(".new-actives").slide({ mainCell:".tab-bd-in", effect:"left", delayTime:400,pnLoop:false,easing:"easeOutCubic" });

});