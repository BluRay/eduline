/**
 * Created by Administrator on 14-10-16.
 */


admin.delWenda=function(_id,action){
    var id = ("undefined"== typeof(_id)|| _id=='') ? admin.getChecked() : _id;
    if(id==''){
        ui.error("问答id不能为null");
        return false;
    }
    if(!confirm("是否确认删除此问答？")){
        return false;
    }
    $.post(U('wenda/AdminIndex/'+action),{ids:id},function(msg){
        admin.ajaxReload(msg);
    },'json');
}

admin.delWendaAll=function(action){

  var ids=admin.getChecked();
   ids = ("undefined"== typeof(ids)|| ids=='') ? admin.getChecked() : ids;
    if(ids==''){
        ui.error("问答id不能为null");
        return false;
    }
    if(!confirm("是否确认删除此问答？")){
        return false;
    }
    $.post(U('wenda/AdminIndex/'+action),{ids:ids},function(msg){
        admin.ajaxReload(msg);
    },'json');
}