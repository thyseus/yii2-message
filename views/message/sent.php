<?php
use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel app\models\MessageSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('message', 'Sent');
$this->params['breadcrumbs'][] = $this->title;

rmrevin\yii\fontawesome\AssetBundle::register($this);

?>
<div class="message-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p> <?= Html::a(Yii::t('message', 'Write a message') . ' <i class="fa fa-plus"></i>', ['compose'], ['class' => 'btn btn-success']) ?> </p>

    <?php Pjax::begin(); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
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
                }
            ],
            [
                'headerOptions' => ['style' => 'width: 200px;'],
                'attribute' => 'created_at',
                'format' => 'datetime',
            ],
            [
                'attribute' => 'title',
                'format' => 'raw', // do not use 'format' => 'html' because the 'data-pjax=0' gets swallowed.
                'value' => function ($data) {
                    return Html::a($data->title, ['view', 'hash' => $data->hash], ['data-pjax' => 0]);
                },
            ],
        ],
    ]); ?>

    <?php Pjax::end(); ?></div>
