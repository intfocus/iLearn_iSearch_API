// JavaScript Document

	
//CSS3 PIE
$(function() {
	if (window.PIE) {
		$('.logout, .creditW, span.buy, .type, .bLink, .funcBtn').each(function() {
			PIE.attach(this);
		});
	}
});