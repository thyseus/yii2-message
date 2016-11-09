<?php

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
        if($message->from != Yii::$app->user->id)
          echo Html::a(Yii::t('message', 'Answer'), ['compose', 'to' => $message->from, 'answers' => $message->hash], [
            'class' => 'btn btn-primary',
          ]) ?>

        <?php
        if($message->to == Yii::$app->user->id)
          echo Html::a(Yii::t('message', 'Delete'), ['delete', 'hash' => $message->hash], [
            'class' => 'btn btn-danger',
            'data' => [
              'confirm' => Yii::t('message', 'Are you sure you want to delete this message?'),
              'method' => 'post',
            ],
          ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $message,
        'attributes' => [
            'created_at',
            ['label' => Yii::t('message', 'from'), 'value' => $message->sender->username ],
            ['label' => Yii::t('message', 'to'), 'value' => $message->recipient->username ],
            'title',
            'message:ntext',
        ],
    ]) ?>

</div>
