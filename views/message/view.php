<?php

use thyseus\message\models\Message;
use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $message app\models\Message */

$this->title = $message->title;
$this->params['breadcrumbs'][] = ['label' => Yii::t('message', 'Inbox'), 'url' => ['inbox']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="message-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?php
        $ignored = Message::isUserIgnoredBy($message->from, Yii::$app->user->id);

        if ($message->from != Yii::$app->user->id)
            if ($ignored)
                echo Html::tag('span', Yii::t('message', 'Answer'), [
                    'class' => 'btn btn-primary disabled',
                    'title' => $ignored ? Yii::t(
                        'message', 'The recipient has added you to the ignore list. You can not send any messages to this person.') : '',
                ]);
            else
                echo Html::a(Yii::t('message', 'Answer'), ['compose', 'to' => $message->from, 'answers' => $message->hash], [
                    'class' => 'btn btn-primary',
                ]) ?>

        <?php
        if ($message->to == Yii::$app->user->id)
            echo Html::a(Yii::t('message', 'Delete'), ['delete', 'hash' => $message->hash], [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => Yii::t('message', 'Are you sure you want to delete this message?'),
                    'method' => 'post',
                ],
            ]) ?>
    </p>

    <?php
    if (isset(Yii::$app->getModule('message')->userProfileRoute))
        $from = Html::a($message->sender->username, array_merge(Yii::$app->getModule('message')->userProfileRoute, ['id' => $message->from]));
    else
        $from = $message->sender->username;

    if (isset(Yii::$app->getModule('message')->userProfileRoute))
        $to = Html::a($message->recipient->username, array_merge(Yii::$app->getModule('message')->userProfileRoute, ['id' => $message->to]));
    else
        $to = $message->recipient->username;

    echo DetailView::widget([
        'model' => $message,
        'attributes' => [
            'created_at',
            [
                'label' => Yii::t('message', 'from'),
                'format' => 'html',
                'value' => $from,
            ],
            [
                'label' => Yii::t('message', 'to'),
                'format' => 'html',
                'value' => $to,
            ],
            'title',
            [
                'attribute' => 'context',
                'visible' => $message->context,
                'format' => 'html',
                'value' => $message->context,
            ],
            'message:html',
        ],
    ]) ?>

</div>
