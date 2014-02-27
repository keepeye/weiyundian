/*!
 * index
 * Author: zhugao
 * Date: 2013-5-11
 */

//banner
function jump(url){
    var j = document.getElementById('jump');
    if(j==null){j = document.createElement('iframe');j.id = 'jump';j.style.display='none';j.src=url;document.body.appendChild(j);}
    else{j.src=url;}
    return false;
}


$(function(){
    var Slider = function(){
        var t = this;
        t.moving = false;
        t.index  = 4;//减2表示第几张!
        t.act    = 0;
        t.allWrap = $('.sl_wrap_in');
        t.wrap   = $($('.sl_wrap_in')[t.act]);
        t.el     = t.wrap.children('.sl');
        t.len    = t.el.length;
        t.timer = null;
        t.tim   = 5000;

        //bind Event
        $('.banner_prev').bind('click',function(){ if(t.moving==false)t.prev()});
        $('.banner_next').bind('click',function(){ if(t.moving==false)t.next()});
		$('.banner_next').click();
        $('.sl_wrap,.device').bind('mouseenter',function(){ t.stop();});
        $('.sl_wrap,.device').bind('mouseleave',function(){
            if($('#ph_num').val() =='') t.timer = setTimeout(function(){t.init()},t.tim);
        
        });
        $('.device').bind('click',function(){$('html').animate({scrollTop:0}, '400');if(t.moving==false) t.setAct(this);
            var deviceName = this.id.substr(2);
            $('.resouce li a').each(function(e){this.href = this.href.replace(deviceName=="ipad"?"iphone":"ipad",deviceName)});
        });
        t.start = setTimeout(function(){t.init()},t.tim);
    };Slider.prototype = {
        init:function(){
            var t = this;
            if(t.len<=1){
                $('.banner_prev,.banner_next').hide();
            }else{
                t.next();
                t.timer = null?false:clearTimeout(t.timer);
                t.timer = setTimeout(function(){t.init();},t.tim);
            }
        },
        next:function(){
            var t = this;
            var i = (t.index+1) >= t.len?0:(t.index+1);
            t.go(i,'left');
        },
        prev:function(){
            var t = this;
            var i = t.index==0 ? (t.len-1):(t.index-1);
            t.go(i,'right');
        },
        go:function(i,direction){
            var t = this;
            var li = t.el.eq(i);
            t.moving = true;
            li.css({'left':direction=='left'?'100%':'-100%','display':'block'});
            t.wrap.animate({left:direction=='left'?'-100%':'100%'},400,function(){t.reset(i);t.index=i;t.moving = false;})
        },
        reset:function(i){
            var t = this;
            t.el.eq(i).css({'left':0});
            t.el.eq(t.index).hide();
            t.wrap.css({'left':0});
        },
        stop:function(){
            this.timer==null?false:clearTimeout(this.timer);
            this.timer = null;
            this.start==null?false:clearTimeout(this.start);
            this.start=null;
        },
        setAct:function(el){
            var t = this;
            t.stop();
            $('.device').removeClass('s_d');$('.device').next('.nav_item_s').remove();
            $(el).addClass('s_d');$(el).after('<div class="nav_item_s">&nbsp;</div>');
            var i = $('.device').index(el);
            t.act = i;

            t.allWrap.hide();
            t.allWrap.css({'left':0});
            t.wrap   = $($('.sl_wrap_in')[t.act]);
            t.el     = t.wrap.children('.sl');
            t.el.hide();t.el.eq(0).show();

            t.wrap.show();
            t.len    = t.el.length;
            t.moving = false;
            t.index  = 0;
            t.timer==null?false:clearTimeout(t.timer);
            t.timer = null;
            t.start==null?false:clearTimeout(t.start);
            t.start = setTimeout(function(){t.init()},t.tim);
            
            t.act == 0?$('#down_box_dzb').removeClass('down_box_btn_ipad').addClass('down_box_btn_iphone'):$('#down_box_dzb').removeClass('down_box_btn_iphone').addClass('down_box_btn_ipad')
        }
    };var slider = new Slider();
});

 
// 客户案例
function showCode(elem, show) {
	var $elem = $(elem),
		$code = $elem.prev('.code');
	if (show) {
		$code.show().css({
			'opacity': 0
		}).stop().animate({
			'margin-top': 78,
			'opacity': 1
		}, function () {
			$code.show();
		});
	} else {
		$code.css({
			'opacity': $code.css('opacity')
		}).stop().animate({
			'margin-top': 98,
			'opacity': 0
		}, function () {
			$code.hide();
		});
	}
}

