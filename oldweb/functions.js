
function alert_delete(id) 
{
	delete_data = confirm("Do you really want to DELETE this entry?");
	if(delete_data == true){
		top.location.href="edit.php?id="+ id + "&mode=delete";
	}
}

function alert_complete(id, number) 
{
	complete_data = confirm("Do you really want to COMPLETE this entry?");
	if (complete_data) top.location.href = "index.php?template=0&submit" + number + id + "=" + number + id;
        else top.location.href = "index.php?template=0";
}

function info_popup(id)  
{ 
	var breite=900; 
 	var hoehe=580; 
	var positionX=((screen.availWidth / 2) - breite / 2); 
	var positionY=((screen.availHeight / 2) - hoehe / 2); 
	var url="output.php?id=" + id; 
	pop=window.open('','','toolbar=0,location=0,directories=0,status=1,menubar=0,scrollbars=1,resizable=1,fullscreen=0,width='+breite+',height='+hoehe+',top=0,left=0'); 
	pop.resizeTo(breite,hoehe); 
	pop.moveTo(positionX,positionY); 
	pop.location=url; 
}

function clear_form() 
{
    var formLength = document.forms[0].elements.length - 3; // Select the first form of the website
    var formElements = document.forms[0].elements;

    for (i = 0; i < formLength; i++) {
        var formElementName = formElements[i].name;
        if (formElementName == "name" || formElementName == "cat1"
            || formElementName == "ex_date_years" || formElementName == "birth_date_years") { // do not empty the dropdown boxes of name & uni.
            continue;
        }
        field_type = formElements[i].type.toLowerCase();
        switch (field_type) {
            case "text":
            case "password":
            case "textarea":
            case "hidden":
                formElements[i].value = "";
                break;
            case "radio":
            case "checkbox":
                if (formElements[i].checked) {
                    formElements[i].checked = false;
                }
                break;
            case "select-one":
            case "select-multi":
                formElements[i].selectedIndex = 0;
                break;
            default:
                break;
        }
    }
}

function poptastic(url)
{
	var newwindow;
	newwindow=window.open(url,'name','scrollbars=1,height=580,width=900');
	if (window.focus) {newwindow.focus();}
}

function poptastic2(url)
{
	var newwindow;
	newwindow=window.open(url,'name','scrollbars=1,height=250,width=450');
	if (window.focus) {newwindow.focus();}
}

function firstSecondUni() 
{
        var selected1 = document.getElementById('university1').value;
        var selected2 = document.getElementById('university2');
        if(selected1 == '0') {
             selected2.options[1].style.display = "";
             selected2.options[2].style.display = "";
             selected2.options[3].style.display = "";
             selected2.options[4].style.display = "";
         } else if(selected1 == '1') {
             selected2.options[1].style.display = "none";
             selected2.options[2].style.display = "";
             selected2.options[3].style.display = "";
             selected2.options[4].style.display = "";
         } else if (selected1 == '2') {
             selected2.options[1].style.display = "";
             selected2.options[2].style.display = "none";
             selected2.options[3].style.display = "";
             selected2.options[4].style.display = "";
         } else if (selected1 == '3') {
             selected2.options[1].style.display = "";
             selected2.options[2].style.display = "";
             selected2.options[3].style.display = "none";
             selected2.options[4].style.display = "";
         } else if (selected1 == '4') {
             selected2.options[1].style.display = "";
             selected2.options[2].style.display = "";
             selected2.options[3].style.display = "";
             selected2.options[4].style.display = "none";
         }
}

var poststr, xmlHttp, reqText;
var list2, cat, poststr2;
var checkSelected;
var AjaxCRequest;
var cRequestHandler;
var checkNew;

