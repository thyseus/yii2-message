<?php

use kartik\select2\Select2;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Message */

if ($answers) {
    $this->title = Yii::t('message', 'Answer message');
} else {
    $this->title = Yii::t('message', 'Write a message');
}
$this->params['breadcrumbs'][] = ['label' => Yii::t('message', 'Inbox'), 'url' => ['inbox']];
$this->params['breadcrumbs'][] = $this->title;

rmrevin\yii\fontawesome\AssetBundle::register($this);
?>
<div class="message-create">

    <?php if (!$dialog): ?>
        <?= $this->render('_actions'); ?>

        <hr>
    <?php endif ?>

    <div class="message-form">

        <?php $form = ActiveForm::begin(['id' => 'message-form']); ?>

        <div class="row">
            <div class="col-md-4">
                <?php
                if ($model->to) {
                    if ($model->to && is_array($model->to) && count($model->to) === 1) {
                        $to = $model->to[0];
                    } else {
                        $to = json_encode($model->to);
                    }

                    $recipient_label = Yii::t('message', 'Recipient');
                    echo "<label class=\"control-label\" for=\"message-recipient\">$recipient_label</label>";
                    $caption_attribute = Message::determineUserCaptionAttribute();

                    if (is_callable($caption_attribute)) {
                        $caption_attribute = call_user_func($caption_attribute, $model->recipient);
                        echo '<p>' . $caption_attribute . '</p>';
                    } else {
                        echo '<p>' . $model->recipient->username . '</p>';
                    }
                    echo '<input type=hidden name="Message[to]" value="' . $to . '" />';
                } else
                    echo $form->field($model, 'to')->widget(Select2::class, [
                        'data' => $possible_recipients,
                        'showToggleAll' => false, # avoid accidental or malicious spam
                        'options' => [
                            'multiple' => $allow_multiple,
                            'placeholder' => Yii::t('message', $allow_multiple ? 'Choose one or more recipients' : 'Choose the recipient'),
                        ],
                        'language' => Yii::$app->language ? Yii::$app->language : null,
                    ]); ?>
            </div>
            <div class="col-md-8">
                <?= $form->field($model, 'title')->textInput(['maxlength' => true, 'required' => 'required']) ?>
            </div>
        </div>

        <?php if ($answers && $origin) { ?>

            <p> <?= Yii::t('message', 'Original message'); ?> </p>

            <?= $origin->message; ?>

            <hr>

        <?php } ?>

        <?= $form->field($model, 'message')->textarea(['rows' => 6]) ?>

        <?= $form->field($model, 'context')->hiddenInput()->label(false); ?>

        <div class="form-group">
            <?php if (!$dialog) { ?>

            <div class="btn-group">
                <?php
                echo Html::submitButton(
                    '<i class="fa fa-floppy-o"></i> ' . Yii::t('message', 'Save as Draft'), [
                    'name' => 'save-as-draft',
                    'class' => 'btn btn-success btn-draft']);

                echo Html::submitButton(
                    '<i class="fa fa-floppy-o"></i> ' . Yii::t('message', 'Save as Template'), [
                    'name' => 'save-as-template',
                    'style' => 'margin-left: 5px;',
                    'class' => 'btn btn-success btn-template']); ?>
                <?php } ?>
            </div>

            <?php
            echo Html::submitButton(
                '<i class="fa fa-envelope"></i> ' . Yii::t('message', 'Send'), [
                $dialog ? null : 'data-confirm' => Yii::t('message', 'Are you sure you want to sent this message?'),
                'name' => 'send-message',
                'class' => 'btn btn-success btn-send-message pull-right']);
            ?>
        </div>

        <input type="hidden" name="draft-hash" value="<?= $draft_hash; ?>">
        <input type="hidden" name="save-draft-url" value="<?= \yii\helpers\Url::to(
            ['manage-draft', 'hash' => $draft_hash]
        ); ?>">

        <?php ActiveForm::end(); ?>

        <?php if (!$dialog) { ?>
            <hr>
            <?php echo Html::a('<i class="fa fa-arrow-left" aria-hidden="true"></i> '
                . Yii::t('message', 'Back to Inbox'), ['/message/message/inbox']) ?>
        <?php } ?>
    </div>

</div>

<?php $this->registerJs(<<<JS
function saveDraft() {
    $.ajax({
        'url': $('input[name="save-draft-url"]').val(),
        'method': 'POST',
        'data': $('form').serialize(),
    });
}

$('input, textarea, select').blur(function() {
    saveDraft(); 
});

$('#message-title').keyup(function() {
    secureTextArea();
}); 

function secureTextArea() {
    secured = $('#message-title').val() == '';
    
    if (secured) {
        $('#message-message').attr('disabled', 'disabled');
    } else {
        $('#message-message').attr('disabled', false);
    }
}

secureTextArea();

setInterval(function() { saveDraft(); }, 10000);


JS
);

