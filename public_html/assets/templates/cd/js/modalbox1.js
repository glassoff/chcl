DHTML_modalMessage = function()
{
	var url;								// url of modal message
	var htmlOfModalMessage;					// html of modal message
	
	var divs_transparentDiv;				// Transparent div covering page content
	var divs_content;						// Modal message div.
	var iframe;								// Iframe used in ie
	var layoutCss;							// Name of css file;
	var width;								// Width of message box
	var height;							// Height of message box
	
	var existingBodyOverFlowStyle;			// Existing body overflow css
	var dynContentObj;						// Reference to dynamic content object
	var cssClassOfMessageBox;				// Alternative css class of message box - in case you want a different appearance on one of them
	var shadowDivVisible;					// Shadow div visible ? 
	var shadowOffset; 						// X and Y offset of shadow(pixels from content box)
	var MSIE;
		
	this.url = '';							// Default url is blank
	this.htmlOfModalMessage = '';			// Default message is blank
	this.layoutCss = 'modal-message.css';	// Default CSS file
	this.height = 300;						// Default height of modal message
	this.width = 400;						// Default width of modal message
	this.cssClassOfMessageBox = false;		// Default alternative css class for the message box
	this.shadowDivVisible = true;			// Shadow div is visible by default
	this.shadowOffset = 5;					// Default shadow offset.
	this.MSIE = false;
	if(navigator.userAgent.indexOf('MSIE')>=0) this.MSIE = true;
};

