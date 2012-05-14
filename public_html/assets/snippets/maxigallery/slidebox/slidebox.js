
/*  Slidebox v0.4.1 Slideshow based on Lightbox JS
 *  Copyright (C) 2006 Olivier Ramonat
 *  
 *  For details, see the Slidebox web site : 
 *    http://olivier.ramonat.free.fr/slidebox/
 *  
 *  Slidebox is a script used to make a slideshow on the current page.  It
 *  is based on Lightbox JS, a simple script to overlay images.
 *
 *  This library is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU Lesser General Public
 *  License as published by the Free Software Foundation; either
 *  version 2.1 of the License, or (at your option) any later version.
 *  
 *  This library is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 *  Lesser General Public License for more details.
 *  
 *  You should have received a copy of the GNU Lesser General Public
 *  License along with this library; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin Street, Fifth Floor,
 *  Boston, MA  02110-1301  USA
 *
 */


/*
	Lightbox JS: Fullsize Image Overlays
	by Lokesh Dhakar - http://www.huddletogether.com

	For more information on this script, visit:
	http://huddletogether.com/projects/lightbox/

	Licensed under the Creative Commons Attribution 2.5 License
        - http://creativecommons.org/licenses/by/2.5/
	(basically, do anything you want, just leave my name and link)
*/


/*--------------------------------------------------------------------------*/
/* WARNING : To configure slidebox.js see slidebox-setup.js. 
 * Do NOT edit this file 
 */

// Global variables

var idx = 0;
var anchors;
var xml_url = "";

function getKey (e) {
   if (e == null) { // ie
      keycode = event.keyCode;
   } else { // mozilla
      keycode = e.which;
   }
   key = String.fromCharCode(keycode).toLowerCase();
   
   var i=0;
 
   for (i=0;i<nextKeys.length;i++){
      if(nextKeys[i] == key){
         gotoLightbox('go_right');
         return false;
      }
   }

   for (i=0;i<prevKeys.length;i++){
      if(prevKeys[i] == key){
         gotoLightbox('go_left');
         return false;
      }
   }

   for (i=0;i<closeKeys.length;i++){
      if(closeKeys[i] == key){
         hideLightbox(); 
         return false;
      }
   }
	
   // changed to be defined in external lang file by doze

   /*switch(key) {
      case "n": gotoLightbox('go_right');return false;
      case " ": gotoLightbox('go_right');return false;
      case "b": gotoLightbox('go_left');return false;
      case "x": hideLightbox(); return false;
      case "c": hideLightbox(); return false;
   }*/
}

function listenkey () {
   document.onkeypress = getKey;
}

function gotoLightbox(direction) {
   var next_link = document.getElementById(direction);
   if (next_link != null) {
      var href = next_link.getAttribute('href');
      hideOnlyLightbox();
      showLightbox(next_link);
   } else {
      hideLightbox();
   }
}

//
// Ajax Request to get an xml file
// Call setXMLCaption on complete
//
function getXMLCaption(href)
{
   var myAjax = new Ajax.Request
      (xml_url,
       {method: 'get', parameters: '',
          onComplete: function (req)
       {
	  setXMLCaption(req.responseXML, href)}}
       );
}

