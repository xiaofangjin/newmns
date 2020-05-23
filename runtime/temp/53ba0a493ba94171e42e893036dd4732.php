<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:50:"E:\items\mns/application/index\view\center\my.html";i:1573121194;}*/ ?>
<!DOCTYPE html>
<html class="size" style="font-size: 23.4375px;"><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		
		<meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=no">
		<title>MNS</title>
		<link href="/public/static/index/css/app.css" rel="stylesheet">
		<script src="/public/static/index/js/jquery-1.js"></script>
		<script src="/public/static/index/js/layer/layer.js"></script>

	<style>

	.aa1{float:left; background:#fff;height:60px;width:95px;border-radius: 17px 17px 0 0;}
	.aa1 img{margin-left: 35px;width: 23px;}
	.xiang{width:87px;height:88px;border-radius:100%;vertical-align:middle;margin-right:10px;}
	</style></head>

	
	<body class="wallet" style="background:#000;height:auto;">
			<div id="app">
				<div class="heade" style="background: #373737;line-height: 50px;display: flex;align-items: center">
					<img style="width: 42px;margin: 2px 0px 0px 20px;" src="/public/static/index/img/bshlogo2.png" alt="">
					<p style="margin-left:10px;font-size:18px;color: #fcd108">个人</p>
				</div>
				<div class="view">
					<div class="userinfo">
						<div style="background:#fcd108;border-radius: 15px;">

							<div style="float: left;width:25%;padding: 28px 10px;">
								<?php if($user['tou']): ?>
								<img src="/public/static/upload/img/<?php echo $user['tou']; ?>" class="xiang">
								<?php else: ?>
								<img src="/public/static/index/img/toux.png" alt="">
								<?php endif; ?>
							</div>
							<div style="height: 150px;">
								<p style="font-size: 16px;padding-top: 33px;">
									昵称：<?php echo $user['name']; ?></p>

								<p style="font-size: 16px;margin-top: 5px;">账号: <?php echo substr($user['userid'],0,3); ?>****<?php echo substr($user['userid'],7); ?> </p>

								<p style="font-size: 16px;margin-top: 5px;">级别：<?php echo $user['kg_level']; if(!empty($user['level'])): ?>（<?php echo $user['level']; ?>）<?php endif; ?>
								</p>
							</div>
						</div>
					</div>


					&lt;<!-- div class="flex-box zone_1"> -->
					<div style="height:80px;width: 225px;margin: auto;position:relative;top:30px;z-index: 1;">
						<div class="aa1" style="margin-right:15%;">
							<a href="<?php echo url('index/center/partner'); ?>" class="">
								<p class="icon">
									<img src="/public/static/index/img/index2.png" alt="" width="auto" height="100%">
								</p>
								<p style="font-size: 16px;margin-top:10px;text-align: center;">商业伙伴</p>
							</a>
						</div>
						<div class="aa1">
							<a href="<?php echo url('index/center/yqhy'); ?>" class="">
								<p class="icon">
									<img src="/public/static/index/img/index5.png" alt="" width="auto" height="100%">
								</p>
								<p style="font-size: 16px;margin-top:10px;text-align: center;">邀请好友</p>
							</a>
						</div>
					</div>

					<div class="my-list-box" style="background-color: #fff;    border-radius: 15px;width: 92%;margin: 9px auto">
						<ul style="border-radius: 15px;    padding-top: 20px;">


							<a href="<?php echo url('index/center/jymx'); ?>" class="router-link-exact-active router-link-active">
								<li class="flex-box my_list">
									<div class="flex-size">
										<i class="icon">
											<img src="/public/static/index/img/index9.png" alt="" width="100%" height="auto">
										</i>
									</div>
									<div class="flex-list">
										<p>交易明细</p>
									</div>
								</li>
							</a>


							<a href="<?php echo url('index/center/symx'); ?>" class="router-link-exact-active router-link-active">
								<li class="flex-box my_list">
									<div class="flex-size">
										<i class="icon">
											<img src="/public/static/index/img/index8.png" alt="" width="100%" height="auto">
										</i>
									</div>
									<div class="flex-list">
										<p>收益明细</p>
									</div>
								</li>
							</a>
							

							<a href="<?php echo url('index/center/sz'); ?>" class="">
								<li class="flex-box my_list">
									<div class="flex-size">
										<i class="icon">
											<img src="/public/static/index/img/index10.png" alt="" width="auto" height="100%">
										</i>
									</div>
									<div class="flex-list">
										<p>设置</p>
									</div>
								</li>
							</a>
						
							<a href="<?php echo url('index/news/xtxx'); ?>" class="">
									<li class="flex-box my_list">
										<div class="flex-size">
											<i class="icon">
												<img src="/public/static/index/img/index13.png" alt="" width="100%" height="auto">
											</i>
										</div>
										<div class="flex-list">
											<p>系统消息</p>
										</div>
									</li>
								</a>
							<a href="http://live.easyliao.com/live/chat.do?c=10676&g=23891&config=34372&tag=0" class="">
								<li class="flex-box my_list">
									<div class="flex-size">
										<i class="icon">
											<img src="/public/static/index/img/index12.png" alt="" width="auto" height="100%">
										</i>
									</div>
									<div class="flex-list">
										<p>在线客服</p>
									</div>
								</li>
							</a>
							
							<a href="havascript:;" onclick="abc()">
								<li class="flex-box my_list">
									<div class="flex-size">
										<i class="icon">
											<img src="/public/static/index/img/index14.png" alt="" width="auto" height="100%">
										</i>
									</div>
									<div class="flex-list" >
										<p>退出</p>
									</div>
								</li>
							</a>

						</ul>
					</div>
					<div style="height:70px;background:#000;"></div>
					<div class="footr">
						<div>
							<a href="<?php echo url('index/index/index'); ?>" class="">
								<img src="/public/static/index/img/nav1.png">
								<p>首页</p>
								<!-- <br>首页 -->
							</a>
						</div>
						<div>
							<a href="<?php echo url('index/deal/jy'); ?>" class="">
								<img src="/public/static/index/img/index1.svg">
								<p>交易</p>
								<!-- <br>资产 -->
							</a>
						</div>
						<div>
							<a href="<?php echo url('index/news/zx'); ?>" class="">
								<img src="/public/static/index/img/index2.svg">
								<p>资讯</p>
								<!-- <br>市场 -->
							</a>
						</div>

						<div>
							<a href="<?php echo url('index/center/my'); ?>" class="ace">
								<img src="/public/static/index/img/nav8.svg">
								<p>个人</p>
							</a>
						</div>
					</div>

				</div>
			</div>
			<script>
                function abc(){

                    layer.confirm('确定退出吗?', {icon: 3, title:'提示'}, function(index){
                        $.post("<?php echo url('index/center/logout'); ?>",function(){
                            location.href="<?php echo url('index/user/login'); ?>";
                        });
                        layer.close(index);
                    });
                }
			</script>
	
</body></html>