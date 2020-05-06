/**
 * @package 	studentsgroups module
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2020, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version		0.1
 */

var debugForm = false;
var table = null;

function initDoc() {
    Dropzone.autoDiscover = false;
    // initToolTips();
    initButtons();
    initDataTables();
}

function initDropZone() {
    var dropZones = [];
    $j('.dropzone').each(function (index, el) {
        var name = $j(el).attr('id');
        if ($j('#' + name).length > 0) {

            if ($j('#' + name).data('type') == 'file') {
                var mimeTypes = '.csv';
                var maxFileSize = 0.25;
                var minWidth = 96;
            }

            dropZones.push(new Dropzone('#' + name, {
                paramName: 'uploaded_' + name,
                maxFiles: 100,
                url: 'ajax/upload.php',
                autoProcessQueue: false,
                addRemoveLinks: true,
                acceptedFiles: mimeTypes,
                maxFilesize: maxFileSize,
                minWidth: minWidth,
                // maxThumbnailFilesize: maxThumbnailFilesize,
                dictInvalidFileType: $j('#dictInvalidFileType').text(),
                dictRemoveFile: $j('#dictRemoveFile').text(),
                dictFileTooBig: $j('#dictFileTooBig').text(),
                dictMinWidth: $j('#dictMinWidth').text() + minWidth + 'px x ' + minWidth + 'px',
                hiddenInputContainer: '#' + name,
                init: function () {
                    var that = this;

                    this.on("error", function (file, message) {
                        that.removeFile(file);
                        setTimeout(function () {
                            showHideDiv("Error", message, false);
                        }, 100);
                    });

                    this.on("success", function (file, responseObject) {
                        $j(file.previewElement).fadeOut('slow');
                        // add an hidden to pass the real, temp uploaded filename
                        if ('undefined' !== typeof responseObject.fileName) {
                            if (debugForm) console.log('adding the hidden input');
                            $j('<input>').attr({
                                type: 'hidden',
                                name: name + 'fileNames[]',
                                value: responseObject.fileName
                            }).appendTo('#' + name);
                        } else {
                            if (debugForm) console.log('NOT adding the hidden input');
                        }
                    });

                    this.on("addedfile", function (file) {
                        /**
                         * set value of dropzone associated text to file name and trigger change to revalidate
                         * if text has attribute data-validate="mandatoryDropzone" proper actions will be taken
                         */
                        $j('input[type="text"][name="' + $j(that.element).attr('id') + '"]').val(file.name).trigger('change');

                    });

                    this.on("removedfile", function (file) {
    					/**
    					 * set value of dropzone associated text to empty and trigger change to revalidate
    					 * if text has attribute data-validate="mandatoryDropzone" proper actions will be taken
    					 */
                        $j('input[type="text"][name="' + $j(that.element).attr('id') + '"]').val('').trigger('change');
                    });
                }
            }));
        }
    });
    return dropZones;
}

function initDataTables() {
    table = $j('#completeGropusList').DataTable({
        "ajax": function (data, callback, settings) {
            $j.ajax({
                'type': 'GET',
                'url': "ajax/getData.php",
                'cache': false,
                'data': { object: 'Groups' }
            })
            .done(function (response) {
                callback(response);
            })
            .fail(function (response) {
                if (debugForm) console.debug('dataTable ajax fail ', response);
                const errmsg = ('responseJSON' in response && 'error' in response.responseJSON) ? response.responseJSON.error : response.statusText;
                const callBackParam = ('responseJSON' in response) ? response.responseJSON : { data: [] };
                $j.when(showHideDiv("(" + response.status + ") " + errmsg, false))
                    .then(() => { callback(callBackParam); });
            });
        },
        "deferRender": true,
        "processing": true,
        "autoWidth": false,
        "columns": [
            { "data": "detailsBtn", "bSearchable": false, "bSortable": false, "sWidth": "3%" },
            { "data": "label", "sWidth": "30%" },
            { "data": "customField0", "sWidth": "20%" },
            { "data": "customField1", "sWidth": "20%" },
            { "data": "actions", "bSearchable": false, "bSortable": false, "sWidth": "8%" }
        ],
        "order": [[1, 'asc']],
        "language": {
            "url": HTTP_ROOT_DIR + "/js/include/jquery/dataTables/dataTablesLang.php"
        },
    });

    table.on('draw', function () { initButtons(); });
}

