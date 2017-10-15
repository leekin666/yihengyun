/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function checkPhone(mobile_phone)
{
  var submit_disabled = false;

  if (mobile_phone == '')
  {
    $("#phone_notice").removeClass().addClass("error");
    $("#phone_notice").html(msg_phone_blank);
    submit_disabled = true;
  }
  else if (!Utils.isPhone(mobile_phone))
  {
      
    $("#phone_notice").removeClass().addClass("error");
    $("#phone_notice").html(msg_phone_not_correct);
    submit_disabled = true;
  }
 
  if( submit_disabled )
  {
    document.forms['formUser'].elements['Submit'].disabled = 'disabled';
    return false;
  }
  Ajax.call( 'user.php?act=check_phone', 'mobile_phone=' + mobile_phone, mobile_phone_callback , 'GET', 'TEXT', true, true );
}

function mobile_phone_callback(result)
{
  if ( result.replace(/\r\n/g,'') == 'ok' )
  {
    $("#phone_notice").removeClass("error").addClass("succeed");
     $("#phone_notice").html("<i></i>");
     $("#phone_verification").val(0);
    document.forms['formUser'].elements['Submit'].disabled = '';
  }
  else
  {
    $("#phone_verification").val(1);
    $("#phone_notice").removeClass().addClass("error");
    document.getElementById('phone_notice').innerHTML = msg_phone_registered;
    document.forms['formUser'].elements['Submit'].disabled = 'disabled';
     
  }
}

function checkEmail(email)
{
  var submit_disabled = false;
  
  if (email == '')
  {
    $("#email_notice").removeClass().addClass("error");
    $("#email_notice").html(msg_email_blank);
    submit_disabled = true;
  }
  else if (!Utils.isEmail(email))
  {
      
    $("#email_notice").removeClass().addClass("error");
    $("#email_notice").html(msg_email_format);
    submit_disabled = true;
  }
 
  if( submit_disabled )
  {
    document.forms['formUser'].elements['Submit'].disabled = 'disabled';
    return false;
  }
  Ajax.call( 'user.php?act=check_email', 'email=' + email, email_callback , 'GET', 'TEXT', true, true );
}

function email_callback(result)
{
  if ( result.replace(/\r\n/g,'') == 'ok' )
  {
    $("#email_notice").removeClass("error").addClass("succeed");
     $("#email_notice").html("<i></i>");
     $("#email_verification").val(0);
    document.forms['formUser'].elements['Submit'].disabled = '';
  }
  else
  {
    $("#email_verification").val(1);
    $("#email_notice").removeClass().addClass("error");
    document.getElementById('email_notice').innerHTML = msg_email_registered;
    document.forms['formUser'].elements['Submit'].disabled = 'disabled';
     
  }
}

/*判断密码强弱*/
function checkStrength(pwd,a){
        var m=0;
        if(pwd == ''){
            m=0;
        }else if(/ /.test(pwd) == true){
            m=0;
        }else if(pwd.length < 6){
            m=0;
        }else{
            var Modes = 0;
            for (i=0; i<pwd.length; i++)
            {
              var charType = 0;
              var t = pwd.charCodeAt(i);
              if (t>=48 && t <=57)
              {
                charType = 1;//数字
              }
              else if (t>=65 && t <=90)
              {
                charType = 2;//大写
              }
              else if (t>=97 && t <=122)
                charType = 4;//小写
              else
                charType = 4;
                Modes |= charType;
            }
            for (i=0;i<4;i++)
            {
              if (Modes & 1) m++;
                Modes>>>=1;
            }

            if (pwd.length<=4)
            {
              m = 1;
            }
           
        }
        a.html("<b></b>");
		a.removeClass().addClass("strength");
        if(m>0){
            if( m == 1){
			    a.addClass("strengthA");
				a.html("<b></b><span>"+weak+"</span>");
            }
            if(m == 2){
               a.addClass("strengthB");
			   a.html("<b></b><span>"+In+"</span>");
            }   
            if(m == 3){
               a.addClass("strengthC");  
			   a.html("<b></b><span>"+strong+"</span>");
            }
        }   
}
/* *
 * 处理邮箱注册用户
 */
