<?php

/**
 * @package 	notifications module
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2021, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version		0.1
 */

namespace Lynxlab\ADA\Module\Notifications;

class QueueManager extends NotificationBase
{
    private $logFile;
    private $lockFile;
    private $debug;
    private $queue;
    private $itemsClassName;
    private $lastGetTS = 0;
    /**
     * Set to true when run from a php cli script
     *
     * @var boolean
     */
    private static $isCLI = false;
    /** @var AMANotificationsDataHandler $dh */
    private $dh;

    const logdir = ROOT_DIR . DIRECTORY_SEPARATOR . 'log' . DIRECTORY_SEPARATOR . 'queuemanager' . DIRECTORY_SEPARATOR;

    public function __construct($itemsClassName, $dh = null)
    {
        $this->enableDebug();
        if (class_exists($itemsClassName)) {
            $this->itemsClassName = $itemsClassName;
            $classNameParts = explode("\\", $this->itemsClassName);
            self::$isCLI = php_sapi_name() == "cli";
            $lockFileName = (self::$isCLI ? 'CLI' : '') . end($classNameParts) . '.php.lock';
            $this->lockFile = ADA_UPLOAD_PATH . $lockFileName;
        } else {
            $this->itemsClassName = null;
            $this->lockFile = null;
            throw new NotificationException(translateFN($itemsClassName . ' non esiste'));
        }

        if (!is_null($dh)) {
            $this->dh = $dh;
        } else {
            $this->dh = AMANotificationsDataHandler::instance(\MultiPort::getDSN($_SESSION['sess_selected_tester']));
        }
    }

    /**
     * Will run the queue manager based on the itemsClassName passed to the constructor
     *
     * @param boolean $dailyLog true to make a daily log file. Default is to make a log for each call
     */
    public function run($dailyLog = false)
    {
        $useDate = ($dailyLog) ? date('d-m-Y') : date('d-m-Y_His');
        $classNameParts = explode("\\", $this->itemsClassName);
        $this->setLogFileName(str_replace("\\", "_", end($classNameParts) . '-' . $useDate . ".log"));
        if ($this->tryLock()) {
            register_shutdown_function(array($this, 'unlinkLockFile'));
            $this->logMessage("\nI've got a lockfile and I'm running as PID:" . getmypid());
            switch ($this->itemsClassName) {
                case EmailQueueItem::fqcn():
                    $this->runEmailQueueItem();
                    break;
            }
        } else {
            $this->logMessage("A process is already running and its lock file points to: " . readlink($this->lockFile) .
                " It should take care of doing your stuff. Bye.");
        }
    }

    /**
     * Will run the queue for EmailQueueItems class
     */
    private function runEmailQueueItem()
    {
        $sleepTime = intval(3600 / constant($this->itemsClassName . '::EMAILS_PER_HOUR') * 1000000); // sleep time in microseconds
        /**
         * Initializre the PHPMailer
         */
        require_once ROOT_DIR.'/include/phpMailer/ADAPHPMailer.php';
        $phpmailer = new \PHPMailer\PHPMailer\ADAPHPMailer();
        $phpmailer->CharSet = strtolower(ADA_CHARSET);
        $phpmailer->configSend();
        $phpmailer->SetFrom(ADA_NOREPLY_MAIL_ADDRESS);
        $phpmailer->AddReplyTo(ADA_NOREPLY_MAIL_ADDRESS);
        $phpmailer->IsHTML(true);
        while (count($this->queue = $this->getNewItems()) > 0) {
            $this->logMessage(sprintf("New Items found, starting main loop for %d items", count($this->queue)));
            /** @var EmailQueueItem $item */
            foreach ($this->queue as $count => $item) {
                if ($item instanceof EmailQueueItem) {
                    set_time_limit(0);
                    $this->logMessage(sprintf("Loop [%04d] - %s %s (ID:%s)", $count + 1, $item->getRecipientEmail(), $item->getRecipientFullName(), $item->getUserId()));
                    try {
                        $phpmailer->clearAllRecipients();
                        $phpmailer->Subject = $item->getSubject();
                        $phpmailer->AddAddress($item->getRecipientEmail(), $item->getRecipientFullName());
                        $phpmailer->Body = trim($item->getBody());
                        $phpmailer->AltBody = \Soundasleep\Html2Text::convert($phpmailer->Body, [ 'ignore_errors' => true, ]);
                        $sentOK = DEV_ALLOW_SENDING_EMAILS ? $phpmailer->Send() : true;
                        if ($sentOK) {
                            $sendResult = constant($this->itemsClassName . '::STATUS_PROCESSED_OK');
                        } else {
                            $sendResult = constant($this->itemsClassName . '::STATUS_PROCESSED_ERROR');
                        }
                        $item->setProcessTS($this->dh->date_to_ts('now'))->setStatus($sendResult)->setSendResult($sentOK);
                        $this->dh->saveEmailQueueItem($item->toArray());
                    } catch (\Exception $e) {
                        $this->logMessage("Exception when persisting item:");
                        $this->logMessage($e->getMessage());
                        $this->logMessage($e->getTraceAsString());
                    }

                    $this->logMessage(
                        sprintf("EMAILS_PER_HOUR is %d, going to sleep for %d microseconds...", constant($this->itemsClassName . '::EMAILS_PER_HOUR'), $sleepTime));
                    usleep($sleepTime);
                    $this->logMessage('...got woken up');
                }
            }
        }
        $this->logMessage("No new items found. DONE!");
    }

