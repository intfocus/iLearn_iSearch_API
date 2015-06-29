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

function moSearchEngine(pages,titles,keywords,indexs){this.ps=pages;this.ts=titles;this.ks=keywords;this.is=indexs;this.container=null;this.sr=[];this.any=false;this.last=[];this.inLast=false;this.titlesOnly=false;this.partWord=false;this.hlKeywords;{for(var i=0;i<this.ks.length;i++)
this.ks[i]=this.ks[i].toLowerCase();}
this.setContainer=function(container){this.container=$(">ul",container);}
this.createResultList=function(result){this.sr=result;this.container.text("");if(!result)return;var html=''
for(var i=0;i<result.length;i++){html+='<li><a href="'+this.ps[result[i]]+'">'+this.ts[result[i]]+'</a></li>';}
this.container.html(html);}
this.search=function(s){var ss=this.parse(s);var sr=[];if(ss.length>0){sr=this.doSearch(ss);if(this.inLast)sr=this.AND(sr,this.last);this.last=sr;}
if(this.partWord){this.hlKeywords=ss.join('|');}
else{this.hlKeywords='';for(var i=0;i<ss.length;i++){if(i>0)this.hlKeywords+='|';var re=/[\u0100-\uFFFF]/;this.hlKeywords+=(re.test(ss[i]))?ss[i]:'\\b'+ss[i]+'\\b';}
}
this.createResultList(sr);}
this.doSearch=function(ss){var sr;for(var si=0;si<ss.length;si++){var sr2=this.titlesOnly?this.matchArray(ts,ss[si])
:(ss[si].toString().match(/[*?]/))?this.matchArray(ks,ss[si])
:this.findArray(ks,ss[si]);if(!this.any&&sr2.length==0){return[];}
else{sr=(si==0)?sr2
:this.any?this.OR(sr,sr2)
:this.AND(sr,sr2);}
if(!this.any&&sr.length==0)return[];}
return sr;}
this.matchArray=function(array,item){var result=[];var re=this.partWord?new RegExp(item,'i')
:this.titlesOnly?new RegExp('(^|\\W)'+item+'(\\W|$)','i')
:new RegExp('^'+item+'$','i');for(var i=0;i<array.length;i++)
if(array[i].match(re)){if(this.titlesOnly)
result[result.length]=i
else
result=this.OR(result,this.is[i]);}
return result;}
this.findArray=function(array,item)
{if(this.partWord){var result=[];for(var i=0;i<array.length;i++)
if(array[i].indexOf(item)>-1){result=this.OR(result,is[i]);}
return result;}
else{var ki=this.findIndex(array,item);if(ki<0)
return[];else
return is[ki];}
}
this.findIndex=function(array,item){var result=-1;for(var i=0;i<array.length;i++)
if(item==array[i]){result=i;break;}
return result;}
this.parse=function(s){s=s.toLowerCase();var ss=[];var re=/[\*\?\w\u00C0-\u00D6\u00D8-\u00F6\u00F8-\u00FF\u0100\u0102-\u0107\u010C-\u0112\u0116-\u011B\u011E\u0122\u0128-\u012A\u012E\u0130\u0136\u0139\u013B\u013D\u0141-\u0147\u014C\u0150\u0154\u0156\u0158-\u015B\u015E-\u0165\u0168-\u016A\u016E-\u0172\u0178-\u017E\u0192\u01A0-\u01A1\u01AF-\u01B0\u02C6\u0386\u0388-\u038A\u038C\u038E-\u03A1\u03A3-\u03CE\u0401-\u040C\u040E-\u044F\u0451-\u045C\u045E\u0490-\u0491\u05D0-\u05EA\u0621-\u063A\u0641-\u064A\u0679\u067E\u0686\u0688\u0691\u0698\u06A9\u06AF\u06BA\u06BE\u06C1\u06D2\u1EA0-\u1EF9]{2,}|[\u0E01-\u0E3A\u0E40-\u0E4F\u0E5A\u0E5B]{2,}|[\u1100-\u11FF\u2E80-\u9FFF\uF900-\uFAFF\uAC00-\uD7FF\uFF00-\uFFEF]/g;var w;while(w=re.exec(s)){var word=w.toString();word=word.replace(/(?=[\*\?])/g,"[\\w\\u00C0-\\u00D6\\u00D8-\\u00F6\\u00F8-\\u00FF\\u0100\\u0102-\\u0107\\u010C-\\u0112\\u0116-\\u011B\\u011E\\u0122\\u0128-\\u012A\\u012E\\u0130\\u0136\\u0139\\u013B\\u013D\\u0141-\\u0147\\u014C\\u0150\\u0154\\u0156\\u0158-\\u015B\\u015E-\\u0165\\u0168-\\u016A\\u016E-\\u0172\\u0178-\\u017E\\u0192\\u01A0-\\u01A1\\u01AF-\\u01B0\\u02C6\\u0386\\u0388-\\u038A\\u038C\\u038E-\\u03A1\\u03A3-\\u03CE\\u0401-\\u040C\\u040E-\\u044F\\u0451-\\u045C\\u045E\\u0490-\\u0491\\u05D0-\\u05EA\\u0621-\\u063A\\u0641-\\u064A\\u0679\\u067E\\u0686\\u0688\\u0691\\u0698\\u06A9\\u06AF\\u06BA\\u06BE\\u06C1\\u06D2\\u1EA0-\\u1EF9]");ss[ss.length]=word;}
return ss;}
this.AND=function(a,b){var result=[];for(var ai=0;ai<a.length;ai++)
if(this.findIndex(b,a[ai])>=0)result[result.length]=a[ai];return result;}
this.OR=function(b,a){var result=[];for(var bi=0;bi<b.length;bi++)
result[result.length]=b[bi];for(var ai=0;ai<a.length;ai++)
if(this.findIndex(result,a[ai])<0)result[result.length]=a[ai];return result;}
}
var se=new moSearchEngine(ps,ts,ks,is);