/* image hover zoom 
 * by Ryumin Dmitry
 * www.scriptcoder.ru
 * */

(function($) {
	$.fn.hoverZoom = function(options) {
		var defaults = {
			
        };
        var opts = $.extend(defaults, options);

        // plugin //

        function getPageScroll(){

        	var yScroll;

        	if (self.pageYOffset) {
        		yScroll = self.pageYOffset;
        	} else if (document.documentElement && document.documentElement.scrollTop){	 // Explorer 6 Strict
        		yScroll = document.documentElement.scrollTop;
        	} else if (document.body) {// all other Explorers
        		yScroll = document.body.scrollTop;
        	}

        	arrayPageScroll = new Array('',yScroll) 
        	return arrayPageScroll;
        }
        function getPageSize(){
        	
        	var xScroll, yScroll;
        	
        	if (window.innerHeight && window.scrollMaxY) {	
        		xScroll = document.body.scrollWidth;
        		yScroll = window.innerHeight + window.scrollMaxY;
        	} else if (document.body.scrollHeight > document.body.offsetHeight){ // all but Explorer Mac
        		xScroll = document.body.scrollWidth;
        		yScroll = document.body.scrollHeight;
        	} else { // Explorer Mac...would also work in Explorer 6 Strict, Mozilla and Safari
        		xScroll = document.body.offsetWidth;
        		yScroll = document.body.offsetHeight;
        	}
        	
        	var windowWidth, windowHeight;
        	if (self.innerHeight) {	// all except Explorer
        		windowWidth = self.innerWidth;
        		windowHeight = self.innerHeight;
        	} else if (document.documentElement && document.documentElement.clientHeight) { // Explorer 6 Strict Mode
        		windowWidth = document.documentElement.clientWidth;
        		windowHeight = document.documentElement.clientHeight;
        	} else if (document.body) { // other Explorers
        		windowWidth = document.body.clientWidth;
        		windowHeight = document.body.clientHeight;
        	}	
        	
        	// for small pages with total height less then height of the viewport
        	if(yScroll < windowHeight){
        		pageHeight = windowHeight;
        	} else { 
        		pageHeight = yScroll;
        	}

        	// for small pages with total width less then width of the viewport
        	if(xScroll < windowWidth){	
        		pageWidth = windowWidth;
        	} else {
        		pageWidth = xScroll;
        	}


        	arrayPageSize = new Array(pageWidth,pageHeight,windowWidth,windowHeight) 
        	return arrayPageSize;
        }
        
        var elem = this;
        
        //preload images
        $(elem).each(function(){
    		$("<img>").attr("src", this.href);
    	});
    	$(elem).click(function(){
    		return false;
    	});
    	var stopped = false;
    	var img_loaded = false;
    	$(elem).hover(function(){
    		stopped = false;
    		img_loaded = false;	
    		
    		var sizes = getPageSize();
    		var body_height = sizes[3];
    		
    		var thumb_offset = $(this).offset();
    		var thumb_width = $(this).width();
    		var thumb_height = $(this).height();
    		
    		var $img = new Image();
    		
    		$("body").append("<div class=\"img-zoom-block\" style=\"position:absolute;\"><span>Loading...</span></div>");
    		var container = $(".img-zoom-block");
    		
    		var container_left = 0;
    		var container_top = 0;
    		
    		var scrolles = getPageScroll();
    		
    		//set load message position
    		container_left = (thumb_offset.left + thumb_width/2) - 30;
    		container_top = (thumb_offset.top + thumb_height/2);
    		
    		container.css('left', container_left + 'px');
    		container.css('top', container_top + 'px');
    		
    		
    		$img.onload = function(){
    			img_loaded = true;
    			if (stopped)
    				return false;
    			//set image
    			$(container.children('span')[0]).replaceWith($img);			
    			var $img_width = $($img).width();
    			var $img_height = $($img).height();
    			
    			if( $img_height > body_height ){
    				$img.height = body_height - 100;
    				
    				$img_width = $($img).width();
    				$img_height = $($img).height();
    			}
    			
    			container_left = 0;
    			container_top = 0;
    			
    			scrolles = getPageScroll();
    			
    			container_left = (thumb_offset.left + thumb_width/2) - ($img_width/2);
    			if(container_left < 0)
    				container_left = 10;
    			container_top = (thumb_offset.top + thumb_height/2) - ($img_height/2);
    			
    			//if page scrolled then move down container
    			if(container_top < scrolles[1])
    				container_top = container_top + (scrolles[1] - container_top) + 0;
    			
    			var diff = (container_top + $img_height) - (body_height + scrolles[1]);
    			if(diff > 0){
    				container_top = container_top - diff - 10;
    			}
    			
    			container.css('left', container_left + 'px');
    			container.css('top', container_top + 'px');

    			container.hover(function(){
    				
    			},
    			function(){
    				stopped = true;
    				$(".img-zoom-block").remove();
    			});

    		};
    		
    		$img.src = this.href;

    	},
    	function(){
    		stopped = true;
    		if (!img_loaded){
    			$(".img-zoom-block").remove();
    		}
    	});	
        
    };	
})(jQuery); 