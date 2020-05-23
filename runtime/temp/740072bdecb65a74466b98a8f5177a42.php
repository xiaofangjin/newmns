<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:52:"E:\items\mns/application/index\view\center\symx.html";i:1573459435;}*/ ?>
<!DOCTYPE html>
<html class="size" style="font-size: 23.4375px;"><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		
		<meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=no">
		<title>MNS</title>
		<link href="/public/static/index/css/app.css" rel="stylesheet">
		<script src="/public/static/index/js/jquery-1.js"></script>
	</head>
<style>
	.nave{display: flex;justify-content: space-around}
	.nave button{
		font-size: 0.5rem;
		background: transparent;
		border: 1px solid #DFBB7F;
		color: #DFBB7F;
		width: 30%;
		margin: 2%;
		border-radius: 10px;
		outline: none;
		padding: 5px;
	}
	.nave button.active{
		background: #DFBB7F;
		color:#000
	}
</style>
	<body class="wallet">

		<div id="app">
			<div>
				<div class="earnings">
					<div class="header-f">
						<a href="javascript:history.go(-1);" class="icon-left">
							<img src="/public/static/index/img/left1.png" alt="">
						</a>
						<h1>我的收益明细</h1>
					</div>
				</div>
				<div class="view view1">

					<div class="nave">
						<button class="btn1 active">推荐明细</button>
						<button class="btn2">业绩明细</button>
						<button class="btn3">团队明细</button>
					</div>
					<div>
						<ul class="item1">
							<?php if(is_array($list[0]) || $list[0] instanceof \think\Collection || $list[0] instanceof \think\Paginator): $i = 0; $__LIST__ = $list[0];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
							<li class="flex-box earningsinfo_list">
								<div class="flex-list left">
									<h4><?php echo $vo['from']; ?></h4>
									<p><?php echo $vo['create_time']; ?></p>
								</div>
								<div class="flex-list right"><b class="up"><?php echo $vo['get_money']; ?></b></div>
							</li>
							<?php endforeach; endif; else: echo "" ;endif; ?>
						</ul>
						<ul class="item2" style="display: none">
							<?php if(is_array($list[1]) || $list[1] instanceof \think\Collection || $list[1] instanceof \think\Paginator): $i = 0; $__LIST__ = $list[1];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
							<li class="flex-box earningsinfo_list">
								<div class="flex-list left">
									<p><?php echo substr($vo['create_time'],0,10); ?>业绩奖</p>
								</div>
								<div class="flex-list right"><b class="up"><?php echo $vo['sum']; ?></b></div>
							</li>
							<?php endforeach; endif; else: echo "" ;endif; ?>
						</ul>
						<ul class="item3" style="display: none">
							<?php if(is_array($list[2]) || $list[2] instanceof \think\Collection || $list[2] instanceof \think\Paginator): $i = 0; $__LIST__ = $list[2];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
							<li class="flex-box earningsinfo_list">
								<div class="flex-list left">
									<p><?php echo substr($vo['create_time'],0,10); ?>团队奖</p>
								</div>
								<div class="flex-list right"><b class="up"><?php echo $vo['sum']; ?></b></div>
							</li>
							<?php endforeach; endif; else: echo "" ;endif; ?>
						</ul>
					</div>
				</div>

			</div>
		</div>
		<script>
			$(".size").css("font-size", $("body").width() * 0.0625 + "px")
			$('.nave>button').click(function () {
				$(this).addClass('active').siblings().removeClass('active')
            })
			$('.btn1').click(function () {
				$('.item1').show().siblings().hide()
            })
            $('.btn2').click(function () {
                $('.item2').show().siblings().hide()
            })
            $('.btn3').click(function () {
                $('.item3').show().siblings().hide()
            })
		</script>


	

</body></html>