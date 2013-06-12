<?php
/**
 * CourseInstanceForm file
 *
 * PHP version 5
 *
 * @package   Default
 * @author    vito <vito@lynxlab.com>
 * @copyright Copyright (c) 2010-2010, Lynx s.r.l.
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 */
require_once 'lib/classes/FForm.inc.php';
/**
 * 
 */
class CourseInstanceSubscriptionsForm extends FForm {
    public function  __construct($presubscriptions, $subscriptions, $instanceId) {
        parent::__construct();

        $data = array(
            ADA_STATUS_PRESUBSCRIBED => translateFN('Preiscritto'),
            ADA_STATUS_SUBSCRIBED => translateFN('Iscritto'),
            ADA_STATUS_REMOVED => translateFN('Rimosso'),
            ADA_STATUS_VISITOR => translateFN('In visita'),
            ADA_SERVICE_SUBSCRIPTION_STATUS_COMPLETED => translateFN('Completato')
            );

        foreach($presubscriptions as $p) {
            $selectId = 'currentStatus[' . $p->getSubscriberId() . ']';
            $hiddenId = 'previousStatus[' . $p->getSubscriberId() . ']';

            $this->addSelect($selectId,
                    $p->getSubscriberFullname(),
                    $data,
                    $p->getSubscriptionStatus());
            
            $this->addHidden($hiddenId)
                 ->withData($p->getSubscriptionStatus());
        }

        foreach($subscriptions as $p) {
            $selectId = 'currentStatus[' . $p->getSubscriberId() . ']';
            $hiddenId = 'previousStatus[' . $p->getSubscriberId() . ']';

            $this->addSelect($selectId,
                    $p->getSubscriberFullname(),
                    $data,
                    $p->getSubscriptionStatus());
            
            $this->addHidden($hiddenId)
                 ->withData($p->getSubscriptionStatus());
        }

        $this->addHidden('instanceId')
             ->withData($instanceId);
    }
}