	function edituser(id){
		var poststr = "trxntype=" + encodeURI('edituserproperties') + "&custid=" + encodeURI(id);
		makePOSTRequest('../../assets/snippets/shoppingCart/admininterop.php',poststr, 'edituserproperties');
	}
	
	function passvalidator(){
		if (document.getElementById('editpassword').value != document.getElementById('editpasswordconfirm').value){
		   document.getElementById('passvalidatediv').innerHTML='<p style=\"color: red;\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Passwords don\'t match</p>';
		   document.getElementById('passvalidatediv').show();;
		   document.getElementById('updateform').hide();
		} else {
		   if (document.getElementById('editpassword').value.length >= 7){
		   	  document.getElementById('passvalidatediv').hide();
		   	  document.getElementById('updateform').show();
		   } else {
			  document.getElementById('passvalidatediv').innerHTML='<p style="color: red;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;There is a 7 character minimum password length.</p>';
		   	  document.getElementById('passvalidatediv').show();
			  document.getElementById('updateform').hide();
		   }
		}
	}
	
	function ordermoredetail(id){
		var poststr = "trxntype=" + encodeURI('vieworderinfo') + "&orderid=" + encodeURI(id);
		makePOSTRequest('../../assets/snippets/shoppingCart/admininterop.php',poststr, 'vieworderinfo');
	}
	
	function updateordergrid(){
		var poststr = "trxntype=" + encodeURI('updateordersgrid') +
					  "&sortordersby=" + encodeURI(document.getElementById('filterbydropdown').value);
		makePOSTRequest('../../assets/snippets/shoppingCart/admininterop.php',poststr, 'updateordersgrid');
	}
	
	function createAllSortables() {
		sections = ['chosenoptionsdiv','alloptionsdiv'];
		Sortable.create('chosenoptionsdiv', {tag:'div',dropOnEmpty:true, containment:sections,only:'alloptdivs'});
		Sortable.create('alloptionsdiv',{tag:'div',dropOnEmpty:true, containment:sections,only:'alloptdivs'});
	}

	function urival(id) {
		return encodeURI(document.getElementById(id).value);
	}
	
	
				
	function makePOSTRequest(url,parameters, name) {
		http_request = false;
		if (window.XMLHttpRequest){
		   http_request = new XMLHttpRequest();
		   if (http_request.overrideMimeType){
		   	  http_request.overrideMimeType('text/html');
		   }
		}else if (window.ActiveXObject){
			  try {
					http_request = new ActiveXObject("Msxml2.XMLHTTP");
			  } catch (e) {
			  	try {
					http_request = new ActiveXObject("Microsoft.XMLHTTP");
				} catch (e){}
			  }
		}
		if (!http_request) {
		   alert('Cannot create XMLHTTP instance');
		   return false;
		}

		http_request.open('POST', url, true);
		http_request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		http_request.setRequestHeader("Content-length", parameters.length);
		http_request.setRequestHeader("Connection", "close");
		http_request.send(parameters);
		nameval=name;
		if (navigator.appName=="Microsoft Internet Explorer"){
			http_request.onreadystatechange = new Function(alertContents());
		} else {
			http_request.onreadystatechange= alertContents();
		}
	}
	
	function makePOSTRequest1(url,parameters, name) {
		http_request1 = false;
		if (window.XMLHttpRequest){
		   http_request1 = new XMLHttpRequest();
		   if (http_request1.overrideMimeType){
		   	  http_request1.overrideMimeType('text/html');
		   }
		}else if (window.ActiveXObject){
			  try {
					http_request1 = new ActiveXObject("Msxml2.XMLHTTP");
			  } catch (e) {
			  	try {
					http_request1 = new ActiveXObject("Microsoft.XMLHTTP");
				} catch (e){}
			  }
		}
		if (!http_request1) {
		   alert('Cannot create XMLHTTP instance');
		   return false;
		}

		http_request1.open('POST', url, true);
		http_request1.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		http_request1.setRequestHeader("Content-length", parameters.length);
		http_request1.setRequestHeader("Connection", "close");
		http_request1.send(parameters);
		nameval1=name;
		if (navigator.appName=="Microsoft Internet Explorer"){
			http_request1.onreadystatechange = new Function(alertContents1());
		} else {
			http_request1.onreadystatechange= alertContents1();
		}
	}
	
	function makePOSTRequest2(url,parameters, name) {
		http_request2 = false;
		if (window.XMLHttpRequest){
		   http_request2 = new XMLHttpRequest();
		   if (http_request2.overrideMimeType){
		   	  http_request2.overrideMimeType('text/html');
		   }
		}else if (window.ActiveXObject){
			  try {
					http_request2 = new ActiveXObject("Msxml2.XMLHTTP");
			  } catch (e) {
			  	try {
					http_request2 = new ActiveXObject("Microsoft.XMLHTTP");
				} catch (e){}
			  }
		}
		if (!http_request2) {
		   alert('Cannot create XMLHTTP instance');
		   return false;
		}

		http_request2.open('POST', url, true);
		http_request2.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		http_request2.setRequestHeader("Content-length", parameters.length);
		http_request2.setRequestHeader("Connection", "close");
		http_request2.send(parameters);
		nameval2=name;
		if (navigator.appName=="Microsoft Internet Explorer"){
			http_request2.onreadystatechange = new Function(alertContents2());
		} else {
			http_request2.onreadystatechange= alertContents2();
		}
	}

	function alertContents() {
		if (http_request.readyState == 4) {
			if (http_request.status == 200) {
				ajaxrendercomponent(nameval);
			} else {
				alert('There was a problem with the request: ' + http_request.responseText);
			}
		} else {
			setTimeout('alertContents()', 4);
		}
	}

	
	function alertContents1() {
		if (http_request1.readyState == 4) {
			if (http_request1.status == 200) {
				ajaxrendercomponent1(nameval1);
			} else {
				alert('There was a problem with the request: ' + http_request1.responseText);
			}
		} else {
			setTimeout('alertContents1()', 4);
		}
	}

	function alertContents2() {
		if (http_request2.readyState == 4) {
			if (http_request2.status == 200) {
				ajaxrendercomponent2(nameval2);
			} else {
				alert('There was a problem with the request: ' + http_request2.responseText);
			}
		} else {
			setTimeout('alertContents2()', 4);
		}
	}