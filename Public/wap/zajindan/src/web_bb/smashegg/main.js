//dialog
var iDialog=(function(){var c='<header>						<dl>							<dd><label>{title}</label></dd>							<dd><span onclick="this.parentNode.parentNode.parentNode.parentNode.classList.remove(\'on\');">{close}</span></dd>						</dl>					</header>					<article class="dialogContent">{content}</article>					<footer></footer>';var b={wrapper:null,cover:null,lastIndex:1000,list:null};var a=function(){this.options={id:"dialogWindow_",classList:"",type:"",wrapper:"",title:"",close:"",content:"",cover:true,btns:[]};};a.prototype={init:function(){if(b.list){return this;}else{b.list={};}var e=document.createElement("section");e.setAttribute("id",id="dialoger");/*e.setAttribute("ontouchmove","event.preventDefault();");*/var d=document.createElement("div");d.setAttribute("class","dialogCover");e.appendChild(d);b.container=e;b.cover=d;document.body.insertBefore(b.container,document.body.childNodes[0]);return this;},open:function(f){window.scrollTo(0, 1); this.init();this.options=a.merge(this.options,f||{});this.options.zIndex=b.lastIndex+=100;this.options.id="dialogWindow_"+this.options.zIndex;b.list[this.options.id]=this;this.options.wrapper=document.createElement("div");this.options.wrapper.setAttribute("data-type",this.options.type);this.options.wrapper.setAttribute("id",this.options.id);this.options.wrapper.setAttribute("class","dialogWindow on "+this.options.classList);this.options.wrapper.setAttribute("style","z-index:"+this.options.zIndex);this.options.wrapper.innerHTML=iTemplate.makeList(c,[this.options],function(j,i){});b.container.insertBefore(this.options.wrapper,this.options.cover?b.cover:null);if(this.options.btns.length){var g=this;var h=document.createElement("div");h.setAttribute("class","box");for(var e=0,d;d=this.options.btns[e];e++){(function(i){var j=document.createElement("a");j.setAttribute("href","javascript:;");j.setAttribute("class","dialogBtn");j.innerHTML=i.name;if(i.fn){j.onclick=function(){i.fn.call(this,g);};}var k=document.createElement("div");k.appendChild(j);h.appendChild(k);})(d);}this.options.wrapper.querySelectorAll("footer")[0].appendChild(h);}return this;},show:function(){var d=this.options.wrapper.classList;d.add("on");return this;},hide:function(){var d=this.options.wrapper.classList;d.remove("on");return this;},die:function(){var d=this;this.hide();setTimeout(function(){delete b.list[d.options.id];b.container.removeChild(d.options.wrapper);},300);return this;}};a.merge=function(f,e,g){for(var d in e){f[d]=e[d];}return f;};return a;})();var iTemplate=(function(){var a=function(){};a.prototype={makeList:function(e,j,i){var g=[],h=[],c=/{(.+?)}/g,d={},f=0;for(var b in j){if(typeof i==="function"){d=i.call(this,b,j[b],f++)||{};}g.push(e.replace(c,function(k,l){return(l in d)?d[l]:(undefined===j[b][l]?j[b]:j[b][l]);}));}return g.join("");}};return new a();})();
//player_min
var playbox = (function() {
	var _playbox = function() {
		var that = this;
		that.box = null;
		that.player = null;
		that.src = null;
		that.on = false;
		that.autoPlayFix = {
			on: true,
			evtName: ("ontouchstart" in window) ? "touchend": "click"
		}
	};
	_playbox.prototype = {
		init: function(box_ele) {
			this.box = "string" === typeof(box_ele) ? document.getElementById(box_ele) : box_ele;
			this.player = this.box.nextSibling;
			this.src = this.player.src;
			this.init = function() {
				return this
			};
			this.autoPlayEvt(true);
			return this
		},
		play: function() {
			if (this.autoPlayFix.on) {
				this.autoPlayFix.on = false;
				this.autoPlayEvt(false)
			};
			this.on = !this.on;
			if (true == this.on) {
				this.player.src = this.src;
				this.player.play()
			} else {
				this.player.pause();
				this.player.src = null
			};
			if ("function" == typeof(this.play_fn)) {
				this.play_fn.call(this)
			}
		},
		handleEvent: function(evt) {
			if (evt.target == this.box) {
				return
			};
			this.play()
		},
		autoPlayEvt: function(important) {
			if (important || this.autoPlayFix.on) {
				document.body.addEventListener(this.autoPlayFix.evtName, this, false)
			} else {
				document.body.removeEventListener(this.autoPlayFix.evtName, this, false)
			}
		}
	};
	return new _playbox()
})();
playbox.play_fn = function() {
	this.box.className = this.on ? "btn_music on": "btn_music"
}
//main.js
function getCoin(urls){
	var urls = urls;
	var num = Math.round((Math.random(3)+ 1)*7);
	var snows = new Array(num);
	snows = snows.join(",").split(",");
	var Tpl = '<div style="top: {top}; left: {left}; -webkit-animation: fade {t1} {t2}, drop {t1} {t2};">\
				<img src="{url}" style="-webkit-animation: counterclockwiseSpinAndFlip {t5};width:{width}; max-width:{maxHeight}">\
				</div>';
	var snowsHTML = iTemplate.makeList(Tpl, snows, function(k,v){
		var obj = {
			top: "-30px",
			left: Math.random()*100 +"%",
			t1:Math.random()*(8-3)+2 +"s",
			t2:Math.random()*2 +"s",
			//t3:Math.random()*(11-5)+5 +"s",
			//t4:Math.random()*4 +"s",
			t5:Math.random()*(8-3)+2 +"s",
			url: urls[0],
			width: Math.round(Math.random()*(38-10)+10) + "px",
			maxHeight:"43px"
		}
		return obj;
	});
	var div = document.createElement("div");
	div.setAttribute("class", "snower");
	div.innerHTML = snowsHTML;
	document.body.appendChild(div);
}