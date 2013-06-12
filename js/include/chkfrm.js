

/* Javascript validation functions */

//function to check empty fields
function isNotEmpty(field) {
    strfield1 = field.value;
    if (strfield1 == "" || strfield1 == null || !isNaN(strfield1) || strfield1.charAt(0) == ' ')
    {
    return false;
    }
  return true;
}

//function that performs only checks on necessary fields
function checkNec(){
var isOk = true;
var cflDiv = document.getElementById('cfl');
var cflFields = cflDiv.getAttribute('title');
var cxflAr = new Array();
var fieldId;
cxflAr = cflFields.split(',');
for (var i=0; i<cxflAr.length; i++){
        fieldId = cxflAr[i];
        var fieldDiv = document.getElementById(fieldId);
        var type = fieldDiv.type;

        if ((type == "text") | (type == "textarea")){
                isOk = isOk & (isNotEmpty(fieldDiv));
        }
}

if (isOk) {
  return true; // goes ahead
}
alert("Per favore riempi tutti i campi obbligatori !")
return false;
}

//function that performs all checks
function checkAll(myform){
var isOk = true;
var stop = myform.elements.length;
for( var x = 0; x<stop; x++ ){
        //alert(myform.childNodes[x].getAttribute('id'));
        var type = myform.elements[x].type;

        if ((type == "text") | (type == "textarea")){
                isOk = isOk & (isNotEmpty(myform.elements[x]));
               // alert("t:"+type+"ok:"+isOk);
        }
}

if (isOk) {
  return true; // goes ahead
}
alert("Please fill all fields!")
return false;
}




//function that performs all functions

function check(myform){
var isOk = true;
var stop = myform.elements.length;
for( var x = 0; x<stop; x++ ){
	//alert(myform.childNodes[x].getAttribute('id'));
	var type = myform.elements[x].type;
	if ((type == "text") | (type == "textarea")){
		isOk = isOk & (isNotEmpty(myform.elements[x]));

	}
}


if (isOk) {
  return true; // goes ahead
}
alert("Attenzione: occorre riempire tutti i campi.")
return false;
}

