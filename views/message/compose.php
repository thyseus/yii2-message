<?php

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

        <?php $user = new Yii::$app->controller->module->userModelClass; ?>

        <?= $form->field($model, 'to')->dropDownList(
            ArrayHelper::map(
                $user::find()->where('id != ' . Yii::$app->user->id)->all(), 'id', 'username')) ?>

        <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'message')->textarea(['rows' => 6]) ?>

        <div class="form-group">
            <?= Html::submitButton(Yii::t('message', 'Send'), ['class' => 'btn btn-success']) ?>
        </div>

        <?php ActiveForm::end(); ?>

    </div>

</div>
