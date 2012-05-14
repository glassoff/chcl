//--(Begin)-->fetch value of button via outerHTML
function getButtonTagValue(buttonObj) {
var regexObj = /<[^<]*value="([^"'<>]*)"[^>]*>/;
//'
var match = regexObj.exec(buttonObj.outerHTML);
if (match != null && match.length > 1) {
return match[1];
} else {
return '';
}
}
//--(End)-->fetch value of button via outerHTML

function insertHiddenField(parentObj, name, value) {
var hiddenField = document.createElement('input');
hiddenField.type='hidden';
hiddenField.name=name;
hiddenField.value=value;
parentObj.parentNode.appendChild(hiddenField);
}

function fixIeButtonTagBug(formId) {
if (document.all || 1==1) {
var baseObject=document;
var elms;
var custFunc;

if (formId) baseObject=document.getElementById(formId);
elms=baseObject.getElementsByTagName('button');

if (elms) { 
for (var x=0; x<elms.length; x++)
if (elms[x].tagName=='BUTTON') {
//this.setAttribute('value', getButtonTagValue(this)); this.setAttribute('name', '"+elms[x].name+"');
custFunc=new Function(
"insertHiddenField(this,'"+elms[x].name+"',getButtonTagValue(this));"+
"return true;"
);

elms[x].onclick=custFunc;
elms[x].name='serviceButtonValue';
}
}
}
}

if (window.addEventListener)
window.addEventListener("load", function() {fixIeButtonTagBug();}, false)
else if (window.attachEvent)
window.attachEvent("onload", function() {fixIeButtonTagBug();})