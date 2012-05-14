//MooTools More, <http://mootools.net/more>. Copyright (c) 2006-2009 Aaron Newton <http://clientcide.com/>, Valerio Proietti <http://mad4milk.net> & the MooTools team <http://mootools.net/developers>, MIT Style License.

MooTools.More={version:"1.2.3.1"};Class.refactor=function(b,a){$each(a,function(e,d){var c=b.prototype[d];if(c&&(c=c._origin)&&typeof e=="function"){b.implement(d,function(){var f=this.previous;
this.previous=c;var g=e.apply(this,arguments);this.previous=f;return g;});}else{b.implement(d,e);}});return b;};Request.implement({options:{initialDelay:5000,delay:5000,limit:60000},startTimer:function(b){var a=(function(){if(!this.running){this.send({data:b});
}});this.timer=a.delay(this.options.initialDelay,this);this.lastDelay=this.options.initialDelay;this.completeCheck=function(c){$clear(this.timer);if(c){this.lastDelay=this.options.delay;
}else{this.lastDelay=(this.lastDelay+this.options.delay).min(this.options.limit);}this.timer=a.delay(this.lastDelay,this);};this.addEvent("complete",this.completeCheck);
return this;},stopTimer:function(){$clear(this.timer);this.removeEvent("complete",this.completeCheck);return this;}});