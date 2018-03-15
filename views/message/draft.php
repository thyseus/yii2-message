<?php

use kartik\select2\Select2;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $draft app\models\Message */

$this->title = Yii::t('message', 'Create a draft');
$this->params['breadcrumbs'][] = ['label' => Yii::t('message', 'drafts'), 'url' => ['drafts']];
$this->params['breadcrumbs'][] = $this->title;

rmrevin\yii\fontawesome\AssetBundle::register($this);
?>
<div class="draft-create">

    <?= $this->render('_actions'); ?>

    <hr>

    <div class="draft-form">

        <?php $form = ActiveForm::begin(['id' => 'draft-form']); ?>

        <?= $form->field($draft, 'to')->widget(Select2::class, [
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

        <?= $form->field($draft, 'title')->textInput(['maxlength' => true, 'required' => 'required']) ?>

        <?= $form->field($draft, 'message')->textarea(['rows' => 6]) ?>

        <?= $form->field($draft, 'context')->hiddenInput()->label(false); ?>

        <?= Html::submitButton(
                '<i class="fa fa-floppy-o"></i> ' . Yii::t('message', 'Save draft'),
            ['name' => 'save-draft', 'class' => 'btn btn-success']) ?>

        <?= Html::submitButton(
                '<i class="fa fa-envelope-o"></i> ' . Yii::t('message', 'Send draft'), [
            'name' => 'send-draft',
            'class' => 'btn btn-success pull-right',
            'data-confirm' => Yii::t('message', 'Are you sure? This draft will be sent to the recipient(s)'),
        ]) ?>


        <?php ActiveForm::end(); ?>
    </div>

</div>
