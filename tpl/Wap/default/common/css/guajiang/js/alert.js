document.writeln("<style type=\"text\/css\">");
document.writeln("");
document.writeln(".window {");
document.writeln("	width:290px;");
document.writeln("	position:fixed;");
document.writeln("	display:none;");
document.writeln("	bottom:30px;");
document.writeln("	left:50%;");
document.writeln("	 z-index:9999;");
document.writeln("	margin:-50px auto 30px -145px;");
document.writeln("	padding:2px;");
document.writeln("	border-radius:0.6em;");
document.writeln("	-webkit-border-radius:0.6em;");
document.writeln("	-moz-border-radius:0.6em;");
document.writeln("	background-color: #ffffff;");
document.writeln("	-webkit-box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);");
document.writeln("	-moz-box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);");
document.writeln("	-o-box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);");
document.writeln("	box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);");
document.writeln("	font:14px\/1.5 Microsoft YaHei,Helvitica,Verdana,Arial,san-serif;");
document.writeln("}");
document.writeln(".window .title {");
document.writeln("	");
document.writeln("	background-color: #A3A2A1;");
document.writeln("	line-height: 26px;");
document.writeln("    padding: 5px 5px 5px 10px;");
document.writeln("	color:#ffffff;");
document.writeln("	font-size:16px;");
document.writeln("	border-radius:0.5em 0.5em 0 0;");
document.writeln("	-webkit-border-radius:0.5em 0.5em 0 0;");
document.writeln("	-moz-border-radius:0.5em 0.5em 0 0;");
document.writeln("	background-image: -webkit-gradient(linear, left top, left bottom, from( #585858 ), to( #565656 )); \/* Saf4+, Chrome *\/");
document.writeln("	background-image: -webkit-linear-gradient(#585858, #565656); \/* Chrome 10+, Saf5.1+ *\/");
document.writeln("	background-image:    -moz-linear-gradient(#585858, #565656); \/* FF3.6 *\/");
document.writeln("	background-image:     -ms-linear-gradient(#585858, #565656); \/* IE10 *\/");
document.writeln("	background-image:      -o-linear-gradient(#585858, #565656); \/* Opera 11.10+ *\/");
document.writeln("	background-image:         linear-gradient(#585858, #565656);");
document.writeln("	");
document.writeln("}");
document.writeln(".window .content {");
document.writeln("	\/*min-height:100px;*\/");
document.writeln("	overflow:auto;");
document.writeln("	padding:10px;");
document.writeln("	background: linear-gradient(#FBFBFB, #EEEEEE) repeat scroll 0 0 #FFF9DF;");
document.writeln("    color: #222222;");
document.writeln("    text-shadow: 0 1px 0 #FFFFFF;");
document.writeln("	border-radius: 0 0 0.6em 0.6em;");
document.writeln("	-webkit-border-radius: 0 0 0.6em 0.6em;");
document.writeln("	-moz-border-radius: 0 0 0.6em 0.6em;");
document.writeln("}");
document.writeln(".window #txt {");
document.writeln("	min-height:30px;font-size:16px; line-height:22px;");
document.writeln("}");
document.writeln(".window .txtbtn {");
document.writeln("	");
document.writeln("	background: #f1f1f1;");
document.writeln("	background-image: -webkit-gradient(linear, left top, left bottom, from( #DCDCDC ), to( #f1f1f1 )); \/* Saf4+, Chrome *\/");
document.writeln("	background-image: -webkit-linear-gradient( #ffffff , #DCDCDC ); \/* Chrome 10+, Saf5.1+ *\/");
document.writeln("	background-image:    -moz-linear-gradient( #ffffff , #DCDCDC ); \/* FF3.6 *\/");
document.writeln("	background-image:     -ms-linear-gradient( #ffffff , #DCDCDC ); \/* IE10 *\/");
document.writeln("	background-image:      -o-linear-gradient( #ffffff , #DCDCDC ); \/* Opera 11.10+ *\/");
document.writeln("	background-image:         linear-gradient( #ffffff , #DCDCDC );");
document.writeln("	border: 1px solid #CCCCCC;");
document.writeln("	border-bottom: 1px solid #B4B4B4;");
document.writeln("	color: #555555;");
document.writeln("	font-weight: bold;");
document.writeln("	text-shadow: 0 1px 0 #FFFFFF;");
document.writeln("	border-radius: 0.6em 0.6em 0.6em 0.6em;");
document.writeln("	display: block;");
document.writeln("	width: 100%;");
document.writeln("	box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);");
document.writeln("	text-overflow: ellipsis;");
document.writeln("	white-space: nowrap;");
document.writeln("	cursor: pointer;");
document.writeln("	text-align: windowcenter;");
document.writeln("	font-weight: bold;");
document.writeln("	font-size: 18px;");
document.writeln("	padding:6px;");
document.writeln("	margin:10px 0 0 0;");
document.writeln("}");
document.writeln(".window .txtbtn:visited {");
document.writeln("	background-image: -webkit-gradient(linear, left top, left bottom, from( #ffffff ), to( #cccccc )); \/* Saf4+, Chrome *\/");
document.writeln("	background-image: -webkit-linear-gradient( #ffffff , #cccccc ); \/* Chrome 10+, Saf5.1+ *\/");
document.writeln("	background-image:    -moz-linear-gradient( #ffffff , #cccccc ); \/* FF3.6 *\/");
document.writeln("	background-image:     -ms-linear-gradient( #ffffff , #cccccc ); \/* IE10 *\/");
document.writeln("	background-image:      -o-linear-gradient( #ffffff , #cccccc ); \/* Opera 11.10+ *\/");
document.writeln("	background-image:         linear-gradient( #ffffff , #cccccc );");
document.writeln("}");
document.writeln(".window .txtbtn:hover {");
document.writeln("	background-image: -webkit-gradient(linear, left top, left bottom, from( #ffffff ), to( #cccccc )); \/* Saf4+, Chrome *\/");
document.writeln("	background-image: -webkit-linear-gradient( #ffffff , #cccccc ); \/* Chrome 10+, Saf5.1+ *\/");
document.writeln("	background-image:    -moz-linear-gradient( #ffffff , #cccccc ); \/* FF3.6 *\/");
document.writeln("	background-image:     -ms-linear-gradient( #ffffff , #cccccc ); \/* IE10 *\/");
document.writeln("	background-image:      -o-linear-gradient( #ffffff , #cccccc ); \/* Opera 11.10+ *\/");
document.writeln("	background-image:         linear-gradient( #ffffff , #cccccc );");
document.writeln("}");
document.writeln(".window .txtbtn:active {");
document.writeln("	background-image: -webkit-gradient(linear, left top, left bottom, from( #cccccc ), to( #ffffff )); \/* Saf4+, Chrome *\/");
document.writeln("	background-image: -webkit-linear-gradient( #cccccc , #ffffff ); \/* Chrome 10+, Saf5.1+ *\/");
document.writeln("	background-image:    -moz-linear-gradient( #cccccc , #ffffff ); \/* FF3.6 *\/");
document.writeln("	background-image:     -ms-linear-gradient( #cccccc , #ffffff ); \/* IE10 *\/");
document.writeln("	background-image:      -o-linear-gradient( #cccccc , #ffffff ); \/* Opera 11.10+ *\/");
document.writeln("	background-image:         linear-gradient( #cccccc , #ffffff );");
document.writeln("	border: 1px solid #C9C9C9;");
document.writeln("	border-top: 1px solid #B4B4B4;");
document.writeln("	box-shadow: 0 1px 4px rgba(0, 0, 0, 0.3) inset;");
document.writeln("}");
document.writeln("");
document.writeln(".window .title .close {");
document.writeln("	float:right;");
document.writeln("	background-image: url(\"data:image\/png;base64,iVBORw0KGgoAAAANSUhEUgAAABoAAAAaCAYAAACpSkzOAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsMAAA7DAcdvqGQAAACTSURBVEhL7dNtCoAgDAZgb60nsGN1tPLVCVNHmg76kQ8E1mwv+GG27cestQ4PvTZ69SFocBGpWa8+zHt\/Up+IN+MhgLlUmnIE1CpBQB2COZibfpnXhHFaIZkYph0SOeeK\/QJ8o7KOek84fkCWSBtfL+Ny2MPpCkPFMH6PWEhWhKncIyEk69VfiUuVhqJefds+YcwNbEwxGqGIFWYAAAAASUVORK5CYII=\");");
document.writeln("	width:26px;");
document.writeln("	height:26px;");
document.writeln("	display:block;	");
document.writeln("}");
document.writeln("<\/style>");
document.writeln("<div class=\"window\" id=\"windowcenter\">");
document.writeln("	<div id=\"title\" class=\"title\">消息提醒<span class=\"close\" id=\"alertclose\"><\/span><\/div>");
document.writeln("	<div class=\"content\">");
document.writeln("	 <div id=\"txt\"><\/div>");
document.writeln("	 <input type=\"button\" value=\"确定\" id=\"windowclosebutton\" name=\"确定\" class=\"txtbtn\">	");
document.writeln("	<\/div>");
document.writeln("<\/div>");
$(document).ready(function () { 

$("#windowclosebutton").click(function () { 
$("#windowcenter").slideUp(500);
}); 
$("#alertclose").click(function () { 
$("#windowcenter").slideUp(500);
}); 

}); 
function alert(title){ 
//var windowHeight; 
//var windowWidth; 
//var popWidth;  
//var popHeight; 
//windowHeight=$(window).height(); 
//windowWidth=$(window).width(); 
//popHeight=$(".window").height(); 
//popWidth=$(".window").width(); 
//var popY=(windowHeight-popHeight)/2; 
//var popX=(windowWidth-popWidth)/2; 
//$("#windowcenter").css("top",popY).css("left",popX).slideToggle("slow"); 
$("#windowcenter").slideToggle("slow"); 
$("#txt").html(title);
//$("#windowcenter").hide("slow"); 
setTimeout('$("#windowcenter").slideUp(500)',8000);
} 