function editGroup(id_group) {
    // ask the server for the edit group form
    const isUpdate = id_group !== null;
    $j.ajax({
        type: 'GET',
        url: 'ajax/getGroupForm.php',
        data: { id: id_group },
        dataType: 'json'
    })
        .done(function (JSONObj) {
            if (JSONObj.status == 'OK') {
                if (JSONObj.html && JSONObj.html.length > 0) {
                    // build the dialog
                    var theDialog = $j('<div />').html(JSONObj.html).dialog({
                        title: JSONObj.dialogTitle,
                        autoOpen: false,
                        modal: true,
                        resizable: false,
                        width: '80%',
                        height: (!isUpdate ? 'auto' : 500),
                        show: {
                            effect: "fade",
                            easing: "easeInSine",
                            duration: 250
                        },
                        hide: {
                            effect: "fade",
                            easing: "easeOutSine",
                            duration: 250
                        }
                    });

                    $j('.ui.dropdown', theDialog).dropdown();
                    $j('.ui.dropdown', theDialog).each(function () {
                        if ('undefined' !== typeof $j(this).data('selected-value')) {
                            $j(this).dropdown('set selected', $j(this).data('selected-value'));
                        }
                    });

                    var dropZones = initDropZone();
                    if (debugForm) console.log(dropZones);

                    // get and hide the submit button
                    var submitButton = theDialog.find('input[type="submit"]');
                    submitButton.hide();

                    // dialog buttons array
                    var dialogButtons = {};

                    // confirm dialog button
                    dialogButtons[i18n['confirm']] = function () {
                        // get form (previously hidden) submit button onclick code
                        var onClickDefaultAction = submitButton.attr('onclick');
                        if (debugForm) console.log('onclick will call', onClickDefaultAction);
                        // execute it, to hava ADA's own form validator
                        var okToSubmit = (onClickDefaultAction.length > 0) ? new Function(onClickDefaultAction)() : false;
                        if (debugForm) console.log('okTosubmit? ', okToSubmit);
                        // and if ok ajax-submit the form
                        if (okToSubmit) {
                            // run other checks...
                            var formName = theDialog.find('form').attr('name');
                            $j('form *[data-notempty="true"]', theDialog).each(
                                function (i, el) {
                                    var val = $j(el).val().trim();
                                    if (val.length <= 0) {
                                        $j(el).siblings('label').addClass('error');
                                        okToSubmit = false;
                                        if (debugForm) console.log('%s is empty', $j(el).attr('name'));
                                    } else {
                                        $j(el).siblings('label').removeClass('error');
                                    }
                                }
                            );

                            // check for non-empty dropzone only for new groups dialog
                            if (!isUpdate) {
                                $j(dropZones).each(function () {
                                    var msg = $j('.dz-message', $j(this.element));
                                    msg.removeClass('error');
                                    if (this.files.length <= 0) {
                                        if (debugForm) console.log('zero files');
                                        okToSubmit = false;
                                        msg.addClass('error');
                                    }
                                });
                            }

                            if (okToSubmit) {
                                $j('#error_form_' + formName, theDialog).addClass('hide_erorr').removeClass('show_error');
                                if (debugForm) console.log('calling uploadFiles with', dropZones[0], debugForm);
                                $j.when(uploadFiles(dropZones[0], debugForm)).done(function () {
                                    ajaxSubmitGroupForm(theDialog.find('form').serialize());
                                });
                                theDialog.dialog('close');
                            } else {
                                $j('#error_form_' + formName, theDialog).removeClass('hide_erorr').addClass('show_error');
                            }
                        }
                    };

                    // cancel dialog button
                    dialogButtons[i18n['cancel']] = function () {
                        theDialog.dialog('close');
                    };

                    // set the defined buttons
                    theDialog.dialog("option", "buttons", dialogButtons);

                    // on dialog close, destroy it
                    theDialog.on('dialogclose', function (event, ui) {
                        $j(this).dialog('destroy').remove();
                    });

                    // on dialog enter keypress, call the confirm click
                    $j('input[type="text"]', theDialog).keypress(function (e) {
                        if (e.which == 13) {
                            e.preventDefault();
                            theDialog.dialog("option", "buttons")[i18n['confirm']]();
                        }
                    });

                    // eventually open the dialog
                    theDialog.dialog('open');
                }
            } else {
                if (JSONObj.msg) showHideDiv('', JSONObj.msg, false);
            }
        })
        .fail(function () { showHideDiv('', 'Server Error', false) });
}

