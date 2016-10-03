/*
 *  ajax瀑布流
 *  callback---需要回调执行之前的代码--比如重新绑定事件
 */
(function($){
	$.fn.mzpinterest = function(settings,callbackmore,callback,callObj){
		if(this.length<1){return;};
		// 默认值
		var defaults ={
			url:'',//后台取数据的url
			level:1,//1：系列连载结构;2:排行榜结构
			data:{
				limit:10,//分页条数
				p:1
			}
		};
		settings=$.extend({},defaults,settings);
		var obj  = this;
		var fun  = {};
		//获取数据
		fun.getData = function(data){
			//console.log(data);
			//$(obj).html('<p><img src="'+THEME_URL+'/images/load.gif"/>&nbsp;&nbsp;数据加载中...</p>');
			$.ajax({
				url : settings.url,
				type : 'POST',
				data : data,
				async: true,
				timeout: 5000,
				dataType : 'json',
				success:function(_data){
					try{
						var htmlmore = '';
						
						if(_data.data){
							if(typeof callbackmore != 'undefined' && callbackmore instanceof Function){ 
								htmlmore = callbackmore(_data);
							}else{
								htmlmore = fun.createMore(_data);	
							}
						}else{
							htmlmore = '';
						}
						
						if($(obj).find('.pinterestitem:last')[0]){
							//填充数据
							$(obj).find('.pinterestitem:last').after(_data.data);
						}else{
							//填充数据
							$(obj).append(_data.data);	
						}
						
						//替换或者增加更多按钮
						if($('.pinterestmore')[0]){
							$('.pinterestmore').replaceWith(htmlmore);
						}else{
							//
							if(settings.level == 1){
								$(obj).parent().parent().find('td.men_slide_item1').append(htmlmore);
							}else{
								//系列连载的特殊结构
								$(obj).parent().after(htmlmore);	
							}
						}
						
						if(typeof callback != 'undefined' && callback instanceof Function){ 
							callback();
						}
					}catch(e){
						//alert('请求错误!');
					}
				},
				error : function(xhr, type) {
					//alert(xhr.responseText);
				}
			});
		};
		//创建查看更多
		fun.createMore = function(_data){
			if((_data.nowPage+1) > _data.totalPages){
				return '';
			}else{
				return '<a class="pinterestmore btn_ind dis_b fl vl_c" data-pageid="'+_data.nowPage+'" href="javascript:;">查看更多</a>';
			}
		}
		//点击加载更多
		$('div.pinterestmore,a.pinterestmore').live('click',function(){
			var data_pageid = parseInt($(this).attr('data-pageid'));
			data_pageid = data_pageid>0?data_pageid:1;
			data_pageid++;
			//重新设置分页变量
			var _settings=$.extend({},settings.data,{p:data_pageid});
			fun.getData(_settings);
		});
		
		//首次取数据
		fun.getData(settings.data);
		
		
		if(typeof callObj != 'undefined' && callObj instanceof Function){ 
			callObj(fun);
		}
	};
	
})(jQuery);
