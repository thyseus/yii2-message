<?php

use kartik\select2\Select2;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $template app\models\Message */

$this->title = Yii::t('message', 'Create a template');
$this->params['breadcrumbs'][] = ['label' => Yii::t('message', 'templates'), 'url' => ['templates']];
$this->params['breadcrumbs'][] = $this->title;

rmrevin\yii\fontawesome\AssetBundle::register($this);
?>
<div class="template-create">

    <?= $this->render('_actions'); ?>

    <hr>

    <div class="template-form">

        <?php $form = ActiveForm::begin(['id' => 'template-form']); ?>

        <?= $form->field($template, 'to')->widget(Select2::class, [
            'data' => $possible_recipients,
            'options' => [
                'multiple' => false,
                'placeholder' => Yii::t('message', 'Choose one recipient'),
            ],
            'pluginOptions' => [
                'allowClear' => true,
            ],
            'language' => Yii::$app->language ? Yii::$app->language : null,
        ]); ?>

        <?= $form->field($template, 'title')->textInput(['maxlength' => true, 'required' => 'required']) ?>

        <?= $form->field($template, 'message')->textarea(['rows' => 6]) ?>

        <?= $form->field($template, 'context')->hiddenInput()->label(false); ?>

        <?= Html::submitButton(
                '<i class="fa fa-floppy-o"></i> ' . Yii::t('message', 'Save template'),
            ['name' => 'save-template', 'class' => 'btn btn-success']) ?>

        <?= Html::submitButton(
                '<i class="fa fa-envelope-o"></i> ' . Yii::t('message', 'Save and send template'), [
            'name' => 'send-template',
            'class' => 'btn btn-success pull-right',
            'data-confirm' => Yii::t('message', 'Are you sure? This template will be sent to the recipient(s)'),
        ]) ?>


        <?php ActiveForm::end(); ?>
    </div>

</div>
