<?php
use thyseus\message\models\Message;
use yii\grid\GridView;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $searchModel app\models\MessageSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('message', 'Templates');
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
                'attribute' => 'created_at',
                'format' => 'datetime',
                'filter' => false,
            ],
            [
                'headerOptions' => ['style' => 'width: 200px;'],
                'attribute' => 'to',
                'format' => 'raw',
                'value' => function ($message) {
                    if ($message->recipient) {
                        if (isset(Yii::$app->getModule('message')->userProfileRoute)) {
                            return Html::a($message->recipient->username, array_merge(
                                Yii::$app->getModule('message')->userProfileRoute, ['id' => $message->to]), ['data-pjax' => 0]);
                        } else {
                            return $message->recipient->username;
                        }
                    }
                },
                'filter' => $users,
            ],
            [
                'attribute' => 'title',
                'format' => 'raw', // do not use 'format' => 'html' because the 'data-pjax=0' gets swallowed.
                'value' => function ($data) {
                    return Html::a(
                            $data->status == Message::STATUS_UNREAD ? '<strong>' . $data->title . '</strong>' : $data->title,
                            ['manage-template', 'hash' => $data->hash], ['data-pjax' => 0]);
                },
            ],
            [
                'headerOptions' => ['style' => 'width: 50px;'],
                'filter' => false,
                'format' => 'raw',
                'value' => function($data) {
                    return Html::a('<i class="fa fa-remove">', ['delete', 'hash' => $data->hash], [
                        'data-method' => 'POST',
                        'data-confirm' => Yii::t('message', 'Are you sure you want to delete this template?'),
                        'title' => Yii::t('message', 'Delete template'),
                    ]);
                }
            ],
        ],
    ]); ?>
