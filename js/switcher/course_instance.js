/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
var datatable;
function initDoc()
{
    createDataTable();
    initToolTips();
    displayDiv();
}
function createDataTable()
{
    datatable = $j('#course_instance_Table').dataTable({
    "bFilter": true,
    "bInfo": true,
    "bSort": true,
    "bAutoWidth": false,
    "bPaginate" : true,
    "aaSorting": [[ 2, "asc" ]],
    "aoColumnDefs": [
        {
           "aTargets": [ 0 ],
           "bVisible":false,
        },
        {
           "aTargets": [ 1 ],
           "sClass": "Id_Column",
        },
        {
           "aTargets": [ 2 ],
           "sClass": "Name_Column",
        },
        {
           "aTargets": [ 3 ],
           "sClass": "LastName_Column",
        },
        {
           "aTargets": [ 4 ],
           "sClass": "Status_Column",
           "sType": "select",
           "bSearchable":false
        },
        {
           "aTargets": [ 5 ],
           "bVisible":false,
           "bSearchable":false
        },

        {
           "aTargets": [ 6 ],
           "sClass": "Date_Column",
           "sType":"date-eu"
        },
        {
           "aTargets": [ 7 ],
           "sClass": "Level_Column",
        },
    ],
    "oLanguage":
     {
        "sUrl": HTTP_ROOT_DIR + "/js/include/jquery/dataTables/dataTablesLang.php"
     }
    });
}
function  initToolTips()
 {
   $j(document).tooltip({
        items:  'span[class="UserName tooltip"]',
        show :     {
                effect : "slideDown",
                delay : 300,
                duration : 100
        },
        hide : {
                effect : "slideUp",
                delay : 100,
                duration : 100
        },
        position : {
                my : "center bottom-5",
                at : "center top"
        },
        content: function(){
            if ('undefined' != typeof $j(this).attr('id') && 'undefined' != $j('#user_tooltip_'+$j(this).attr('id'))) {
                return $j('#user_tooltip_'+$j(this).attr('id')).attr('title');
            }
            return null;
        }

    });
}

 function displayDiv()
{
    $j('.table_result').animate({"height": "toggle"});
    $j('.table_result').animate({'marginLeft':'0'},"slow");
}
function saveStatus(select)
{
    var myVal = select.value;

    var SelectRow = $j(select).parents('tr')[0];
    var indexRow=datatable.fnGetPosition($j(select).parents('tr')[0]);
    var aData = datatable.fnGetData( SelectRow );
    var idUser=null;
    var idInstance=null;
    var indexColumn=null;
    var dateRegexp = /\d{1,2}\/{1}\d{1,2}\/{1}\d{2,4}/;
    var htmlRegexp = /<[a-z][\s\S]*>/;

    $j.each(aData,function(i,val){
    	/**
    	 * if val is a (sort of) date or not html, skip to next iteration
    	 */
    	if (dateRegexp.test(val) || !htmlRegexp.test(val)) {
    		return;
    	} else if( 'undefined' !== typeof $j(val).attr('class') && $j(val).attr('class').indexOf('UserName')!=-1) {
            idUser=$j(val).attr('id');// text();
        } else if ( 'undefined' !== typeof $j(val).attr('class') && $j(val).attr('class')==='id_instance') {
            idInstance=$j(val).text();
        } else if ( 'undefined' !== typeof $j(val).attr('class') && $j(val).attr('class')==='hidden_status') {
            indexColumn=i;
        }
    });

    var data = {
        'status' : select.value,
        'id_user': idUser,
        'id_instance': idInstance
    }
     $j.ajax({
       type	: 'POST',
       url	: HTTP_ROOT_DIR+ '/switcher/ajax/updateSubscription.php',
       data	: data,
       dataType :'json'
       })
       .done   (function( JSONObj )
       {
           showHideDiv(JSONObj.title,JSONObj.msg);
           var selectedText = $j(select).find('option[value="'+myVal+'"]').text();
           var cloned = $j(aData[indexColumn]).text(selectedText).clone();
           datatable.fnUpdate(cloned[0].outerHTML, indexRow,indexColumn, false);

           $j(select).find('option').each(function(i,e){
              $j(e).prop('selected', false).removeAttr('selected');
           });
           $j(select).val(myVal);
           $j(select).find('option[value="'+myVal+'"]').prop('selected', true).attr('selected', 'selected');

           datatable.fnUpdate($j(select)[0].outerHTML, indexRow, indexColumn+4, false);
           // console.log($j(select)[0].outerHTML);
           datatable.fnStandingRedraw();
           /* if user status is removed  it deletes user row from datatable */
           if(select.value == 3)
           {
               datatable.fnDeleteRow( SelectRow );
           }
       })
       .fail   (function() {
            console.log("ajax call has failed");
	} );

}

