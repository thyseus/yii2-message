<?php

use thyseus\message\models\Message;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $message app\models\Message */

$this->title = $message->title;
$this->params['breadcrumbs'][] = ['label' => Yii::t('message', 'Inbox'), 'url' => ['inbox']];
$this->params['breadcrumbs'][] = 'Nachricht: ' . $this->title;

rmrevin\yii\fontawesome\AssetBundle::register($this);
?>
<div class="message-view">
    <br>
    <p>
        <?php
        $ignored = Message::isUserIgnoredBy($message->from, Yii::$app->user->id);
        if ($message->from != Yii::$app->user->id) {
            if ($ignored) {
                echo Html::tag('span', Yii::t('message', 'Answer'), [
                    'class' => 'btn btn-primary disabled',
                    'title' => $ignored ? Yii::t(
                        'message', 'The recipient has added you to the ignore list. You can not send any messages to this person.') : '',
                ]);
            } else {
                echo Html::a('<i class="fa fa-reply" aria-hidden="true"></i> ' . Yii::t('message', 'Answer'), [
                    'compose', 'to' => $message->from, 'answers' => $message->hash]);
            }
        }
        ?>

        <?php
        if ($message->to == Yii::$app->user->id) {
            echo Html::a('<i class="fa fa-remove"></i> ' . Yii::t('message', 'Delete'), ['delete', 'hash' => $message->hash], [
                'class' => 'text-red ml',
                'data' => [
                    'confirm' => Yii::t('message', 'Are you sure you want to delete this message?'),
                    'method' => 'post',
                ],
            ]);
        }
        ?>
    </p>
    <?php
    if (isset(Yii::$app->getModule('message')->userProfileRoute)) {
        $from = Html::a($message->sender->username, array_merge(Yii::$app->getModule('message')->userProfileRoute, ['id' => $message->from]));
    } else {
        $from = $message->sender->username;
    }

    if (isset(Yii::$app->getModule('message')->userProfileRoute)) {
        $to = Html::a($message->recipient->username, array_merge(Yii::$app->getModule('message')->userProfileRoute, ['id' => $message->to]));
    } else {
        $to = $message->recipient->username;
    }
    ?>
    <hr>

    <div class="panel panel-default">
        <div class="panel-heading">
            <?= Yii::t('message', 'title'); ?>: <?= Html::encode($this->title) ?>
        </div>
        <div class="panel-body">
            <?= $message->message ? $message->message : ('<mark>' . Yii::t('message', 'No message content given') . '.</mark>'); ?>
        </div>
        <div class="panel-footer">
            <small> <?= Yii::t('message', 'Message from'); ?>: <?= $from ?><br>
                <?= Yii::t('message', 'sent at'); ?>: <?= Yii::$app->formatter->asDate($message->created_at, 'long'); ?>
                <?= Yii::t('message', 'at'); ?> <?= Yii::$app->formatter->asDate($message->created_at, 'php:H:i:s'); ?> Uhr<br>
                <?php if ($message->context) : ?>
                    <?= Yii::t('message', 'Referring to'); ?>: <?= $message->context; ?>
                <?php endif; ?>
            </small>
        </div>
    </div>
    <hr>
    <?= Html::a('<i class="fa fa-arrow-left" aria-hidden="true"></i> ' . Yii::t('message', 'Back to Inbox'), ['/message/message/inbox']) ?>
</div>