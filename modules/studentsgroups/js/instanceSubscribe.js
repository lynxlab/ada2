/**
 * @package 	studentsgroups module
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2020, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version		0.1
 */

/**
 * This file is loaded by switcher/list_instances.js
 */
debugForm = false;
var studentsgroupsAPI = {
    GroupInstanceSubscribe: function (url) {

        this.baseUrl = url;
        this.courseId = null;
        this.instanceId = null;
        this.groupId = null;

        this.setCourseId = function (id) {
            this.courseId = id;
        }

        this.setInstanceId = function (id) {
            this.instanceId = id;
        }

        this.setGroupId = function (id) {
            this.groupId = id;
        }

        this.subscribeGroup = function () {
            // ask the server for the subscribe group form
            $j.ajax({
                type: 'GET',
                url: this.baseUrl + '/ajax/getSubscribeGroupForm.php',
                // data: { id: id_group },
                dataType: 'json'
            })
            .done((JSONObj) => {
                if (JSONObj.status == 'OK') {
                    if (JSONObj.html && JSONObj.html.length > 0) {
                        // build the dialog
                        var theDialog = $j('<div />').html(JSONObj.html).dialog({
                            title: JSONObj.dialogTitle,
                            autoOpen: false,
                            modal: true,
                            resizable: false,
                            width: '80%',
                            // height: (!isUpdate ? 'auto' : 500),
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

                        theDialog.find('select[name="subscribegroup"]').on('change', (event) => {
                            this.groupId = parseInt($j(event.target).val());
                        });

                        theDialog.find('select[name="subscribegroup"]').val(
                            theDialog.find('select[name="subscribegroup"] > option').first().attr('value')
                        ).trigger('change');

                        // get and hide the submit button
                        var submitButton = theDialog.find('input[type="submit"]');
                        submitButton.hide();

                        // dialog buttons array
                        var dialogButtons = {};

                        // confirm dialog button
                        dialogButtons[i18n['confirm']] = () => {
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

                                if (okToSubmit) {
                                    $j('#error_form_' + formName, theDialog).addClass('hide_erorr').removeClass('show_error');
                                    $j.when(this.ajaxSubmitSubscribeForm())
                                        .done(function () {
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

        this.ajaxSubmitSubscribeForm = function () {
            $j.ajax({
                type: 'POST',
                url: this.baseUrl + '/ajax/getSubscribeGroupForm.php',
                data: {
                    courseId: this.courseId,
                    instanceId: this.instanceId,
                    groupId: this.groupId
                },
                dataType: 'json'
            })
            .done(function (JSONObj) {
                if (JSONObj.status.length > 0) {
                    $j.when(showHideDiv('', JSONObj.msg, JSONObj.status == 'OK')).then(function () {

                    });
                }
            });
        }
    }
}