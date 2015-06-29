//=======================================================================//
//                                                                       //
//  Macrobject Software Code Library                                     //
//  Copyright (c) 2004-2010 Macrobject Software, All Rights Reserved     //
//  http://www.macrobject.com                                            //
//  info@macrobject.com                                                  //
//                                                                       //
//  Warning!!!                                                           //
//      The library can only be used in files created by Macrobject      //
//      Software Products.                                               //
//                                                                       //
//=======================================================================//

function getCookie(name){var start=document.cookie.indexOf(name+"=");var len=start+name.length+1;if((!start)&&(name!=document.cookie.substring(0,name.length))){return"";}
if(start==-1)return"";var end=document.cookie.indexOf(';',len);if(end==-1)end=document.cookie.length;return unescape(document.cookie.substring(len,end));}
function setCookie(name,value,expires,path,domain,secure){var today=new Date();today.setTime(today.getTime());if(expires){expires=expires*1000*60*60*24;}
else{expires=365*1000*60*60*24;}
var expires_date=new Date(today.getTime()+(expires));document.cookie=name+'='+escape(value)+
((expires)?';expires='+expires_date.toGMTString():'')+
((path)?';path='+path:'')+
((domain)?';domain='+domain:'')+
((secure)?';secure':'');}
function moHelpBookmark(pages,titles,name){this.ps=pages;this.ts=titles;this.bmName=name;this.container=null;this.setContainer=function(container){this.container=$(">ul",container);}
this.getBookmarks=function(){return getCookie(this.bmName).split(",");}
this.clearBookmarks=function(){setCookie(this.bmName,"");}
this.addBookmark=function(href){if(!getCookie(this.bmName).match(new RegExp('\\b'+href+'\\b'),'')){setCookie(this.bmName,getCookie(this.bmName)+','+href);}
}
this.delBookmark=function(href){setCookie(this.bmName,getCookie(this.bmName).replace(new RegExp('(,|^)'+href+'(?=,|$)','g'),''));}
this.createBookmarks=function(){this.container.text("");var bms=this.getBookmarks();if(!bms)return;var html='';for(var i=0;i<bms.length;i++){if(bms[i]){html+='<li><a href="'+bms[i]+'">'+this.findTitle(bms[i])+'</a></li>';}
}
this.container.html(html);}
this.findTitle=function(href){for(var i=0;i<this.ps.length;i++){if(href==this.ps[i]){return this.ts[i];}
}
return;}
}
var hb=new moHelpBookmark(ps,ts,"Bookmarks_2178CB987803BFC4324CA8DBBE52D36D");