cRequestHandler = function( url, isTarget, isForm ) {
   reqText = xmlHttp.responseText;

   isTarget = (( document.getElementById ) ? document.getElementById( isTarget ) : document.all[ isTarget ] );

   if (xmlHttp.readyState === 4 || xmlHttp.statusText === "OK") {
       // i skipped the xmlHttp.status statement, for offline run. But if you need it, then simply add the whole stament again starting on this line. 
        if ( isForm === 1 ) {
            isTarget.value = reqText;
        } else {
            isTarget.innerHTML = reqText;
        }
    } else {
        //isTarget.innerHTML = "cRequest Error:\n" + (( xmlHttp.status ) ? xmlHttp.statust : "<span style=\"color : #F00; letter-spacing : 2px;\">Undefined Request Status -</span><br />" ) + "\n" + (( xmlHttp.statusText ) ? xmlHttp.statusText : "<span style=\"color : #F00; letter-spacing : 2px;\">Undefined Request Text Status -</span><br />\n" );
    }
};

AjaxCRequest = function( url, params, isTarget, isForm ) {
   xmlHttp = null;
   try {
      if ( window.XMLHttpRequest ) {
      xmlHttp = new XMLHttpRequest();
      } else if ( window.ActiveXObject ) {
         try {
         xmlHttp = new ActiveXObject("Microsoft.XMLHTTP");
         } catch( ms ) {
         xmlHttp = new ActiveXObject("Msxml2.XMLHTTP");
         }
      }
   } catch( e ) {
      if ( window.createRequest ) {
      xmlHttp = window.createRequest();
      } else {
      xmlHttp = null;
      }
   }
   if ( xmlHttp !== null ) {
      xmlHttp.onreadystatechange = function( ) {
      cRequestHandler( url, isTarget, isForm );
      };
   xmlHttp.open( "POST", url, true );
      if ( xmlHttp.setRequestHeader ) {    xmlHttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
      xmlHttp.setRequestHeader("Content-length", params.length);
      xmlHttp.setRequestHeader("Connection", "close" )
      } xmlHttp.send( params );
   } else { 
   alert("Your Browser does not support AJAX Request!");
   }
};

checkSelected = function( origlist ) {
   origlist = (( document.getElementById ) ? document.getElementById( origlist ) : document.all[ origlist ] );
   poststr = "&cat1=" + encodeURIComponent( origlist.options[ origlist.selectedIndex ].value );

   AjaxCRequest("ajaxdata.php", poststr, "div1", 0);
};

checkNew = function( origlist2, catid ) {
   origlist2 = (( document.getElementById ) ? document.getElementById( origlist2 ) : document.all[ origlist2 ] );
   catid = (( document.getElementById ) ? document.getElementById( catid ) : document.all[ catid ] );
   poststr2 = "&cat2=" + encodeURIComponent( origlist2.options[ origlist2.selectedIndex ].value );
   AjaxCRequest(("ajaxdata2.php?&cat2=" + catid ), poststr2, "div2", 0);
};

$(document).ready(function() {

    // process the form
    $('#entry_form').submit(function (event) {

        $.ajax({
            type: 'POST', // define the type of HTTP verb we want to use (POST for our form)
            url: 'update.php', // the url where we want to POST
            data: $(this).serialize(), // serialize form data
            dataType: 'json', // what type of data do we expect back from the server
            encode: true,
            success: function (data) {
                if (data.mode != "edit") { // do not clear the form if edit
                    clear_form();
                }
                $('#messageForm').css({ 'display': 'block' }).html('<h2>' + data.text + '</h2>');
                setTimeout(function () { $('#messageForm').css({ 'display': 'none' }) }, 5000);
            },
            error: function (e) {
                alert("Error: " + e);
            }
        });

        // stop the form from submitting the normal way and refreshing the page
        event.preventDefault();
    });

});

function studentStatus (id, status)
{
    $.ajax({
        type: 'POST', // define the type of HTTP verb we want to use
        url: 'ajaxCalls/AjaxCalls.php', // the url
        data: {'studentId': id, 'studentStatus': status}, // serialize form data
        dataType: 'json', // what type of data do we expect back from the server
        encode: true,
        success: function (data) {
            if (status == 'drop')
            {
                var iid = "#nr" + id.toString();
                $(iid).addClass('dropped');
            }
        },
        error: function (e) {
            alert("Error: " + e);
        }
    });
}
