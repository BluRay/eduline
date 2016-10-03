/*
 *  获取地址控件
 */
(function($){
	$.fn.mzgetaddress = function(settings,callback){
		if(this.length<1){return;};
		// 默认值
		var defaults ={
			uploadpath:'ddd',//上传文件的路劲
			getProvince:'',//获取省信息
			getCity:'',
			error:false,
			dataItem:{}
		};
		settings=$.extend({},defaults,settings);
		//遮罩层
		settings.name = 'mz_div_address';
		settings.divname = 'mz_div_address_kj';
		var obj  = this;
		var fun  = {};
		var _fun = {};//_fun是可以暴露出去的函数
		//创建面板
		var panelTextArea,_panelTextArea;
		
		//创建HTML
		fun.createHtml = function(){
			//创建面板
			_panelTextArea=$("<div></div>",{"id":settings.name,"class":'mz-div-mask'});
			var count = 0;
			var cols  = 1;
			var html   = '<div id="'+settings.divname+'" class="mz-div-address-kj">';
    			html  += '<div class="title">';
        		html  += '<span>请选择工作地点</span>';
                html  += '<a class="btnaddressclose" href="javascript:void(0);">χ</a>';
				html  += '</div>';
				html  += '<div class="content">';
					html  += '<div class="_i">';
						//current
						for(var key in settings.ProvinceCity){
							count ++;
							
								html  += '<div class="item mz_nowrap" data-group="'+cols+'"><input id="mztxtTrade'+settings.ProvinceCity[key].id+'" name="address[]" data-group="'+cols+'" data-parent="0" type="checkbox" value="'+settings.ProvinceCity[key].id+'"><label for="emztxtTrade'+settings.ProvinceCity[key].id+'">'+settings.ProvinceCity[key].name+'</label></div>';
							if((count % 6) == 0){
								html  += '<span id="mz_span_address'+cols+'"></span>';
								cols ++;
							}
					    }
					html  += '<span id="mz_span_address'+cols+'"></span>';
						
					html  += '</div>';
					html  += '<ul id="mz_change_address_ul" class="_i2">';
						//html  += '<li value="1"><span>北京dddddddddd</span><a href="javascript:;" class="del">X</a></li>';
					html  += '</ul>';
				html  += '</div>';
				html  += '<div class="footer">';
					html  += '<input type="button" class="btnok btnaddressok" value="确定"/>';
					html  += '<input type="button" class="btncancel btnaddressclose" value="取消"/><span id="mz_address_tips" class="mz_address_tips mz_nowrap"></span>';
				html  += '</div>';
			html  += '</div>';
			$('body').append(_panelTextArea);
        	_panelTextArea.after(html);
			//处理的真正的面板
			panelTextArea = $('#'+settings.divname);
		};
		//创建市的html
		fun.createCityHtml = function(pid){
			if(!settings.City){
				fun.setError('City数据没有返回');return;
			}
			var html   = '<ul id="myul_city_'+pid+'" class="mz-ul myul_city" style="display:;">';
				for(var key in settings.City){
					html  += '<li class="mz_nowrap"><input id="mzyestxtTrade'+settings.City[key].id+'" data-parent="'+settings.City[key].pid+'" name="trade[]" type="checkbox" value="'+settings.City[key].id+'"><label title="'+settings.City[key].name+'" for="mzyestxtTrade'+settings.City[key].id+'">'+settings.City[key].name+'</label></li>';
				}
				html  += '</ul>';
			settings.City = {};
			return html;
		}
		//创建选择的项html
		fun.createItem = function(){
			if(!settings.dataItem){
				fun.setError('dataItem没有数据');return;
			}
			var html = '';
			for(var key in settings.dataItem){
				html  += '<li data-parent="'+settings.dataItem[key].pid+'" data-id="'+settings.dataItem[key].id+'"><span data-parent="'+settings.dataItem[key].pid+'" data-id="'+settings.dataItem[key].id+'">'+settings.dataItem[key].text+'</span><a data-id="'+settings.dataItem[key].id+'" href="javascript:;" class="del">X</a></li>';
			}
			$('#mz_change_address_ul').html(html);
		}
		
		fun.getItemData = function(){
			var _data = [];
			var data = panelTextArea.find('div._i input:checked');
			$.each(data,function(i,k){
				var data_parent = $(k).attr('data-parent');
				var id = $(k).val();
				_data[id] = {'pid':data_parent,'id':id,'text':$(k).siblings('label').html()};
			});
			settings.dataItem = _data;
		}
		fun.setAddress = function(){
			if(!settings.dataItem){
				fun.setError('dataItem没有数据');return;
			}
			panelTextArea.find('div._i input').attr('checked',false);
			for(var key in settings.dataItem){
				if(settings.dataItem[key].pid == 0){
					$('#mztxtTrade'+settings.dataItem[key].id).attr('checked',true);
				}else{
					$('#mzyestxtTrade'+settings.dataItem[key].id).attr('checked',true);	
				}	
			}
			fun.createItem();
		}
		fun.setError = function($error){
			settings.error = $error?true:false;
			$('#mz_address_tips').html($error);return;
		}
		//隐藏和显示
		fun.hideDiv = function(type){
			$('#'+settings.name).css('display',type?'':'none');
			$('#'+settings.divname).css('display',type?'':'none');
		}
		//获取省信息
		fun.getProvince = function(){
			if(!settings.getProvince){
				fun.setError('getProvince属性没有设置');return;
			}
			$.ajax({
				type:'POST',
				url:settings.getProvince,
				data:'is=ok',
				dataType:"json",
				async: false,
				success: function(d){
					try{
						settings.ProvinceCity = d;
					}catch(e){
						fun.setError(e.toString());return;
					}
				},
				error: function(xhr, ajaxOptions, thrownError){
					fun.setError(xhr.toString());return;
				}
			});
		}
		//获取市信息
		fun.getCity = function(pid){
			if(!settings.getCity){
				fun.setError('getCity属性没有设置');return;
			}
			$.ajax({
				type:'POST',
				url:settings.getCity,
				data:'pid='+pid,
				dataType:"json",
				async: false,
				success: function(d){
					try{
						settings.City = d;
					}catch(e){
						fun.setError(e.toString());return;
					}
				},
				error: function(xhr, ajaxOptions, thrownError){
					fun.setError(xhr.toString());return;
				}
			});
		}
		
		//首要就要取全部的省信息
		fun.getProvince();
		
		//开始创建HTML
		if(!_panelTextArea){
			if(!settings.ProvinceCity){
				fun.setError('ProvinceCity数据没有返回');return;
			}
			fun.createHtml();
			//处理初始化数据的选中
			fun.setAddress();
		}
		
		//关闭按钮
		panelTextArea.find('.btnaddressclose').live('click',function(){
			fun.hideDiv(false);	
		});
		//确定按钮
		panelTextArea.find('.btnaddressok').live('click',function(){
			fun.hideDiv(false);
			//返回本示例
			if(typeof callback != 'undefined' && callback instanceof Function){ 
				callback(settings.dataItem);
			}
		});
		//点击省的事件=====显示市
		panelTextArea.find('div.item label').live('click',function(){
			var data_group = $(this).siblings('input').attr('data-group');
			var pid        = $(this).siblings('input').val();
			//先根据省的信息查找是否已经有返回数据了
			//先关闭所有的市信息
			panelTextArea.find('ul.myul_city').css('display','none');
			panelTextArea.find('div.item').removeClass('current');
			
			if($('#myul_city_'+pid)[0]){
				//如果存在则显示这个省的市信息就好了
				$('#myul_city_'+pid).css('display','');
			}else{
				//获取市信息
				fun.getCity(pid);
				var html = fun.createCityHtml(pid);
				$('#mz_span_address'+data_group).after(html);
			}
			//添加当前显示
			$(this).parent('div').addClass('current');
			
			if($(this).siblings('input').attr('checked') != undefined){
				$('#myul_city_'+pid).find('input').attr({'checked':false,'disabled':'disabled'});
			}
			//再统一处理
			fun.setAddress();
		});
		
		//
		panelTextArea.find('div.item input').live('click',function(){
			var pid        = $(this).val();
			var checked    = $(this).attr('checked');
			
			if(checked != undefined){
				$('#myul_city_'+pid).find('input').attr({'checked':false,'disabled':'disabled'});
			}else{
				$('#myul_city_'+pid).find('input').attr({'disabled':null});	
			}
			var data = panelTextArea.find('div._i input:checked');
			if(data.size() > 5){
				$(this).attr('checked',false);
				fun.setError('最多支持选择5个');return;
			}
			
			fun.getItemData();
			fun.createItem();
		});
		panelTextArea.find('ul.myul_city input').live('click',function(){
			var data = panelTextArea.find('div._i input:checked');
			if(data.size() > 5){
				$(this).attr('checked',false);
				fun.setError('最多支持选择5个');return;
			}
			
			fun.getItemData();
			fun.createItem();
		});
		panelTextArea.find('ul._i2 li a').live('click',function(){
			//删除一个东西
			var data_id = $(this).attr('data-id');
			delete(settings.dataItem[data_id]);
			//再统一处理
			fun.setAddress();
		});
		panelTextArea.find('ul._i2 li span').live('click',function(){
			var id = $(this).attr('data-id');
			var parent = $(this).attr('data-parent');
			$('#mztxtTrade'+(parent?parent:id)).siblings('label').click();
		});
		
		//
		obj.bind('click',function(){
			fun.hideDiv(true);
		});
		
		
		//手动关闭一次
		fun.hideDiv(false);	
	};
	
})(jQuery);