//
// setXMLCaption()
// Parse XML file and add caption, previous link and next link to lightbox
//
function setXMLCaption(result, href)
{
   var objCaption  = document.getElementById('lightboxCaption');
   var objNumber   = document.getElementById('lightboxNumber');
   var objTitle   = document.getElementById('lightboxTitle'); // added by doze

   // Delete old objCaption content
   while (objCaption.hasChildNodes()) {
      objCaption.removeChild(objCaption.firstChild);
   }

   // Delete old objNumber content
   while (objNumber.hasChildNodes()) {
      objNumber.removeChild(objNumber.firstChild);
   }

   // Delete old objTitle content (added by doze)
   while (objTitle.hasChildNodes()) {
      objTitle.removeChild(objTitle.firstChild);
   }

   var all_source   = result.getElementsByTagName('source');

   var i = 0;

   for (i = 0; i < all_source.length; i++) {
      var node = all_source[i];
      if (node.getAttribute("id") == href) {
         /* Add this caption and create link for previous and next images */

         //  Add previous link
         if (i != 0) {
            var previous_node = all_source[i-1];
            var prev_href = previous_node.getAttribute("id");
            var prev_link = document.createElement("a");
            prev_link.setAttribute("href", prev_href);
            prev_link.setAttribute("id", "go_left");
            prev_link.onclick = function () {
               hideOnlyLightbox();
               showLightbox(this);
               return false;
            }

            if (previous_link_image != '') {
               var objbackImg = document.createElement("img");
               objbackImg.setAttribute("src", previous_link_image);
               prev_link.appendChild(objbackImg);
            } else {
               var objbackText = document.createElement("div");
	       objbackText.setAttribute('id','backText');
               objbackText.innerHTML = backText; // changed to come from lang file by doze
               prev_link.appendChild(objbackText);
            }
            objCaption.appendChild(prev_link);
         }

         //  Add next link
         if (i < (all_source.length - 1)) {
            var next_node     = all_source[i+1];
            var next_href = next_node.getAttribute("id");
            var next_link = document.createElement("a");
            next_link.setAttribute("href", next_href);
            next_link.setAttribute("id", "go_right");
            next_link.onclick = function () {
               hideOnlyLightbox();
               showLightbox(this);
               return false;
            }

            if (next_link_image != '') {
               var objnextImg = document.createElement("img");
               objnextImg.setAttribute("src", next_link_image);
               next_link.appendChild(objnextImg);
            } else {
               var objnextText = document.createElement("div");
	       objnextText.setAttribute('id','nextText');
               objnextText.innerHTML = nextText; // changed to come from lang file by doze
               next_link.appendChild(objnextText);
             }
            objCaption.appendChild(next_link);
         }
 
		//  Add number
		var number_elm = node.getElementsByTagName("number");
		var number     = document.createTextNode
			(number_elm[0].firstChild.nodeValue);
		objNumber.appendChild(number);

         //  Add title (added by doze)
         var title_elm = node.getElementsByTagName("title");
         var title     = document.createTextNode
            (title_elm[0].firstChild.nodeValue);
         objTitle.appendChild(title);

         //  Add caption
         var caption_elm = node.getElementsByTagName("caption");
         var caption     = document.createTextNode
            (caption_elm[0].firstChild.nodeValue);
         objCaption.appendChild(caption);
         return;
      }
   }
}

//
// getPageScroll()
// Returns array with x,y page scroll values.
// Core code from - quirksmode.org
//
function getPageScroll(){

   var yScroll;

   if (self.pageYOffset) {
      yScroll = self.pageYOffset;
   } else if (document.documentElement && document.documentElement.scrollTop){
      // Explorer 6 Strict
      yScroll = document.documentElement.scrollTop;
   } else if (document.body) {// all other Explorers
      yScroll = document.body.scrollTop;
   }

   arrayPageScroll = new Array('',yScroll)
   return arrayPageScroll;
}

