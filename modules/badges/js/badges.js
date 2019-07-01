/**
 * @package 	badges module
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2019, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version		0.1
 */

var debugForm = false;
var table = null;

function initDoc() {
    Dropzone.autoDiscover = false;
    initToolTips();
    initButtons();
    initDataTables();
}

function initDropZone() {
    var dropZones = [];
    $j('.dropzone').each(function(index, el) {
    	var name = $j(el).attr('id');
    	if ($j('#'+name).length>0) {

    		if ($j('#'+name).data('type') == 'image') {
    			var mimeTypes = 'image/png';
                var maxFileSize = 0.25;
                var minWidth = 96;
    			var maxThumbnailFilesize = maxFileSize;
    		}

    		dropZones.push (new Dropzone('#'+name, {
    			paramName : 'uploaded_'+name,
    			maxFiles: 1,
    			url : 'ajax/upload.php',
    			autoProcessQueue: false,
    			addRemoveLinks: true,
    			acceptedFiles: mimeTypes,
                maxFilesize: maxFileSize,
                minWidth: minWidth,
    			// maxThumbnailFilesize: maxThumbnailFilesize,
    			dictInvalidFileType: $j('#dictInvalidFileType').text(),
    			dictRemoveFile: $j('#dictRemoveFile').text(),
                dictFileTooBig: $j('#dictFileTooBig').text(),
                dictNonSquare:  $j('#dictNonSquare').text(),
                dictMinWidth:   $j('#dictMinWidth').text() + minWidth + 'px x '+minWidth+'px',
    			hiddenInputContainer: '#'+name,
                accept: function(file, done) {
                    file.doAccept = done;
                    file.doReject = function(message) { done(message); };
                },
    			init: function() {
                    var that = this;

    				this.on("error", function(file, message) {
    					that.removeFile(file);
    					setTimeout(function() {
                            showHideDiv("Error", message, false);
    					}, 100);
    				});

    				this.on("success", function(file, responseObject) {
						$j(file.previewElement).fadeOut('slow');
						// add an hidden to pass the real, temp uploaded filename
						if ('undefined' !== typeof responseObject.fileName) {
							if (debugForm) console.log('adding the hidden input');
							$j('<input>').attr({
								type: 'hidden',
								name: name+'fileNames[]',
								value: responseObject.fileName
							}).appendTo('#'+name);
						} else {
							if (debugForm) console.log('NOT adding the hidden input');
						}
                    });

                    this.on("thumbnail", function(file){
                        if ('function' === typeof file.doReject) {
                            if ('undefined' == typeof file.width || 'undefined' == typeof file.height) {
                                file.doReject('cannot read file');
                            } else if ('undefined' != typeof file.width && 'undefined' != typeof file.height
                                        && file.width != file.height) {
                                file.doReject(that.options.dictNonSquare);
                            } else if ('undefined' != typeof file.width && 'undefined' != typeof file.height
                                        && file.width < that.options.minWidth) {
                                file.doReject(that.options.dictMinWidth);
                            } else {
                                file.doAccept();
                            }
                        }
                    });

    				this.on("addedfile", function(file) {
                        /**
                         * set value of dropzone associated text to file name and trigger change to revalidate
                         * if text has attribute data-validate="mandatoryDropzone" proper actions will be taken
                         */
                        $j('input[type="text"][name="'+$j(that.element).attr('id')+'"]').val(file.name).trigger('change');

    				});

    				this.on("removedfile", function(file) {
    					/**
    					 * set value of dropzone associated text to empty and trigger change to revalidate
    					 * if text has attribute data-validate="mandatoryDropzone" proper actions will be taken
    					 */
    					$j('input[type="text"][name="'+$j(that.element).attr('id')+'"]').val('').trigger('change');
    				});
    			}
    		}));
        }
    });
    return dropZones;
}

function initDataTables() {
    table = $j('#completeBadgesList').DataTable({
        "ajax": function (data, callback, settings) {
            $j.ajax({
                'type': 'GET',
                'url': "ajax/getData.php",
                'cache' : false,
                'data': { object: 'Badge' }
            })
            .done(function (response) {
                callback(response);
            })
            .fail(function (response) {
                if (debugForm) console.debug('dataTable ajax fail ', response.responseJSON);
                $j.when(showHideDiv("(" + response.status + ") " + response.statusText, ('error' in response.responseJSON) ? response.responseJSON.error : 'unkown error', false))
                    .then(function () { callback(response.responseJSON); });
            });
        },
        "deferRender": true,
        "processing": true,
        "autoWidth": false,
        "columns": [
            { "bSearchable": false, "bSortable": false, "sWidth": "3%" },
            { "sWidth": "30%" },
            { "sWidth": "20%" },
            { "sWidth": "20%" },
            { "bSearchable": false, "bSortable": false, "sWidth": "8%" }
        ],
        "order": [[1, 'asc']],
        "language": {
            "url": HTTP_ROOT_DIR + "/js/include/jquery/dataTables/dataTablesLang.php"
        },
    });

    table.on('draw', function() { initButtons(); });
}

