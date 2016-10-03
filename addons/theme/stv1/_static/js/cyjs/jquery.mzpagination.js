/*
 *  ajax分页插件
 */
(function($){
	$.fn.mzpagination = function(settings,callback,callObj,callReturn){
		if(this.length<1){return;};
		// 默认值
		var defaults ={
			url:'',//后台取数据的url
			ispage:true,//是否分页
			rollPage:4,//分页偏移量
			hashtml:false,//是否在服务器拼接所需html---返回不一样
			data:{
				limit:4,//分页条数
				p:1
			}
		};
		settings=$.extend({},defaults,settings);
		var obj  = this;
		var fun  = {};
		//获取数据
		fun.getData = function(data){
			$(obj).html('<p><img src="'+THEME_URL+'/images/load.gif"/>&nbsp;&nbsp;数据加载中...</p>');
			$.ajax({
				url : settings.url,
				type : 'POST',
				data : data,
				async: true,
				timeout: 5000,
				dataType : 'json',
				success:function(_data){
					try{
						var html = '';
						if(typeof callback != 'undefined' && callback instanceof Function){ 
							html = callback(_data.data);
						}else{
							if(settings.hashtml){
								html = _data.data;			
							}else{
								html = fun.myHtml(_data.data);		
							}
						}
						//如果没有数据---增加友好显示
						html = _data.data?html:'<div style="text-align:center;height:auto;overflow:hidden;">暂无数据</div>';
						$(obj).html(html);
						if(settings.ispage && _data.data){
							fun.pageHtml(_data);
						}
						if(!_data.data){
							//清除分页数据
							$(obj).siblings('ul.pagination').remove();
							settings.pagination = null;
						}
						if(typeof callReturn != 'undefined' && callReturn instanceof Function){ 
							callReturn();
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
		fun.myHtml = function(_data){
			return '我的分页啊';
		}
		//创建分页的html
		fun.pageHtml = function(_data){
			if(_data.nowPage == 1){
				var $firstPage = '<li><a href="javascript:void(0);">首页</a></li>';
			}else{
				var $firstPage = '<li><a href="javascript:void(0);" data-id="1">首页</a></li>';	
			}
			if((_data.nowPage-1) <= 0){
				var $upRow     = '<li><a href="javascript:void(0);">上一页</a></li>';
			}else{
				var $upRow     = '<li><a href="javascript:void(0);" data-id="'+(_data.nowPage-1)+'">上一页</a></li>';
			}
			if((_data.nowPage+1) > _data.totalPages){
				var $downRow   = '<li><a href="javascript:void(0);">下一页</a></li>';
			}else{
				var $downRow   = '<li><a href="javascript:void(0);" data-id="'+(_data.nowPage+1)+'">下一页</a></li>';
			}
			if(_data.nowPage == _data.totalPages){
				var $lastPage  = '<li><a href="javascript:void(0);">末页</a></li>';
			}else{
				var $lastPage  = '<li><a href="javascript:void(0);" data-id="'+_data.totalPages+'">末页</a></li>';
			}
			
			var $nowCoolPage = Math.ceil(_data.nowPage/settings.rollPage);
			//便宜算法
			var $linkPage  = '';
			for(var i=1; i<=settings.rollPage; i++){
				var $page = ($nowCoolPage-1)*settings.rollPage+i;
				if($page != _data.nowPage){
					if($page<=_data.totalPages){
						$linkPage += '<li><a href="javascript:void(0);" data-id="'+$page+'">'+$page+'</a></li>';	
					}else{
						break;
					}
				}else{
					$linkPage += '<li class="active"><span>'+$page+'</span></li>';
					/*if(_data.totalPages != 1){
						$linkPage += '<li class="active"><span>'+$page+'</span></li>';
					}*/
				}
			}
			//创建分页
			var html = '';
			
			html += $firstPage;
			html += $upRow;
			//这个地方要处理成最后只显示5条
			html += $linkPage;
			html += $downRow;
			html += $lastPage;
			
			if(!settings.pagination){
				settings.pagination = $('<ul></ul>',{'class':'pagination fr mt10 mb10 mr15'});
				$(obj).after(settings.pagination);
			}
			
			settings.pagination.html(html);
			//绑定分页按钮事件
			settings.pagination.find('li').click(function(){
				var nowPage = $(this).find('a').attr('data-id');
				if(nowPage){
					var _settings=$.extend({},settings.data,{p:nowPage});
					fun.getData(_settings);
				}
			});
		};
		//强制传入数据分页
		fun.getData_coerce = function($data){
			//强制改变里面的值
			settings.data=$.extend({},settings.data,$data);
			fun.getData(settings.data);
		};
		
		$(obj).html('<p><img src="'+THEME_URL+'/images/load.gif"/>&nbsp;&nbsp;数据加载中...</p>');
		setTimeout(function(){
			//首次加载
			fun.getData(settings.data);
		},500);
		if(typeof callObj != 'undefined' && callObj instanceof Function){ 
			callObj(fun);
		}
	};
	
})(jQuery);
