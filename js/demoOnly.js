// JavaScript Document
function Toshow(i){
	document.getElementById(i).style.display ="block"
}
function Nonshow(i){
	document.getElementById(i).style.display ="none"
}
function toggleDialog(i) {
	var dialog = document.getElementById(i)
	if (dialog.style.display !='block') {
		dialog.style.display ='block'		
		}
	else {
		dialog.style.display ="none"
		}	
}
function clickNormal() {
	$("#normal").removeClass("hide")
	$("#certified").addClass("hide")
	$("#normalBtn").addClass("tabActive")
	$("#certiBtn").removeClass("tabActive")
	$("#down1").removeClass("hide")
	$("#down2").addClass("hide")
}
function clickCertificate() {
	$("#normal").addClass("hide")
	$("#certified").removeClass("hide")
	$("#normalBtn").removeClass("tabActive")
	$("#certiBtn").addClass("tabActive")
	$("#down2").removeClass("hide")
	$("#down1").addClass("hide")
}