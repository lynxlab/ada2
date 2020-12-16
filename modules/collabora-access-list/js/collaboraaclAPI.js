/**
 * @package     collabora-access-list module
 * @author      giorgio <g.consorti@lynxlab.com>
 * @copyright   Copyright (c) 2020, Lynx s.r.l.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version     0.1
 */

debug = false;
const collaboraaclAPI = {
    GrantAccess: function (url) {

        this.baseUrl = url;

        this.GrantAccessForm = function (dataObj, callback) {
            // ask the server for the grant access form
            $j.ajax({
                type: 'GET',
                url: this.baseUrl + '/ajax/getGrantAccessForm.php',
                data: dataObj,
                dataType: 'json',
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

                            // get and hide the submit button
                            var submitButton = theDialog.find('input[type="submit"]');
                            submitButton.hide();

                            // dialog buttons array
                            var dialogButtons = {};

                            // confirm dialog button
                            dialogButtons[i18n['confirm']] = () => {
                                // get form (previously hidden) submit button onclick code
                                var onClickDefaultAction = submitButton.attr('onclick');
                                if (debug) console.log('onclick will call', onClickDefaultAction);
                                // execute it, to hava ADA's own form validator
                                var okToSubmit = (onClickDefaultAction.length > 0) ? new Function(onClickDefaultAction)() : false;
                                if (debug) console.log('okTosubmit? ', okToSubmit);
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
                                                if (debug) console.log('%s is empty', $j(el).attr('name'));
                                            } else {
                                                $j(el).siblings('label').removeClass('error');
                                            }
                                        }
                                    );

                                    if (okToSubmit) {
                                        $j('#error_form_' + formName, theDialog).addClass('hide_erorr').removeClass('show_error');
                                        // prepare data to be submitted
                                        $j('select#grantedUsers option').prop('selected', true);
                                        const grantedUsers = $j('select#grantedUsers').val() || [];
                                        $j('select#grantedUsers option').prop('selected', false);
                                        $j.when(this.ajaxSubmitGrantAccessForm($j.extend({}, dataObj, { grantedUsers: grantedUsers })))
                                            .done(function (response) {
                                                if ('function' === typeof callback) {
                                                    callback(response.fileAclId || 0);
                                                }
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

                            $j('#users', theDialog).multiselect({
                                keepRenderingSort: true
                            });

                            // eventually open the dialog
                            theDialog.dialog('open');
                            $j('#multiselect_rightSelected').focus().trigger('click').blur();
                        }
                    } else {
                        if (JSONObj.msg) this.showHideDiv('', JSONObj.msg, false);
                    }
                })
                .fail(() => { this.showHideDiv('', 'Server Error', false) });
        }

        this.ajaxSubmitGrantAccessForm = function (submitData) {
            return $j.ajax({
                type: 'POST',
                url: this.baseUrl + '/ajax/getGrantAccessForm.php',
                data: submitData || {},
                dataType: 'json',
            })
                .done((JSONObj) => {
                    if (JSONObj.status.length > 0) {
                        $j.when(this.showHideDiv('', JSONObj.msg, JSONObj.status == 'OK')).then(function () {

                        });
                    }
                });
        }

        /**
         * shows and after 500ms removes the div to give feedback to the user about
         * the status of the executed operation (if it's been saved, delete or who knows what..)
         *
         * @param  title title to be displayed
         * @param  message message to the user
         * @return jQuery promise
         */
        this.showHideDiv = function (title, message, isOK) {
            var errorClass = (!isOK) ? ' error' : '';
            var content = "<div id='ADAJAX' class='saveResults popup" + errorClass + "'>";
            if (title.length > 0) content += "<p class='title'>" + title + "</p>";
            if (message.length > 0) content += "<p class='message'>" + message + "</p>";
            content += "</div>";
            var theDiv = $j(content);
            theDiv.css("position", "fixed");
            theDiv.css("z-index", 9000);
            theDiv.css("width", "350px");
            theDiv.css("top", ($j(window).height() / 2) - (theDiv.outerHeight() / 2));
            theDiv.css("left", ($j(window).width() / 2) - (theDiv.outerWidth() / 2));
            theDiv.hide().appendTo('body').fadeIn(500).delay(2000);
            var thePromise = theDiv.fadeOut(500);
            $j.when(thePromise).done(function () { theDiv.remove(); });
            return thePromise;
        }
    }
}