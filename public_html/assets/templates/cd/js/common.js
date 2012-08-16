//popups
$('[data-popup-open]').live('click', function(){

	var popupClass = $(this).data('popup-open');
	    popupElem = $('.' + popupClass),
		popupHandler = $(this).data('popup-handler'),
        popupObj = popupElem.data('popup');

    if (!popupObj) {
        if(popupHandler){
            popupObj = eval('new ' + popupHandler + '(popupElem, this)');
        } else{
            popupObj = new Popup(popupElem, this);
        }

        popupElem.data('popup', popupObj);
	}

	if (popupClass == 'b-popup_photo') {
	  popupObj.updateContent(this);
	}

	popupObj.show(this);

	return false;

});

/* Popup */

function Popup(container, element, onhide){

	var _this = this;

	this.container = $(container);
	this.overlay = $('.b-overlay');
	this.body = $(document.body);
	this.page = $('body');
	this.scrollbarWidth = this.getScrollbarWidth();
	this.content = $('.popup-content', this.container);
	this.element = element;
	this.onhide = onhide || function(){};

	this.container.live('click', function(e){
		_this.hide();
	});

	this.container.find('.b-popup__close').live('click', function(e){
		_this.hide();
	});

	this.container.find('.b-popup__wrap').live('click', function(e){
		$(document).click();
		e.stopPropagation();
	});

	// forms handler
	this.content.each(function(){
		var content = this;
		$(this).delegate('form', 'submit', function(){
			var form = this;
			var submit = $(form).find('[type=submit]:first');

			var data = $(form).serializeArray();
			data.push({name: $(submit).attr('name'), value: $(submit).attr('value')});

			$.ajax({
				url: $(form).attr('action'),
				type: $(form).attr('method'),
				data: data,
				success: function(data, textStatus, jqXHR){
					$(content).html(data);
				},
				error: function(){
					alert('Произошла неизвестная ошибка, попробуйте еще раз.');
				}
			});
			
			return false;
		});

	});

}

Popup.prototype.getScrollbarWidth = function(){

	var outer = document.createElement('div');
	outer.style.position = 'absolute';
	outer.style.top = '0';
	outer.style.left = '0';
	outer.style.visibility = 'hidden';
	outer.style.width = '200px';
	outer.style.height = '150px';
	outer.style.overflow = 'hidden';

	var inner = document.createElement('div');
	inner.style.width = '100%';
	inner.style.height = '200px';

	outer.appendChild (inner);
	document.body.appendChild (outer);

	var w1 = inner.offsetWidth;
	outer.style.overflow = 'scroll';

	var w2 = inner.offsetWidth;
	if (w1 === w2) {
		w2 = outer.clientWidth;
	}

	document.body.removeChild (outer);

	return (w1 - w2);

};

Popup.prototype.show = function(element){

	var _this = this;
	
	this.element = element;

	$(window).bind('resize.popup', function(){
		_this.resize();
	});

	this.resize();

	this.container.show();
	this.overlay.show();

};

Popup.prototype.hide = function(){

	$(window).unbind('resize.popup');

	if ($.browser.msie) {
		document.documentElement.style.overflowY = '';
	} else {
		document.body.style.overflowY = '';
	}

	this.page.width('');

	this.container.hide();
	this.overlay.hide();

    this.onhide(this);
};

Popup.prototype.resize = function(){

	var html = $(document.documentElement);

	if (document.documentElement.scrollHeight > document.documentElement.offsetHeight) {
		if (html.hasClass('ie8')) {
			this.page.width(this.body.width() - this.scrollbarWidth);
		} else {
			this.page.width(this.body.width());
		}
	} else {
		if (html.hasClass('ie7')) {
			this.page.width(this.body.width() - this.scrollbarWidth);
		} else {
			this.page.width(this.body.width());
		}
	}

	if ($.browser.msie) {
		document.documentElement.style.overflowY = 'hidden';
	} else {
		document.body.style.overflowY = 'hidden';
	}

};
