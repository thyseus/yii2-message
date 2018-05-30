<?php

use thyseus\message\models\Message;
use yii\helpers\StringHelper;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $message app\models\Message */

$this->title = StringHelper::truncate($message->title, 80);
$this->params['breadcrumbs'][] = ['label' => Yii::t('message', 'Inbox'), 'url' => ['inbox']];
$this->params['breadcrumbs'][] = Yii::t('message', 'Message: ') . $this->title;

?>
<div class="message-view">

    <?= $this->render('_actions'); ?>

    <p>
        <?php
        $ignored = Message::isUserIgnoredBy($message->from, Yii::$app->user->id);

        if ($message->from != Yii::$app->user->id && $message->from) {
            if ($ignored) {
                echo Html::tag('span', Yii::t('message', 'Answer'), [
                    'class' => 'btn btn-primary disabled',
                    'title' => $ignored ? Yii::t(
                        'message', 'The recipient has added you to the ignore list. You can not send any messages to this person.') : '',
                ]);
            } else if ($message->from === null) {
                echo '<span class="alert alert-info">';
                echo Yii::t('message', 'This message has been sent from the System');
                echo '</span>';
            } else {
                echo Html::a(
                    '<i class="fa fa-reply" aria-hidden="true"></i> '
                    . Yii::t('message', 'Answer'),
                    ['compose', 'to' => $message->from, 'answers' => $message->hash]);
            }
        }
        ?>
    </p>

    <?php
    if ($message->sender !== null) {
        if (isset(Yii::$app->getModule('message')->userProfileRoute)) {
            $from = Html::a($message->sender->username, array_merge(Yii::$app->getModule('message')->userProfileRoute, ['id' => $message->from]));
        } else {
            $from = $message->sender->username;
        }
    } else {
        $from = Yii::$app->getModule('message')->no_sender_caption;
    }

    if (isset(Yii::$app->getModule('message')->userProfileRoute)) {
        $to = Html::a(
            $message->recipient->username,
            array_merge(
                Yii::$app->getModule('message')->userProfileRoute,
                ['id' => $message->to]
            ));
    } else {
        $to = $message->recipient->username;
    }
    ?>

    <div class="panel panel-default">
        <div class="panel-heading">
            <?= Yii::t('message', 'title'); ?>: <?= Html::encode($this->title) ?>
        </div>
        <div class="panel-body">
            <?= $message->message
                ? nl2br($message->message)
                : ('<mark>' . Yii::t('message', 'No message content given') . '.</mark>'); ?>
        </div>
        <div class="panel-footer">
            <small> <?= Yii::t('message', 'Message from'); ?>: <?= $from ?><br>
                <?= Yii::t('message', 'Message to'); ?>: <?= $to ?><br>
                <?= Yii::t('message', 'sent at'); ?>: <?= Yii::$app->formatter->asDate($message->created_at, 'long'); ?>
                <?= Yii::t('message', 'at'); ?>
                <?= Yii::$app->formatter->asDate($message->created_at, 'php:H:i:s'); ?>
                <br>
                <?php if ($message->context) : ?>
                    <?= Yii::t('message', 'Referring to'); ?>: <?= $message->context; ?>
                <?php endif; ?>
            </small>
        </div>
    </div>

    <?= Html::a(
            '<i class="fa fa-arrow-left" aria-hidden="true"></i> '
            . Yii::t('message', 'Back to Inbox'),
            ['/message/message/inbox']) ?>

    <?php
    if ($message->to == Yii::$app->user->id) {
        echo Html::a(
            '<i class="fa fa-remove"></i> '
            . Yii::t('message', 'Delete message'),
            ['delete', 'hash' => $message->hash], [
            'class' => 'text-red ml',
            'data' => [
                'confirm' => Yii::t('message',
                    'Are you sure you want to delete this message?'),
                'method' => 'post',
            ],
        ]);
    }
    ?>
</div>