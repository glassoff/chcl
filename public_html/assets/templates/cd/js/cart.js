jQuery(function(){
	if (typeof(opt_price)=='undefined'){
		opt_price = 0;
	}
	if (typeof(opt_prices)=='undefined'){
		opt_prices = {};
	}	
	if (typeof(package_price)=='undefined'){
		package_price = 0;
	}
	if (typeof(package_prices)=='undefined'){
		package_prices = {};
	}	
	if (typeof(update_cart_url)=='undefined'){
		update_cart_url = "cabinet/2958";
	}	
	
	if(typeof(site_url)=='undefined'){
		  var site_url = jQuery('base').size()>0 ? jQuery('base:first').attr('href')
		  : window.location.protocol+'//'+window.location.host+'/';
		}

	//buy tabs
	$('ul.buy-tabs__header').each(function() {
		$(this).find('li').each(function(i) {
		  $(this).click(function(){
		    $(this).addClass('current').siblings().removeClass('current')
		      .parents('div.buy-tabs').find('div.buy-tabs__content').hide().end().find('div.buy-tabs__content:eq('+i+')').fadeIn(150);
			setTabCookie();
		  });
		});
	});	
	
	
	// addition to cart
	jQuery("form#cart-form, form#cart-form-retail").submit(function(){
		var el = jQuery("input[type='submit'],input[type='image'],button[type='submit']",this).eq(0);

		jQuery('#noselected').remove();
		var tovar_selected = false;
		jQuery('.opt-table input.cart-count').each(function(){
			if ( jQuery(this).val() > 0 ){
				tovar_selected = true;
				return false;
			}    
		});
		if(!tovar_selected){
			el.after('<div id="noselected" class="message">Укажите количество единиц товара</div>');
			return false;	
		}

		var cartdata = {};
		cartdata = jQuery(this).serializeArray();
		jQuery.post("cabinet/2958", cartdata, function(data){
			updateCartSidebar();
			showHelper(el, data);
			setTimeout ( function(){jQuery("#stuffHelper").hide();}, 1000 ); 
			jQuery('.opt-table input.cart-count').each(function(){
				jQuery(this).val(0);    
			});
			jQuery('.opt-table span[id^="cost_"]').each(function(){
				jQuery(this).html(0);    
			});			
		});		
		
		return false;
	});
	
	jQuery("form#cart-form-retail").submit(function(){
		var el = jQuery("input[type='submit'],input[type='image'],button[type='submit']",this).eq(0);

		jQuery('#noselected').remove();
		var tovar_selected = false;
		/*jQuery('.opt-table input.cart-count').each(function(){
			if ( jQuery(this).val() > 0 ){
				tovar_selected = true;
				return false;
			}    
		});*/
		tovar_selected = true;
		
		if(!tovar_selected){
			el.after('<div id="noselected" class="message">Укажите количество единиц товара</div>');
			return false;	
		}

		var cartdata = {};
		cartdata = jQuery(this).serializeArray();
		jQuery.post("cabinet/2958", cartdata, function(data){
			updateCartSidebar();
			showHelper(el, data);
			setTimeout ( function(){jQuery("#stuffHelper").hide();}, 1000 ); 
			/*jQuery('.opt-table input.cart-count').each(function(){
				jQuery(this).val(0);    
			});
			jQuery('.opt-table span[id^="cost_"]').each(function(){
				jQuery(this).html(0);    
			});	*/		
		});		
		
		return false;
	});	
	
	var cartElem = jQuery("div.korzina form:first");

	function updateCart(params){
		var cartdata = {};
		cartdata = jQuery("div.korzina form:first").serializeArray();
		
		cartdata = cartdata.concat(params);
		
		var orderData = $('form[name=orderform]').serializeArray();
		if(orderData){
			cartdata = cartdata.concat(orderData);
		}
		
		if(cartdata.length <= 0) 
			return false;
		showLoading(true, $('.kor_objects table:first'));
		
		jQuery.post(update_cart_url, cartdata, function(data){
			updateCartSidebar();
			jQuery('#cart-content').eq(0).html(data);
			setCounter();
			cartRowHover();
			showLoading(false);
		});		
	}
	
	cartElem.live("submit", function(){
		updateCart();
		return false;
	});
	
	cartElem.find('select').live('change', function(){
		updateCart();
	});
	
	$('.cart-del-link', cartElem).live('click', function(){
		updateCart([{name: 'cart['+$(this).data('id')+'][remove]', value: 1}]);
		return false;
	});
	
	jQuery.fn.setCounterToField = function(opt){
		  st = jQuery.extend({style:'default',wrapdiv:false}, opt);
		  var imgpath = site_url+'assets/snippets/ecart/img/';
		  function checkKey(e){
		    var key_code = e.which ? e.which : e.keyCode;
		    return (key_code>47&&key_code<58)||key_code==8 ? true : false;
		  };
		  function changeCount(field,action){
                    jQuery('#noselected').remove();
		    var count = parseInt(jQuery(field).attr('value'));
		    var num = action==1 ? count+1 : count-1;
		    if(num>=0){
		      jQuery(field).val(num);
		      onCountChange(field);
		    }
		  };
		  var countButs = '<img class="field-arr-up" src="'+imgpath+'arr_up.gif" width="17" height="9" alt="" />'
		                + '<img class="field-arr-down" src="'+imgpath+'arr_down.gif" width="17" height="9" alt="" />'+"\n";
		  var field = jQuery(this);
		  if(st.wrapdiv)
		    jQuery(this).wrap('<div></div>');
		  jQuery(this)
		  .css({'height':'16px','border':'1px solid #888','vertical-align':'bottom','text-align':'center','padding':'1px 2px','font-size':'13px','width':'30px'})
		  .after(countButs)
		  .keypress(function(e){return checkKey(e);});
		  jQuery(this).next('img').click(function(){
		    changeCount(field,1);
		    updateCart();
		  })
		  .css({'cursor':'pointer','margin':'0 0 11px 1px','vertical-align':'bottom'})
		  .next('img').click(function(){
		    changeCount(field,2);
		    updateCart();
		  })
		  .css({'cursor':'pointer','margin':'0 0 1px -17px','vertical-align':'bottom'});
		};

	setCounter();
	cartRowHover();
	
	function setCounter(){
		var count_inputs = jQuery("input.cart-count");
		count_inputs.each(function(){
			jQuery(this).setCounterToField();
		});
		
		count_inputs.change(function(){
			onCountChange(this);
		});		
	}

	
	// on reload page
	jQuery("input.cart-count").each(function(){
		onCountChange(this);
	});
	
	/* process order form */
	var orderForm = $('form[name=orderform]');
	
	function updateOrderForm(){
		updateCart();			
	}
	
	if(orderForm.length){
		$('select, input[type=radio]', orderForm).live('change', function(){
			/*if($(this).attr('name')=='order[region]'){
				if($('#_region-'+$(this).val()).length){
					$('#_region-'+$(this).val()).atrr('checked', 'checked')
				}
				else{
					$('#_region-no').attr('checked', 'checked');
				}
			}*/
			updateOrderForm();
		});
		
		$('select, input', orderForm).live('change', function(){
			window.onbeforeunload = function(){return 'Вы не завершили оформление заказа.';};
		});	
		
		orderForm.live('submit', function(){
			window.onbeforeunload = null;
		});
	}
	
	//colors selector
	$('.colors-selector').each(function(){
		var container = $('ul', this);
		var input = $(this).children('input').get(0);
		var colors = container.children('li');
		
		$(input).val(container.children('li.act').attr('rel'));
		
		$(colors).click(function(){
			$(colors).removeClass('act');
			$(this).addClass('act');
			
			$(input).val($(this).attr('rel'));
		});	
		
	});

});

