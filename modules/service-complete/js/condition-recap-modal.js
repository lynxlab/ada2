/**
 * SERVICE-COMPLETE MODULE.
 *
 * @package        service-complete module
 * @author         Giorgio Consorti <g.consorti@lynxlab.com>
 * @copyright      Copyright (c) 2021, Lynx s.r.l.
 * @license        http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link           service-complete
 * @version        0.1
 */

 function initSummaryModal(modulePath) {
     const summaryModalId = 'summaryModal';
     $j('.servicecomplete-summary-modal.button').on('click', function() {
         $j.ajax({
             method: 'GET',
             url: `${modulePath}/ajax/getSummaryModal.php`,
             data: $j(this).data(),
             dataType: 'html',
         })
         .done(function(html){
             if (html.length>0) {
                 $j('body').append($j(html).attr('id', summaryModalId));
                 $j(`#${summaryModalId}`).modal('setting', {
                     onHidden: function() {
                         $j(`#${summaryModalId}`).parent().remove();
                     }
                 }).modal('show');
             }
         })
         .fail(function(resp){
         });
     });
 }
