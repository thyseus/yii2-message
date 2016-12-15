<?php
use thyseus\message\models\Message;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;


/* @var $this yii\web\View */
/* @var $searchModel app\models\MessageSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('message', 'Inbox');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="message-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p> <?= Html::a(Yii::t('message', 'Write a message'), ['compose'], ['class' => 'btn btn-success']) ?> </p>

    <?php Pjax::begin(); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            [
                'headerOptions' => ['style' => 'width: 200px;'],
                'attribute' => 'from',
                'value' => function ($data) {
                    return $data->sender->username;
                },
                'filter' => $users,
            ],
            [
                'headerOptions' => ['style' => 'width: 200px;'],
                'attribute' => 'created_at',
                'filter' => false,
            ],
            [
                'attribute' => 'title',
                'format' => 'raw', // do not use 'format' => 'html' because the 'data-pjax=0' gets swallowed.
                'value' => function ($data) {
                    return Html::a($data->title, ['view', 'hash' => $data->hash], ['data-pjax' => 0]);
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
                            return '<span class="glyphicon glyphicon-envelope" title="'.Yii::t('message', 'unread').'">';
                            break;
                        case Message::STATUS_READ:
                            return '<span class="glyphicon glyphicon-ok" title="'.Yii::t('message', 'read').'">';
                            break;
                        case Message::STATUS_ANSWERED:
                            return '<span class="glyphicon glyphicon-repeat" title="'.Yii::t('message', 'answered').'">';
                            break;
                    }
                },
            ],
        ],
    ]); ?>

    <?php Pjax::end(); ?></div>
