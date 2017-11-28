<?php
use thyseus\message\models\Message;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\Pjax;


/* @var $this yii\web\View */
/* @var $searchModel app\models\MessageSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('message', 'Inbox');
$this->params['breadcrumbs'][] = $this->title;

rmrevin\yii\fontawesome\AssetBundle::register($this);

?>
<div class="message-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p> <?= Html::a(Yii::t('message', 'Write a message') . ' <i class="fa fa-plus"></i>', ['compose'], ['class' => 'btn btn-success']) ?> </p>

    <hr>

    <?php Pjax::begin(); ?>

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
                        if (isset($module->userProfileRoute)) {
                            return Html::a($message->sender->username, array_merge(
                                $module->userProfileRoute, ['id' => $message->from]), ['data-pjax' => 0]);
                        } else {
                            return $message->sender->username;
                        }
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
                            return '<span class="glyphicon glyphicon-envelope" title="' . Yii::t('message', 'unread') . '">';
                            break;
                        case Message::STATUS_READ:
                            return '<span class="glyphicon glyphicon-ok" title="' . Yii::t('message', 'read') . '">';
                            break;
                        case Message::STATUS_ANSWERED:
                            return '<span class="glyphicon glyphicon-repeat" title="' . Yii::t('message', 'answered') . '">';
                            break;
                    }
                },
            ],
            [
                'headerOptions' => ['style' => 'width: 50px;'],
                'filter' => false,
                'format' => 'raw',
                'value' => function($data) {
                    return Html::a('<i class="glyphicon glyphicon-remove">', ['delete', 'hash' => $data->hash], [
                        'data-pjax' => 0,
                        'data-method' => 'POST',
                        'data-confirm' => Yii::t('message', 'Are you sure you want to delete this message?'),
                        'title' => Yii::t('message', 'Delete message'),
                    ]);
                }
            ],
        ],
    ]); ?>

    <?php Pjax::end(); ?></div>
