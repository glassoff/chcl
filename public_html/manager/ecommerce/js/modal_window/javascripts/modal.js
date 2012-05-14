
function showBox(){
    Element.show($('overlay'));
    center('box');
    return false;
}

function hideBox(){
    Element.hide($('box'));
    Element.hide($('overlay'));
    return false;
}

function center(element){
	element = $(element);
    var my_width  = 0;
    var my_height = 0;

    if ( typeof( window.innerWidth ) == 'number' ){
        my_width  = window.innerWidth;
        my_height = window.innerHeight;
    }else if ( document.documentElement && 
             ( document.documentElement.clientWidth ||
               document.documentElement.clientHeight ) ){
        my_width  = document.documentElement.clientWidth;
        my_height = document.documentElement.clientHeight;
    }
    else if ( document.body && 
            ( document.body.clientWidth || document.body.clientHeight ) ){
        my_width  = document.body.clientWidth;
        my_height = document.body.clientHeight;
    }
    document.getElementById('box').style.position = 'absolute';
    document.getElementById('box').style.zIndex   = 99;
    var scrollY = 0;

    if ( document.documentElement && document.documentElement.scrollTop ){
        scrollY = document.documentElement.scrollTop;
    }else if ( document.body && document.body.scrollTop ){
        scrollY = document.body.scrollTop;
    }else if ( window.pageYOffset ){
        scrollY = window.pageYOffset;
    }else if ( window.scrollY ){
        scrollY = window.scrollY;
    }
    var elementDimensions = originalDim(document.getElementById('box'));
    var setX = ( my_width  - elementDimensions.width  ) / 2;
	//var setX = (my_width - document.getElementById('box').style.width) / 2;
    var setY = ( my_height - elementDimensions.height ) / 2 + scrollY;
	//var setY = (my_height - document.getElementById('box').style.height) / 2 + scrollY;
    //var setX = ( setX < 0 ) ? 0 : setX;
    //var xsetY = ( setY < 0 ) ? 0 : setY;
    document.getElementById('box').style.left = setX + "px";
    document.getElementById('box').style.top  = setY + "px";
    document.getElementById('box').style.display  = 'block';
}

function originalDim(element) {
	
	 var els = element.style;
 	 var originalVisibility = els.visibility;
 	    var originalPosition = els.position;
 	    els.visibility = 'hidden';
 	    els.position = 'absolute';
	    els.display = '';
	    var originalWidth = element.clientWidth;
 	    var originalHeight = element.clientHeight;
 	    els.display = 'none';
	    els.position = originalPosition;
 	    els.visibility = originalVisibility;
	    return {width: originalWidth, height: originalHeight};
	
}