<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Message */

$this->title = Yii::t('message', 'Manage Signature');
$this->params['breadcrumbs'][] = ['label' => Yii::t('message', 'Inbox'), 'url' => ['inbox']];
$this->params['breadcrumbs'][] = $this->title;

rmrevin\yii\fontawesome\AssetBundle::register($this);
?>
<div class="signature-create">

    <?= $this->render('_actions'); ?>

    <hr>

    <div class="signature-form">

        <?php $form = ActiveForm::begin(['id' => 'signature-form']); ?>

        <?= $form->errorSummary($signature); ?>

        <?= $form->field($signature, 'message')
            ->textarea(['rows' => 6])
            ->label(Yii::t('message', 'Signature')) ?>

        <div class="form-group">
            <?= Html::submitButton(
                '<i class="fa fa-floppy-o"></i> ' . Yii::t('message', 'Save Signature'), [
                'class' => 'btn btn-success pull-right']); ?>
        </div>

        <?php ActiveForm::end(); ?>

    </div>
</div>
