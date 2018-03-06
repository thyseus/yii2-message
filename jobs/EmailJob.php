<?php

namespace thyseus\message\jobs;

use yii\base\BaseObject;
use yii\queue\Job;

/**
 * Class EmailJob
 * This EmailJob is being pushed to the yii2-queue in case 'useMailQueue' is set to true in the module configuration.
 * @package thyseus\message\jobs
 */
class EmailJob extends BaseObject implements Job
{
    public $mailing;

    /**
     * Send the mail.
     * @param $queue
     */
    public function execute($queue)
    {
        return $this->mailing->send();
    }
}