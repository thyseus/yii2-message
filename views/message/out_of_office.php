<?php

use thyseus\message\models\Message;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Message */

$this->title = Yii::t('message', 'Manage Out of Office Message');
$this->params['breadcrumbs'][] = ['label' => Yii::t('message', 'Inbox'), 'url' => ['inbox']];
$this->params['breadcrumbs'][] = $this->title;

rmrevin\yii\fontawesome\AssetBundle::register($this);
?>
<div class="out-of-office-create">

    <?= $this->render('_actions'); ?>

    <hr>

    <div class="out-of-office-form">

        <?php $form = ActiveForm::begin(['id' => 'out-of-office-form']); ?>

        <?= $form->errorSummary($outOfOffice); ?>

        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title"><?= Yii::t('message', 'Please Note'); ?></h3>
            </div>
            <div class="panel-body">
                <p> <?= Yii::t('message', 'When setting an "Out of Office" message, you will still receive messages as usual, but the recipient will automatically be informed with the message you provided.'); ?> </p>

                <p> <?= Yii::t('message', 'This automatic response will only be sent as long as its status is set to "active".'); ?></p>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <?= $form->field($outOfOffice, 'title')
                    ->textInput([
                        'placeholder' => Yii::t('message',
                            'Title of your automatically sent answer'),
                    ])
                    ->label(Yii::t('message', 'Title')) ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($outOfOffice, 'status')
                    ->dropDownList([
                        Message::STATUS_OUT_OF_OFFICE_INACTIVE => Yii::t('message', 'Inactive'),
                        Message::STATUS_OUT_OF_OFFICE_ACTIVE => Yii::t('message', 'Active'),
                    ])
                    ->label(Yii::t('message', 'Status')) ?>
            </div>
        </div>

        <?= $form->field($outOfOffice, 'message')
            ->textarea(['rows' => 6])
            ->label(Yii::t('message', 'Message')) ?>


        <div class="form-group">
            <?= Html::submitButton(Yii::t('message', 'Remove Out of Office Message'), [
                'name' => 'remove-out-of-office-message',
                'class' => 'btn btn-danger',
                'data-confirm' => Yii::t('message',
                    'Are you sure to remove your Out of Office Message?'),
            ]); ?>

            <?= Html::submitButton('<i class="fa fa-floppy-o"></i> '
                . Yii::t('message', 'Save Out of Office Message'), [
                'class' => 'btn btn-success pull-right']); ?>
        </div>

        <?php ActiveForm::end(); ?>

    </div>
</div>
