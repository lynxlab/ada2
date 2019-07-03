/**
 * @package 	badges module
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2019, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version		0.1
 */


function initDoc(dataUrl) {
    $j.ajax({
        type: "GET",
        url: dataUrl,
        cache: false,
        beforeSend: function() {
        }
    })
    .done(function(userbadges) {
        if (Object.keys(userbadges).length > 0) {
            var messages = {
                unrewarded: $j('#unrewardedMSG').html(),
                rewarded: $j('#rewardedMSG').html(),
                nobadges: $j('#noBadgesMSG').html(),
                error: $j('#badgesErrorMSG').html()
            };

            for (var courseId in userbadges) {
                if (userbadges.hasOwnProperty(courseId) && 'course' in userbadges[courseId]) {
                    var course = userbadges[courseId].course;
                    if ('name' in course) {
                        // make the main course div
                        var courseCont = $j('<div class="courseContainer"></div>');
                        courseCont.appendTo($j('#userBadgesContainer'));
                        // header with course name
                        $j('<h4 class="ui top attached header">'+course.name+'</h4>').appendTo(courseCont);
                        if ('courseInstances' in userbadges[courseId]) {
                            for (instanceId in userbadges[courseId].courseInstances) {
                                if (userbadges[courseId].courseInstances.hasOwnProperty(instanceId)) {
                                    var instance = userbadges[courseId].courseInstances[instanceId];
                                    if ('name' in instance) {
                                        var rewards = instance.badges.filter(function(b){ return b.issuedOn!==null; }).length,
                                        countBadges = instance.badges.length;
                                        var instanceCont = $j('<div class="ui fluid accordion attached segment"></div>');
                                        instanceCont.appendTo(courseCont);
                                        // instance name as accordion title
                                        $j('<div class="title"><i class="dropdown icon"></i>'+
                                            instance.name +
                                            '<div class="ui right label">'+
                                            $j('#rewardsCountMSG').text().replace("{rewards}",rewards).replace("{countBadges}",countBadges).toLowerCase() +
                                            '</div>'+
                                        '</div>').appendTo(instanceCont);
                                        // instance badges as accordion content
                                        var content = $j('<div class="content">'+
                                            badgesToHTML(instance, messages)
                                        +'</div>').appendTo(instanceCont);
                                    }
                                }
                            }
                        }
                    }
                }
                $j('.ui.accordion').accordion();
            }
        }
    })
    .always(function() {
    });
}
