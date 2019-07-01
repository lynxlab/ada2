/**
 * @package 	badges module
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2019, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version		0.1
 */

var debugForm = false;
var table = null;

function initDoc(courseId) {
    initToolTips();
    initButtons();
    initDataTables(courseId);
}

function initDataTables(courseId) {
    table = $j('#completeCourseBadgesList').DataTable({
        "ajax": function (data, callback, settings) {
            $j.ajax({
                'type': 'GET',
                'url': "ajax/getData.php",
                'cache': false,
                'data': { object: 'CourseBadge', courseId: courseId }
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
            // { "bSearchable": false, "bSortable": false, "sWidth": "3%" },
            // { "sWidth": "30%" },
            { "sWidth": "41%" },
            { "sWidth": "41%" },
            { "bSearchable": false, "bSortable": false, "sWidth": "8%" }
        ],
        "order": [[0, 'asc']],
        "language": {
            "url": HTTP_ROOT_DIR + "/js/include/jquery/dataTables/dataTablesLang.php"
        },
    });
    table.on('draw', function() { initButtons(); });
}

function ajaxSubmitBadgeForm(button) {
    var theForm = button.parents('form').first();

    if (!button.hasClass('disabled')) {
        $j.ajax({
            type: 'POST',
            url: 'ajax/saveCourseBadge.php',
            data: theForm.serialize(),
            dataType: 'json',
            beforeSend: function() { button.addClass('disabled'); }
        })
        .done(function (JSONObj) {
            if (JSONObj.status.length > 0) {
                $j.when(showHideDiv('', JSONObj.msg, JSONObj.status == 'OK')).then(function () {
                    if (JSONObj.status == 'OK') {
                        if (null !== table) table.ajax.reload(null, false);
                        else self.document.location.reload();
                    }
                    button.removeClass('disabled');
                });
            }
        })
        .fail(function () {
            $j.when(showHideDiv('', 'Server Error', false)).
            done(function() { button.removeClass('disabled'); });
        });
    }
}

function deleteCourseBadge(jqueryObj, data, message) {
    if ('undefined' === typeof message) message = $j('#deleteCourseBadgeMSG').text();
    // the trick below should emulate php's urldecode behaviour
    if (confirm(decodeURIComponent((message + '').replace(/\+/g, '%20')))) {
        $j.ajax({
            type: 'POST',
            url: 'ajax/deleteCourseBadge.php',
            data: data,
            dataType: 'json'
        })
        .done(function (JSONObj) {
            if (JSONObj) {
                if (JSONObj.status == 'OK') {
                    // deletes the corresponding row from the DOM with a fadeout effect
                    showHideDiv('', JSONObj.msg, true);
                    jqueryObj.parents("tr").fadeOut("slow", function () {
                        var pos = $j('#completeCourseBadgesList').dataTable().fnGetPosition(this);
                        $j('#completeCourseBadgesList').dataTable().fnDeleteRow(pos);
                    });
                } else {
                    showHideDiv('', JSONObj.msg, false);
                }
            }
        })
        .fail(function () { showHideDiv('', 'Server Error', false) });
    }
}
