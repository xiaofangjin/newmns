<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:52:"E:\items\mns/application/index\view\index\index.html";i:1575101054;}*/ ?>
<!DOCTYPE html>
<html class="size" ><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=no">
		<title>MNS</title>
		
        <script src="/public/static/index/js/jquery-1.js"></script>
        		    		
    <link rel="stylesheet" href="/public/static/index/css/meCen.css">
    <link href="/public/static/index/css/app.css" rel="stylesheet">
    <script type="text/javascript" src="/public/static/index/js/jquery-1.9.1.min.js"></script>
    <script type="text/javascript" src="/public/static/index/js/jquery.glide.min.js"></script>
    <style>
    .qpan{margin-top:20px;}
    .qpan a{width:29%;height:80px;background:#4a4949;margin:4px;display:inline-block;border-radius: 10px;text-align:center;}
    .qpan img{width:30px;margin-top:10px;}
    .qpan p{margin-top:10px;color:#fff;}

    .shuj li{width:90%;height:50px;margin-bottom:10px;margin: 25px auto;}
    .shuj img{float:left;}
    .shuj p{font-size:16px;margin: 0px 0px 0px 9px;color:#8a8787;}

    .yuans{background:linear-gradient(to right,#dbca86 10%,#db9c11 100%) !important;}

    .zhif a{width:32%;height:80px;display: inline-block;text-align: center;margin-top: 20px;}
    .zhif a img{float:none;}
    .zhif a p{margin: 10px 0px 0px 0px;color:#fff;}
    .fontline{
        display:block;
        width:31em;
        word-break:keep-all;
        white-space:nowrap;
        overflow:hidden;
        text-overflow:ellipsis;
    }
.footr p{
    line-height: 16px;
}
.layui-progress-bar{background: #db9a0d !important;}
    </style>
    </head>
    <body class="bg96" style="background:#000;margin-top:0">

    <div class="heade" style="background: #373737;line-height: 50px;display: flex;align-items: center">
        <img style="width: 42px;margin: 2px 0px 0px 20px;" src="/public/static/index/img/bshlogo2.png" alt="">
        <p style="margin-left:10px;font-size:18px;">首页</p>
    </div>

    <div class="big_width100" style="margin-top:0">
        <div style="height:360px">
           <!--  <ul class="slides">

                <li class="slide">
                    <div class="box"><img style="height:250px" src="/static/index/images/bg1.png" alt=""></div>
                </li>
                <li class="slide">
                    <div class="box"><img style="height:250px" src="/static/index/images/bg2.png" alt=""></div>
                </li>

            </ul> -->
            <div style="height: 44px;font-size: 23px;text-align: center;margin-top: 15px;    line-height: 44px;">
                总收益            </div>
            <div style="text-align:center;">
                <img src="/public/static/index/img/11.png" alt="">
            </div>
            <div style="text-align:center;margin-top:10px;">
                <span style="font-size: 30px;"><?php echo $user['total']; ?></span><span style="margin-left: 15px;font-size: 18px;">MNS</span>
            </div>

            <div class="qpan">
                <div class="nav" style="width:90%;margin:auto;">
                    <a id="dij" href="javascript:;" class="yuans">
                        <img src="/public/static/index/img/22.png" alt="">
                        <p>钱包</p>
                    </a>
                    <a id="dij1" href="javascript:;">
                        <img src="/public/static/index/img/33.png" alt="">
                        <p>收益</p>
                    </a>
                    <a id="dij2" href="javascript:;">
                        <img src="/public/static/index/img/44.png" alt="">
                        <p>支付</p>
                    </a>

                </div>
            </div>

            <div>
                <div class="shuj" id="shu1">
                    <ul>
                        <li>
                            <img src="/public/static/index/img/icon-11.png" alt="">
                            <div style="width:100px;float: left;line-height: 50px;">
                                <p>本金</p>
                            </div>
                            <div style="float:right;line-height: 50px;font-size:18px;"><?php echo $user['money']; ?></div>
                        </li>

                        <li>
                            <img src="/public/static/index/img/icon-22.png" alt="">
                            <div style="width:100px;float: left;line-height: 50px;">
                                <p>团队总业绩</p>
                            </div>
                            <div style="float:right;line-height: 50px;font-size:18px;"><?php echo $total; ?></div>
                        </li>
                    </ul>
                </div>

                <div class="shuj" id="shu2" style="display:none;">
                    <ul>
                        <li>
                            <img src="/public/static/index/img/icon-11.png" alt="">
                            <div style="width:130px;float: left;line-height: 50px;">
                                <p>当日静态收益</p>
                            </div>
                            <div style="float:right;line-height: 50px;font-size:18px;"><?php echo $jing; ?></div>
                        </li>

                        <li>
                            <img src="/public/static/index/img/icon-22.png" alt="">
                            <div style="width:130px;float: left;line-height: 50px;">
                                <p>当日动态收益</p>
                            </div>
                            <div style="float:right;line-height: 50px;font-size:18px;"><?php echo $dong; ?></div>
                        </li>
                    </ul>
                </div>

                <div class="shuj" id="shu3" style="display:none;">

                    <div class="zhif" style="width: 80%;margin: auto;margin-top: 25px;">
                        <a href="<?php echo url('index/index/cz'); ?>" >
                            <img src="/public/static/index/img/i1.png" alt=""><br>
                            <p>充值</p>
                        </a>
                        <a href="<?php echo url('index/index/tx'); ?>" >
                            <img src="/public/static/index/img/i2.png" alt=""><br>
                            <p>提现</p>
                        </a>
                        <a href="<?php echo url('index/index/ft'); ?>" >
                            <img src="/public/static/index/img/i3.png" alt=""><br>
                            <p>复投</p>
                        </a>

                    </div>

                </div>
            </div>

        </div>

    </div>

    <div id="jind" >
        <h2 style="text-align: center;line-height: 50px;font-size: 16px;" >三倍出局滚动条</h2>
        <div style="height:28px;width: 90%; margin: auto;">
                <p style="float: left;margin-left: 15px;font-size:16px;color:#8a8787;">已提现金额：<?php echo $user['tixian']; ?></p>
                <p style="float: right;font-size:14px;color:#8a8787;">本次出局金额：<?php echo $chuju; ?></p>
        </div>
		<div style="margin:20px;" class="layui-progress layui-progress-big" lay-showpercent="true">
                <div class="layui-progress-bar" lay-percent="<?php echo $jin; ?>%"></div>
      </div>
        <!--<div style="background:#000;">-->
            <!--<div style="height:28px;width: 90%; margin: auto;">-->
                    <!--<p style="float: right;font-size:16px;color:#8a8787;margin-top:10px;">1000万枚</p>-->
            <!--</div>-->
        <!--</div>-->
    </div>
	<div style="height:60px;"></div>
<div class="footr">
	<div>
		<a href="<?php echo url('index/index/index'); ?>" class="ace">
            <img src="/public/static/index/img/nav2.png">
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
            		
        </a>
	</div>
	<div>
		<a href="<?php echo url('index/center/my'); ?>" class="">
            <img src="/public/static/index/img/gr.svg">
            <p>个人</p>
			<!-- <br>个人 -->
		</a>
	</div>
</div>
<link rel="stylesheet" href="/public/static/index/js/layui/css/layui.css">
 <script src="/public/static/index/js/layui/layui.all.js"></script>
<script>
layui.use('element', function(){
  var $ = layui.jquery
  ,element = layui.element; //Tab的切换功能，切换事件监听等，需要依赖element模块
});
    $('#logout').click(function(){
        $.get('/index.php/index/ucenter/logout.html', {type:'logout'}, function(res){
            if (res.code == 1) {
                location.href = res.url;
            }
        });
    });
</script>

    <!--<link rel="stylesheet" href="/public/static/index/css/cssdialog.css">-->

    <script src="/public/static/index/js/dialog.js"></script>

    <script type="text/javascript">
        var glide = $('.slider').glide({
            //autoplay:true,//是否自动播放 默认值 true如果不需要就设置此值
            animationTime: 500, //动画过度效果，只有当浏览器支持CSS3的时候生效
            arrows: false, //是否显示左右导航器
            arrowsWrapperClass: "arrowsWrapper",//滑块箭头导航器外部DIV类名
            arrowMainClass: "slider-arrow",//滑块箭头公共类名
            arrowRightClass: "slider-arrow--right",//滑块右箭头类名
            arrowLeftClass: "slider-arrow--left",//滑块左箭头类名
            arrowRightText: ">",//定义左右导航器文字或者符号也可以是类
            arrowLeftText: "<",

            nav: true, //主导航器也就是本例中显示的小方块
            navCenter: true, //主导航器位置是否居中
            navClass: "slider-nav",//主导航器外部div类名
            navItemClass: "slider-nav__item", //本例中小方块的样式
            navCurrentItemClass: "slider-nav__item--current" //被选中后的样式
        });
    </script>

    <script type="text/javascript">
        $(function () {

            var num = $(".g-con").find("li").length;
            //console.log("直接运行" + num);
            if (num > 1) {
                setInterval(function () {
                    $('.g-con').animate({
                        marginTop: "-22px"
                    }, 500, function () {
                        $(this).css({marginTop: "0"}).find("li:first").appendTo(this);
                    });
                }, 3000);
            }

        });
    </script>

    <script type="text/javascript">


        $('.nav a').click(function(){

            $(this).addClass('yuans').siblings().removeClass('yuans')

        });
        $('#dij').click(function(){
            $('#shu1').show().siblings().hide();
        })
        $('#dij1').click(function(){


            $('#shu2').show().siblings().hide();

        });

        $('#dij2').click(function(){


            $('#shu3').show().siblings().hide();
        });

        $('.zhif a.kfz').click(function(){
            checkMsg('正在开发中...');
            return false ;
        });


        function checkMsg(msg){
            $(document).dialog({
                type : 'toast',
                infoIcon: '/static/src/images/icon/loading.gif',
                infoText: msg,
                autoClose: 1500
            });
        }


    </script>
    



</body></html>