// 滑动到锚点
function slideTarget(elem) {
	var id = $(elem).attr('href'),
		$target = $(id);
	$('html, body').stop().animate({
		scrollTop: $target.offset().top - 100 // 排除顶部悬浮导航条的高度
	}, 1000);
}

// 标记导航active
function activeNav(num) {
	var num = num + 100 + 80, // 顶部悬浮导航条的高度 + 距离目标块的边距
		$nav = $('#nav');
	$('div[data-connect=nav]').each(function () {
		var $this = $(this),
			id = $this.attr('id');

		/* if (id === 'footer') {
			// 关于我
			num = num + 200;
		} */
		if (num >= $this.data('num')) {
			$('a.active', $nav).removeClass('active');
			$('a[href=#'+id+']', $nav).addClass('active');
		} else {
			$('a[href=#'+id+']', $nav).removeClass('active');
		}
	});
}

 

// $(function () {
// 	var is_click = false,
// 		timeout = false;

	 

// 	$('.block_case .circle').hover(
// 		function () {
// 			showCode(this, true);
// 		},
// 		function () {
// 			showCode(this, false);
// 		}
// 	);

// 	// 点击导航，移到锚点
// 	$('#nav a:not(.login)').click(function (event) {
// 		slideTarget(this);
// 		$('a.active', $('#nav')).removeClass('active');
// 		$(this).addClass('active');
// 		is_click = true;
// 		return false;
// 	});

// 	// 在每个块上添加 data-num 标记该块的 offset().top， activeNav() 中会用到
// 	$('div[data-connect=nav]').each(function () {
// 		$(this).data('num', $(this).offset().top);
// 	});

// 	// 监听滚动事件
// 	$(window).on('scroll', function () {
// 		var $this = $(this);
// 		if (timeout) {
// 			clearTimeout(timeout);
// 		}
// 		timeout = setTimeout(function () {
// 			var num = $this.scrollTop();
// 			if (!is_click) {
// 				activeNav(num);
// 			}
// 			is_click = false;
// 		}, 100);
// 	});

	 
// 	var arVersion = navigator.appVersion.split("MSIE");
// 	var version = parseFloat(arVersion[1]);

// 	$(window).scroll(function () {
// 	    var bw = $(document.body).width();
// 	    var yw = $(".head").width();
// 	    var bh = $(document.body).height();
// 	    var yh = $(".head").height();
// 	    var yscroll = $(document).scrollTop();
// 	    if ($(document).scrollTop() > 0) {
// 	        if (version < 7.0) {
// 	            $(".head").css('top', yscroll).css('left', (bw - yw) / 2).css('position', 'absolute');
// 	        } else {
// 	            $(".head").css('top', 0).css('left', (bw - yw) / 2).css('position', 'fixed');
// 	        }

// 	    } else {
// 	        $(".head").css('position', 'static'); /*恢复到初始地方*/
// 	    }
// 	    /*窗口改变之后*/
// 	    $(window).resize(function () {
// 	        bw = $(document.body).width()
// 	        $(".head").css('left', (bw - yw) / 2)
// 	    });
// 	});

// });


//tab
$(function() {
    var $div_li = $("div.tab-menu ul li");
    $div_li.click(function(){
        $(this).addClass("selected")
            .siblings().removeClass("selected");
        var index = $div_li.index(this);

        $("div.tab-box div.tab-box > div")
        .eq(index).removeClass('hide')
        .siblings().addClass('hide');
    }).hover(function(){
        $(this).addClass("hover");
    },function(){
        $(this).removeClass("hover");
    });
})