//
// getPageSize()
// Returns array with page width, height and window width, height
// Core code from - quirksmode.org
//
function getPageSize(){

   var xScroll, yScroll;
   if (window.innerHeight && window.scrollMaxY) {
      xScroll = document.body.scrollWidth;
      yScroll = window.innerHeight + window.scrollMaxY;
   } else if (document.body.scrollHeight > document.body.offsetHeight){
      // all but Explorer Mac
      xScroll = document.body.scrollWidth;
      yScroll = document.body.scrollHeight;
   } else {
      // Explorer Mac...would also work in Explorer 6 Strict,
      // Mozilla and Safari
      xScroll = document.body.offsetWidth;
      yScroll = document.body.offsetHeight;
   }

   var windowWidth, windowHeight;
   if (self.innerHeight) {	// all except Explorer
      windowWidth = self.innerWidth;
      windowHeight = self.innerHeight;
   } else if (document.documentElement
              && document.documentElement.clientHeight) {
      // Explorer 6 Strict Mode
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

//
// showLightbox()
// Preloads images. Pleaces new image in lightbox then centers and displays.
//
function showLightbox(objLink)
{

  // prep objects
   var objOverlay = document.getElementById('overlay');
   var objLightbox = document.getElementById('lightbox');
   var objImage = document.getElementById('lightboxImage');
   var objLoadingImage = document.getElementById('loadingImage');
   var objLightboxDetails = document.getElementById('lightboxDetails');
   var objCaption   = document.getElementById('lightboxCaption');
   var objNumber   = document.getElementById('lightboxNumber');
   var objTitle   = document.getElementById('lightboxTitle'); // added by doze

   var arrayPageSize   = getPageSize();
   var arrayPageScroll = getPageScroll();

   // center loadingImage if it exists
   if (objLoadingImage) {
      objLoadingImage.style.top
         = (arrayPageScroll[1]
            + ((arrayPageSize[3] - 35 - objLoadingImage.height) / 2) + 'px');
      objLoadingImage.style.left
         = (((arrayPageSize[0] - 20 - objLoadingImage.width) / 2) + 'px');
      objLoadingImage.style.display = 'block';
   }

   // set height of Overlay to take up whole page and show
   objOverlay.style.height = (arrayPageSize[1] + 'px');
   objOverlay.style.display = 'block';

   // Change xml_url 'on the fly'
   var objLink_rel = objLink.getAttribute('rel');

   if (objLink_rel != "" && objLink_rel != null) {
        xml_url = objLink_rel.substring(9, objLink_rel.length) + '.xml';
   }

   // Get xml captions
   if (xml_url != "") {
      getXMLCaption (objLink.href);
   }

   // preload image
   imgPreload = new Image();
   imgPreload.onload = function() {
      objImage.src = objLink.href;
      // center lightbox and make sure that the top and left
      // values are not negative
      // and the image placed outside the viewport
      var lightboxTop = arrayPageScroll[1]
         + ((arrayPageSize[3] - 35 - imgPreload.height) / 2);
      var lightboxLeft = ((arrayPageSize[0] - 20 - imgPreload.width) / 2);

      objLightbox.style.top = (lightboxTop < 0) ? "0px" : lightboxTop + "px";
      objLightbox.style.left
         = (lightboxLeft < 0) ? "0px" : lightboxLeft + "px";

      objLightboxDetails.style.width = imgPreload.width + 'px';

      objCaption.style.display = 'none';

      objNumber.style.display = 'none';

      objTitle.style.display = 'none'; // added by doze

      // Hide select boxes as they will 'peek' through the image in IE
      selects = document.getElementsByTagName("select");
      for (i = 0; i != selects.length; i++) {
         selects[i].style.visibility = "hidden";
      }

      objLightbox.style.display = 'none';

      // After image is loaded, update the overlay height
      // as the new image might have
      // increased the overall page height.
      arrayPageSize = getPageSize();
      objOverlay.style.height = (arrayPageSize[1] + 'px');
   }

   imgPreload.src = objLink.href;

   waitOnComplete (imgPreload);
}

//
// Wait until image is loaded. Then display it
//
function waitOnComplete(obj) {
   if (obj.complete) {
    // A small pause between the image loading and displaying is required
    // this prevents the previous image displaying for a short burst
    // causing flicker.
      var showImg = function () { displayLightbox (obj) };
      setTimeout (showImg, 500);
   } else {
      var loopWhenNotComplete = function () { waitOnComplete (obj);}
      setTimeout (loopWhenNotComplete, 500);
   }
}

//
// Display image and caption
//
function displayLightbox(obj) {
   var objLightbox = document.getElementById('lightbox');
   var objCaption  = document.getElementById('lightboxCaption');
   var objNumber  = document.getElementById('lightboxNumber');
   var objTitle  = document.getElementById('lightboxTitle'); // added by doze
   var objImage    = document.getElementById('lightboxImage');
   var objLoadingImage = document.getElementById('loadingImage');

   // center lightbox
   // center lightbox and make sure that the top and left
   // values are not negative
   // and the image placed outside the viewport

   var lightboxTop = arrayPageScroll[1]
      + ((arrayPageSize[3] - 35 - imgPreload.height) / 2);

   var lightboxLeft = ((arrayPageSize[0] - 40 - imgPreload.width) / 2);

   objLightbox.style.top  = (lightboxTop < 0) ? "0px" : lightboxTop + "px";
   objLightbox.style.left = (lightboxLeft < 0) ? "0px" : lightboxLeft + "px";

   objLightbox.style.display   = 'block';
   objLightbox.style.width = imgPreload.width + 'px';

   objImage.style.visibility = 'visible';
   objImage.src = obj.src;

   // display caption if exist
   objCaption.style.width = imgPreload.width + 'px';
   objCaption.style.display = 'block';

   // display number if exist
   // objNumber.style.width = imgPreload.width + 'px'; // commented by doze to not fill whole area
   objNumber.style.display = 'inline';

   objTitle.style.display = 'inline'; // added by doze

   // Listen key to print previous or next image
   listenkey();

   //	clear LoadingImage, as IE will flip out w/animated gifs
   if (objLoadingImage) { objLoadingImage.style.display = 'none'; }
}

//
// hideLightbox()
//
function hideLightbox()
{
   // get objects
   var objOverlay  = document.getElementById('overlay');
   var objLightbox = document.getElementById('lightbox');

   // hide lightbox, overlay
   objOverlay.style.display  = 'none';
   objLightbox.style.display = 'none';
   
   // restore hidden select boxes (added by doze, bug reported by sottwell)
   selects = document.getElementsByTagName("select");
   for (i = 0; i != selects.length; i++) {
   	 selects[i].style.visibility = "visible";
   }

   document.onkeypress = '';
}

//
// hideOnlyLightbox()
// Do not hide overlay when go to next or previous link during a slideshow
//
function hideOnlyLightbox()
{
   var objLightbox = document.getElementById('lightbox');
   objLightbox.style.display = 'none';
}

//
// initLightbox()
// Function runs on window load, going through link tags looking
// for rel="lightbox" or rel="lightbox_nameofxmlfile" where the xml file
// that contains the captions is nameofxmlfile.xml
// These links receive onclick events that enable the lightbox display
// for their targets.
// The function also inserts html markup at the top of the page
// which will be used as a
// container for the overlay pattern and the inline image.
//
function initLightbox()
{
   if (!document.getElementsByTagName) {
      return false;
   }

   var anchors = document.getElementsByTagName("a");
   var i = 0;

   // loop through all anchor tags
   for (i = 0; i < anchors.length; i++){
      var anchor = anchors[i];
      if (anchor.getAttribute("href")) {
         var rel_attr = anchor.getAttribute("rel");
         if (rel_attr != null && rel_attr.indexOf("lightbox") == 0) {
            anchor.onclick = function () {showLightbox(this); return false;}
         }
      }
   }

   // the rest of this code inserts html at the top of the page that looks
   // like this:
   //
   // <div id="overlay">
   //		<a href="#" onclick="hideLightbox(); return false;">
   //                <img id="loadingImage" /></a>
   //	</div>
   // <div id="lightbox">
   //		<a href="#" onclick="hideLightbox(); return false;"
   //              title="Click anywhere to close image">
   //                <img id="closeButton" />
   //            </a>
   //		<div id="lightboxCaption"></div>
   // </div>

   var objBody = document.getElementsByTagName("body").item(0);

   // create overlay div and hardcode some functional styles
   // (aesthetic styles are in CSS file)
   var objOverlay = document.createElement("div");
   objOverlay.setAttribute('id','overlay');
   objOverlay.style.display = 'none';
   objOverlay.style.position = 'absolute';
   objOverlay.style.top = '0';
   objOverlay.style.left = '0';
   objOverlay.style.zIndex = '90';
   objOverlay.style.width = '100%';
   objBody.insertBefore(objOverlay, objBody.firstChild);

   var arrayPageSize   = getPageSize();
   var arrayPageScroll = getPageScroll();

   // preload and create loader image
   var imgPreloader = new Image();

   // if loader image found, create link to hide lightbox
   // and create loadingimage
   imgPreloader.onload=function(){

      var objLoadingImageLink = document.createElement("a");
      objLoadingImageLink.setAttribute('href','#');
      objLoadingImageLink.onclick = function () {hideLightbox(); return false;}
      objOverlay.appendChild(objLoadingImageLink);

      var objLoadingImage = document.createElement("img");
      objLoadingImage.src = loadingImage;
      objLoadingImage.setAttribute('id','loadingImage');
      objLoadingImage.style.position = 'absolute';
      objLoadingImage.style.zIndex = '150';
      objLoadingImageLink.appendChild(objLoadingImage);

      imgPreloader.onload = function() {};
      // clear onLoad, as IE will flip out w/animated gifs

      return false;
   }

   imgPreloader.src = loadingImage;

   // create lightbox div, same note about styles as above
   var objLightbox = document.createElement("div");
   objLightbox.setAttribute('id','lightbox');
   objLightbox.style.display  = 'none';
   objLightbox.style.position = 'absolute';
   objLightbox.style.zIndex   = '100';
   objBody.insertBefore(objLightbox, objOverlay.nextSibling);

   // create details div, a container for the  user message
   var objLightboxDetails = document.createElement("div");
   objLightboxDetails.setAttribute('id','lightboxDetails');
   objLightbox.appendChild(objLightboxDetails);

   // create link
   var objLink = document.createElement("a");
   objLink.setAttribute('id', 'closeLightbox');
   objLink.setAttribute('href','#');
   objLink.onclick = function () {hideLightbox(); return false;}
   objLightbox.appendChild(objLink);

   // preload and create close button image
   var imgPreloadCloseButton = new Image();

   // if close button image found,
   imgPreloadCloseButton.onload = function() {
      var objCloseButton = document.createElement("img");
      objCloseButton.src = closeButton;
      objCloseButton.setAttribute('id','closeButton');
      objCloseButton.style.position = 'absolute';
      objCloseButton.style.cursor = 'default';
      objCloseButton.style.zIndex = '200';
      objLink.appendChild(objCloseButton);
      return false;
   }

   imgPreloadCloseButton.src = closeButton;

   // create image
   var objImage = document.createElement("img");
   objImage.style.cursor = 'pointer';
   objImage.setAttribute('id','lightboxImage');
   objImage.setAttribute('title', imageTitle);
   objImage.onclick = function () {gotoLightbox('go_right'); return false;}
   objImage.style.visibility = 'hidden';
   objLightbox.appendChild(objImage);

   // create caption
   var objCaption = document.createElement("div");
   objCaption.setAttribute('id','lightboxCaption');
   objCaption.style.display = 'none';
   objLightbox.appendChild(objCaption);

   // create user message
   var objuserMsg = document.createElement("div");
   objuserMsg.setAttribute('id','userMsg');
   objuserMsg.innerHTML = objuserMessage;
   objLightboxDetails.appendChild(objuserMsg);

	// create title (added by doze)
   var objTitle = document.createElement("div");
   objTitle.setAttribute('id','lightboxTitle');
   objTitle.style.display = 'none';
   objLightboxDetails.appendChild(objTitle);

   // create number
   var objNumber = document.createElement("div");
   objNumber.setAttribute('id','lightboxNumber');
   objNumber.style.display = 'none';
   objLightboxDetails.appendChild(objNumber);
   
   // Make the slidebox thumbs visible
 
   slidebox_end_init()
}

//
// addLoadEvent()
// Adds event to window.onload without overwriting currently
// assigned onload functions.
// Function found at Simon Willison's weblog - http://simon.incutio.com/
//
function addLoadEvent(func)
{
   var oldonload = window.onload;
   if (typeof window.onload != 'function'){
      window.onload = func;
   } else {
      window.onload = function(){
         oldonload();
         func();
      }
   }
}

addLoadEvent(initLightbox);	// run initLightbox onLoad
