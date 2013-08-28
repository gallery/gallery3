/*!
 * jQuery Cookie Plugin v1.3.1
 * https://github.com/carhartl/jquery-cookie
 *
 * Copyright 2013 Klaus Hartl
 * Released under the MIT license
 */
(function(d){"function"===typeof define&&define.amd?define(["jquery"],d):d(jQuery)})(function(d){function k(a){return e.raw?a:decodeURIComponent(a.replace(n," "))}function l(a){0===a.indexOf('"')&&(a=a.slice(1,-1).replace(/\\"/g,'"').replace(/\\\\/g,"\\"));a=k(a);try{return e.json?JSON.parse(a):a}catch(c){}}var n=/\+/g,e=d.cookie=function(a,c,b){if(void 0!==c){b=d.extend({},e.defaults,b);if("number"===typeof b.expires){var f=b.expires,h=b.expires=new Date;h.setDate(h.getDate()+f)}c=e.json?JSON.stringify(c):
String(c);return document.cookie=[e.raw?a:encodeURIComponent(a),"=",e.raw?c:encodeURIComponent(c),b.expires?"; expires="+b.expires.toUTCString():"",b.path?"; path="+b.path:"",b.domain?"; domain="+b.domain:"",b.secure?"; secure":""].join("")}c=document.cookie.split("; ");b=a?void 0:{};f=0;for(h=c.length;f<h;f++){var g=c[f].split("="),m=k(g.shift()),g=g.join("=");if(a&&a===m){b=l(g);break}a||(b[m]=l(g))}return b};e.defaults={};d.removeCookie=function(a,c){return void 0!==d.cookie(a)?(d.cookie(a,"",
d.extend({},c,{expires:-1})),!0):!1}}); 
