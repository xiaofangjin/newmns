<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:51:"E:\items\mns/application/index\view\user\login.html";i:1590218306;}*/ ?>
<!DOCTYPE html>
<html class="size" style="font-size: 23.4375px;"><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		
		<meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=no">
		<title>MNS</title>
		<link href="/public/static/index/css/app.css" rel="stylesheet">
		<script src="/public/static/index/js/jquery-1.js"></script>
		<link rel="stylesheet" href="/public/static/index/css/dialog.css">
		<script src="/public/static/index/js/layer/layer.js"></script>
	<style>
		html{background:#000002;}
		.forget .flex-list a{
			width: 30%;
			padding: 5px 10px;
		}
		
	</style>
	</head>
	<body class="loginBg">
		<div id="landing">
			<div class="header0"></div>
			<div>
				<h4 class="title" style="margin-bottom: 35px;">
					<img src="/public/static/index/img/bshlogo.png" alt="">
				</h4>
				<div class="landing_box">
					<div class="landing_ipt register_phone">
						<span><img src="/public/static/index/img/dh.png" alt=""></span>
						<input name="phone" placeholder="请输入手机号码" type="text" id="userid" value="<?php echo \think\Cookie::get('username'); ?>">
					</div>
					<div class="landing_ipt register_phone">
						<span><img src="/public/static/index/img/mm.png" alt=""></span>
						<input name="password" placeholder="请输入登录密码" type="password" id="password" value="<?php echo \think\Cookie::get('password'); ?>">
					</div>

					<div class="landing_ipt">
						<div id="embed-captcha"></div>
					</div>
				</div>

				<div class="landing_btn">
					<button type="button" class="btn" id="button1">登录</button>
				</div>
				<div class="forget flex-box">
					<div class="flex-list">
						<a href="<?php echo url('index/user/wjmm'); ?>">重置密码</a>
						<a href="javascript:;">下载APP</a>
						<a href="<?php echo url('index/user/sign'); ?>">注册</a>
					</div>
				</div>

			</div>

		</div>

		<script src="/public/static/index/js/zepto.min.js"></script>
	    <script src="/public/static/index/js/dialog.js"></script>
	    <script src="/public/static/index/js/gt.js"></script>
		<script type="text/javascript">
			var handlerEmbed = function (captchaObj) {
				// 将验证码加到id为captcha的元素里，同时会有三个input的值：geetest_challenge, geetest_validate, geetest_seccode
				captchaObj.appendTo("#embed-captcha");
			};
			$.ajax({
				// 获取id，challenge，success（是否启用failback）
				url: "<?php echo url('index/user/getCaptcha'); ?>?t=" + (new Date()).getTime(), // 加随机数防止缓存
				type: "get",
				dataType: "json",
				success: function (data) {
					console.log(data);
					// 使用initGeetest接口
					// 参数1：配置参数
					// 参数2：回调，回调的第一个参数验证码对象，之后可以使用它做appendTo之类的事件
					initGeetest({
						gt: data.gt,
						challenge: data.challenge,
						new_captcha: data.new_captcha,
						product: "embed", // 产品形式，包括：float，embed，popup。注意只对PC版验证码有效
						offline: !data.success // 表示用户后台检测极验服务器是否宕机，一般不需要关注
					}, handlerEmbed);
				}
			});
			$("#remember").click(function(){
                var val = $(this).val();
			    if (val == 1){
			        $(this).val(2);
				} else{
                    $(this).val(1);
				}
			})

			 $(".btn").on('click',function(){
					var userid = $("#userid").val();
					var password = $("#password").val();
					// var remember = $("#remember").val();
					 var geetest_challenge= $("input[name='geetest_challenge']").val();
					 var geetest_validate= $("input[name='geetest_validate']").val();
					 var geetest_seccode= $("input[name='geetest_seccode']").val();
					 var remember = 2;
                     if (userid == ''){
                         layer.msg('请输入账号');
                         return false;
                     }
                     if (password == ''){
                         layer.msg('请输入登录密码');
                         return false;
                     }
				 if (geetest_challenge == '' || geetest_validate=='' || geetest_seccode==''){
					 layer.msg('请点击验证');
					 return false;
				 }
                     var load = layer.load();
                     $.ajax({
                         url:"<?php echo url('index/user/dologin'); ?>",
                         type:'post',
                         data:{userid:userid,pwd:password,remember:remember,geetest_challenge:geetest_challenge,
							 geetest_validate:geetest_validate,geetest_seccode:geetest_seccode},
                         dataType:'json',
                         success:function(res){
                             if(res.con==1){
                                     location.href="<?php echo url('index/index/index'); ?>";
                             }else{
                                 layer.close(load);
                                 layer.msg(res.message);
                             }
                         }
                     })
		 })
		 </script>
	

</body></html>