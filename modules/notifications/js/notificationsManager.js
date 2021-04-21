(function (window, undefined) {
    const debug = false;

    class NotificationsManager {

        constructor(options) {
            this.options = $j.extend({}, NotificationsManager.defaults, options);
            this.scrollBodyToHash();
        }

        scrollBodyToHash() {
            if (window.location.hash.length > 0 && $j('#expandNodes').length > 0) {
                // expand all nodes if the location has an hash
                $j('#expandNodes').trigger('click');
                // scroll to the named anchor
                const anchor = $j('a[name="' + window.location.hash.substr(1) + '"]');
                const parent = anchor.parent('.listItem.container');
                const promise = $j('html, body')
                    .animate({
                        'scrollTop': anchor.offset().top - parseFloat(parent.css("paddingTop")) - parseFloat(parent.css("marginTop"))
                    }, 1500, 'easeInOutExpo')
                    .promise();
                promise.done(function () {
                    $j('.previewMessage', parent).trigger('click');
                    // do fading 2 times
                    for (var i = 0; i < 2; i++) {
                        parent.fadeTo('slow', 0.5).fadeTo('slow', 1.0);
                    }
                });
                return promise;
            }
        }

        addSubscribeHandler(container, element) {
            $j(container).on('click', element + ':not(.disabled)', (event) => {
                const button = $j(event.currentTarget);
                const saveData = button.data();
                var savedclass = null;
                // if there's an icon, replace it with loader
                if ($j('i.icon', button).length > 0) {
                    savedclass = $j('i.icon', button).attr('class');
                    $j('i.icon', button).attr('class', 'loading icon');
                }
                button.toggleClass('disabled');
                $j.when(this.saveNotification(saveData))
                    .done((response) => {
                        if ('data' in response) {
                            if ('notificationId' in response.data) {
                                button.attr('data-notification-id', response.data.notificationId);
                                button.data('notification-id', response.data.notificationId);
                            }
                            if ('isActive' in response.data) {
                                button.removeClass('green red');
                                button.addClass(response.data.isActive ? 'green' : 'red');
                                button.attr('data-is-active', response.data.isActive ? 1 : 0);
                                button.data('is-active', response.data.isActive ? 1 : 0);
                            }
                        }
                        if ($j('#ADAJAX').length <= 0) {
                            this.showHideDiv('', response.msg, response.status == 'OK');
                        }
                    })
                    .fail((response) => {
                        this.showHideDiv('', 'Unknown error', false);
                        if (debug) {
                            console.log(response);
                        }
                    })
                    .always((response) => {
                        if (savedclass != null) {
                            $j('i.icon', button).attr('class', savedclass);
                        }
                        button.toggleClass('disabled');
                    });
            });
        }

        saveNotification(saveData) {
            return $j.ajax({
                method: 'POST',
                url: this.options.url + '/ajax/saveNotification.php',
                data: saveData,
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
        showHideDiv(title, message, isOK) {
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

    NotificationsManager.defaults = {
        url: MODULES_NOTIFICATIONS_HTTP,
    };
    window.NotificationsManager = NotificationsManager;
})(this);