DHTML_modalMessage.prototype = {
	// {{{ setSource(urlOfSource)
    /**
     *	Set source of the modal dialog box
     * 	
     *
     * @public	
     */		
	setSource : function(urlOfSource)
	{
		this.url = urlOfSource;
		
	}	
	// }}}	
	,
	// {{{ setHtmlContent(newHtmlContent)
    /**
     *	Setting static HTML content for the modal dialog box.
     * 	
     *	@param String newHtmlContent = Static HTML content of box
     *
     * @public	
     */		
	setHtmlContent : function(newHtmlContent)
	{
		this.htmlOfModalMessage = newHtmlContent;
		
	}
	// }}}		
	,	
	setSize : function(width,height)
	{
		if(width)this.width = width;
		if(height)this.height = height;		
	}
	// }}}		
	,		
	
	setCssClassMessageBox : function(newCssClass)
	{
		this.cssClassOfMessageBox = newCssClass;
		if(this.divs_content){
			if(this.cssClassOfMessageBox)
				this.divs_content.className=this.cssClassOfMessageBox;
			else
				this.divs_content.className='modalDialog_contentDiv';	
		}
					
	}
	// }}}		
	,	
	
	setShadowOffset : function(newShadowOffset)
	{
		this.shadowOffset = newShadowOffset
					
	}
	// }}}		
	,	
	
	display : function()
	{
		if(!this.divs_transparentDiv){
			this.__createDivs();
		}	
		
		// Redisplaying divs
		this.divs_transparentDiv.style.display='block';
		this.divs_content.style.display='block';
		this.divs_shadow.style.display='block';		
		if(this.MSIE)this.iframe.style.display='block';	
		this.__resizeDivs();
		
		/* Call the __resizeDivs method twice in case the css file has changed. The first execution of this method may not catch these changes */
		window.refToThisModalBoxObj = this;		
		setTimeout('window.refToThisModalBoxObj.__resizeDivs()',150);
		
		this.__insertContent();	// Calling method which inserts content into the message div.
	}
	// }}}		
	,

	setShadowDivVisible : function(visible)
	{
		this.shadowDivVisible = visible;
	}
	// }}}	
	,

	close : function()
	{
		this.divs_transparentDiv.style.display='none';
		this.divs_content.style.display='none';
		this.divs_shadow.style.display='none';
		if(this.MSIE)this.iframe.style.display='none';
		
	}	
	// }}}	
	,
	addEvent : function(whichObject,eventType,functionName,suffix)
	{ 
	  if(!suffix)suffix = '';
	  if(whichObject.attachEvent){ 
	    whichObject['e'+eventType+functionName+suffix] = functionName; 
	    whichObject[eventType+functionName+suffix] = function(){whichObject['e'+eventType+functionName+suffix]( window.event );} 
	    whichObject.attachEvent( 'on'+eventType, whichObject[eventType+functionName+suffix] ); 
	  } else 
	    whichObject.addEventListener(eventType,functionName,false); 	    
	} 
	// }}}	
	,
	__createDivs : function()
	{
		// Creating transparent div
		this.divs_transparentDiv = document.createElement('DIV');
		this.divs_transparentDiv.className='modalDialog_transparentDivs';
		this.divs_transparentDiv.style.left = '0px';
		this.divs_transparentDiv.style.top = '0px';
		
		document.body.appendChild(this.divs_transparentDiv);
		// Creating content div
		this.divs_content = document.createElement('DIV');
		this.divs_content.className = 'modalDialog_contentDiv';
		this.divs_content.id = 'DHTMLSuite_modalBox_contentDiv';
		this.divs_content.style.zIndex = 100000;
		
		if(this.MSIE){
			this.iframe = document.createElement('<IFRAME src="about:blank" frameborder=0>');
			this.iframe.style.zIndex = 90000;
			this.iframe.style.position = 'absolute';
			document.body.appendChild(this.iframe);	
		}
			
		document.body.appendChild(this.divs_content);
		// Creating shadow div
		this.divs_shadow = document.createElement('DIV');
		this.divs_shadow.className = 'modalDialog_contentDiv_shadow';
		this.divs_shadow.style.zIndex = 95000;
		document.body.appendChild(this.divs_shadow);
		window.refToModMessage = this;
		this.addEvent(window,'scroll',function(e){ window.refToModMessage.__repositionTransparentDiv() });
		this.addEvent(window,'resize',function(e){ window.refToModMessage.__repositionTransparentDiv() });
		

	}
	// }}}
	,	
	__getBrowserSize : function()
	{
    	var bodyWidth = document.documentElement.clientWidth;
    	var bodyHeight = document.documentElement.clientHeight;
    	
		var bodyWidth, bodyHeight; 
		if (self.innerHeight){ // all except Explorer 
		 
		   bodyWidth = self.innerWidth; 
		   bodyHeight = self.innerHeight; 
		}  else if (document.documentElement && document.documentElement.clientHeight) {
		   // Explorer 6 Strict Mode 		 
		   bodyWidth = document.documentElement.clientWidth; 
		   bodyHeight = document.documentElement.clientHeight; 
		} else if (document.body) {// other Explorers 		 
		   bodyWidth = document.body.clientWidth; 
		   bodyHeight = document.body.clientHeight; 
		} 
		return [bodyWidth,bodyHeight];		
		
	}
	,	
    __resizeDivs : function()
    {
    	
    	var topOffset = Math.max(document.body.scrollTop,document.documentElement.scrollTop);

		if(this.cssClassOfMessageBox)
			this.divs_content.className=this.cssClassOfMessageBox;
		else
			this.divs_content.className='modalDialog_contentDiv';	
			    	
    	if(!this.divs_transparentDiv)return;
    	
    	// Preserve scroll position
    	var st = Math.max(document.body.scrollTop,document.documentElement.scrollTop);
    	var sl = Math.max(document.body.scrollLeft,document.documentElement.scrollLeft);
    	
    	window.scrollTo(sl,st);
    	setTimeout('window.scrollTo(' + sl + ',' + st + ');',10);
    	this.__repositionTransparentDiv();    	
		var brSize = this.__getBrowserSize();
		var bodyWidth = brSize[0];
		var bodyHeight = brSize[1];    	
    	// Setting width and height of content div
      	this.divs_content.style.width = this.width + 'px';
    	this.divs_content.style.height= this.height + 'px';  	    	
    	var tmpWidth = this.divs_content.offsetWidth;	
    	var tmpHeight = this.divs_content.offsetHeight;
    	this.divs_content.style.left = Math.ceil((bodyWidth - tmpWidth) / 2) + 'px';;
    	this.divs_content.style.top = (Math.ceil((bodyHeight - tmpHeight) / 2) +  topOffset) + 'px';
    	
 		if(this.MSIE){
 			this.iframe.style.left = this.divs_content.style.left;
 			this.iframe.style.top = this.divs_content.style.top;
 			this.iframe.style.width = this.divs_content.style.width;
 			this.iframe.style.height = this.divs_content.style.height;
 		}
 		
    	this.divs_shadow.style.left = (this.divs_content.style.left.replace('px','')/1 + this.shadowOffset) + 'px';
    	this.divs_shadow.style.top = (this.divs_content.style.top.replace('px','')/1 + this.shadowOffset) + 'px';
    	this.divs_shadow.style.height = tmpHeight + 'px';
    	this.divs_shadow.style.width = tmpWidth + 'px';  	
    	if(!this.shadowDivVisible)this.divs_shadow.style.display='none';	// Hiding shadow if it has been disabled
    	
    	
    }
    // }}}	
    ,
   
    __repositionTransparentDiv : function()
    {
    	this.divs_transparentDiv.style.top = Math.max(document.body.scrollTop,document.documentElement.scrollTop) + 'px';
    	this.divs_transparentDiv.style.left = Math.max(document.body.scrollLeft,document.documentElement.scrollLeft) + 'px';
		var brSize = this.__getBrowserSize();
		var bodyWidth = brSize[0];
		var bodyHeight = brSize[1];
    	this.divs_transparentDiv.style.width = bodyWidth + 'px';
    	this.divs_transparentDiv.style.height = bodyHeight + 'px';		
		   	
    }
	// }}}	
	,

    __insertContent : function()
    {
		if(this.url){	// url specified - load content dynamically
			ajax_loadContent('DHTMLSuite_modalBox_contentDiv',this.url);
		}else{	// no url set, put static content inside the message box
			this.divs_content.innerHTML = this.htmlOfModalMessage;	
		}
    }		
}

messageObj = new DHTML_modalMessage();	// We only create one object of this class
messageObj.setShadowOffset(5);	// Large shadow

function displayStaticMessage(messageContent,cssClass,width,height)
{
	messageObj.setHtmlContent(messageContent);
	messageObj.setSize(width,height);
	messageObj.setCssClassMessageBox('modalDialog_contentDiv_login');
	messageObj.setSource(false);	// no html source since we want to use a static message here.
	messageObj.setShadowDivVisible(false);	// Disable shadow for these boxes	
	messageObj.display();
}

function closeMessage()
{
	messageObj.close();	
}
