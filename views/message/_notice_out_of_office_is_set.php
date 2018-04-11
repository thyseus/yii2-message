<?php

/* @var $this \yii\web\View */

use thyseus\message\models\Message;
use yii\helpers\Html;

if (!isset(Yii::$app->get('i18n')->translations['message*'])) {
    Yii::$app->get('i18n')->translations['message*'] = [
        'class' => \yii\i18n\PhpMessageSource::class,
        'basePath' => '@vendor/thyseus/yii2-message/messages',
        'sourceLanguage' => 'en-US'
    ];
}

?>

<?php if (!Yii::$app->user->isGuest && Message::find()->where([
        'from' => Yii::$app->user->id,
        'status' => Message::STATUS_OUT_OF_OFFICE_ACTIVE,
    ])->exists()) { ?>
    <div class="panel panel-danger">
        <div class="panel-heading">
            <h3 class="panel-title"><?= Yii::t('message', 'Please Note'); ?></h3>
        </div>
        <div class="panel-body">
            <p> <?= Yii::t('message', 'You have set an "out-of-office" Message. Currently everybody that sends you a Message will automatically receive your configured answer.'); ?></p>

            <p> <?= Html::a('<i class="far fa-calendar-times"></i> '
                    . Yii::t('message', 'Manage Out of Office Message'),
                    ['//message/message/out-of-office'], ['class' => 'btn btn-success pull-right']) ?> </p>
        </div>
    </div>

<?php }