$(function(){
	function inputRegister(){
		var frm = $("form[name='formUserE']");
        var username = $("#username");
		var email = $("#regName");
		var password = $("#pwd");
		var confirm_password = $("#pwdRepeat");
		var captcha = $("#captcha");
		var msg = '';
                
		password.blur(function(){
			var p = $(this).val();
			$("#password_notice").removeClass().addClass("error");
			if(p == ''){
				$("#password_notice").html("<i></i>"+password_empty);
			}else if(/ /.test(p) == true){
				$("#password_notice").html("<i></i>"+passwd_balnk );
			}else if(p.length < 6){
				$("#password_notice").html("<i></i>"+password_shorter );
			}else{
				$("#password_notice").removeClass("error").addClass("succeed");
				$("#password_notice").html("<i></i>");
		    }
		})
		password.keyup(function(){
		   var a = $("#password_notice");
		   var val = $(this).val();
		   checkStrength(val,a);
		});
		
		confirm_password.blur(function(){
			var c=$(this).val();
			var pass=$("#pwd").val();
			$("#confirm_password").removeClass().addClass("error");
			if(c == ''){
				$("#confirm_password").html(msg_confirm_pwd_blank);
			} else if( c!=pass ){
				$("#confirm_password").html("<i></i>"+confirm_password_invalid );
			}else{
				 $("#confirm_password").removeClass("error").addClass("succeed");
				$("#confirm_password").html("<i></i>");
			}
		});
		
		captcha.keyup(function(){
			var a=$(this).val();
			if(a.length >3){
			   var e =$("#email_enabled_captcha").val();
			 	if(e == 1){
					if(a == ''){
						$("#identifying_code").removeClass().addClass("error");
						$("#identifying_code").html(msg_identifying_code);
					}else{
						 Ajax.call( 'user.php?act=captchas', 'captcha=' + a, check_captcha_callback , 'GET', 'TEXT', true, true );
					}
			 	} 
			} 
		});
		
		function check_captcha_callback(result){
		   if(result == 2){
				$("#identifying_code").removeClass().addClass("error");
				$("#identifying_code").html(msg_identifying_not_correct);
				$("#captcha_verification").val(1);
			}else if(result == 3){
				$("#identifying_code").removeClass().addClass("succeed");
				$("#identifying_code").html("<i></i>");
				$("#captcha_verification").val(0);
			}
		}
	}
	inputRegister();
}) 
// function check_captcha_callback(result){
//       if ( result== 3 )
//        {
//          $("#identifying_code").removeClass().addClass("succeed");
//          $("#identifying_code").html("<i></i>");
//        }
//        else if(result == 2)
//        {
//          $("#identifying_code").removeClass().addClass("error");
//          $("#identifying_code").html('<i></i>' + msg_identifying_not_correct);
//
//        }
// }
function register()
{
	var frm  = document.forms['formUserE'];
	var username  = frm.elements['username'].value;
	var email  = frm.elements['email'].value;
	var password  = Utils.trim(frm.elements['password'].value);
	var confirm_password = Utils.trim(frm.elements['confirm_password'].value);
	var email_verification=$("#email_verification").val();
	var captcha = frm.elements['captcha'] ? Utils.trim(frm.elements['captcha'].value) : '';
	var captcha_verification = $("#captcha_verification").val();
	var email_enabled_captcha = $("#email_enabled_captcha").val();
	var msg = "";
  
  	var msn = frm.elements['extend_field1'] ? Utils.trim(frm.elements['extend_field1'].value) : '';
	var qq = frm.elements['extend_field2'] ? Utils.trim(frm.elements['extend_field2'].value) : '';
	var home_phone = frm.elements['extend_field4'] ? Utils.trim(frm.elements['extend_field4'].value) : '';
	var office_phone = frm.elements['extend_field3'] ? Utils.trim(frm.elements['extend_field3'].value) : '';
	var passwd_answer = frm.elements['passwd_answer'] ? Utils.trim(frm.elements['passwd_answer'].value) : '';
	var sel_question =  frm.elements['sel_question'] ? Utils.trim(frm.elements['sel_question'].value) : '';
	
	if (msn.length > 0 && (!Utils.isEmail(msn)))
	{
		$("form[name='formUserE']").find('.extend_field1').html(msn_invalid);
		msg += msn_invalid + '\n';  
	}
	
	if (qq.length > 0 && (!Utils.isNumber(qq)))
	{
		$("form[name='formUserE']").find('.extend_field2').html(qq_invalid);
		msg += qq_invalid + '\n';  
	}
	
	if (office_phone.length>0)
	{
		var reg = /^[\d|\-|\s]+$/;
		if (!reg.test(office_phone))
		{
			$("form[name='formUserE']").find('.extend_field3').html(office_phone_invalid);
			msg += office_phone_invalid + '\n';  
		}
	}
	
	if (home_phone.length>0)
	{
		var reg = /^[\d|\-|\s]+$/;
		
		if (!reg.test(home_phone))
		{
			$("form[name='formUserE']").find('.extend_field4').html(home_phone_invalid);
			msg += home_phone_invalid + '\n';  
		}
	}
	
	if (passwd_answer.length > 0 && sel_question == 0 || document.getElementById('passwd_quesetion') && passwd_answer.length == 0)
	{
		$("form[name='formUserE']").find('.sel_question').html(no_select_question);
		msg += no_select_question + '\n';
	}
  
  // 检查输入
  if(email_verification == 1){
     $("#email_notice").removeClass().addClass("error");
     $("#email_notice").html(msg_email_registered);
     msg += email_empty + '\n';
  }
  if (username.length == 0)
  {
    $("#username_notice_0").removeClass().addClass("error");
    $("#username_notice_0").html('<i></i>'+ username_empty);
    msg += username_empty + '\n';
  }
  if (email.length == 0)
  {
    $("#email_notice").removeClass().addClass("error");
    $("#email_notice").html(msg_email_blank);
    msg += email_empty + '\n';
  }
  else
  {
    if ( !(Utils.isEmail(email)))
    {
      $("#email_notice").removeClass().addClass("error");
	$("#email_notice").html(msg_email_format);
      msg += email_invalid + '\n';
    }
  }
  if (password.length == 0)
  {
     $("#password_notice").removeClass().addClass("error");       
      $("#password_notice").html("<i></i>"+password_empty);
    msg += password_empty + '\n';
  }
  else if (password.length < 6)
  { 
   $("#password_notice").removeClass().addClass("error");
   $("#password_notice").html("<i></i>"+password_shorter );
    msg += password_shorter + '\n';
  }
  
  if (/ /.test(password) == true)
  {
      $("#password_notice").removeClass().addClass("error");
      $("#password_notice").html("<i></i>"+passwd_balnk );
	msg += passwd_balnk + '\n';
  }
   if(confirm_password == ''){
       $("#confirm_password").removeClass().addClass("error");
      $("#confirm_password").html(msg_confirm_pwd_blank);
    msg += msg_confirm_pwd_blank + '\n';
  }
  if (confirm_password != password )
  {
      $("#confirm_password").removeClass().addClass("error");
      $("#confirm_password").html("<i></i>"+confirm_password_invalid );
    msg += confirm_password_invalid + '\n';
  }
 
  if(email_enabled_captcha == 1){
       if(captcha == ''){
            $("#identifying_code").removeClass().addClass("error");
            $("#identifying_code").html(msg_identifying_code);
            msg += msg_identifying_code + '\n';
        }else if(captcha.length<4){
            $("#identifying_code").removeClass().addClass("error");
            $("#identifying_code").html(msg_identifying_not_correct);
            msg += msg_identifying_not_correct + '\n';
        }
        if(captcha_verification == 1){
             $("#identifying_code").removeClass().addClass("error");
             $("#identifying_code").html(msg_identifying_not_correct);
             msg += msg_identifying_not_correct + '\n';
        }
  }
  
  if(!frm.elements['agreement'].checked)
  {
	   $("#agreement").removeClass().addClass("error");
       $("#agreement").html("<i></i>"+agreement);
		msg += agreement + '\n';  
  }
  
  $("form[name='formUserE'] :input[name='extend_field']").each(function(index, element) {
		index = Number(index + 1);
		var exis_need = $(this).attr('id');
		exis_need = exis_need.split("d");
		var id = exis_need[1];
		var ex_val = $("form[name='formUserE']").find('#exis_need' + id).val();
		var extend_field_val = $("form[name='formUserE']").find("#extend_field" + id).val();
		
		if(ex_val == 1 && extend_field_val == ''){
			$("form[name='formUserE']").find(".extend_field" + id).html(msg_blank);
			msg += msg_blank + '\n';  
		}
	});
 
  if (msg.length > 0)
  {
    return false;
  }
  else
  {
    return true;
  }
}

