<?php

namespace thyseus\message\events;

use yii\base\Event;

class MessageEvent extends Event {
    /**
     * @var array Extra post data that has been given with the request. Contains the Yii::$app->request->post() array as
     * it is.
     */
    public $postData;

    /**
     * @var the message that has been sent
     */
    public $message;
}