    /**
     * Gets the new enqueued items on each call using criteria based on the itemsClassName
     *
     * @return array
     */
    private function getNewItems()
    {
        $whereArr = [];
        switch ($this->itemsClassName) {
            case EmailQueueItem::fqcn():
                $whereArr = [
                    'enqueueTS' => [
                        'op' => '>',
                        'value' => $this->lastGetTS,
                    ],
                    'processTS' => null,
                    'status' => constant($this->itemsClassName . '::STATUS_ENQUEUED'),
                ];
                break;
        }
        $this->lastGetTS = $this->dh->date_to_ts('now');
        return  $this->dh->findBy($this->itemsClassName, $whereArr);
    }


    /**
     * Gets a lockfile that will be a symlink to the /proc/ entry of the running process
     *
     * If lock file exists, check if stale.
     * If exists and is not stale, return false
     * Else, create lock file and return true.
     *
     * See: http://php.net/manual/en/function.getmypid.php#112782
     *
     * @return boolean
     */
    private function tryLock()
    {
        if (@symlink("/proc/" . getmypid(), $this->lockFile) !== FALSE) return true;

        // link already exists, check if it's stale
        if (is_link($this->lockFile) && !is_dir($this->lockFile)) {
            unlink($this->lockFile);
            # try to lock again
            return $this->tryLock();
        }
        return false;
    }

    /**
     * Removes the lock file, if any.
     * Must be public for register_shutdown_function to use it
     */
    public function unlinkLockFile()
    {
        if (unlink($this->lockFile)) $this->logMessage("Lockfile removed\n");
        else $this->logMessage("Lockfile " . $this->lockFile . " *NOT* removed\n");
    }

    /**
     * logs a message in the log file defined in the logFile private property.
     * and sends output to the iframe in the browser as well
     *
     * @param string $text the message to be logged
     * @param boolean $sendToBrowser true if text must be send to the browser, false to log into logfile only
     *
     * @return unknown_type
     *
     * @access private
     */
    private function logMessage($text)
    {
        if ($this->debug) {
            // the file must exists, otherwise logger won't log
            if (!is_file($this->logFile)) touch($this->logFile);
            \ADAFileLogger::log($text, $this->logFile);
        }
    }

    /**
     * Sets the log file name
     *
     * @param string $filename
     */
    private function setLogFileName($filename)
    {
        // make the module's own log dir if it's needed
        if (!is_dir(self::logdir)) {
            $oldmask = umask(0);
            mkdir(self::logdir, 0775, true);
            umask($oldmask);
        }
        // set the log file name
        $this->logFile = self::logdir . (self::$isCLI ? 'CLI' : '') . $filename;
    }

    /**
     * Enables debug output
     *
     * @access public
     */
    public function enableDebug()
    {
        $this->debug = true;
    }

    /**
     * Disables debug output
     *
     * @access public
     */
    public function disableDebug()
    {
        $this->debug = false;
    }
}
