
(function(){function log(args){console.log("$f.fireEvent",[].slice.call(args));}
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
function select(query){var index=query.indexOf(".");if(index!=-1){var tag=query.substring(0,index)||"*";var klass=query.substring(index+1,query.length);var els=[];each(document.getElementsByTagName(tag),function(){if(this.className&&this.className.indexOf(klass)!=-1){els.push(this);}});return els;}}
function stopEvent(e){e=e||window.event;if(e.preventDefault){e.stopPropagation();e.preventDefault();}else{e.returnValue=false;e.cancelBubble=true;}
return false;}
function bind(to,evt,fn){to[evt]=to[evt]||[];to[evt].push(fn);}
function makeId(){return"_"+(""+Math.random()).substring(2,10);}
var Clip=function(json,index,player){var self=this;var cuepoints={};var listeners={};self.index=index;if(typeof json=='string'){json={url:json};}
extend(this,json,true);each(("Begin*,Start,Pause*,Resume*,Seek*,Stop*,Finish*,LastSecond,Update,BufferFull,BufferEmpty,BufferStop").split(","),function(){var evt="on"+this;if(evt.indexOf("*")!=-1){evt=evt.substring(0,evt.length-1);var before="onBefore"+evt.substring(2);self[before]=function(fn){bind(listeners,before,fn);return self;};}
self[evt]=function(fn){bind(listeners,evt,fn);return self;};if(index==-1){if(self[before]){player[before]=self[before];}
if(self[evt]){player[evt]=self[evt];}}});extend(this,{onCuepoint:function(points,fn){if(arguments.length==1){cuepoints.embedded=[null,points];return self;}
if(typeof points=='number'){points=[points];}
var fnId=makeId();cuepoints[fnId]=[points,fn];if(player.isLoaded()){player._api().fp_addCuepoints(points,index,fnId);}
return self;},update:function(json){extend(self,json);if(player.isLoaded()){player._api().fp_updateClip(json,index);}
var conf=player.getConfig();var clip=(index==-1)?conf.clip:conf.playlist[index];extend(clip,json,true);},_fireEvent:function(evt,arg1,arg2,target){if(evt=='onLoad'){each(cuepoints,function(key,val){if(val[0]){player._api().fp_addCuepoints(val[0],index,key);}});return false;}
target=target||self;if(evt=='onCuepoint'){var fn=cuepoints[arg1];if(fn){return fn[1].call(player,target,arg2);}}
if(arg1&&"onBeforeBegin,onMetaData,onStart,onUpdate,onResume".indexOf(evt)!=-1){extend(target,arg1);if(arg1.metaData){if(!target.duration){target.duration=arg1.metaData.duration;}else{target.fullDuration=arg1.metaData.duration;}}}
var ret=true;each(listeners[evt],function(){ret=this.call(player,target,arg1,arg2);});return ret;}});if(json.onCuepoint){var arg=json.onCuepoint;self.onCuepoint.apply(self,typeof arg=='function'?[arg]:arg);delete json.onCuepoint;}
each(json,function(key,val){if(typeof val=='function'){bind(listeners,key,val);delete json[key];}});if(index==-1){player.onCuepoint=this.onCuepoint;}};var Plugin=function(name,json,player,fn){var listeners={};var self=this;var hasMethods=false;if(fn){extend(listeners,fn);}
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
var fn=listeners[evt];if(fn){fn.apply(self,arg);if(evt.substring(0,1)=="_"){delete listeners[evt];}}}});};function Player(wrapper,params,conf){var
self=this,api=null,html,commonClip,playlist=[],plugins={},listeners={},playerId,apiId,playerIndex,activeIndex,swfHeight,wrapperHeight;extend(self,{id:function(){return playerId;},isLoaded:function(){return(api!==null);},getParent:function(){return wrapper;},hide:function(all){if(all){wrapper.style.height="0px";}
if(api){api.style.height="0px";}
return self;},show:function(){wrapper.style.height=wrapperHeight+"px";if(api){api.style.height=swfHeight+"px";}
return self;},isHidden:function(){return api&&parseInt(api.style.height,10)===0;},load:function(fn){if(!api&&self._fireEvent("onBeforeLoad")!==false){each(players,function(){this.unload();});html=wrapper.innerHTML;if(html&&!flashembed.isSupported(params.version)){wrapper.innerHTML="";}
flashembed(wrapper,params,{config:conf});if(fn){fn.cached=true;bind(listeners,"onLoad",fn);}}
return self;},unload:function(){if(html.replace(/\s/g,'')!==''){if(self._fireEvent("onBeforeUnload")===false){return self;}
try{if(api){api.fp_close();self._fireEvent("onUnload");}}catch(error){}
api=null;wrapper.innerHTML=html;}
return self;},getClip:function(index){if(index===undefined){index=activeIndex;}
return playlist[index];},getCommonClip:function(){return commonClip;},getPlaylist:function(){return playlist;},getPlugin:function(name){var plugin=plugins[name];if(!plugin&&self.isLoaded()){var json=self._api().fp_getPlugin(name);if(json){plugin=new Plugin(name,json,self);plugins[name]=plugin;}}
return plugin;},getScreen:function(){return self.getPlugin("screen");},getControls:function(){return self.getPlugin("controls");},getConfig:function(copy){return copy?clone(conf):conf;},getFlashParams:function(){return params;},loadPlugin:function(name,url,props,fn){if(typeof props=='function'){fn=props;props={};}
var fnId=fn?makeId():"_";self._api().fp_loadPlugin(name,url,props,fnId);var arg={};arg[fnId]=fn;var p=new Plugin(name,null,self,arg);plugins[name]=p;return p;},getState:function(){return api?api.fp_getState():-1;},play:function(clip,instream){function play(){if(clip!==undefined){self._api().fp_play(clip,instream);}else{self._api().fp_play();}}
if(api){play();}else{self.load(function(){play();});}
return self;},getVersion:function(){var js="flowplayer.js 3.1.4";if(api){var ver=api.fp_getVersion();ver.push(js);return ver;}
return js;},_api:function(){if(!api){throw"Flowplayer "+self.id()+" not loaded when calling an API method";}
return api;},setClip:function(clip){self.setPlaylist([clip]);return self;},getIndex:function(){return playerIndex;}});each(("Click*,Load*,Unload*,Keypress*,Volume*,Mute*,Unmute*,PlaylistReplace,ClipAdd,Fullscreen*,FullscreenExit,Error,MouseOver,MouseOut").split(","),function(){var name="on"+this;if(name.indexOf("*")!=-1){name=name.substring(0,name.length-1);var name2="onBefore"+name.substring(2);self[name2]=function(fn){bind(listeners,name2,fn);return self;};}
self[name]=function(fn){bind(listeners,name,fn);return self;};});each(("pause,resume,mute,unmute,stop,toggle,seek,getStatus,getVolume,setVolume,getTime,isPaused,isPlaying,startBuffering,stopBuffering,isFullscreen,toggleFullscreen,reset,close,setPlaylist,addClip,playFeed").split(","),function(){var name=this;self[name]=function(a1,a2){if(!api){return self;}
var ret=null;if(a1!==undefined&&a2!==undefined){ret=api["fp_"+name](a1,a2);}else{ret=(a1===undefined)?api["fp_"+name]():api["fp_"+name](a1);}
return ret==='undefined'||ret===undefined?self:ret;};});self._fireEvent=function(a){if(typeof a=='string'){a=[a];}
var evt=a[0],arg0=a[1],arg1=a[2],arg2=a[3],i=0;if(conf.debug){log(a);}
if(!api&&evt=='onLoad'&&arg0=='player'){api=api||el(apiId);swfHeight=api.clientHeight;each(playlist,function(){this._fireEvent("onLoad");});each(plugins,function(name,p){p._fireEvent("onUpdate");});commonClip._fireEvent("onLoad");}
if(evt=='onLoad'&&arg0!='player'){return;}
if(evt=='onError'){if(typeof arg0=='string'||(typeof arg0=='number'&&typeof arg1=='number')){arg0=arg1;arg1=arg2;}}
if(evt=='onContextMenu'){each(conf.contextMenu[arg0],function(key,fn){fn.call(self);});return;}
if(evt=='onPluginEvent'){var name=arg0.name||arg0;var p=plugins[name];if(p){p._fireEvent("onUpdate",arg0);p._fireEvent(arg1,a.slice(3));}
return;}
if(evt=='onPlaylistReplace'){playlist=[];var index=0;each(arg0,function(){playlist.push(new Clip(this,index++,self));});}
if(evt=='onClipAdd'){if(arg0.isInStream){return;}
arg0=new Clip(arg0,arg1,self);playlist.splice(arg1,0,arg0);for(i=arg1+1;i<playlist.length;i++){playlist[i].index++;}}
var ret=true;if(typeof arg0=='number'&&arg0<playlist.length){activeIndex=arg0;var clip=playlist[arg0];if(clip){ret=clip._fireEvent(evt,arg1,arg2);}
if(!clip||ret!==false){ret=commonClip._fireEvent(evt,arg1,arg2,clip);}}
each(listeners[evt],function(){ret=this.call(self,arg0,arg1);if(this.cached){listeners[evt].splice(i,1);}
if(ret===false){return false;}
i++;});return ret;};function init(){if($f(wrapper)){$f(wrapper).getParent().innerHTML="";playerIndex=$f(wrapper).getIndex();players[playerIndex]=self;}else{players.push(self);playerIndex=players.length-1;}
wrapperHeight=parseInt(wrapper.style.height,10)||wrapper.clientHeight;if(typeof params=='string'){params={src:params};}
playerId=wrapper.id||"fp"+makeId();apiId=params.id||playerId+"_api";params.id=apiId;conf.playerId=playerId;if(typeof conf=='string'){conf={clip:{url:conf}};}
if(typeof conf.clip=='string'){conf.clip={url:conf.clip};}
conf.clip=conf.clip||{};if(wrapper.getAttribute("href",2)&&!conf.clip.url){conf.clip.url=wrapper.getAttribute("href",2);}
commonClip=new Clip(conf.clip,-1,self);conf.playlist=conf.playlist||[conf.clip];var index=0;each(conf.playlist,function(){var clip=this;if(typeof clip=='object'&&clip.length){clip={url:""+clip};}
each(conf.clip,function(key,val){if(val!==undefined&&clip[key]===undefined&&typeof val!='function'){clip[key]=val;}});conf.playlist[index]=clip;clip=new Clip(clip,index,self);playlist.push(clip);index++;});each(conf,function(key,val){if(typeof val=='function'){if(commonClip[key]){commonClip[key](val);}else{bind(listeners,key,val);}
delete conf[key];}});each(conf.plugins,function(name,val){if(val){plugins[name]=new Plugin(name,val,self);}});if(!conf.plugins||conf.plugins.controls===undefined){plugins.controls=new Plugin("controls",null,self);}
plugins.canvas=new Plugin("canvas",null,self);params.bgcolor=params.bgcolor||"#000000";params.version=params.version||[9,0];params.expressInstall='http://www.flowplayer.org/swf/expressinstall.swf';function doClick(e){if(!self.isLoaded()&&self._fireEvent("onBeforeClick")!==false){self.load();}
return stopEvent(e);}
html=wrapper.innerHTML;if(html.replace(/\s/g,'')!==''){if(wrapper.addEventListener){wrapper.addEventListener("click",doClick,false);}else if(wrapper.attachEvent){wrapper.attachEvent("onclick",doClick);}}else{if(wrapper.addEventListener){wrapper.addEventListener("click",stopEvent,false);}
self.load();}}
if(typeof wrapper=='string'){flashembed.domReady(function(){var node=el(wrapper);if(!node){throw"Flowplayer cannot access element: "+wrapper;}else{wrapper=node;init();}});}else{init();}}
var players=[];function Iterator(arr){this.length=arr.length;this.each=function(fn){each(arr,fn);};this.size=function(){return arr.length;};}
window.flowplayer=window.$f=function(){var instance=null;var arg=arguments[0];if(!arguments.length){each(players,function(){if(this.isLoaded()){instance=this;return false;}});return instance||players[0];}
if(arguments.length==1){if(typeof arg=='number'){return players[arg];}else{if(arg=='*'){return new Iterator(players);}
each(players,function(){if(this.id()==arg.id||this.id()==arg||this.getParent()==arg){instance=this;return false;}});return instance;}}
if(arguments.length>1){var swf=arguments[1];var conf=(arguments.length==3)?arguments[2]:{};if(typeof arg=='string'){if(arg.indexOf(".")!=-1){var instances=[];each(select(arg),function(){instances.push(new Player(this,clone(swf),clone(conf)));});return new Iterator(instances);}else{var node=el(arg);return new Player(node!==null?node:arg,swf,conf);}}else if(arg){return new Player(arg,swf,conf);}}
return null;};extend(window.$f,{fireEvent:function(){var a=[].slice.call(arguments);var p=$f(a[0]);return p?p._fireEvent(a.slice(1)):null;},addPlugin:function(name,fn){Player.prototype[name]=fn;return $f;},each:each,extend:extend});if(typeof jQuery=='function'){jQuery.prototype.flowplayer=function(params,conf){if(!arguments.length||typeof arguments[0]=='number'){var arr=[];this.each(function(){var p=$f(this);if(p){arr.push(p);}});return arguments.length?arr[arguments[0]]:new Iterator(arr);}
return this.each(function(){$f(this,clone(params),conf?clone(conf):{});});};}})();(function(){var jQ=typeof jQuery=='function';var options={width:'100%',height:'100%',allowfullscreen:true,allowscriptaccess:'always',quality:'high',version:null,onFail:null,expressInstall:null,w3c:false,cachebusting:false};if(jQ){jQuery.tools=jQuery.tools||{};jQuery.tools.flashembed={version:'1.0.4',conf:options};}
function isDomReady(){if(domReady.done){return false;}
var d=document;if(d&&d.getElementsByTagName&&d.getElementById&&d.body){clearInterval(domReady.timer);domReady.timer=null;for(var i=0;i<domReady.ready.length;i++){domReady.ready[i].call();}
domReady.ready=null;domReady.done=true;}}
var domReady=jQ?jQuery:function(f){if(domReady.done){return f();}
if(domReady.timer){domReady.ready.push(f);}else{domReady.ready=[f];domReady.timer=setInterval(isDomReady,13);}};function extend(to,from){if(from){for(key in from){if(from.hasOwnProperty(key)){to[key]=from[key];}}}
return to;}
var cx=/[\u0000\u00ad\u0600-\u0604\u070f\u17b4\u17b5\u200c-\u200f\u2028-\u202f\u2060-\u206f\ufeff\ufff0-\uffff]/g,escapable=/[\\\"\x00-\x1f\x7f-\x9f\u00ad\u0600-\u0604\u070f\u17b4\u17b5\u200c-\u200f\u2028-\u202f\u2060-\u206f\ufeff\ufff0-\uffff]/g,gap,indent,meta={'\b':'\\b','\t':'\\t','\n':'\\n','\f':'\\f','\r':'\\r','"':'\\"','\\':'\\\\'},rep;function string2JsonString(string){escapable.lastIndex=0;return escapable.test(string)?'"'+string.replace(escapable,function(a){var c=meta[a];return typeof c==='string'?c:'\\u'+('0000'+a.charCodeAt(0).toString(16)).slice(-4);})+'"':'"'+string+'"';}
function asString(obj){switch(typeOf(obj)){case'string':return string2JsonString(obj);case'array':return'['+map(obj,function(el){return asString(el);}).join(',')+']';case'function':return'"function()"';case'object':var str=[];for(var prop in obj){if(obj.hasOwnProperty(prop)){str.push('"'+prop+'":'+asString(obj[prop]));}}
return'{'+str.join(',')+'}';}
return String(obj).replace(/\s/g," ").replace(/\'/g,"\"");}
function typeOf(obj){if(obj===null||obj===undefined){return false;}
var type=typeof obj;return(type=='object'&&obj.push)?'array':type;}
if(window.attachEvent){window.attachEvent("onbeforeunload",function(){__flash_unloadHandler=function(){};__flash_savedUnloadHandler=function(){};});}
function map(arr,func){var newArr=[];for(var i in arr){if(arr.hasOwnProperty(i)){newArr[i]=func(arr[i]);}}
return newArr;}
function getHTML(p,c){var e=extend({},p);var ie=document.all;var html='<object width="'+e.width+'" height="'+e.height+'"';if(ie&&!e.id){e.id="_"+(""+Math.random()).substring(9);}
if(e.id){html+=' id="'+e.id+'"';}
if(e.cachebusting){e.src+=((e.src.indexOf("?")!=-1?"&":"?")+Math.random());}
if(e.w3c||!ie){html+=' data="'+e.src+'" type="application/x-shockwave-flash"';}else{html+=' classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"';}
html+='>';if(e.w3c||ie){html+='<param name="movie" value="'+e.src+'" />';}
e.width=e.height=e.id=e.w3c=e.src=null;for(var k in e){if(e[k]!==null){html+='<param name="'+k+'" value="'+e[k]+'" />';}}
var vars="";if(c){for(var key in c){if(c[key]!==null){vars+=encodeURIComponent(key)+'='
+encodeURIComponent(typeof c[key]=='object'?asString(c[key]):c[key])
+'&';}}
vars=vars.substring(0,vars.length-1);html+='<param name="flashvars" value="'+vars+'" />';}
html+="</object>";return html;}
function Flash(root,opts,flashvars){var version=flashembed.getVersion();extend(this,{getContainer:function(){return root;},getConf:function(){return opts;},getVersion:function(){return version;},getFlashvars:function(){return flashvars;},getApi:function(){return root.firstChild;},getHTML:function(){return getHTML(opts,flashvars);}});var required=opts.version;var express=opts.expressInstall;var ok=!required||flashembed.isSupported(required);if(ok){opts.onFail=opts.version=opts.expressInstall=null;root.innerHTML=getHTML(opts,flashvars);}else if(required&&express&&flashembed.isSupported([6,65])){extend(opts,{src:express});flashvars={MMredirectURL:location.href,MMplayerType:'PlugIn',MMdoctitle:document.title};root.innerHTML=getHTML(opts,flashvars);}else{if(root.innerHTML.replace(/\s/g,'')!==''){}else{root.innerHTML="<h2>Flash version "+required+" or greater is required</h2>"+"<h3>"+
(version[0]>0?"Your version is "+version:"You have no flash plugin installed")+"</h3>"+
(root.tagName=='A'?"<p>Click here to download latest version</p>":"<p>Download latest version from <a href='http://www.adobe.com/go/getflashplayer'>here</a></p>");if(root.tagName=='A'){root.onclick=function(){location.href='http://www.adobe.com/go/getflashplayer';};}}}
if(!ok&&opts.onFail){var ret=opts.onFail.call(this);if(typeof ret=='string'){root.innerHTML=ret;}}
if(document.all){window[opts.id]=document.getElementById(opts.id);}}
window.flashembed=function(root,conf,flashvars){if(typeof root=='string'){var el=document.getElementById(root);if(el){root=el;}else{domReady(function(){flashembed(root,conf,flashvars);});return;}}
if(!root){return;}
if(typeof conf=='string'){conf={src:conf};}
var opts=extend({},options);extend(opts,conf);return new Flash(root,opts,flashvars);};extend(window.flashembed,{getVersion:function(){var version=[0,0];if(navigator.plugins&&typeof navigator.plugins["Shockwave Flash"]=="object"){var _d=navigator.plugins["Shockwave Flash"].description;if(typeof _d!="undefined"){_d=_d.replace(/^.*\s+(\S+\s+\S+$)/,"$1");var _m=parseInt(_d.replace(/^(.*)\..*$/,"$1"),10);var _r=/r/.test(_d)?parseInt(_d.replace(/^.*r(.*)$/,"$1"),10):0;version=[_m,_r];}}else if(window.ActiveXObject){try{var _a=new ActiveXObject("ShockwaveFlash.ShockwaveFlash.7");}catch(e){try{_a=new ActiveXObject("ShockwaveFlash.ShockwaveFlash.6");version=[6,0];_a.AllowScriptAccess="always";}catch(ee){if(version[0]==6){return version;}}
try{_a=new ActiveXObject("ShockwaveFlash.ShockwaveFlash");}catch(eee){}}
if(typeof _a=="object"){_d=_a.GetVariable("$version");if(typeof _d!="undefined"){_d=_d.replace(/^\S+\s+(.*)$/,"$1").split(",");version=[parseInt(_d[0],10),parseInt(_d[2],10)];}}}
return version;},isSupported:function(version){var now=flashembed.getVersion();var ret=(now[0]>version[0])||(now[0]==version[0]&&now[1]>=version[1]);return ret;},domReady:domReady,asString:asString,getHTML:getHTML});if(jQ){jQuery.fn.flashembed=function(conf,flashvars){var el=null;this.each(function(){el=flashembed(this,conf,flashvars);});return conf.api===false?this:el;};}})();