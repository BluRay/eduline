/*
 *  选择分类
 */
(function($){
	$.fn.mzVideoLevel = function(settings,callback){
		if(this.length<1){return;};
		// 默认值
		var defaults ={
			type:1,//分类类型 1:课程分类，2:点播分类
			selectCount:1,
			isadmin:true//是否是后台
		};
		settings=$.extend({},defaults,settings);
		var obj  = this;
		var fun  = {};
		var _fun  = {};
		
		settings.myhiddenfileid = $(obj).attr('id')+'hidden';
		
		var _myhiddenfile = obj.after('<input id="'+settings.myhiddenfileid+'" name="'+settings.myhiddenfileid+'" type="hidden" value="0" />');
		
		//创建父级的HTML
		fun.createParenHtml = function(){
			if(!settings.parentLevelAll){
				alert('顶级分类没有数据');return;
			}
			if(settings.isadmin){
				if(settings.defaultids){
					var dealDefault = function(){
						
					}
					var _defaultids = settings.defaultids.split(',');
					$.each(_defaultids,function(i,k){
						if(i == 0){
							var html = fun.createSelect(settings.parentLevelAll,'mzTopLevel mzLevel',k);
							obj.after(html);
						}else{
							//找到最后一个
							fun.mzSelectChange(obj.parent().find('.mzLevel:last')[0],k);	
						}
					});
					fun.setMyItem();
				}else{
					var html = fun.createSelect(settings.parentLevelAll,'mzTopLevel mzLevel');
					obj.after(html);
				}
			}else{
				//前台	
				
				
				
			}
			
		};
		//创建选择框的HTML
		fun.createSelect = function($data,_class,$default){
			var $default = $default || 0;
			settings.selectCount ++;
			var _s = 'selected="true"';
			//后台
			var html  = '<select data-level="'+settings.selectCount+'" name="mzLevelSelect'+settings.selectCount+'" class="'+_class+'">';
				html += '<option value="0">请选择</option>';
				$.each($data,function(i,k){
					html += '<option value="'+k.zy_video_category_id+'" '+(($default==k.zy_video_category_id)?_s:'')+'>'+k.title+'</option>';
				});
				html += '</select>';
			return html;
		}
		
		
		//取得父级的
		fun.getParentLevelAll = function(){
			var url = U('widget/VideoLevel/getParentLevelAll');
			$.ajax({
				type: "POST",
				url: url,
				data: "name=John&type="+settings.type,
				async: false,
				dataType: "JSON",
				success: function(data){
					try{
						var data = eval('('+data+')');
						settings.parentLevelAll = data;
					}catch($e){
						
					}
				}
			});
			
		}
		//取得子级的
		fun.getChildren = function(_pid){
			var url = U('widget/VideoLevel/getChildrenAll');
			$.ajax({
				type: "POST",
				url: url,
				data: "pid="+_pid+"&type="+settings.type,
				async: false,
				dataType: "JSON",
				success: function(data){
					try{
						var data = eval('('+data+')');
						settings.childrenLevelAll = data;
					}catch($e){
						
					}
				}
			});
			
		}
		fun.setMyItem = function(){
			var _myItem = [];
			//把目前选中的项找出来
			$('.mzLevel').each(function(index, element) {
                _myItem.push($(element).val());
            });
			settings._myItem = _myItem.join(',');
			$('#'+settings.myhiddenfileid).val(settings._myItem);	
		}
		//选择框的选择事件
		fun.mzSelectChange = function(_this,$default){
			var $default = $default || 0;
			var dataLevel = $(_this).attr('data-level');
			//把下面的全部去掉
			$(_this).parent().find('select').each(function(index, element) {
               var _dataLevel = $(element).attr('data-level');
			   if(parseInt(_dataLevel) > parseInt(dataLevel)){
				  $(element).remove(); 
			   }
            });
			
			fun.setMyItem();
			
			if(parseInt(_this.value) <= 0){
				return;
			}
			//取他自己的子集
			fun.getChildren(_this.value);
			if(!settings.childrenLevelAll){
				return;	
			}
			//拼接成html
			var html = fun.createSelect(settings.childrenLevelAll,'mzLevel',$default);
			//加载它的后面
			$(_this).after(html);
		}
		
		//取得父级的
		fun.getParentLevelAll();
		//创建父级的html
		fun.createParenHtml();
		
		
		$('.mzLevel').live('change',function(){
			fun.mzSelectChange(this);
		});
		
		//外部允许调用的
		_fun.getData = function(){
			return settings._myItem;
		};
		
		
		//返回本示例
		if(typeof callback != 'undefined' && callback instanceof Function){ 
			callback(_fun);
		}
		
		return false;
	};
})(jQuery);