function uploadFiles(aDropZone, debug) {
    var deferred = $j.Deferred();
    if ('undefined' !== typeof aDropZone && aDropZone.files.length > 0) {
        $j('.dz-progress', $j(aDropZone.element)).show();
        aDropZone.on('queuecomplete', function () {
            aDropZone.options.autoProcessQueue = false;
            aDropZone.off('queuecomplete');
            aDropZone.off('error');
            $j('.dz-progress', $j(aDropZone.element)).hide();
            if (debug) console.debug('resolving uploadFiles promise');
            aDropZone.removeAllFiles();
            deferred.resolve();
        });
        aDropZone.on('error', function () {
            aDropZone.off('queuecomplete');
            aDropZone.off('error');
            $j('.dz-progress', $j(aDropZone.element)).hide();
            if (debug) console.debug('rejecting uploadFiles promise');
            aDropZone.removeAllFiles();
            deferred.reject();
        });
        aDropZone.options.autoProcessQueue = true;
        aDropZone.processQueue();
        return deferred.promise();
    } else return deferred.resolve().promise();
}

function ajaxSubmitGroupForm(data) {
    // first upload the file
    // then ask the server to save the group
    $j.ajax({
        type: 'POST',
        url: 'ajax/getGroupForm.php',
        data: data,
        dataType: 'json'
    })
        .done(function (JSONObj) {
            if (JSONObj.status.length > 0) {
                $j.when(showHideDiv('', JSONObj.msg, JSONObj.status == 'OK')).then(function () {
                    if (null !== table) table.ajax.reload(null, false);
                    else self.document.location.reload();
                });
            }
        });
}

function deleteGroup(jqueryObj, id_group, message) {
    if ('undefined' === typeof message) message = $j('#deleteGroupMSG').text();
    // the trick below should emulate php's urldecode behaviour
    if (confirm(decodeURIComponent((message + '').replace(/\+/g, '%20')))) {
        $j.ajax({
            type: 'POST',
            url: 'ajax/deleteGroup.php',
            data: { id: id_group },
            dataType: 'json'
        })
        .done(function (JSONObj) {
            if (JSONObj) {
                if (JSONObj.status == 'OK') {
                    // deletes the corresponding row from the DOM with a fadeout effect
                    showHideDiv('', JSONObj.msg, true);
                    jqueryObj.parents("tr").fadeOut("slow", function () {
                        var pos = $j('#completeGropusList').dataTable().fnGetPosition(this);
                        $j('#completeGropusList').dataTable().fnDeleteRow(pos);
                    });
                } else {
                    showHideDiv('', JSONObj.msg, false);
                }
            }
        })
        .fail(function () { showHideDiv('', 'Server Error', false) });
    }
}

function toggleGroupDetails(groupId, imgObj) {

    const row = table.row($j(imgObj).parents('tr'));

    if (row.child.isShown()) {
        /* This row is already open - close it */
        imgObj.src = HTTP_ROOT_DIR + "/layout/" + ADA_TEMPLATE_FAMILY + "/img/details_open.png";
        row.child.hide();
    } else {
        /* Open this row */
        imgObj.src = HTTP_ROOT_DIR + "/js/include/jquery/ui/images/ui-anim_basic_16x16.gif";
        var imageReference = imgObj;
        $j.ajax({
            method: 'GET',
            url: 'ajax/getData.php',
            data: { object: 'Groups' , id: groupId}
        })
        .done(function (JSONObj) {
            if ('data' in JSONObj && JSONObj.data.length>0 && 'members' in JSONObj.data[0] && JSONObj.data[0].members.length>0) {
                row.child( ()=>{
                    const html = ["<ol class='group-users-details ui list'>"];
                    JSONObj.data[0].members.map((el) => {
                        html.push(`<li class='group-users-item'>${el.nome} ${el.coognome} (${el.username})</li>`)
                    });
                    html.push('</ol>');
                    return html.join("\n");
                } ).show();
            }



        })
        .fail(function () {
            console.log("ajax call has failed");
        })
        .always(function () {
            imageReference.src = HTTP_ROOT_DIR + "/layout/" + ADA_TEMPLATE_FAMILY + "/img/details_close.png";
        });

    }
}