var shkHelper = '<div id="stuffHelper" style="border-radius: 10px; -o-border-radius:10px; -moz-border-radius: 10px; -webkit-border-radius: 10px; -khtml-border-radius: 10px;">'
+'<div><b id="stuffHelperName"></b></div>'
+'</div>';

function showHelper(elem,name){
  jQuery('#stuffHelper').remove();
  jQuery('body').append(shkHelper);

  var elHelper = jQuery('#stuffHelper');
  var btPos = getCenterPos(elHelper,elem);
  jQuery('#stuffHelperName').html(name);

  jQuery('#stuffHelper').css({'top':btPos.y+'px','left':btPos.x+'px'}).fadeIn(500);
};

function getPosition(elem){
	  var el = jQuery(elem).get(0);
		var p = {x: el.offsetLeft, y: el.offsetTop};
		while (el.offsetParent){
			el = el.offsetParent;
			p.x += el.offsetLeft;
			p.y += el.offsetTop;
			if (el != document.body && el != document.documentElement){
				p.x -= el.scrollLeft;
				p.y -= el.scrollTop;
			}
		}
		return p;
	};
	
function getCenterPos(elA,elB){
	  posB = new Object();
	  cntPos = new Object();
	  posB = getPosition(elB);
	  var correct;
	  cntPos.y = Math.round((jQuery(elB).outerHeight()-jQuery(elA).outerHeight())/2)+posB.y;
	  cntPos.x = Math.round((jQuery(elB).outerWidth()-jQuery(elA).outerWidth())/2)+posB.x;
	  if(cntPos.x+jQuery(elA).outerWidth()>jQuery(window).width()){
	    cntPos.x = Math.round(jQuery(window).width()-jQuery(elA).outerWidth())-2;
	  }
	  if(cntPos.x<0){
	    cntPos.x = 2;
	  }
	  return cntPos;
	};

