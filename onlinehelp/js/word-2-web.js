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

var $myLayout,$layoutCenter,$westAccordion,$topicTree,$indexTree,$resultTree,$bookmarkTree;function resizeWidgets(){$myLayout.resizeAll();$westAccordion.accordion("resize");};$(document).ready(function(){$myLayout=$('body').layout({north:{spacing_open:0
,spacing_closed:0
,resizable:false
,closable:false
}
,south:{spacing_open:0
,spacing_closed:0
,resizable:false
,closable:false
}
,west:{spacing_open:6
,spacing_closed:6
,size:290
,resizerClass:"ui-widget-header"
,togglerClass:"ui-state-highlight"
}
,center:{}
,west__onresize:function(){$("#accordion1").accordion("resize");}
});$layoutCenter=$(".ui-layout-center");window.onresize=resizeWidgets;$westAccordion=$("#accordion1").accordion({fillSpace:true
,header:"p"
});$.fn.moTree.defaultOptions.nodeTooltip=true;$.fn.moTree.defaultOptions.autoCollapse=true;var $breadcrumbs=$("#breadcrumbs");function updateBreadcrumbs($node){if($breadcrumbs.length==0)return;$breadcrumbs.text("");var i=0;var html="";$node.parentsUntil("#topicTree").find(">a").each(function(){var text=(i>0)?($(this).attr("href")!="#")
?'<a href="'+$(this).attr("href")+'">'+$(this).text()+"</a> &gt; "
:$(this).text()+"</a> &gt; "
:$(this).text();html=text+html;i++;});html=":: "+html;$breadcrumbs.html(html);$breadcrumbs.find("a").click(function(event){$topicTree.selectNodeByHref($(this).attr("href"),true);return false;});}
var $ajaxContentHolder=$("#ajaxContentHolder");$topicTree=$("#topicTree").moTree({targetContainer:"#ajaxContentHolder"
,autoSelectFirst:false
,onClick:function($node){if($node.attr("href")!="#"){$ajaxContentHolder.attr("page",$node.attr("href"));updateBreadcrumbs($node);}
}
,onNodeCreate:function($node){ps[ps.length]=$node.attr("href");ts[ts.length]=$node.text();}
});function highlight(n,k){if(n.hasChildNodes){for(var i=0;i<n.childNodes.length;i++){if(highlight(n.childNodes[i],k))break;}
}
if((n.nodeType==3)&&n.nodeValue.match(k)){n.parentNode.innerHTML=n.parentNode.innerHTML.replace(k,'$1<span class="highlight">$2</span>');return true;}
return false;}
var isSearchResultClick=false;$ajaxContentHolder.ajaxComplete(function(e,xhr,settings){var archor=" ";var re=/#([^\/\\]+)$/g;var arr=re.exec($ajaxContentHolder.attr("page"));if(arr){archor=arr[1];$ajaxContentHolder.attr("file",$ajaxContentHolder.attr("page").replace(re,""));}
else{$layoutCenter.scrollTop(0);}
if(isSearchResultClick){isSearchResultClick=false;var keywords=$.trim($("#searchText").val());highlight($ajaxContentHolder.get(0),new RegExp("(</?[^>]+>)|("+se.hlKeywords+")","gi"));}
$('#ajaxContentHolder a').each(function(){var href=$(this).attr("href");if(href)href=href.replace(/#.*$/,"");if(href&&!href.match(/^\w+:/)){$(this).click(function(){$ajaxContentHolder.load($(this).attr("href"));$topicTree.selectNodeByHref($(this).attr("href").replace(/#.*$/,""),true
);return false;});}
else{var name=$(this).attr("name");if(name==archor){$layoutCenter.scrollTop($layoutCenter.scrollTop()+$(this).offset().top-$layoutCenter.offset().top-10);}
}
});$('#ajaxContentHolder img').each(function(){var len=settings.url.replace(/[^\/]+/g,"").length;var src=$(this).attr("src");for(var i=0;i<len;i++){src=src.replace(/^\.\.\//g,"");}
$(this).attr("src",src);});$("#ajaxContentHolder .TrialNote")
.wrap('<div class="ui-state-highlight ui-corner-all" style="padding: 0 .7em;"></div>')
.wrap('<div class="ui-widget"></div>');});var $indexContentHolder=$("#indexTree");var indexHref;$indexContentHolder.ajaxComplete(function(e,xhr,settings){if(settings.url==indexHref){$indexContentHolder.unbind("ajaxComplete");$indexTree=$indexContentHolder.moTree({targetContainer:null
,onClick:function($node){$topicTree.selectNodeByHref($node.attr("href"),true);}
});}
});$("#indexTreeButton").click(function(){var btn=$(this);btn.unbind("click");indexHref=btn.attr("href");$indexContentHolder.load(indexHref);});$("#searchText").keydown(function(event){event.stopPropagation();})
.keypress(function(event){event.stopPropagation();if(event.keyCode==13){$("#searchButton").click();}})
.keyup(function(event){event.stopPropagation();})
.click(function(event){event.stopPropagation();$(this).focus();return false;});var onSearchFinished;function doSearch(){var btn=$(this);se.setContainer("#searchResult");var keywords=$.trim($("#searchText").val());se.search(keywords);$resultTree=$("#searchResult").moTree({targetContainer:null
,onClick:function($node){isSearchResultClick=true;$topicTree.selectNodeByHref($node.attr("href"),true);}
});if(onSearchFinished){onSearchFinished();}
}
var searchScriptState="not loaded";$("#searchButton").click(function(){$("#searchResult").prepend('<img src="css/images/ajax-loader.gif" width="16" height="16"/>');if(searchScriptState=="loaded"){doSearch();}
if(searchScriptState=="not loaded"){$westAccordion.accordion("activate",1);searchScriptState="loading";$.getScript("js/moSearchData.js",function(){$.getScript("js/moSearch.js",function(){searchScriptState="loaded";doSearch();});});}
$("#searchResult>img").remove();});function displayBookmarks(){hb.setContainer("#bookmarkPane");hb.createBookmarks();$bookmarkTree=$("#bookmarkPane").moTree({targetContainer:null
,onClick:function($node){$topicTree.selectNodeByHref($node.attr("href"),true);}
});}
$("#bookmarkButton").click(function(){$(this).unbind("click");$.getScript("js/moBookmark.js",function(){displayBookmarks();});});$("#btnAddBookmark").click(function(){if(typeof(hb)=="undefined")return;var href=$ajaxContentHolder.attr("page");hb.addBookmark(href);displayBookmarks();$bookmarkTree.selectNodeByHref(href);});$("#btnDelBookmark").click(function(){if(typeof(hb)=="undefined")return;($bookmarkTree.selectedNode)?hb.delBookmark($bookmarkTree.selectedNode.attr("href"))
:hb.delBookmark($ajaxContentHolder.attr("page"));displayBookmarks();});$("#btnClearBookmark").click(function(){if(typeof(hb)=="undefined")return;if(!confirm("清除書籤?"))return;hb.clearBookmarks();displayBookmarks();});$("#navigatePrev").click(function(){$topicTree.getPrevNode($topicTree.$selectedNode,true,true);});$("#navigateNext").click(function(){$topicTree.getNextNode($topicTree.$selectedNode,true,true);});$(".captionButton").hover(function(){$(this).css("text-decoration","underline");},function(){$(this).css("text-decoration","none");});$(".ui-layout-north").addClass("ui-widget-content ui-widget-header");$(".ui-layout-south").addClass("ui-widget-content ui-widget-header");$(".ui-layout-west").addClass("ui-widget-header");function checkParams(){var kw=location.href.match(/\?search(=([^\/\\&]+)(?:&(display|index)=(\d+))?)?$/i);if(kw){$("#searchText").val(kw[2]);var index=kw[4];onSearchFinished=function(){$resultTree.selectNodeByIndex(index,true);};return $("#searchButton").click();}
kw=location.href.match(/\?((topic|display|index)=)?((\d+)|(.+))$/i);if(kw){var index=kw[4];var href=kw[5];if(index){return $topicTree.selectNodeByIndex(index,true);}
if(href){return $topicTree.selectNodeByHref(href,true);}
}
return $topicTree.selectNodeByIndex(0,true);}checkParams();if(typeof(fixPNG)=="function")fixPNG();});