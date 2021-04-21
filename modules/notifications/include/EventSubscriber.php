<?php
/**
 * @package 	notifications module
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2021, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version		0.1
 */

namespace Lynxlab\ADA\Module\Notifications;
use PHPSQLParser\PHPSQLCreator;
use PHPSQLParser\PHPSQLParser;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Lynxlab\ADA\Module\EventDispatcher\Events\CoreEvent;
use Lynxlab\ADA\Module\EventDispatcher\Events\ForumEvent;
use Lynxlab\ADA\Module\EventDispatcher\Events\NodeEvent;
use Lynxlab\ADA\Module\EventDispatcher\Subscribers\ADAMethodSubscriberInterface;
use Lynxlab\ADA\Module\EventDispatcher\Subscribers\ADAScriptSubscriberInterface;

/**
 * EventSubscriber Class, defines node events names and handlers for this module
 */
class EventSubscriber implements ADAMethodSubscriberInterface, ADAScriptSubscriberInterface, EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            ForumEvent::INDEXACTIONINIT => 'addForumIndexActions',
            NodeEvent::POSTADDREDIRECT => 'postAddRedircet',
        ];
    }

    public static function getSubscribedMethods()
    {
        return [
            'AMA_Tester_DataHandler::get_notes_for_this_course_instance' => [
                CoreEvent::AMAPDOPREGETALL => 'preGetNotes',
                CoreEvent::AMAPDOPOSTGETALL => 'postGetNotes',
            ],
        ];
    }

    public static function getSubscribedScripts()
    {
        return [
            'main_index.php' => [
                CoreEvent::PAGEPRERENDER => 'mainIndexPreRender',
            ],
        ];
    }

    /**
     * Add module's own query parts where needed
     *
     * @param CoreEvent $event
     * @return void
     */
    public function preGetNotes(CoreEvent $event)
    {
        $args = $event->getArguments();
        $queryParts = new PHPSQLParser($args['query']);
        $add = new PHPSQLParser('SELECT N.id_istanza');
        if (is_array($queryParts->parsed['SELECT']) && count($queryParts->parsed['SELECT']) > 0) {
            // set delimiter of the last parsed SELECT
            $queryParts->parsed['SELECT'][count($queryParts->parsed['SELECT']) - 1]['delim'] = ',';
        }
        // add own fields
        foreach ($add->parsed['SELECT'] as $v) {
            $queryParts->parsed['SELECT'][] = $v;
        }
        $query = new PHPSQLCreator($queryParts->parsed);
        $args['query'] = $query->created;
        $event->setArguments($args);
    }

    /**
     * Modify query results where needed
     *
     * @param CoreEvent $event
     * @return void
     */
    public function postGetNotes(CoreEvent $event)
    {
        $args = $event->getArguments();
        $values = $args['retval'];
        $id_instance = intval(reset($values)['id_istanza']);
        $ntDH = AMANotificationsDataHandler::instance(\MultiPort::getDSN($_SESSION['sess_selected_tester']));
        $result = $ntDH->findBy('Notification', [
            'userId' => $_SESSION['sess_userObj']->getId(),
            'instanceId' => $id_instance,
            'notificationType' => Notification::getNotificationFromNodeType(ADA_NOTE_TYPE),
        ]);
        $notificationNodes = [];
        foreach ($result as $notification) {
            $notificationNodes[$notification->getNodeId()] = [
                'notificationId' => $notification->getNotificationId(),
                'isActive' => $notification->getIsActive(),
            ];
        }
        $values = array_map(function ($el) use ($notificationNodes) {
            if (array_key_exists($el['id_nodo'], $notificationNodes)) {
                $el['hasNotifications'] = $notificationNodes[$el['id_nodo']]['isActive'];
                $el['notificationId'] = $notificationNodes[$el['id_nodo']]['notificationId'];
            }
            return $el;
        }, $values);
        $args['retval'] = $values;
        $event->setArguments($args);
    }

    /**
     * ForumEvent::INDEXACTIONDONE
     * Add the bell icon button to set or unset active notification flag on forum notes
     *
     * @param ForumEvent $event
     * @return void
     */
    public function addForumIndexActions(ForumEvent $event)
    {
        $container = $event->getSubject();
        $nodeData = $event->getArguments();
        if (array_key_exists('level', $nodeData['params']) && $nodeData['params']['level'] >= 1 ) {
            $color = 'red';
            $title = translateFN('Non ricevi notifiche; clicca per attivarle');
            $isActive = false;
            if (array_key_exists('hasNotifications', $nodeData['params']['node']) && $nodeData['params']['node']['hasNotifications']) {
                $color = 'green';
                $title = translateFN('Ricevi notifiche; clicca per disattivarle');
                $isActive = true;
            }
            $button = \CDOMElement::create('button', 'class:ui tiny ' . $color . ' icon button noteSubscribe,title:' . $title);
            $button->addChild(\CDOMElement::create('i', 'class:bell outline icon'));
            if (array_key_exists('notificationId', $nodeData['params']['node'])) {
                $button->setAttribute('data-notification-id', $nodeData['params']['node']['notificationId']);
            }
            $button->setAttribute('data-node-id', $nodeData['params']['node']['id_nodo']);
            $button->setAttribute('data-instance-id', $nodeData['params']['node']['id_istanza']);
            $button->setAttribute('data-is-active', (int)$isActive);
            $button->setAttribute('data-notification-type', Notification::getNotificationFromNodeType(ADA_NOTE_TYPE));
            $container->addChild($button);
        }
    }

    /**
     * Add this module's own javascript where needed
     *
     * @param CoreEvent $event
     * @return void
     */
    public function mainIndexPreRender(CoreEvent $event)
    {
        $renderData = $event->getArguments();
        $moduleJS = [
            'layout_dataAr' => [
                'JS_filename' => [
                    'initval' => [],
                    'additems' => [
                        JQUERY_UI,
                        MODULES_NOTIFICATIONS_PATH . '/js/modules_define.js.php',
                        MODULES_NOTIFICATIONS_PATH . '/js/notificationsManager.js',
                    ],
                ],
                'CSS_filename' => [
                    'initval' => [],
                    'additems' => [
                        MODULES_NOTIFICATIONS_PATH . '/layout/' . $_SESSION['sess_template_family'] . '/css/showHideDiv.css',
                    ],
                ],
            ],
            'options' => [
                'onload_func' => [
                    'initval' => '',
                    'additems' => function ($v) {
                        return $v . '; new NotificationsManager().addSubscribeHandler(\'.noteActions\',\'button.noteSubscribe\');';
                    }
                ],
            ],
        ];
        /**
         * modify render data
         */
        foreach ($moduleJS as $renderKey => $renderSubkeys) {
            foreach ($renderSubkeys as $renderSubKey => $renderVal) {
                if (!array_key_exists($renderSubKey, $renderData[$renderKey])) {
                    $renderData[$renderKey][$renderSubKey] = $renderVal['initval'];
                }
                $addItems = $renderVal['additems'];
                if (is_callable($addItems)) {
                    $renderData[$renderKey][$renderSubKey] = $addItems($renderData[$renderKey][$renderSubKey]);
                } else if (is_array($renderData[$renderKey][$renderSubKey])) {
                    if (is_array($addItems)) {
                        $renderData[$renderKey][$renderSubKey] = array_merge($renderData[$renderKey][$renderSubKey], $addItems);
                    } else {
                        $renderData[$renderKey][$renderSubKey][] = $addItems;
                    }
                } else {
                    $renderData[$renderKey][$renderSubKey] = $addItems;
                }
            }
        }
        $event->setArguments($renderData);
    }

    /**
     * Closes the browser connection, then enqueues the forum note notification and runs the queue
     *
     * @param NodeEvent $event
     *
     * @return void
     */
    public function postAddRedircet(NodeEvent $event) {
        self::closeBrowserConnection();
        // populate the email queue
        $this->enqueueForumNote($event, true);
        // run the queuemanager on the emailqueue, using a dayly log file (true param)
        (new QueueManager(EmailQueueItem::fqcn()))->run(true);
    }

    /**
     * Enquues a notification email for each student and tutor subscribed to note instance
     * and having set their notification preferences to receive emails
     *
     * @param NodeEvent $event
     * @param boolean $isNewNode
     *
     * @return void
     */
    public function enqueueForumNote(NodeEvent $event, $isNewNode = true) {
        $nodeData = $event->getSubject();
        if ($isNewNode) {
            $nodeId = $nodeData['id'];
            $instanceId = array_key_exists('id_instance', $nodeData) ? $nodeData['id_instance'] : $_SESSION['sess_id_course_instance'];
            $instanceSubscribedList = [];
            $notifyUserList = [];
            if (in_array($nodeData['type'], [ ADA_NOTE_TYPE ])) {
                $ntDH = AMANotificationsDataHandler::instance(\MultiPort::getDSN($_SESSION['sess_selected_tester']));
                // load all students and tutors of the course instance
                $students =  $ntDH->get_students_for_course_instance($instanceId);
                if (!\AMA_DB::isError($students)) {
                    $instanceSubscribedList = array_merge($instanceSubscribedList, array_map(function($el){
                        return [
                            'id_utente' => intval($el['id_utente']),
                            'nome' => $el['nome'],
                            'cognome' => $el['cognome'],
                            'e_mail' => $el['e_mail'],
                            'type' => AMA_TYPE_STUDENT,
                        ];
                    }, $students));
                }

                $tutors = $ntDH->course_instance_tutor_info_get($instanceId);
                if (!\AMA_DB::isError($tutors)) {
                    $instanceSubscribedList = array_merge($instanceSubscribedList, array_map(function($el){
                        return [
                            'id_utente' => intval($el['id_utente_tutor']),
                            'nome' => $el['nome'],
                            'cognome' => $el['cognome'],
                            'e_mail' => $el['e_mail'],
                            'type' => AMA_TYPE_TUTOR,
                        ];
                    }, $tutors));
                }

                if (count($instanceSubscribedList)>0) {
                    // load users notification preferences for the forum notes
                    $notifyUserList = $ntDH->findBy('Notification', [
                        'userId' => [
                            'op' => 'IN',
                            'value' => sprintf("(%s)", implode(', ', array_map(function($el) { return $el['id_utente']; }, $instanceSubscribedList))),
                        ],
                        'nodeId' => $nodeData['parent_id'],
                        'instanceId' => $instanceId,
                        'notificationType' => Notification::getNotificationFromNodeType($nodeData['type']),
                        'isActive' => true,
                    ]);
                    if (is_array($notifyUserList) && count($notifyUserList)>0) {
                        $qItem = new EmailQueueItem();
                        $qItem->setEmailType(EmailQueueItem::NEWFORUMNOTE);
                        // prepare data for the emailqueue: course, course instance, layout objects
                        $instanceObj = new \Course_instance($instanceId);
                        $courseObj = new \Course($instanceObj->getCourseId());
                        $layoutObj = Notification::getLayoutObj(EmailQueueItem::getEmailConfigFromType($qItem->getEmailType())['template']);
                        $qItem->setSubject(
                            trim(
                                sprintf("[%s] %s %s",
                                    PORTAL_NAME ,
                                    translateFN(EmailQueueItem::getEmailConfigFromType($qItem->getEmailType())['subject']),
                                    $courseObj->getTitle()
                            )
                        ));
                        $qItem->setStatus(EmailQueueItem::STATUS_ENQUEUED);

                        $saveData = [];
                        // foreach notifyUserList, build an EmailQueueItem with rendered template fields
                        foreach ($notifyUserList as $notifyUser) {
                            $userData = array_filter($instanceSubscribedList, function($el) use ($notifyUser) {
                                return $notifyUser->getUserId() == $el['id_utente'] && strlen($el['e_mail'])>0;
                            });
                            if (is_array($userData) && count($userData)>0) {
                                $userData = reset($userData);
                                $qItem->setUserId($userData['id_utente']);
                                $qItem->setRecipientEmail($userData['e_mail']);
                                $qItem->setRecipientFullName($userData['nome'].' '.$userData['cognome']);
                                $qItem->setBody(trim(
                                    Notification::HTMLFromTPL(
                                            EmailQueueItem::getEmailConfigFromType($qItem->getEmailType())['template'],
                                            [
                                                'userFirstName' => $userData['nome'],
                                                'userLastName' => $userData['cognome'],
                                                'courseTitle' => $courseObj->getTitle(),
                                                'instanceTitle' => $instanceObj->getTitle(),
                                                'nodeName' => $nodeData['name'],
                                                'nodeContent' => $nodeData['text'],
                                                'indexHref' => sprintf("%s/browsing/main_index.php?op=forum&id_course=%d&id_course_instance=%d#%s",
                                                    HTTP_ROOT_DIR,
                                                    $courseObj->getId(),
                                                    $instanceObj->getId(),
                                                    $nodeId
                                                ),
                                                'replyHref' => sprintf("%s/services/addnode.php?id_parent=%s&id_course=%d&id_course_instance=%d&type=NOTE",
                                                    HTTP_ROOT_DIR,
                                                    $nodeId,
                                                    $courseObj->getId(),
                                                    $instanceObj->getId()
                                                ),
                                                'nodeHref' => sprintf("%s/browsing/view.php?id_node=%s&id_course=%d&id_course_instance=%d",
                                                    HTTP_ROOT_DIR,
                                                    $nodeId,
                                                    $courseObj->getId(),
                                                    $instanceObj->getId()
                                                ),
                                            ],
                                            MODULES_NOTIFICATIONS_PATH, $layoutObj
                                    ))
                                );
                                $qItem->setEnqueueTS(time());
                            }
                            $saveData[] = $qItem->toArray();
                        }
                        // add entries to the queue table
                        $result = $ntDH->multiSaveEmailQueueItems($saveData);
                    }
                }
            }
        }
    }

    /**
     * Does header and buffer stuff to close the connection to the browser
     *
     * @return void
     */
    private static function closeBrowserConnection() {
        session_write_close();
        // buffer the output, close the connection with the browser and run a "background" task
        ob_end_clean();
        header("Connection: close\r\n");
        header("Content-Encoding: none\r\n");
        ignore_user_abort(true);
        // capture output
        ob_start();
        // flush all output
        ob_end_flush();
        flush();
        @ob_end_clean();
        if (function_exists('fastcgi_finish_request')) fastcgi_finish_request();
    }
}
