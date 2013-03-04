/*!
 * jQuery Cookie Plugin v1.3.1
 * https://github.com/carhartl/jquery-cookie
 *
 * Copyright 2013 Klaus Hartl
 * Released under the MIT license
 */
(function(d){"function"===typeof define&&define.amd?define(["jquery"],d):d(jQuery)})(function(d){function m(a){return a}function n(a){return decodeURIComponent(a.replace(j," "))}function k(a){0===a.indexOf('"')&&(a=a.slice(1,-1).replace(/\\"/g,'"').replace(/\\\\/g,"\\"));try{return e.json?JSON.parse(a):a}catch(c){}}var j=/\+/g,e=d.cookie=function(a,c,b){if(void 0!==c){b=d.extend({},e.defaults,b);if("number"===typeof b.expires){var g=b.expires,f=b.expires=new Date;f.setDate(f.getDate()+g)}c=e.json?
JSON.stringify(c):String(c);return document.cookie=[encodeURIComponent(a),"=",e.raw?c:encodeURIComponent(c),b.expires?"; expires="+b.expires.toUTCString():"",b.path?"; path="+b.path:"",b.domain?"; domain="+b.domain:"",b.secure?"; secure":""].join("")}c=e.raw?m:n;b=document.cookie.split("; ");for(var g=a?void 0:{},f=0,j=b.length;f<j;f++){var h=b[f].split("="),l=c(h.shift()),h=c(h.join("="));if(a&&a===l){g=k(h);break}a||(g[l]=k(h))}return g};e.defaults={};d.removeCookie=function(a,c){return void 0!==
d.cookie(a)?(d.cookie(a,"",d.extend(c,{expires:-1})),!0):!1}}); 
