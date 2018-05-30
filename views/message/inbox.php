<?php
use thyseus\message\models\Message;
use yii\grid\GridView;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $searchModel app\models\MessageSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('message', 'Inbox');
$this->params['breadcrumbs'][] = $this->title;

rmrevin\yii\fontawesome\AssetBundle::register($this);

?>
<div class="message-index">
    <?= $this->render('_actions'); ?>

    <hr>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            [
                'headerOptions' => ['style' => 'width: 200px;'],
                'attribute' => 'from',
                'format' => 'raw',
                'value' => function ($message) {
                    $module = Yii::$app->getModule('message');
                    if ($message->sender !== null) {
                        return $message->sender->linkTo();
                    } else {
                        return $module->no_sender_caption;
                    }
                },
                'filter' => $users,
            ],
            [
                'headerOptions' => ['style' => 'width: 200px;'],
                'attribute' => 'created_at',
                'format' => 'datetime',
                'filter' => false,
            ],
            [
                'attribute' => 'title',
                'format' => 'raw', // do not use 'format' => 'html' because the 'data-pjax=0' gets swallowed.
                'value' => function ($data) {
                    return Html::a(
                            $data->status == Message::STATUS_UNREAD ? '<strong>' . $data->title . '</strong>' : $data->title,
                            ['view', 'hash' => $data->hash], ['data-pjax' => 0]);
                },
            ],
            [
                'headerOptions' => ['style' => 'width: 50px;'],
                'filter' => [
                    0 => Yii::t('message', 'unread'),
                    1 => Yii::t('message', 'read'),
                    2 => Yii::t('message', 'answered'),
                ],
                'attribute' => 'status',
                'format' => 'raw',
                'value' => function ($data) {
                    switch ($data->status) {
                        case Message::STATUS_UNREAD:
                            return '<span class="fas fa-envelope" title="' . Yii::t('message', 'unread') . '">';
                            break;
                        case Message::STATUS_READ:
                            return '<span class="fas fa-eye" title="' . Yii::t('message', 'read') . '">';
                            break;
                        case Message::STATUS_ANSWERED:
                            return '<span class="fas fa-check" title="' . Yii::t('message', 'answered') . '">';
                            break;
                    }
                },
            ],
            [
                'headerOptions' => ['style' => 'width: 50px;'],
                'filter' => false,
                'format' => 'raw',
                'value' => function($data) {
                    return Html::a('<i class="fa fa-remove">', ['delete', 'hash' => $data->hash], [
                        'data-pjax' => 0,
                        'data-method' => 'POST',
                        'data-confirm' => Yii::t('message', 'Are you sure you want to delete this message?'),
                        'title' => Yii::t('message', 'Delete message'),
                    ]);
                }
            ],
        ],
    ]); ?>
