getChildCategory = function(obj){
		var cid = $(obj).children().attr("data");
		$.post(U('classroom/Video/getCategoryData'),{cid:cid},function(txt){
			$(obj).children().removeAttr("data");
			if(txt.status == 0){ 
				return false
			} else {
				var percate = '';
				$.each(txt.list.data,function(index,vo){
					percate  += '<li>'
						+'<a href="javascript:;" data="'+vo.cid+'">'+vo.title+'</a>'
						+'</li>';
				});
				var insertCateHtml = '<div class="propAttrs_box" style="display:block;">'
								+ '<div class="attr clearfix">'
								+ '<div class="attrKey">'+txt.list.next_name+'£∫</div>'
								+ '<div class="attrValues mtb5 clearfix">'
								+ '<ul class="av-collapse clearfix">'
								+ percate
								+ '</ul>'
								+ '</div>'
								+ '</div>'
								+ '</div>';
				}
				$(obj).closest('div.attr').after(insertCateHtml);	
		},'json');
};

$('.propAttrs > div').eq(0).find('li').live('click',function(){
	alert('fuck');
	getChildCategory(this);
});
$(function(){
	//var childcategory = $(getByClass(document,'attr')[0]);
	//alert(childcategory);
	$(".attr:not(:first) a").live('click',function(){
		var cid = $(this).attr('data');
		var _this = this;
		$.post(U('classroom/Video/getCategoryData'),{cid:cid},function(txt){
			if(txt.status == 0){ 
				return false
			} else {
				var percate = '';
				$.each(txt.list.data,function(index,vo){
					percate  += '<li>'
						+'<a href="javascript:;" data="'+vo.zy_video_category_id+'" databack="'+vo.zy_video_category_id+'">'+vo.title+'</a>'
						+'</li>';
				});
				var insertCateHtml = '<div class="attr clearfix">'
                    +'<div class="attrKey">√≈¿‡£∫</div>'
                    +'<div class="attrValues mtb5 clearfix">'
                       +'<ul class="av-collapse clearfix">'
					   +percate
                        +'</ul>'
                    +'</div>'
                +'</div>';
				}
				$(_this).parent().parent().parent().parent().after(insertCateHtml);	
		},'json');
	});
});