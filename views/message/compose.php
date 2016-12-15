<?php

use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Message */

$this->title = Yii::t('message', 'Write a message');
$this->params['breadcrumbs'][] = ['label' => Yii::t('message', 'Inbox'), 'url' => ['inbox']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="message-create">

    <h1> <?= Html::encode($this->title) ?> </h1>

    <div class="message-form">

        <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($model, 'to')->widget(Select2::className(), [
            'data' => $possible_recipients,
            'options' => [
                'multiple' => true,
            ],
            'language' => Yii::$app->language ? Yii::$app->language : null,
        ]); ?>

        <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'message')->textarea(['rows' => 6]) ?>

        <div class="form-group">
            <?= Html::submitButton(Yii::t('message', 'Send'), ['class' => 'btn btn-success']) ?>
        </div>

        <?php ActiveForm::end(); ?>

    </div>

</div>
