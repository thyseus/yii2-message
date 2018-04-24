<?php

use thyseus\message\models\Message;
use yii\grid\GridView;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $searchModel app\models\MessageSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('message', 'Drafts');
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
                        ['manage-draft', 'hash' => $data->hash], ['data-pjax' => 0]);
                },
            ],
            [
                'headerOptions' => ['style' => 'width: 60px;'],
                'filter' => false,
                'format' => 'raw',
                'value' => function ($data) {
                    $view = Html::a('<i class="fas fa-eye"></i>',
                        ['manage-draft', 'hash' => $data->hash], [
                            'title' => Yii::t('message', 'Open draft'),
                        ]);

                    $delete = Html::a('<i class="fas fa-trash"></i>',
                        ['delete', 'hash' => $data->hash], [
                            'data-method' => 'POST',
                            'data-confirm' => Yii::t('message', 'Are you sure you want to delete this draft?'),
                            'title' => Yii::t('message', 'Delete draft'),
                        ]);

                    return $view . '&nbsp;' . $delete;
                }
            ],
        ],
    ]); ?>