function editBadge(id_badge) {
    // ask the server for the edit badge form
    $j.ajax({
        type: 'GET',
        url: 'ajax/getBadgeForm.php',
        data: { uuid: id_badge },
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
                    if (debugForm) console.log('okTosubmit? ',okToSubmit);
                    // and if ok ajax-submit the form
                    if (okToSubmit) {
                        // run other checks...
                        var formName = theDialog.find('form').attr('name');
                        $j('form *[data-notempty="true"]', theDialog).each(
                            function(i,el) {
                                var val = $j(el).val().trim();
                                if (val.length<=0) {
                                    $j(el).siblings('label').addClass('error');
                                    okToSubmit = false;
                                    if (debugForm) console.log('%s is empty', $j(el).attr('name'));
                                } else {
                                    $j(el).siblings('label').removeClass('error');
                                }
                            }
                        );

                        // check for non-empty dropzone only for new badge dialog
                        if ($j('#badgeuuid', theDialog).length <=0) {
                            $j(dropZones).each(function() {
                                    var msg = $j('.dz-message', $j(this.element));
                                    msg.removeClass('error');
                                    if (this.files.length <=0) {
                                        if (debugForm) console.log('zero files');
                                        okToSubmit = false;
                                        msg.addClass('error');
                                    }
                                }
                            );
                        }

                        if (okToSubmit) {
                            $j('#error_form_'+formName, theDialog).addClass('hide_erorr').removeClass('show_error');
                            $j.when(uploadFiles(dropZones[0]), debugForm).done(function() {
                                ajaxSubmitBadgeForm(theDialog.find('form').serialize());
                            });
                            theDialog.dialog('close');
                        } else {
                            $j('#error_form_'+formName, theDialog).removeClass('hide_erorr').addClass('show_error');
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

function uploadFiles (aDropZone, debug) {
    var deferred = $j.Deferred();
    if (aDropZone.files.length > 0) {
        $j('.dz-progress',$j(aDropZone.element)).show();
        aDropZone.on('queuecomplete', function() {
            aDropZone.options.autoProcessQueue = false;
            aDropZone.off('queuecomplete');
            aDropZone.off('error');
            $j('.dz-progress',$j(aDropZone.element)).hide();
            if (debug) console.debug('resolving uploadFiles promise');
            aDropZone.removeAllFiles();
            deferred.resolve();
        });
        aDropZone.on('error', function() {
            aDropZone.off('queuecomplete');
            aDropZone.off('error');
            $j('.dz-progress',$j(aDropZone.element)).hide();
            if (debug) console.debug('rejecting uploadFiles promise');
            aDropZone.removeAllFiles();
            deferred.reject();
        });
        aDropZone.options.autoProcessQueue = true;
        aDropZone.processQueue();
        return deferred.promise();
    } else return deferred.resolve().promise();
}

function ajaxSubmitBadgeForm(data) {
    // first upload the file

    // then ask the server to save the badge
    $j.ajax({
        type: 'POST',
        url: 'ajax/saveBadge.php',
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

function deleteBadge(jqueryObj, id_badge, message) {
    if ('undefined' === typeof message) message = $j('#deleteBadgeMSG').text();
    // the trick below should emulate php's urldecode behaviour
    if (confirm(decodeURIComponent((message + '').replace(/\+/g, '%20')))) {
        $j.ajax({
            type: 'POST',
            url: 'ajax/deleteBadge.php',
            data: { uuid: id_badge },
            dataType: 'json'
        })
        .done(function (JSONObj) {
            if (JSONObj) {
                if (JSONObj.status == 'OK') {
                    // deletes the corresponding row from the DOM with a fadeout effect
                    showHideDiv('', JSONObj.msg, true);
                    jqueryObj.parents("tr").fadeOut("slow", function () {
                        var pos = $j('#completeBadgesList').dataTable().fnGetPosition(this);
                        $j('#completeBadgesList').dataTable().fnDeleteRow(pos);
                    });
                } else {
                    showHideDiv('', JSONObj.msg, false);
                }
            }
        })
        .fail(function () { showHideDiv('', 'Server Error', false) });
    }
}
