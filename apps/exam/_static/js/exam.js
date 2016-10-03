function time_djs(){
  if (test_time > 0 && $("#time").val()==1) {
     test_time = test_time - 1;
     var second = Math.floor(test_time % 60);// 计算秒     
     var minite = Math.floor((test_time / 60) % 60);//计算分
     var hour = Math.floor((test_time / 3600) % 24);
     if(hour<10){
      hour ="0"+hour;
     }
     if(minite<10){
      minite ="0"+minite;
     }
     if(second<10){
      second ="0"+second;
     }
    if(test_time==300){
      ui.error("考试时间还有五分钟，请尽快完成作答！");
      $("#time_go").css("color","red");
      }
    if(test_time==60){
      ui.error("考试时间还有一分钟，请准备提交试卷！");
    }
    $("#time_go").html(hour+"："+minite + "：" + second);
  }else{
    if(test_time==0){
      ui.error("考试时间到，本次成绩将不会被记录！");
      test_time=-1;
      $("#time").val(0);
    }
  }
}
$(document).bind("keydown",function(e){   
    e=window.event||e;
    if(e.keyCode==116){
    e.keyCode = 0;
    return false; //屏蔽F5刷新键   
    }
});
$(function(){
  var rollSet = $('#float_box');
  var offset = rollSet.offset();
  $(window).scroll(function () {
    var st = $(this).scrollTop();
    $("#show").css("margin-top",st);
   // 检查对象的顶部是否在游览器可见的范围内
   var scrollTop = $(window).scrollTop();
   if(offset.top < scrollTop){
    rollSet.addClass('fixed');
   }else{
    rollSet.removeClass('fixed');
   }
  });
});
function selected(num){
  $("#num"+num).css("background-color","#e4e4e4");
}
function inputtext(num){
  if($("#user_question"+num).val()){
    $("#num"+num).css("background-color","#e4e4e4");
  }else{
    $("#num"+num).css("background-color","");
  }
}
function add(a,b){
 suma=a+b;
 return suma;
 }
function checkForm(){
    if(confirm("你确定要提交试卷?")){
      var rightcount=0;
      var errorcount=0;
      var question_list="";
      var score=0;
      if($("#time").val()==0){
        ui.error("交卷时间已过!");
        return false;
      }else{
        $("#time").val(0);
        var sum=$("#sum").val();
        for(var i=1;i<=sum;i++){
          var user_question="";
          //获取正确答案,试题ID,试题类型
          zq_answer=$("input[name='answer"+i+"']").val();
          question_id=$("input[name='question_id"+i+"']").val();
          point=$("input[name='point"+i+"']").val();
          question_type=$("input[name='question_type"+i+"']").val();
          //判断问题类型获取值
          if(question_type==1 || question_type==4){
            user_question=$("input[name='user_question"+i+"']:checked").val();
          }else if(question_type==2){
            $("input[name='user_question"+i+"']:checked").each(function(){
              user_question+=$(this).val()+','; 
            });
            user_question=user_question.substring(0,user_question.length-1);
          }else if(question_type==3){
            user_question=$("input[name='user_question"+i+"']").val();
          }
          //判断用户答案是否正确
          if(user_question==zq_answer){
            question_list+=question_id+"-"+user_question+"+";
            rightcount++;
            score=+score+(+point);
          }else{
            if(user_question){
              question_list+=question_id+"-"+user_question+"+"; 
            }else{
              question_list+=question_id+"-"+null+"+"; 
            }
            errorcount++;
          }
        }
        question_list=question_list.substring(0,question_list.length-1);
        $("#question_list").val(question_list);
        $("#errorcount").val(errorcount);
        $("#rightcount").val(rightcount);
        $("#user_score").val(score);
      }
      return true;
    }
  }
  function j_validateCallback(form,call,callback) {
    var $form = $(form);
    if(typeof call != 'undefined' && call instanceof Function){    
      $i = call($form);
      if(!$i){
        return false;
      }
    }
    var _submitFn = function(){
      $.ajax({
        type: form.method || 'POST',
        url:$form.attr("action"),
        data:$form.serializeArray(),
        dataType:"json",
        cache: false,
        success: function(xMLHttpRequest, textStatus, errorThrown){
          if(typeof callback != 'undefined' && callback instanceof Function){   
            callback($form,xMLHttpRequest);
          }  
        },
        error: function(xhr, ajaxOptions, thrownError){
          ui.error("未知错误!");
        }
      });
    }
    _submitFn();
    return false;
  }
  function post_callback(_form,data){
    if(data.status != undefined){
      if(data.status == '0'){
        ui.error(data.info);
      } else {
        $("#bg").css("padding-top",document.body.offsetHeight);
        if($("#tm_mode").val()==0){
          $("#bg").show();
          $("#show").show();
          $("#divbox1").show();
          $("#result").html("是否查看考试结果?");
        }else{
          $("#bg").show();
          $("#show").show();
          var result_tm=$("#result_tm").val();
          $("#divbox2").show();
          var time=new Date(parseInt(result_tm) * 1000).toLocaleString().replace(/:d{1,2}$/,' ');
          $("#result").html("本次考试结果公布时间："+time);
        }
      }
    }
  }