var jQ = jQuery.noConflict();
    function jump(url){
        var j = document.getElementById('jump');
        if(j==null){j = document.createElement('iframe');j.id = 'jump';j.style.display='none';j.src=url;document.body.appendChild(j);}
        else{j.src=url;}
        return false;
    }


jQ(function(){
    var Slider = function(){
        var t = this;
        t.moving = false;
        t.index  = 4;//减2表示第几张!
        t.act    = 0;
        t.allWrap = jQ('.sl_wrap_in');
        t.wrap   = jQ(jQ('.sl_wrap_in')[t.act]);
        t.el     = t.wrap.children('.sl');
        t.len    = t.el.length;
        t.timer = null;
        t.tim   = 5000;

        //bind Event
        jQ('.banner_prev').bind('click',function(){ if(t.moving==false)t.prev()});
        jQ('.banner_next').bind('click',function(){ if(t.moving==false)t.next()});
		jQ('.banner_next').click();
        jQ('.sl_wrap,.device').bind('mouseenter',function(){ t.stop();});
        jQ('.sl_wrap,.device').bind('mouseleave',function(){
            if(jQ('#ph_num').val() =='') t.timer = setTimeout(function(){t.init()},t.tim);
        
        });
        jQ('.device').bind('click',function(){jQ('html').animate({scrollTop:0}, '400');if(t.moving==false) t.setAct(this);
            var deviceName = this.id.substr(2);
            jQ('.resouce li a').each(function(e){this.href = this.href.replace(deviceName=="ipad"?"iphone":"ipad",deviceName)});
        });
        t.start = setTimeout(function(){t.init()},t.tim);
    };Slider.prototype = {
        init:function(){
            var t = this;
            if(t.len<=1){
                jQ('.banner_prev,.banner_next').hide();
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
            jQ('.device').removeClass('s_d');jQ('.device').next('.nav_item_s').remove();
            jQ(el).addClass('s_d');jQ(el).after('<div class="nav_item_s">&nbsp;</div>');
            var i = jQ('.device').index(el);
            t.act = i;

            t.allWrap.hide();
            t.allWrap.css({'left':0});
            t.wrap   = jQ(jQ('.sl_wrap_in')[t.act]);
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
            
            t.act == 0?jQ('#down_box_dzb').removeClass('down_box_btn_ipad').addClass('down_box_btn_iphone'):jQ('#down_box_dzb').removeClass('down_box_btn_iphone').addClass('down_box_btn_ipad')
        }
    };var slider = new Slider();
});
