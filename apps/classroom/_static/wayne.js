// JavaScript Document

//���ؿγ̡�ר��
/**
	id Ҫ���ص�id
	type: Album ���� Video
	property ��ǰ�����״̬
*/
admin.delObject= function(id,type,property) {
	if(!type){
		return false;
	}
	if( confirm('你确定要删除吗？') ){
		$.post(U('classroom/Admin'+type+'/del'+type),{id:id,is_del:property},function(txt){
			if(txt.status == 0){
				ui.error(txt.info);
			} else {
				ui.success(txt.info);
				window.location.href = window.location.href;
			}
		},'json');
	}
	return true;
};

/**
	ɾ��γ̻���ר��
	id Ҫɾ���id
	type: Album ���� Video
	property Ҫɾ�����ݶ���(����ask���ʼ�note������review)
*/
admin.delContent = function(id,type,property) {
	if(!type){
		return false;
	}
	if( confirm('你确定要删除吗？') ){
		$.post(U('classroom/Admin'+type+'/delProperty'),{id:id,property:property},function(txt){
			if(txt.status == 0){
				ui.error(txt.info);
			} else {
				ui.success(txt.info);
				window.location.href = window.location.href;
			}
		},'json');
	}
};

//��ʼ�γ����
admin.crossVideo = function(id,cross){
	if(!id){
		return false;
	}
	$.post(U('classroom/AdminVideo/crossVideo'),{id:id,cross:cross},function(txt){
		if(txt.status == 0){
			ui.error(txt.info);
		} else {
			ui.success(txt.info);
			window.location.href = window.location.href;
		}
	},'json');
};

//视频批量审核的JS
admin.crossVideos = function(_id,action,title,type){
	var id = ("undefined"== typeof(_id)|| _id=='') ? admin.getChecked() : _id;
    if(id==''){
        ui.error(L('PUBLIC_SELECT_TITLE_TYPE',{'title':title,'type':type}));
        return false;
	}
   if(confirm(L('PUBLIC_CONFIRM_DO',{'title':title,'type':type}))){
	   $.post(U('classroom/AdminVideo/'+action),{id:id},function(msg){
			admin.ajaxReload(msg);
  	 },'json');
   }
};
