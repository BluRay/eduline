// 加入群组
function joingroup(gid) {
    if(MID=='0'){
	  reg_login()
	  return ;
    }

    ui.box.load(U('group/Group/joinGroup')+'&gid='+gid,'加入群组');
}
function addPost(gid){
    if(MID=='0'){
        reg_login()
        return ;
    }
    window.location="{:U('group/Topic/add',array('gid'=>$gid))}";
}
// 删除群组
function delgroup(gid) {
	if(MID=='0'){
	  reg_login()
	  return ;
    }
    ui.box.load(U('group/Group/delGroupDialog')+'&gid='+gid,'解散群组');
}
// 退出群组
function quitgroup(gid) {
	if(MID=='0'){
	  reg_login()
	  return ;
    }
    ui.box.load(U('group/Group/quitGroupDialog')+'&gid='+gid,'退出群组');
}
// 过滤html，字串检测长度
function checkPostContent(content)
{
	content = content.replace(/&nbsp;/g, "");
	content = content.replace(/<br>/g, "");
	content = content.replace(/<p>/g, "");
	content = content.replace(/<\/p>/g, "");
	return getLength(content);
}