function initDoc() {

    var lastCol = $j('table.doDataTable thead th').length;
    var colDefs = [
           {"aTargets" : [0], "sWidth":"50%" },
           {"aTargets" : [2,4], "sType":"date-eu" },
           {"aTargets" : [3], "sType":"formatted-num" },
           {"aTargets": [lastCol-1], "sClass" : "actionCol", "bSortable":false}
    ];

    datatable = $j('table.doDataTable').dataTable({
    	"aaSorting": [[ 2, "desc" ]],
        "bFilter": true,
        "bInfo": true,
        "bSort": true,
        "bAutoWidth": true,
        "bPaginate" : true,
        "aoColumnDefs": colDefs,
        "oLanguage": {
           "sUrl": HTTP_ROOT_DIR + "/js/include/jquery/dataTables/dataTablesLang.php"
        }
    });

    var icon = $j('#bagesPopupLink').siblings('.icon').first();
    var dataUrl = $j('#bagesPopupLink').parent('.item').data('dataurl');
    var jsUrl = $j('#bagesPopupLink').parent('.item').data('jsurl');
    if ('undefined' !== typeof dataUrl) {
        $j.ajax({
            type: "GET",
            url: dataUrl,
            cache: false,
            beforeSend: function() {
                icon.addClass('loading');
            }
        })
        .done(function(userbadges) {
            fillBadges(userbadges, jsUrl);
        })
        .always(function() {
            icon.removeClass('loading');
        });
    }

    $j('#bagesPopupLink').on('click', function() {
        $j('#badgesModal').modal('show');
    });

    var fillBadges = function(userbadges, jsUrl) {
        var messages = {
            unrewarded: $j('#unrewardedMSG').html(),
            rewarded: $j('#rewardedMSG').html(),
            nobadges: $j('#noBadgesMSG').html(),
            error: $j('#badgesErrorMSG').html()
        };

        var doShow = function() {
            var course = userbadges[Object.keys(userbadges)[0]];
            var instance = course.courseInstances[Object.keys(course.courseInstances)[0]];

            var rewards = instance.badges.filter(function(b){ return b.issuedOn!==null; }).length,
                countBadges = instance.badges.length;
            $j('<span class="rewardsCount"> '+
                $j('#rewardsCountMSG').text().replace("{rewards}",rewards).replace("{countBadges}",countBadges)
            +'</span>').appendTo($j('#bagesPopupLink'));

            $j('.content','#badgesModal').html(
                // userbadges has one course only, pass it
                badgesToHTML(instance, messages)
            );
        }

        if ('function' === typeof badgesToHTML) {
            doShow();
        } else {
            $j.getScript(jsUrl).done(function(){
                doShow();
            });
        }
    }
}