function showLoading(show, container){
	if(show==true){
	  $('body').append('<div id="shkLoading"></div>');
	  var loader = $('#shkLoading');
	  var shopCart = $(container);
	  var btPos = getCenterPos(loader,shopCart);
	  $('#shkLoading').css({'top':btPos.y+'px','left':btPos.x+'px'}).fadeIn(300);
	}else{
	  $('#shkLoading').fadeOut(300,function(){
	    $(this).remove();
	  });
	}
}

function onCountChange(elem){
	if (opt_price){
		if(package_price)
			var price_item = package_price;
		else
			var price_item = opt_price;
		
		var name = jQuery(elem).attr('name');
		var reg = /items\[(\d+)\]\[quantity\]/;
		var arr = reg.exec(name);
		var row_id = arr[1];
		
		var size = jQuery("input[name='items["+row_id+"][size_z]']").val();
		if(size){
			size = size;
			//alert("|"+size+"|");
			if( opt_prices[size] > 0 ){
				if(package_price)
					price_item = package_prices[size];
				else
					price_item = opt_prices[size];
			}
		}
		
		var new_cost;
		new_cost = jQuery(elem).val() * price_item;
		
		jQuery("span#cost_"+row_id).html(new_cost);
	}
}

function updateCartSidebar(){
	jQuery("#cartsidebar").load("cabinet/2961");
}

function cartRowHover(){
	jQuery(".cartRowTitle").each(function(){
		var row_id = jQuery(this).attr('rel');
		jQuery(this).easyTooltip({useElement: jQuery("#cartRowHover_" + row_id).attr('id')});
	});	
}

jQuery(function(){
	//jQuery(".tovar-image").hoverZoom();
	jQuery(".tovar-image").jqzoom({
		zoomWidth: 400,
		zoomHeight: 352,
		xOffset: 7,
		position: 'right',
		preloadText: 'Загрузка...'
	});
	
	$(".scrollable").scrollable();
});

function setTabCookie(){
	var activeTab = $('.buy-tabs .buy-tabs__header li.current').attr('id');
	
    var current_date = new Date;
	var expires = new Date ( current_date.getFullYear() + 1, current_date.getMonth(), current_date.getDate() );
		
	document.cookie = "buytab="+activeTab+";" + "; expires=" + expires.toGMTString();
}
function getTabCookie(){
	var results = document.cookie.match ( '(^|;) ?buytab=([^;]*)(;|$)' );
	return results ? unescape(results[2]) : false;
}

/* order process */
jQuery(function(){

});
