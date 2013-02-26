!function(){function log(args){console.log("$f.fireEvent",[].slice.call(args));}
function clone(obj){if(!obj||typeof obj!='object'){return obj;}
var temp=new obj.constructor();for(var key in obj){if(obj.hasOwnProperty(key)){temp[key]=clone(obj[key]);}}
return temp;}
function each(obj,fn){if(!obj){return;}
var name,i=0,length=obj.length;if(length===undefined){for(name in obj){if(fn.call(obj[name],name,obj[name])===false){break;}}}else{for(var value=obj[0];i<length&&fn.call(value,i,value)!==false;value=obj[++i]){}}
return obj;}
function el(id){return document.getElementById(id);}
function extend(to,from,skipFuncs){if(typeof from!='object'){return to;}
if(to&&from){each(from,function(name,value){if(!skipFuncs||typeof value!='function'){to[name]=value;}});}
return to;}
function select(query){var index=query.indexOf(".");if(index!=-1){var tag=query.slice(0,index)||"*";var klass=query.slice(index+1,query.length);var els=[];each(document.getElementsByTagName(tag),function(){if(this.className&&this.className.indexOf(klass)!=-1){els.push(this);}});return els;}}
function stopEvent(e){e=e||window.event;if(e.preventDefault){e.stopPropagation();e.preventDefault();}else{e.returnValue=false;e.cancelBubble=true;}
return false;}
function bind(to,evt,fn){to[evt]=to[evt]||[];to[evt].push(fn);}
function queryescape(url){return url.replace(/&amp;/g,'%26').replace(/&/g,'%26').replace(/=/g,'%3D');}
function makeId(){return"_"+(""+Math.random()).slice(2,10);}
var Clip=function(json,index,player){var self=this,cuepoints={},listeners={};self.index=index;if(typeof json=='string'){json={url:json};}
extend(this,json,true);each(("Begin*,Start,Pause*,Resume*,Seek*,Stop*,Finish*,LastSecond,Update,BufferFull,BufferEmpty,BufferStop").split(","),function(){var evt="on"+this;if(evt.indexOf("*")!=-1){evt=evt.slice(0,evt.length-1);var before="onBefore"+evt.slice(2);self[before]=function(fn){bind(listeners,before,fn);return self;};}
self[evt]=function(fn){bind(listeners,evt,fn);return self;};if(index==-1){if(self[before]){player[before]=self[before];}
if(self[evt]){player[evt]=self[evt];}}});extend(this,{onCuepoint:function(points,fn){if(arguments.length==1){cuepoints.embedded=[null,points];return self;}
if(typeof points=='number'){points=[points];}
var fnId=makeId();cuepoints[fnId]=[points,fn];if(player.isLoaded()){player._api().fp_addCuepoints(points,index,fnId);}
return self;},update:function(json){extend(self,json);if(player.isLoaded()){player._api().fp_updateClip(json,index);}
var conf=player.getConfig();var clip=(index==-1)?conf.clip:conf.playlist[index];extend(clip,json,true);},_fireEvent:function(evt,arg1,arg2,target){if(evt=='onLoad'){each(cuepoints,function(key,val){if(val[0]){player._api().fp_addCuepoints(val[0],index,key);}});return false;}
target=target||self;if(evt=='onCuepoint'){var fn=cuepoints[arg1];if(fn){return fn[1].call(player,target,arg2);}}
if(arg1&&"onBeforeBegin,onMetaData,onStart,onUpdate,onResume".indexOf(evt)!=-1){extend(target,arg1);if(arg1.metaData){if(!target.duration){target.duration=arg1.metaData.duration;}else{target.fullDuration=arg1.metaData.duration;}}}
var ret=true;each(listeners[evt],function(){ret=this.call(player,target,arg1,arg2);});return ret;}});if(json.onCuepoint){var arg=json.onCuepoint;self.onCuepoint.apply(self,typeof arg=='function'?[arg]:arg);delete json.onCuepoint;}
each(json,function(key,val){if(typeof val=='function'){bind(listeners,key,val);delete json[key];}});if(index==-1){player.onCuepoint=this.onCuepoint;}};var Plugin=function(name,json,player,fn){var self=this,listeners={},hasMethods=false;if(fn){extend(listeners,fn);}
each(json,function(key,val){if(typeof val=='function'){listeners[key]=val;delete json[key];}});extend(this,{animate:function(props,speed,fn){if(!props){return self;}
if(typeof speed=='function'){fn=speed;speed=500;}
if(typeof props=='string'){var key=props;props={};props[key]=speed;speed=500;}
if(fn){var fnId=makeId();listeners[fnId]=fn;}
if(speed===undefined){speed=500;}
json=player._api().fp_animate(name,props,speed,fnId);return self;},css:function(props,val){if(val!==undefined){var css={};css[props]=val;props=css;}
json=player._api().fp_css(name,props);extend(self,json);return self;},show:function(){this.display='block';player._api().fp_showPlugin(name);return self;},hide:function(){this.display='none';player._api().fp_hidePlugin(name);return self;},toggle:function(){this.display=player._api().fp_togglePlugin(name);return self;},fadeTo:function(o,speed,fn){if(typeof speed=='function'){fn=speed;speed=500;}
if(fn){var fnId=makeId();listeners[fnId]=fn;}
this.display=player._api().fp_fadeTo(name,o,speed,fnId);this.opacity=o;return self;},fadeIn:function(speed,fn){return self.fadeTo(1,speed,fn);},fadeOut:function(speed,fn){return self.fadeTo(0,speed,fn);},getName:function(){return name;},getPlayer:function(){return player;},_fireEvent:function(evt,arg,arg2){if(evt=='onUpdate'){var json=player._api().fp_getPlugin(name);if(!json){return;}
extend(self,json);delete self.methods;if(!hasMethods){each(json.methods,function(){var method=""+this;self[method]=function(){var a=[].slice.call(arguments);var ret=player._api().fp_invoke(name,method,a);return ret==='undefined'||ret===undefined?self:ret;};});hasMethods=true;}}
var fn=listeners[evt];if(fn){var ret=fn.apply(self,arg);if(evt.slice(0,1)=="_"){delete listeners[evt];}
return ret;}
return self;}});};function Player(wrapper,params,conf){var self=this,api=null,isUnloading=false,html,commonClip,playlist=[],plugins={},listeners={},playerId,apiId,playerIndex,activeIndex,swfHeight,wrapperHeight;extend(self,{id:function(){return playerId;},isLoaded:function(){return(api!==null&&api.fp_play!==undefined&&!isUnloading);},getParent:function(){return wrapper;},hide:function(all){if(all){wrapper.style.height="0px";}
if(self.isLoaded()){api.style.height="0px";}
return self;},show:function(){wrapper.style.height=wrapperHeight+"px";if(self.isLoaded()){api.style.height=swfHeight+"px";}
return self;},isHidden:function(){return self.isLoaded()&&parseInt(api.style.height,10)===0;},load:function(fn){if(!self.isLoaded()&&self._fireEvent("onBeforeLoad")!==false){var onPlayersUnloaded=function(){if(html&&!flashembed.isSupported(params.version)){wrapper.innerHTML="";}
if(fn){fn.cached=true;bind(listeners,"onLoad",fn);}
flashembed(wrapper,params,{config:conf});};var unloadedPlayersNb=0;each(players,function(){this.unload(function(wasUnloaded){if(++unloadedPlayersNb==players.length){onPlayersUnloaded();}});});}
return self;},unload:function(fn){if(html.replace(/\s/g,'')!==''){if(self._fireEvent("onBeforeUnload")===false){if(fn){fn(false);}
return self;}
isUnloading=true;try{if(api){if(api.fp_isFullscreen()){api.fp_toggleFullscreen();}
api.fp_close();self._fireEvent("onUnload");}}catch(error){}
var clean=function(){api=null;wrapper.innerHTML=html;isUnloading=false;if(fn){fn(true);}};if(/WebKit/i.test(navigator.userAgent)&&!/Chrome/i.test(navigator.userAgent)){setTimeout(clean,0);}else{clean();}}
else if(fn){fn(false);}
return self;},getClip:function(index){if(index===undefined){index=activeIndex;}
return playlist[index];},getCommonClip:function(){return commonClip;},getPlaylist:function(){return playlist;},getPlugin:function(name){var plugin=plugins[name];if(!plugin&&self.isLoaded()){var json=self._api().fp_getPlugin(name);if(json){plugin=new Plugin(name,json,self);plugins[name]=plugin;}}
return plugin;},getScreen:function(){return self.getPlugin("screen");},getControls:function(){return self.getPlugin("controls")._fireEvent("onUpdate");},getLogo:function(){try{return self.getPlugin("logo")._fireEvent("onUpdate");}catch(ignored){}},getPlay:function(){return self.getPlugin("play")._fireEvent("onUpdate");},getConfig:function(copy){return copy?clone(conf):conf;},getFlashParams:function(){return params;},loadPlugin:function(name,url,props,fn){if(typeof props=='function'){fn=props;props={};}
var fnId=fn?makeId():"_";self._api().fp_loadPlugin(name,url,props,fnId);var arg={};arg[fnId]=fn;var p=new Plugin(name,null,self,arg);plugins[name]=p;return p;},getState:function(){return self.isLoaded()?api.fp_getState():-1;},play:function(clip,instream){var p=function(){if(clip!==undefined){self._api().fp_play(clip,instream);}else{self._api().fp_play();}};if(self.isLoaded()){p();}else if(isUnloading){setTimeout(function(){self.play(clip,instream);},50);}else{self.load(function(){p();});}
return self;},getVersion:function(){var js="flowplayer.js 3.2.12";if(self.isLoaded()){var ver=api.fp_getVersion();ver.push(js);return ver;}
return js;},_api:function(){if(!self.isLoaded()){throw"Flowplayer "+self.id()+" not loaded when calling an API method";}
return api;},setClip:function(clip){each(clip,function(key,val){if(typeof val=='function'){bind(listeners,key,val);delete clip[key];}else if(key=='onCuepoint'){$f(wrapper).getCommonClip().onCuepoint(clip[key][0],clip[key][1]);}});self.setPlaylist([clip]);return self;},getIndex:function(){return playerIndex;},bufferAnimate:function(enable){api.fp_bufferAnimate(enable===undefined||enable);return self;},_swfHeight:function(){return api.clientHeight;}});each(("Click*,Load*,Unload*,Keypress*,Volume*,Mute*,Unmute*,PlaylistReplace,ClipAdd,Fullscreen*,FullscreenExit,Error,MouseOver,MouseOut").split(","),function(){var name="on"+this;if(name.indexOf("*")!=-1){name=name.slice(0,name.length-1);var name2="onBefore"+name.slice(2);self[name2]=function(fn){bind(listeners,name2,fn);return self;};}
self[name]=function(fn){bind(listeners,name,fn);return self;};});each(("pause,resume,mute,unmute,stop,toggle,seek,getStatus,getVolume,setVolume,getTime,isPaused,isPlaying,startBuffering,stopBuffering,isFullscreen,toggleFullscreen,reset,close,setPlaylist,addClip,playFeed,setKeyboardShortcutsEnabled,isKeyboardShortcutsEnabled").split(","),function(){var name=this;self[name]=function(a1,a2){if(!self.isLoaded()){return self;}
var ret=null;if(a1!==undefined&&a2!==undefined){ret=api["fp_"+name](a1,a2);}else{ret=(a1===undefined)?api["fp_"+name]():api["fp_"+name](a1);}
return ret==='undefined'||ret===undefined?self:ret;};});self._fireEvent=function(a){if(typeof a=='string'){a=[a];}
var evt=a[0],arg0=a[1],arg1=a[2],arg2=a[3],i=0;if(conf.debug){log(a);}
if(!self.isLoaded()&&evt=='onLoad'&&arg0=='player'){api=api||el(apiId);swfHeight=self._swfHeight();each(playlist,function(){this._fireEvent("onLoad");});each(plugins,function(name,p){p._fireEvent("onUpdate");});commonClip._fireEvent("onLoad");}
if(evt=='onLoad'&&arg0!='player'){return;}
if(evt=='onError'){if(typeof arg0=='string'||(typeof arg0=='number'&&typeof arg1=='number')){arg0=arg1;arg1=arg2;}}
if(evt=='onContextMenu'){each(conf.contextMenu[arg0],function(key,fn){fn.call(self);});return;}
if(evt=='onPluginEvent'||evt=='onBeforePluginEvent'){var name=arg0.name||arg0;var p=plugins[name];if(p){p._fireEvent("onUpdate",arg0);return p._fireEvent(arg1,a.slice(3));}
return;}
if(evt=='onPlaylistReplace'){playlist=[];var index=0;each(arg0,function(){playlist.push(new Clip(this,index++,self));});}
if(evt=='onClipAdd'){if(arg0.isInStream){return;}
arg0=new Clip(arg0,arg1,self);playlist.splice(arg1,0,arg0);for(i=arg1+1;i<playlist.length;i++){playlist[i].index++;}}
var ret=true;if(typeof arg0=='number'&&arg0<playlist.length){activeIndex=arg0;var clip=playlist[arg0];if(clip){ret=clip._fireEvent(evt,arg1,arg2);}
if(!clip||ret!==false){ret=commonClip._fireEvent(evt,arg1,arg2,clip);}}
each(listeners[evt],function(){ret=this.call(self,arg0,arg1);if(this.cached){listeners[evt].splice(i,1);}
if(ret===false){return false;}
i++;});return ret;};function init(){if($f(wrapper)){$f(wrapper).getParent().innerHTML="";playerIndex=$f(wrapper).getIndex();players[playerIndex]=self;}else{players.push(self);playerIndex=players.length-1;}
wrapperHeight=parseInt(wrapper.style.height,10)||wrapper.clientHeight;playerId=wrapper.id||"fp"+makeId();apiId=params.id||playerId+"_api";params.id=apiId;html=wrapper.innerHTML;if(typeof conf=='string'){conf={clip:{url:conf}};}
conf.playerId=playerId;conf.clip=conf.clip||{};if(wrapper.getAttribute("href",2)&&!conf.clip.url){conf.clip.url=wrapper.getAttribute("href",2);}
if(conf.clip.url){conf.clip.url=queryescape(conf.clip.url);}
commonClip=new Clip(conf.clip,-1,self);conf.playlist=conf.playlist||[conf.clip];var index=0;each(conf.playlist,function(){var clip=this;if(typeof clip=='object'&&clip.length){clip={url:""+clip};}
if(clip.url){clip.url=queryescape(clip.url);}
each(conf.clip,function(key,val){if(val!==undefined&&clip[key]===undefined&&typeof val!='function'){clip[key]=val;}});conf.playlist[index]=clip;clip=new Clip(clip,index,self);playlist.push(clip);index++;});each(conf,function(key,val){if(typeof val=='function'){if(commonClip[key]){commonClip[key](val);}else{bind(listeners,key,val);}
delete conf[key];}});each(conf.plugins,function(name,val){if(val){plugins[name]=new Plugin(name,val,self);}});if(!conf.plugins||conf.plugins.controls===undefined){plugins.controls=new Plugin("controls",null,self);}
plugins.canvas=new Plugin("canvas",null,self);html=wrapper.innerHTML;function doClick(e){if(/iPad|iPhone|iPod/i.test(navigator.userAgent)&&!/.flv$/i.test(playlist[0].url)&&!checkForIpadSupport()){return true;}
if(!self.isLoaded()&&self._fireEvent("onBeforeClick")!==false){self.load();}
return stopEvent(e);}
function checkForIpadSupport(){return self.hasiPadSupport&&self.hasiPadSupport();}
function installPlayer(){if(html.replace(/\s/g,'')!==''){if(wrapper.addEventListener){wrapper.addEventListener("click",doClick,false);}else if(wrapper.attachEvent){wrapper.attachEvent("onclick",doClick);}}else{if(wrapper.addEventListener&&!checkForIpadSupport()){wrapper.addEventListener("click",stopEvent,false);}
self.load();}}
setTimeout(installPlayer,0);}
if(typeof wrapper=='string'){var node=el(wrapper);if(!node){throw"Flowplayer cannot access element: "+wrapper;}
wrapper=node;init();}else{init();}}
var players=[];function Iterator(arr){this.length=arr.length;this.each=function(fn){each(arr,fn);};this.size=function(){return arr.length;};var self=this;for(name in Player.prototype){self[name]=function(){var args=arguments;self.each(function(){this[name].apply(this,args);});};}}
window.flowplayer=window.$f=function(){var instance=null;var arg=arguments[0];if(!arguments.length){each(players,function(){if(this.isLoaded()){instance=this;return false;}});return instance||players[0];}
if(arguments.length==1){if(typeof arg=='number'){return players[arg];}else{if(arg=='*'){return new Iterator(players);}
each(players,function(){if(this.id()==arg.id||this.id()==arg||this.getParent()==arg){instance=this;return false;}});return instance;}}
if(arguments.length>1){var params=arguments[1],conf=(arguments.length==3)?arguments[2]:{};if(typeof params=='string'){params={src:params};}
params=extend({bgcolor:"#000000",version:[10,1],expressInstall:"http://releases.flowplayer.org/swf/expressinstall.swf",cachebusting:false},params);if(typeof arg=='string'){if(arg.indexOf(".")!=-1){var instances=[];each(select(arg),function(){instances.push(new Player(this,clone(params),clone(conf)));});return new Iterator(instances);}else{var node=el(arg);return new Player(node!==null?node:clone(arg),clone(params),clone(conf));}}else if(arg){return new Player(arg,clone(params),clone(conf));}}
return null;};extend(window.$f,{fireEvent:function(){var a=[].slice.call(arguments);var p=$f(a[0]);return p?p._fireEvent(a.slice(1)):null;},addPlugin:function(name,fn){Player.prototype[name]=fn;return $f;},each:each,extend:extend});if(typeof jQuery=='function'){jQuery.fn.flowplayer=function(params,conf){if(!arguments.length||typeof arguments[0]=='number'){var arr=[];this.each(function(){var p=$f(this);if(p){arr.push(p);}});return arguments.length?arr[arguments[0]]:new Iterator(arr);}
return this.each(function(){$f(this,clone(params),conf?clone(conf):{});});};}}();!function(){var IE=document.all,URL='http://get.adobe.com/flashplayer',JQUERY=typeof jQuery=='function',RE=/(\d+)[^\d]+(\d+)[^\d]*(\d*)/,GLOBAL_OPTS={width:'100%',height:'100%',id:"_"+(""+Math.random()).slice(9),allowfullscreen:true,allowscriptaccess:'always',quality:'high',version:[3,0],onFail:null,expressInstall:null,w3c:false,cachebusting:false};if(window.attachEvent){window.attachEvent("onbeforeunload",function(){__flash_unloadHandler=function(){};__flash_savedUnloadHandler=function(){};});}
function extend(to,from){if(from){for(var key in from){if(from.hasOwnProperty(key)){to[key]=from[key];}}}
return to;}
function map(arr,func){var newArr=[];for(var i in arr){if(arr.hasOwnProperty(i)){newArr[i]=func(arr[i]);}}
return newArr;}
window.flashembed=function(root,opts,conf){if(typeof root=='string'){root=document.getElementById(root.replace("#",""));}
if(!root){return;}
if(typeof opts=='string'){opts={src:opts};}
return new Flash(root,extend(extend({},GLOBAL_OPTS),opts),conf);};var f=extend(window.flashembed,{conf:GLOBAL_OPTS,getVersion:function(){var fo,ver;try{ver=navigator.plugins["Shockwave Flash"].description.slice(16);}catch(e){try{fo=new ActiveXObject("ShockwaveFlash.ShockwaveFlash.7");ver=fo&&fo.GetVariable("$version");}catch(err){try{fo=new ActiveXObject("ShockwaveFlash.ShockwaveFlash.6");ver=fo&&fo.GetVariable("$version");}catch(err2){}}}
ver=RE.exec(ver);return ver?[1*ver[1],1*ver[(ver[1]*1>9?2:3)]*1]:[0,0];},asString:function(obj){if(obj===null||obj===undefined){return null;}
var type=typeof obj;if(type=='object'&&obj.push){type='array';}
switch(type){case'string':return string2JsonString(obj);case'array':return'['+map(obj,function(el){return f.asString(el);}).join(',')+']';case'function':return'"function()"';case'object':var str=[];for(var prop in obj){if(obj.hasOwnProperty(prop)){str.push('"'+prop+'":'+f.asString(obj[prop]));}}
return'{'+str.join(',')+'}';}
return String(obj).replace(/\s/g," ").replace(/\'/g,"\"");},getHTML:function(opts,conf){opts=extend({},opts);var html='<object width="'+opts.width+'" height="'+opts.height+'" id="'+opts.id+'" name="'+opts.id+'"';if(opts.cachebusting){opts.src+=((opts.src.indexOf("?")!=-1?"&":"?")+Math.random());}
if(opts.w3c||!IE){html+=' data="'+opts.src+'" type="application/x-shockwave-flash"';}else{html+=' classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"';}
html+='>';if(opts.w3c||IE){html+='<param name="movie" value="'+opts.src+'" />';}
opts.width=opts.height=opts.id=opts.w3c=opts.src=null;opts.onFail=opts.version=opts.expressInstall=null;for(var key in opts){if(opts[key]){html+='<param name="'+key+'" value="'+opts[key]+'" />';}}
var vars="";if(conf){for(var k in conf){if(conf[k]){var val=conf[k];vars+=encodeURIComponent(k)+'='
+encodeURIComponent(/function|object/.test(typeof val)?f.asString(val):val)
+'&';}}
vars=vars.slice(0,-1);html+='<param name="flashvars" value="'+vars+'" />';}
html+="</object>";return html;},isSupported:function(ver){return VERSION[0]>ver[0]||VERSION[0]==ver[0]&&VERSION[1]>=ver[1];}});var VERSION=f.getVersion();function Flash(root,opts,conf){if(f.isSupported(opts.version)){root.innerHTML=f.getHTML(opts,conf);}else if(opts.expressInstall&&f.isSupported([6,65])){root.innerHTML=f.getHTML(extend(opts,{src:opts.expressInstall}),{MMredirectURL:encodeURIComponent(location.href),MMplayerType:'PlugIn',MMdoctitle:document.title});}else{if(!root.innerHTML.replace(/\s/g,'')){root.innerHTML="<h2>Flash version "+opts.version+" or greater is required</h2>"+"<h3>"+
(VERSION[0]>0?"Your version is "+VERSION:"You have no flash plugin installed")+"</h3>"+
(root.tagName=='A'?"<p>Click here to download latest version</p>":"<p>Download latest version from <a href='"+URL+"'>here</a></p>");if(root.tagName=='A'||root.tagName=="DIV"){root.onclick=function(){location.href=URL;};}}
if(opts.onFail){var ret=opts.onFail.call(this);if(typeof ret=='string'){root.innerHTML=ret;}}}
if(IE){window[opts.id]=document.getElementById(opts.id);}
extend(this,{getRoot:function(){return root;},getOptions:function(){return opts;},getConf:function(){return conf;},getApi:function(){return root.firstChild;}});}
var cx=/[\u0000\u00ad\u0600-\u0604\u070f\u17b4\u17b5\u200c-\u200f\u2028-\u202f\u2060-\u206f\ufeff\ufff0-\uffff]/g,escapable=/[\\\"\x00-\x1f\x7f-\x9f\u00ad\u0600-\u0604\u070f\u17b4\u17b5\u200c-\u200f\u2028-\u202f\u2060-\u206f\ufeff\ufff0-\uffff]/g,gap,indent,meta={'\b':'\\b','\t':'\\t','\n':'\\n','\f':'\\f','\r':'\\r','"':'\\"','\\':'\\\\'},rep;function string2JsonString(string){escapable.lastIndex=0;return escapable.test(string)?'"'+string.replace(escapable,function(a){var c=meta[a];return typeof c==='string'?c:'\\u'+('0000'+a.charCodeAt(0).toString(16)).slice(-4);})+'"':'"'+string+'"';}
if(JQUERY){jQuery.tools=jQuery.tools||{version:'@VERSION'};jQuery.tools.flashembed={conf:GLOBAL_OPTS};jQuery.fn.flashembed=function(opts,conf){return this.each(function(){$(this).data("flashembed",flashembed(this,opts,conf));});};}}();$f.addPlugin("ipad",function(options){var STATE_UNLOADED=-1;var STATE_LOADED=0;var STATE_UNSTARTED=1;var STATE_BUFFERING=2;var STATE_PLAYING=3;var STATE_PAUSED=4;var STATE_ENDED=5;var self=this;var currentVolume=1;var onStartFired=false;var stopping=false;var playAfterSeek=false;var activeIndex=0;var activePlaylist=[];var lastSecondTimer;var endTime=null;var startTime=0;var clipDefaults={accelerated:false,autoBuffering:false,autoPlay:true,baseUrl:null,bufferLength:3,connectionProvider:null,cuepointMultiplier:1000,cuepoints:[],controls:{},duration:0,extension:'',fadeInSpeed:1000,fadeOutSpeed:1000,image:false,linkUrl:null,linkWindow:'_self',live:false,metaData:{},originalUrl:null,position:0,playlist:[],provider:'http',scaling:'scale',seekableOnBegin:false,start:0,url:null,urlResolvers:[]};var currentState=STATE_UNLOADED;var previousState=STATE_UNLOADED;var isiDevice=/iPad|iPhone|iPod/i.test(navigator.userAgent);var video=null;function extend(to,from,includeFuncs){if(from){for(key in from){if(key){if(from[key]&&typeof from[key]=="function"&&!includeFuncs)
continue;if(from[key]&&typeof from[key]=="object"&&from[key].length===undefined){var cp={};extend(cp,from[key]);to[key]=cp;}else{to[key]=from[key];}}}}
return to;}
var opts={simulateiDevice:false,controlsSizeRatio:1.5,controls:true,debug:false,validExtensions:'mov|m4v|mp4|avi|mp3|m4a|aac|m3u8|m3u|pls',posterExtensions:'png|jpg'};extend(opts,options);var validExtensions=opts.validExtensions?new RegExp('^\.('+opts.validExtensions+')$','i'):null;var posterExtensions=new RegExp('^\.('+opts.posterExtensions+')$','i');function log(){if(opts.debug){if(isiDevice){var str=[].splice.call(arguments,0).join(', ');console.log.apply(console,[str]);}else{console.log.apply(console,arguments);}}}
function stateDescription(state){switch(state){case-1:return"UNLOADED";case 0:return"LOADED";case 1:return"UNSTARTED";case 2:return"BUFFERING";case 3:return"PLAYING";case 4:return"PAUSED";case 5:return"ENDED";}
return"UNKOWN";}
function actionAllowed(eventName){var ret=$f.fireEvent(self.id(),"onBefore"+eventName,activeIndex);return ret!==false;}
function stopEvent(e){e.stopPropagation();e.preventDefault();return false;}
function setState(state,force){if(currentState==STATE_UNLOADED&&!force)
return;previousState=currentState;currentState=state;stopPlayTimeTracker();if(state==STATE_PLAYING)
startPlayTimeTracker();log(stateDescription(state));}
function resetState(){video.fp_stop();onStartFired=false;stopping=false;playAfterSeek=false;setState(STATE_UNSTARTED);setState(STATE_UNSTARTED);}
var _playTimeTracker=null;function startPlayTimeTracker(){if(_playTimeTracker)
return;console.log("starting tracker");_playTimeTracker=setInterval(onTimeTracked,100);onTimeTracked();}
function stopPlayTimeTracker(){clearInterval(_playTimeTracker);_playTimeTracker=null;}
function onTimeTracked(){var currentTime=Math.floor(video.fp_getTime()*10)*100;var duration=Math.floor(video.duration*10)*100;var fireTime=(new Date()).time;function fireCuePointsIfNeeded(time,cues){time=time>=0?time:duration-Math.abs(time);for(var i=0;i<cues.length;i++){if(cues[i].lastTimeFired>fireTime){cues[i].lastTimeFired=-1;}else if(cues[i].lastTimeFired+500>fireTime){continue;}else{if(time==currentTime||(currentTime-500<time&&currentTime>time)){cues[i].lastTimeFired=fireTime;$f.fireEvent(self.id(),'onCuepoint',activeIndex,cues[i].fnId,cues[i].parameters);}}}}
$f.each(self.getCommonClip().cuepoints,fireCuePointsIfNeeded);$f.each(activePlaylist[activeIndex].cuepoints,fireCuePointsIfNeeded);}
function replay(){resetState();playAfterSeek=true;video.fp_seek(0);}
function scaleVideo(clip){}
function addAPI(){console.log(video);function fixClip(clip){var extendedClip={};extend(extendedClip,clipDefaults);extend(extendedClip,self.getCommonClip());extend(extendedClip,clip);if(extendedClip.ipadUrl)
url=decodeURIComponent(extendedClip.ipadUrl);else if(extendedClip.url)
url=extendedClip.url;if(url&&url.indexOf('://')==-1&&extendedClip.ipadBaseUrl)
url=extendedClip.ipadBaseUrl+'/'+url;else if(url&&url.indexOf('://')==-1&&extendedClip.baseUrl)
url=extendedClip.baseUrl+'/'+url;extendedClip.originalUrl=extendedClip.url;extendedClip.completeUrl=url;extendedClip.extension=extendedClip.completeUrl.substr(extendedClip.completeUrl.lastIndexOf('.'));var queryIndex=extendedClip.extension.indexOf('?');if(queryIndex>-1)
extendedClip.extension=extendedClip.extension.substr(0,queryIndex);extendedClip.type='video';delete extendedClip.index;log("fixed clip",extendedClip);return extendedClip;}
video.fp_play=function(clip,inStream,forcePlay,poster){var url=null;var autoBuffering=true;var autoPlay=true;log("Calling play() "+clip,clip);if(inStream){log("ERROR: inStream clips not yet supported");return;}
if(clip!==undefined){if(typeof clip=="number"){if(activeIndex>=activePlaylist.length)
return;activeIndex=clip;clip=activePlaylist[activeIndex];}else{if(typeof clip=="string"){clip={url:clip};}
video.fp_setPlaylist(clip.length!==undefined?clip:[clip]);}
if(activeIndex==0&&activePlaylist.length>1&&posterExtensions.test(activePlaylist[activeIndex].extension)){var poster=activePlaylist[activeIndex].url;console.log("Poster image available with url "+poster);++activeIndex;console.log("Not last clip in the playlist, moving to next one");video.fp_play(activeIndex,false,true,poster);return;}
if(validExtensions&&!validExtensions.test(activePlaylist[activeIndex].extension)){return;}
clip=activePlaylist[activeIndex];url=clip.completeUrl;if(clip.autoBuffering!==undefined&&clip.autoBuffering===false)
autoBuffering=false;if(clip.autoPlay===undefined||clip.autoPlay===true||forcePlay===true){autoBuffering=true;autoPlay=true;}else{autoPlay=false;}}else{log("clip was not given, simply calling video.play, if not already buffering");if(currentState!=STATE_BUFFERING){video.play();}
return;}
log("about to play "+url,autoBuffering,autoPlay);resetState();if(url){log("Changing SRC attribute"+url);video.setAttribute('src',url);}
if(autoBuffering){if(!actionAllowed('Begin'))
return false;if(poster){autoPlay=clip.autoPlay;video.setAttribute('poster',poster);video.setAttribute('preload',"none");}
$f.fireEvent(self.id(),'onBegin',activeIndex);log("calling video.load()");video.load();}
if(autoPlay){log("calling video.play()");video.play();}}
video.fp_pause=function(){log("pause called");if(!actionAllowed('Pause'))
return false;video.pause();};video.fp_resume=function(){log("resume called");if(!actionAllowed('Resume'))
return false;video.play();};video.fp_stop=function(){log("stop called");if(!actionAllowed('Stop'))
return false;stopping=true;video.pause();try{video.currentTime=0;}catch(ignored){}};video.fp_seek=function(position){log("seek called "+position);if(!actionAllowed('Seek'))
return false;var seconds=0;var position=position+"";if(position.charAt(position.length-1)=='%'){var percentage=parseInt(position.substr(0,position.length-1))/100;var duration=video.duration;seconds=duration*percentage;}else{seconds=position;}
try{video.currentTime=seconds;}catch(e){log("Wrong seek time");}};video.fp_getTime=function(){return video.currentTime;};video.fp_mute=function(){log("mute called");if(!actionAllowed('Mute'))
return false;currentVolume=video.volume;video.volume=0;};video.fp_unmute=function(){if(!actionAllowed('Unmute'))
return false;video.volume=currentVolume;};video.fp_getVolume=function(){return video.volume*100;};video.fp_setVolume=function(volume){if(!actionAllowed('Volume'))
return false;video.volume=volume/100;};video.fp_toggle=function(){log('toggle called');if(self.getState()==STATE_ENDED){replay();return;}
if(video.paused)
video.fp_play();else
video.fp_pause();};video.fp_isPaused=function(){return video.paused;};video.fp_isPlaying=function(){return!video.paused;};video.fp_getPlugin=function(name){if(name=='canvas'||name=='controls'){var config=self.getConfig();return config['plugins']&&config['plugins'][name]?config['plugins'][name]:null;}
log("ERROR: no support for "+name+" plugin on iDevices");return null;};video.fp_close=function(){setState(STATE_UNLOADED);video.parentNode.removeChild(video);video=null;};video.fp_getStatus=function(){var bufferStart=0;var bufferEnd=0;try{bufferStart=video.buffered.start();bufferEnd=video.buffered.end();}catch(ignored){}
return{bufferStart:bufferStart,bufferEnd:bufferEnd,state:currentState,time:video.fp_getTime(),muted:video.muted,volume:video.fp_getVolume()};};video.fp_getState=function(){return currentState;};video.fp_startBuffering=function(){if(currentState==STATE_UNSTARTED)
video.load();};video.fp_setPlaylist=function(playlist){log("Setting playlist");activeIndex=0;for(var i=0;i<playlist.length;i++)
playlist[i]=fixClip(playlist[i]);activePlaylist=playlist;$f.fireEvent(self.id(),'onPlaylistReplace',playlist);};video.fp_addClip=function(clip,index){clip=fixClip(clip);activePlaylist.splice(index,0,clip);$f.fireEvent(self.id(),'onClipAdd',clip,index);};video.fp_updateClip=function(clip,index){extend(activePlaylist[index],clip);return activePlaylist[index];};video.fp_getVersion=function(){return'3.2.3';}
video.fp_isFullscreen=function(){var isfullscreen=video.webkitDisplayingFullscreen;if(isfullscreen!==undefined)
return isfullscreen;return false;}
video.fp_toggleFullscreen=function(){if(video.fp_isFullscreen())
video.webkitExitFullscreen();else
video.webkitEnterFullscreen();}
video.fp_addCuepoints=function(points,index,fnId){var clip=index==-1?self.getCommonClip():activePlaylist[index];clip.cuepoints=clip.cuepoints||{};points=points instanceof Array?points:[points];for(var i=0;i<points.length;i++){var time=typeof points[i]=="object"?(points[i]['time']||null):points[i];if(time==null)continue;time=Math.floor(time/100)*100;var parameters=time;if(typeof points[i]=="object"){parameters=extend({},points[i],false);if(parameters['time']===undefined)delete parameters['time'];if(parameters['parameters']!==undefined){extend(parameters,parameters['parameters'],false);delete parameters['parameters'];}}
clip.cuepoints[time]=clip.cuepoints[time]||[];clip.cuepoints[time].push({fnId:fnId,lastTimeFired:-1,parameters:parameters});}}
$f.each(("toggleFullscreen,stopBuffering,reset,playFeed,setKeyboardShortcutsEnabled,isKeyboardShortcutsEnabled,css,animate,showPlugin,hidePlugin,togglePlugin,fadeTo,invoke,loadPlugin").split(","),function(){var name=this;video["fp_"+name]=function(){log("ERROR: unsupported API on iDevices "+name);return false;};});}
function addListeners(){var events=['abort','canplay','canplaythrough','durationchange','emptied','ended','error','loadeddata','loadedmetadata','loadstart','pause','play','playing','progress','ratechange','seeked','seeking','stalled','suspend','volumechange','waiting'];var eventsLogger=function(e){log("Got event "+e.type,e);}
for(var i=0;i<events.length;i++)
video.addEventListener(events[i],eventsLogger,false);var onBufferEmpty=function(e){log("got onBufferEmpty event "+e.type)
setState(STATE_BUFFERING);$f.fireEvent(self.id(),'onBufferEmpty',activeIndex);};video.addEventListener('emptied',onBufferEmpty,false);video.addEventListener('waiting',onBufferEmpty,false);var onBufferFull=function(e){if(previousState==STATE_UNSTARTED||previousState==STATE_BUFFERING){}else{log("Restoring old state "+stateDescription(previousState));setState(previousState);}
$f.fireEvent(self.id(),'onBufferFull',activeIndex);};video.addEventListener('canplay',onBufferFull,false);video.addEventListener('canplaythrough',onBufferFull,false);var onMetaData=function(e){var clipDuration;startTime=activePlaylist[activeIndex].start;if(activePlaylist[activeIndex].duration>0){clipDuration=activePlaylist[activeIndex].duration;endTime=clipDuration+startTime;}else{clipDuration=video.duration;endTime=null;}
video.fp_updateClip({duration:clipDuration,metaData:{duration:video.duration}},activeIndex);activePlaylist[activeIndex].duration=video.duration;activePlaylist[activeIndex].metaData={duration:video.duration};$f.fireEvent(self.id(),'onMetaData',activeIndex,activePlaylist[activeIndex]);};video.addEventListener('loadedmetadata',onMetaData,false);video.addEventListener('durationchange',onMetaData,false);var onTimeUpdate=function(e){if(endTime&&video.currentTime>endTime){video.fp_seek(startTime);resetState();return stopEvent(e);}};video.addEventListener("timeupdate",onTimeUpdate,false);var onStart=function(e){if(currentState==STATE_PAUSED){if(!actionAllowed('Resume')){log("Resume disallowed, pausing");video.fp_pause();return stopEvent(e);}
$f.fireEvent(self.id(),'onResume',activeIndex);}
setState(STATE_PLAYING);if(!onStartFired){onStartFired=true;$f.fireEvent(self.id(),'onStart',activeIndex);}};video.addEventListener('playing',onStart,false);var onPlay=function(e){startLastSecondTimer();}
video.addEventListener('play',onPlay,false);var onFinish=function(e){if(!actionAllowed('Finish')){if(activePlaylist.length==1){log("Active playlist only has one clip, onBeforeFinish returned false. Replaying");replay();}else if(activeIndex!=(activePlaylist.length-1)){log("Not the last clip in the playlist, but onBeforeFinish returned false. Returning to the beginning of current clip");video.fp_seek(0);}else{log("Last clip in playlist, but onBeforeFinish returned false, start again from the beginning");video.fp_play(0);}
return stopEvent(e);}
setState(STATE_ENDED);$f.fireEvent(self.id(),'onFinish',activeIndex);if(activePlaylist.length>1&&activeIndex<(activePlaylist.length-1)){log("Not last clip in the playlist, moving to next one");video.fp_play(++activeIndex,false,true);}};video.addEventListener('ended',onFinish,false);var onError=function(e){setState(STATE_LOADED,true);$f.fireEvent(self.id(),'onError',activeIndex,201);if(opts.onFail&&opts.onFail instanceof Function)
opts.onFail.apply(self,[]);};video.addEventListener('error',onError,false);var onPause=function(e){log("got pause event from player"+self.id());if(stopping)
return;if(currentState==STATE_BUFFERING&&previousState==STATE_UNSTARTED){log("forcing play");setTimeout(function(){video.play();},0);return;}
if(!actionAllowed('Pause')){video.fp_resume();return stopEvent(e);}
stopLastSecondTimer();setState(STATE_PAUSED);$f.fireEvent(self.id(),'onPause',activeIndex);}
video.addEventListener('pause',onPause,false);var onSeek=function(e){$f.fireEvent(self.id(),'onBeforeSeek',activeIndex);};video.addEventListener('seeking',onSeek,false);var onSeekDone=function(e){if(stopping){stopping=false;$f.fireEvent(self.id(),'onStop',activeIndex);}
else
$f.fireEvent(self.id(),'onSeek',activeIndex);log("seek done, currentState",stateDescription(currentState));if(playAfterSeek){playAfterSeek=false;video.fp_play();}else if(currentState!=STATE_PLAYING)
video.fp_pause();};video.addEventListener('seeked',onSeekDone,false);var onVolumeChange=function(e){$f.fireEvent(self.id(),'onVolume',video.fp_getVolume());};video.addEventListener('volumechange',onVolumeChange,false);}
function startLastSecondTimer(){lastSecondTimer=setInterval(function(){if(video.fp_getTime()>=video.duration-1){$f.fireEvent(self.id(),'onLastSecond',activeIndex);stopLastSecondTimer();}},100);}
function stopLastSecondTimer(){clearInterval(lastSecondTimer);}
function onPlayerLoaded(){video.fp_play(0);}
function installControlbar(){}
if(isiDevice||opts.simulateiDevice){if(!window.flashembed.__replaced){var realFlashembed=window.flashembed;window.flashembed=function(root,opts,conf){if(typeof root=='string'){root=document.getElementById(root.replace("#",""));}
if(!root){return;}
var style=window.getComputedStyle(root,null);var width=parseInt(style.width);var height=parseInt(style.height);while(root.firstChild)
root.removeChild(root.firstChild);var container=document.createElement('div');var api=document.createElement('video');container.appendChild(api);root.appendChild(container);container.style.height=height+'px';container.style.width=width+'px';container.style.display='block';container.style.position='relative';container.style.background='-webkit-gradient(linear, left top, left bottom, from(rgba(0, 0, 0, 0.5)), to(rgba(0, 0, 0, 0.7)))';container.style.cursor='default';container.style.webkitUserDrag='none';api.style.height='100%';api.style.width='100%';api.style.display='block';api.id=opts.id;api.name=opts.id;api.style.cursor='pointer';api.style.webkitUserDrag='none';api.type="video/mp4";api.playerConfig=conf.config;$f.fireEvent(conf.config.playerId,'onLoad','player');};flashembed.getVersion=realFlashembed.getVersion;flashembed.asString=realFlashembed.asString;flashembed.isSupported=function(){return true;}
flashembed.__replaced=true;}
var __fireEvent=self._fireEvent;self._fireEvent=function(a){if(a[0]=='onLoad'&&a[1]=='player'){video=self.getParent().querySelector('video');if(opts.controls)
video.controls="controls";addAPI();addListeners();setState(STATE_LOADED,true);video.fp_setPlaylist(video.playerConfig.playlist);onPlayerLoaded();__fireEvent.apply(self,[a]);}
var shouldFireEvent=currentState!=STATE_UNLOADED;if(currentState==STATE_UNLOADED&&typeof a=='string')
shouldFireEvent=true;if(shouldFireEvent)
return __fireEvent.apply(self,[a]);}
self._swfHeight=function(){return parseInt(video.style.height);}
self.hasiPadSupport=function(){return true;}}
return self;});