/* *
 * 处理手机注册用户
 */
function mobileRegister()
{
	
  var frm  = document.forms['formUser'];
  var register_type  = frm.elements['register_type'] ? frm.elements['register_type'].value : 0;
  var username  = frm.elements['username'].value;
  var mobile_phone  = frm.elements['mobile_phone'] ? frm.elements['mobile_phone'].value : '';
  var email  = frm.elements['email'] ? frm.elements['email'].value : '';
  var mobile_code  = frm.elements['mobile_code'] ? frm.elements['mobile_code'].value : '';
  var captcha  = frm.elements['captcha'] ? frm.elements['captcha'].value : '';
  var phone_code_callback  = $("#phone_code_callback").val();
  var enabled_captcha  = $("#enabled_captcha").val();
  var phone_captcha_verification  = $("#phone_captcha_verification").val();
  var phone_verification  = $("#phone_verification").val();
  var password  = Utils.trim(frm.elements['password'].value);
  var confirm_password = Utils.trim(frm.elements['confirm_password'].value);
  var msg = "";
  
  	var msn = frm.elements['extend_field1'] ? Utils.trim(frm.elements['extend_field1'].value) : '';
	var qq = frm.elements['extend_field2'] ? Utils.trim(frm.elements['extend_field2'].value) : '';
	var home_phone = frm.elements['extend_field4'] ? Utils.trim(frm.elements['extend_field4'].value) : '';
	var office_phone = frm.elements['extend_field3'] ? Utils.trim(frm.elements['extend_field3'].value) : '';
	var passwd_answer = frm.elements['passwd_answer'] ? Utils.trim(frm.elements['passwd_answer'].value) : '';
	var sel_question =  frm.elements['sel_question'] ? Utils.trim(frm.elements['sel_question'].value) : '';
	
	if (msn.length > 0 && (!Utils.isEmail(msn)))
	{
		$("form[name='formUser']").find('.extend_field1').html(msn_invalid);
		msg += msn_invalid + '\n';  
	}
	
	if (qq.length > 0 && (!Utils.isNumber(qq)))
	{
		$("form[name='formUser']").find('.extend_field2').html(qq_invalid);
		msg += qq_invalid + '\n';  
	}
	
	if (office_phone.length>0)
	{
		var reg = /^[\d|\-|\s]+$/;
		if (!reg.test(office_phone))
		{
			$("form[name='formUser']").find('.extend_field3').html(office_phone_invalid);
			msg += office_phone_invalid + '\n';  
		}
	}
	
	if (home_phone.length>0)
	{
		var reg = /^[\d|\-|\s]+$/;
		
		if (!reg.test(home_phone))
		{
			$("form[name='formUser']").find('.extend_field4').html(home_phone_invalid);
			msg += home_phone_invalid + '\n';  
		}
	}
	
	if (passwd_answer.length > 0 && sel_question == 0 || document.getElementById('passwd_quesetion') && passwd_answer.length == 0)
	{
		$("form[name='formUser']").find('.sel_question').html(no_select_question);
		msg += no_select_question + '\n';
	}
	
  // 检查输入
  if (username.length == 0)
  {
    $("#username_notice_1").removeClass().addClass("error");
    $("#username_notice_1").html('<i></i>'+ username_empty);
    msg += username_empty + '\n';
  }

  if(register_type == 0){
	  //邮箱验证
	  if (email.length == 0)
	  {
		$("#email_notice").removeClass().addClass("error");
		$("#email_notice").html(msg_email_blank);
		msg += email_empty + '\n';
	  }
	  else
	  {
		if ( !(Utils.isEmail(email)))
		{
		  $("#email_notice").removeClass().addClass("error");
		$("#email_notice").html(msg_email_format);
		  msg += email_invalid + '\n';
		}
	  }
	  
	  if(email_verification == 1){
		  $("#email_notice").removeClass().addClass("error");
		  $("#email_notice").html(msg_email_registered);
		  msg += msg_email_registered + '\n';
	  }
  }else{
	  //手机验证
	  if (mobile_phone.length == 0)
	  {
		$("#phone_notice").removeClass().addClass("error");
		$("#phone_notice").html(msg_phone_blank);
		msg += msg_phone_blank + '\n';
	  }
	  else
	  {
		if ( ! (Utils.isPhone(mobile_phone)))
		{
		$("#phone_notice").removeClass().addClass("error");
		$("#phone_notice").html(msg_phone_invalid);
		  msg += msg_phone_invalid + '\n';
		}
	  }
	  if(phone_verification == 1){
		  $("#phone_notice").removeClass().addClass("error");
		$("#phone_notice").html(msg_phone_registered);
		  msg += msg_phone_registered + '\n';
	  }
	  
	  if(frm.elements['mobile_code']){
		  if(mobile_code == ''){
			  $("#code_notice").removeClass().addClass("error");
			  $("#code_notice").html(msg_mobile_code_blank);
			   msg += msg_mobile_code_blank + '\n';
		  }
	  }
	  
	  if(phone_code_callback == 1){
			$("#code_notice").removeClass().addClass("error");
		  $("#code_notice").html(msg_mobile_code_not_correct);
		   msg += msg_mobile_code_not_correct + '\n';
	  }
  }
  
  if (password.length == 0)
  {
    $("#phone_password").removeClass().addClass("error");
    $("#phone_password").html("<i></i>"+password_empty);
    msg += password_empty + '\n';
  }
  else if (password.length < 6)
  {
     $("#phone_password").removeClass().addClass("error");
    $("#phone_password").html("<i></i>"+password_empty);
    msg += password_empty + '\n';
  }
  if (/ /.test(password) == true)
  {
       $("#phone_password").removeClass().addClass("error");
        $("#phone_password").html("<i></i>"+passwd_balnk);
	msg += passwd_balnk + '\n';
  }

  if(confirm_password == ''){
      $("#phone_confirm_password").removeClass().addClass("error");
      $("#phone_confirm_password").html(msg_confirm_pwd_blank);
       msg += msg_confirm_pwd_blank + '\n';
  }else if (confirm_password != password )
  {
    $("#phone_confirm_password").removeClass().addClass("error");
    $("#phone_confirm_password").html("<i></i>"+confirm_password_invalid );
    msg += confirm_password_invalid + '\n';
  }
   if(enabled_captcha == 1){
        if(captcha == ''){
            $("#phone_captcha").removeClass().addClass("error");
            $("#phone_captcha").html(msg_identifying_code);
            msg += msg_identifying_code + '\n';
        }else if(captcha.length<4){
            $("#phone_captcha").removeClass().addClass("error");
            $("#phone_captcha").html(msg_identifying_not_correct);
            msg += msg_identifying_not_correct + '\n';
        }
        if(phone_captcha_verification != 0){
            $("#phone_captcha").removeClass().addClass("error");
            $("#phone_captcha").html(msg_identifying_not_correct);
                        msg += msg_identifying_not_correct + '\n';

        }
   }
   
  if(!frm.elements['mobileagreement'].checked)
  {
	   $("#mobileagreement").removeClass().addClass("error");
       $("#mobileagreement").html("<i></i>"+agreement);
		msg += agreement + '\n';  
  }
  
  $("form[name='formUser'] :input[name='extend_field']").each(function(index, element) {
		index = Number(index + 1);
		var exis_need = $(this).attr('id');
		exis_need = exis_need.split("d");
		var id = exis_need[1];
		var ex_val = $("form[name='formUser']").find('#exis_need' + id).val();
		var extend_field_val = $("form[name='formUser']").find("#extend_field" + id).val();
		
		if(ex_val == 1 && extend_field_val == ''){
			$("form[name='formUser']").find(".extend_field" + id).html(msg_blank);
			msg += msg_blank + '\n';  
		}
	});

  if (msg.length > 0)
  {
    return false;
  }
  else
  {
    return true;
  }
}
/*处理手机注册*/
$(function(){
    function phoneRegister(){
        var frm = $("form[name='formUser']");
        var mobile_code=frm.find("input[name='mobile_code']");
        var mobile_phone=frm.find("input[name='mobile_phone']");
        var password=frm.find("input[name='password']");
        var confirm_password=frm.find("input[name='confirm_password']");
        var captcha=frm.find("input[name='captcha']");
        var zphone = $("#zphone");
        var sms_btn = $(".sms-btn")
        mobile_code.blur(function(){
            var m =$(this).val();
            if(m == ''){
                 $("#code_notice").removeClass().addClass("error");
                 $("#code_notice").html(msg_mobile_code_blank);
            }
            else{
                Ajax.call( 'user.php?act=code_notice', 'code=' + m, phone_code_callback , 'GET', 'TEXT', true, true );
            }
        });
     
        sms_btn.click(function(){
            sendSms();
        })
        password.blur(function(){
            var p = $(this).val();
            $("#phone_password").removeClass().addClass("error");
            if(p == ''){
                $("#phone_password").html("<i></i>"+password_empty);
            }else if(/ /.test(p) == true){
                $("#phone_password").html("<i></i>"+passwd_balnk );
            }else if(p.length < 6){
                $("#phone_password").html("<i></i>"+password_shorter );
            }
//            else if((Utils.IsLetters(p)) == false){
//                $("#phone_password").html("<i></i>必须为字母加数字");
//            }
            else{
                $("#phone_password").removeClass("error").addClass("succeed");
                $("#phone_password").html("<i></i>");
            }
        })
         confirm_password.blur(function(){
            var c=$(this).val();
            var pass=password.val();
            $("#phone_confirm_password").removeClass().addClass("error");
           if(c == ''){
                $("#phone_confirm_password").html(msg_confirm_pwd_blank);
            }else if( c!=pass ){
                $("#phone_confirm_password").html("<i></i>"+confirm_password_invalid );
            } else{
                 $("#phone_confirm_password").removeClass("error").addClass("succeed");
                $("#phone_confirm_password").html("<i></i>");
            }
         })
         password.keyup(function(){
            var a = $("#phone_password");
           $("#phone_password").html("");
           var val = $(this).val();
           checkStrength(val,a);
        })
            captcha.keyup(function(){
                 var a=$(this).val();
                if(a.length > 3){
                     var e = $("#enabled_captcha").val();
                    if( e == 1){
                        if(a == ''){
                        $("#phone_captcha").removeClass().addClass("error");
                        $("#phone_captcha").html(msg_identifying_code);
                        }else{
                             Ajax.call( 'user.php?act=phone_captcha', 'captcha=' + a, phone_captcha_callback , 'GET', 'TEXT', true, true );
                        }
                    }
                }
             })
            function phone_captcha_callback(result){
                if(result == 1){
                    $("#phone_captcha").removeClass().addClass("error");
                    $("#phone_captcha").html(msg_identifying_code);
                }else if(result == 2){
                    $("#phone_captcha").removeClass().addClass("error");
                    $("#phone_captcha").html(msg_identifying_not_correct);
                    $("#phone_captcha_verification").val(1);
                }else{
                    $("#phone_captcha").removeClass().addClass("succeed");
                    $("#phone_captcha").html("<i></i>");
                    $("#phone_captcha_verification").val(0);
                }
            }
    }
    phoneRegister()
})
  function phone_code_callback(result){
           if(result == 1){
               $("#code_notice").removeClass().addClass("error");
               $("#code_notice").html(msg_mobile_code_blank);
               $("#phone_code_callback").val(1);
           }else if(result == 2){
                $("#code_notice").removeClass().addClass("error");
               $("#code_notice").html(msg_mobile_code_not_correct);
               $("#phone_code_callback").val(1);
           }else{
                $("#code_notice").removeClass("error").addClass("succeed");
		$("#code_notice").html("<i></i>");
                $("#phone_code_callback").val(0);
           }
       }
