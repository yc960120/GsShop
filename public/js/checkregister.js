
$(function(){
	var flag1 = false;
	//判断用户名
		$('[name=username]').blur(function(){
			var username = $('[name=username]').val();
			 $('#span').html('');
			if(username == ''){
				$('#span').css('display','block');
				$('#span').html('用户名不能为空');
				 flag1 = false;
			} else if(username.length < 6){
				$('#span').css('display','block');
				$('#span').html('用户名长度不能小于6');
				 flag1 = false;
			} else {
				$('#span').css('display','none');
				flag1 = true;
				$.post('/index/index/selname',{username:username},check,'json');
			}
			checktj();
		});

		//判断密码
		var flag2 = false;
		$('[name=password]').blur(function(){
			 var password = $('[name=password]').val();
			 var repwd = $('[name=repwd]').val();
			 $('#span1').html('');
			 if(password.length < 4 ){
			 	$('#span1').css('display','block');
			 	 $('#span1').html('密码长度不合法');
			 	 flag2 = false;
			 }else{
			 	$('#span1').css('display','none');
			 	flag2 = true;
			 }
			 if( repwd != ''){
			 	if(password != repwd){
			 		$('#span2').css('display','block');
 					$('#span2').html('两次密码不一致');	
 					flag2 = false;
 				}else{
 					$('#span2').css('display','none');
 					flag2 = true;
 					
 				}
			}
			checktj();
		});

		//判断确认密码
		var flag3 = false;
		$('[name=repwd]').blur(function(){
				 var password = $('[name=password]').val();
				 var repwd = $('[name=repwd]').val();
				 //清空显示的文本提示
			    $('#span2').html('');
			    if(password == '' && password == repwd){
			    	$('#span2').css('display','block');
			    	$('#span2').html('请输入密码');
			    	flag3 = false;
			    } else if (password != repwd){
			    	$('#span2').css('display','block');
	 				$('#span2').html('两次密码不一致');	
	 				flag3 = false;
		 		} else {
		 			$('#span2').css('display','none');
		 			flag3 = true;
		 		}
		 		checktj();
			});

		
		//判断邮箱
		var flag4 = false;
		$('[name=email]').blur(function(){
			var email = $('[name=email]').val();
			var preg = /^[a-z\d]+(\.[a-z\d]+)*@([\da-z](-[\da-z])?)+(\.{1,2}[a-z]+)+$/;
			//清空显示的文本提示
			$('#span3').html('');
			if(email == ''){
				$('#span3').css('display','block');
				$('#span3').html('邮箱不能为空');
				flag4 = false;
			} else if (!(preg.test(email))){
				$('#span3').css('display','block');
				$('#span3').html('邮箱格式不正确');
				flag4 = false;
			} else {
				$('#span3').css('display','none');
				flag4 = true;
				
			}
			checktj();
		});

		//判断手机号码
		var flag5 = false;
		$('[name=phone]').blur(function(){
			var phone = $('[name=phone]').val();
			var preg =  /\d{2,3}-?\d{7,8}$/; 
			$('#span4').html('');
			if(phone == ''){
				$('#span4').css('display','block');
				$('#span4').html('电话号码不为空');
				flag5 = false;
			} else if(!(preg.test(phone))){
				$('#span4').css('display','block');
				$('#span4').html('电话号码格式不正确');
				flag5 = false;
			} else {
				$('#span4').css('display','none');
				flag5 = true;
				
			}
			checktj();
		});


		//判断是否能够提交
		function checktj()
		{
			if(flag1&&flag2&&flag3&&flag4&&flag5){
				$('[name=submit]').removeAttr('disabled');
			}
		}

	/*	$("#tj").click(function(){
			if(!flag){
				$('[name=submit]').removeAttr('disabled');
				}
			
		});
*/
				/*$('[name=submit]').click(function(){
					var username = $('[name=username]').val();
					var password = $('[name=password]').val();
					var email = $('[name=email]').val();
					var phone = $('[name=phone]').val();
					$.ajax({
						url:"/index/index/register",
						type:"POST",
						data:{
							"username":username,
							"password":password,
							"email":email,
							"phone":phone;
						},
						success:checkreg,
					});
					$.post('index/index/doregister',{username:username,password:password,email:email,phone:phone},checkreg,'json');
				});*/
		
		function check(data){
					if(data.state == 1){
						$('#span').html('该用户名已被注册');
						$('#span').css('display','block');
					}else{
						$('#span').html('该用户名合法');
						$('#span').css('display','block');
					}
				}
	

});