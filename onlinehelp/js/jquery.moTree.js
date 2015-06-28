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

(function($){$.fn.moTree=function(newOptions){var moTree=this;var options=$.extend({},$.fn.moTree.defaultOptions,newOptions);moTree.$selectedNode=null;moTree.$nodes=null;moTree.playAnimation=function($nodeLi){var isTreeHidden=moTree.css("display")==="none";if(!options.nodeAnimation)return;if(!$nodeLi){if(options.autoCollapse){$(".moTreeExpanded>ul",moTree).each(function(){if($(".selectedNode",this).length==0){(isTreeHidden)?$(this).hide()
:$(this).animate({height:"hide"},"slow");}
});}
if(moTree.$selectedNode){moTree.$selectedNode.parents(".moTreeCollapsed>ul").animate({height:"show"},"slow");$(">ul",moTree.$selectedNode.parent()).animate({height:"show"},"slow");}
}
else{$(">ul",$nodeLi).animate({height:"toggle"},"slow");}
}
moTree.selectNodeByHref=function(href,isFireClickEvent){var filter="a[href='%s']".replace(/%s/,href);var $node=$(filter,moTree);(isFireClickEvent)?$node.click()
:moTree.selectNode($node);return $node;}
moTree.selectNodeByIndex=function(index,isFireClickEvent){if(index<0||index>=moTree.$nodes.length-1)return null;var $node=moTree.$nodes.eq(index);while(index<moTree.$nodes.length-1&&$node.attr("href")==options.nodeEmptyHref){index++;$node=moTree.$nodes.eq(index);}
if(index<moTree.$nodes.length-1){(isFireClickEvent)?$node.click()
:moTree.selectNode($node);return $node;}
return null;}
moTree.expandNode=function($node){moTree.playAnimation();if(options.autoCollapse){$(".moTreeExpanded",moTree).toggleClass("moTreeExpanded").toggleClass("moTreeCollapsed");}
$node.parents(".moTreeCollapsed").toggleClass("moTreeExpanded").toggleClass("moTreeCollapsed");}
moTree.selectNode=function($node){if(moTree.$selectedNode){moTree.$selectedNode.toggleClass("selectedNode");}
moTree.$selectedNode=$node;moTree.$selectedNode.toggleClass("selectedNode");moTree.expandNode($node);return moTree;}
moTree.getNextNode=function($node,isRequireHref,isFireClickEvent){if(!$node)$node=moTree.$selectedNode;var index=$node?moTree.$nodes.index($node)+1
:-1;if(index<0||index>moTree.$nodes.length-1)return null;$node=moTree.$nodes.eq(index);while(isRequireHref&&$node.attr("href")==options.nodeEmptyHref){index++;if(index>moTree.$nodes.length-1)return null;$node=moTree.$nodes.eq(index);}
if(isFireClickEvent)$node.click();return $node;}
moTree.getPrevNode=function($node,isRequireHref,isFireClickEvent){if(!$node)$node=moTree.$selectedNode;var index=$node?moTree.$nodes.index($node)-1
:-1;if(index<0||index>moTree.$nodes.length-1)return null;$node=moTree.$nodes.eq(index);while(isRequireHref&&$node.attr("href")==options.nodeEmptyHref){index--;if(index<0)return null;$node=moTree.$nodes.eq(index);}
if(isFireClickEvent)$node.click();return $node;}
return this.each(function(){$("li",this).each(function(){($("ul",this).length<=0)?$(this).addClass("moTreeTopic")
:(options.initExpanded)?$(this).addClass("moTreeExpanded")
:$(this).addClass("moTreeCollapsed");});moTree.$nodes=$("a",this);moTree.$nodes.each(function(){if(options.nodeTooltip&&!$(this).attr("title")){$(this).attr("title",$(this).text());}
if(options.onNodeCreate){options.onNodeCreate($(this));}
}).before('<img class="moTreeIcon" src="'+options.imagePath+'/moTreeN.gif"/>');$("li:has(ul) .moTreeIcon",this).click(function(){moTree.playAnimation($(this).parent());$(this).parent().toggleClass("moTreeExpanded").toggleClass("moTreeCollapsed");});$("a",this).click(function(){moTree.selectNode($(this));if(options.targetContainer){var href=$(this).attr("href");if(href!=options.nodeEmptyHref){href=href.replace(/#.*$/g,"");$(options.targetContainer).load(href);}
}
if(options.onClick){options.onClick($(this));}
if(options.targetContainer||options.onClick){return false;}
});if(options.autoSelectFirst){moTree.$nodes.first().click();}
});}
$.fn.moTree.defaultOptions={initExpanded:false
,autoCollapse:false
,autoSelectFirst:false
,nodeAnimation:true
,nodeTooltip:false
,nodeEmptyHref:"#"
,targetContainer:null
,onClick:null
,onNodeCreate:null
,imagePath:"css/images"
};})(jQuery);