function downloadCertificates(idInstance) {
    // get the idstudents of the displayed row
    var anNodes = $j("#course_instance_Table tbody tr");
    var shownIds = [], rowData = [];
    for (var i = 0; i < anNodes.length; ++i) {
        rowData.push(datatable.fnGetData(anNodes[i]));
    }
    $j.each(rowData,function(i, row) {
        if (null != row && 'undefined' != typeof row.length && row.length >0){
            $j.each(row, function(j, val){
                try {
                    if ($j(val).hasClass('UserName') && !isNaN($j(val).attr('id'))) {
                        shownIds.push(parseInt($j(val).attr('id')));
                    }
                } catch (e) {
                    // if an error occurs during the
                    // evaluation of $j(val) skip to the next iteration
                }
            });
        }
    });
    delete rowData;

    var certificateAction = function(idInstance, check, email, selectedIds) {
        return $j.ajax({
            type	: 'POST',
            url	: HTTP_ROOT_DIR+ '/switcher/ajax/zipInstanceCertificates.php',
            data	: { id_instance: idInstance, check: check, email: email, selectedIds: selectedIds },
            dataType :'json'
            })
        .done(function (response){
            if ('data' in response && response.data.length>0) {
                if (response.data != 'OK') {
                    showHideDiv('Download', response.data);
                }
            }
        });
    }
	if ('udenfined' !== typeof $j(this).attr('disabled') && idInstance>0) {
        $j.when(certificateAction(idInstance,1,0,shownIds)).done(function(checkResponse){
            if ('data' in checkResponse && checkResponse.data.length>0 && checkResponse.data=='OK') {
                if ('count' in checkResponse && !isNaN(parseInt(checkResponse.count)) && parseInt(checkResponse.count)>30) {
                    // ask the ajax to run in the background and send an email
                    certificateAction(idInstance,0,1,shownIds);
                } else {
                    // do the download
                    if ($j('#pleaseWaitMSG').length>0) $j.blockUI.defaults.message = $j('#pleaseWaitMSG').html();
                    $j.blockUI.defaults.css.padding = 15;
                    var options = {
                            url: HTTP_ROOT_DIR+ '/switcher/ajax/zipInstanceCertificates.php',
                            data: { id_instance: idInstance, check: 0, selectedIds: shownIds },
                            beforeDownload: function() {
                                $j(this).attr('disabled','disabled');
                                $j.blockUI();
                            },
                            afterDownload: function(attemptsExpired) {
                                if (attemptsExpired) alert ($j('#attemptsExpired').text());
                                $j.unblockUI();
                                $j(this).removeAttr('disabled');
                            }
                    };
                    doDownload(options);
                }
            }
        });
	}
}

function showHideDiv (title, message)
{
    var theDiv = $j("<div id='ADAJAX' class='saveResults'><p class='title'>"+title+"</p><p class='message'>"+message+"</p></div>");
    theDiv.css("position","fixed");
    theDiv.css("width", "350px");
    theDiv.css("top", ($j(window).height() / 2) - (theDiv.outerHeight() / 2));
    theDiv.css("left", ($j(window).width() / 2) - (theDiv.outerWidth() / 2));
    theDiv.hide().appendTo('body').fadeIn(500).delay(2000).fadeOut(500, function() {
    theDiv.remove();
    if (typeof reload != 'undefined' && reload) self.location.reload(true); });

}
function goToSubscription(path)
{
	$j.when(
	    $j('.table_result').effect('drop', function() {
	        $j('#course_instance_Table').effect('slide');
	    })
	).done(
		function() {
			self.document.location.href = path+'.php'+location.search;
		}
	);
}

