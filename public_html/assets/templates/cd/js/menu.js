if (navigator.appName=="Netscape") var br = 'netscape';
if (navigator.appVersion.indexOf("MSIE")!=-1) var br = 'ie';
if (navigator.userAgent.indexOf("Opera")!=-1) var br = 'opera';
if (navigator.userAgent.indexOf("Firefox")!=-1) var br = 'ff';
var actualwidth=0;
var menuwidth=970;
var menuheight=92;
var scrollspeed=14;
var lefttime = null;
var righttime = null;
switch (br) {
    case 'ff':{
        cross_scroll= document.getElementById("test2");
        actualwidth = document.getElementById("buttons").clientWidth;
    };break;    
    case 'ie':{
        cross_scroll= document.getElementById("test2");
        actualwidth = document.getElementById("buttons").clientWidth;
    };break;            
    case 'netscape': {
        cross_scroll= document.test2;
        actualwidth = document.buttons.width;
    };break;    
    case 'opera':{ 
        cross_scroll= document.getElementById("test2");
        actualwidth = document.getElementById("buttons").clientWidth;
    };break;    
}
function moveleft(){
    
    switch (br) {    
        case 'ff':{
            if (parseInt(cross_scroll.style.left) > (menuwidth-actualwidth))  {
                cross_scroll.style.left = parseInt(cross_scroll.style.left)-scrollspeed+"px";
            } else {
                cross_scroll.style.left = parseInt(menuwidth-actualwidth)+"px";
            }
        };break;    
        case 'ie':{
            if (parseInt(cross_scroll.style.left) > (menuwidth-actualwidth)) {
                cross_scroll.style.left = parseInt(cross_scroll.style.left)-scrollspeed+"px";
            } else {
                cross_scroll.style.left = parseInt(menuwidth-actualwidth)+"px";
            }  
        };break;           
        case 'netscape':{
            if (parseInt(cross_scroll.left) > (menuwidth-actualwidth)) {
                cross_scroll.left = parseInt(cross_scroll.left)-scrollspeed+"px";
            } else {
                cross_scroll.left = parseInt(menuwidth-actualwidth)+"px";
            }  
        };break;    
        case 'opera':{
            if (parseInt(cross_scroll.style.left) > (menuwidth-actualwidth)) {
                cross_scroll.style.left = parseInt(cross_scroll.style.left)-scrollspeed+"px";
            } else {
                cross_scroll.style.left = parseInt(menuwidth-actualwidth)+"px";
            } 
        };break;    
    } 
    lefttime = setTimeout("moveleft()",50);
}

function moveright(){
    switch (br) {
        case 'ff':{
            if (parseInt(cross_scroll.style.left)<0) {
                cross_scroll.style.left=parseInt(cross_scroll.style.left)+scrollspeed+"px";
            } else {
                cross_scroll.style.left = '0px';
            }
        };break;    
        case 'ie':{
            if (parseInt(cross_scroll.style.left)<0) {
                cross_scroll.style.left=parseInt(cross_scroll.style.left)+scrollspeed+"px";
            } else {
                cross_scroll.style.left = '0px';
            }  
        };break;           
        case 'netscape':{
            if (parseInt(cross_scroll.left)<0) {
                cross_scroll.left=parseInt(cross_scroll.left)+scrollspeed+"px";
            } else {
                cross_scroll.left = '0px';
            }
        };break;
        case 'opera':{
            if (parseInt(cross_scroll.style.left)<0) {
                cross_scroll.style.left=parseInt(cross_scroll.style.left)+scrollspeed+"px";
            } else {
                cross_scroll.style.left = '0px';
            }
        };break;
    }    
    righttime=setTimeout("moveright()",50);
}