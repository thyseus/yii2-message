<?php

/* @var $this \yii\web\View */

use thyseus\message\models\Message;
use yii\helpers\Html;

$action = Yii::$app->controller->action->id;

?>

<div class="inbox-button-holder">

    <?php
    if ($action == 'compose') {
        $caption = '<i class="fas fa-plus"></i> <strong>' . Yii::t('message', 'Write a message') . '</strong>';
    } else {
        $caption = '<i class="fas fa-plus"></i> ' . Yii::t('message', 'Write a message');
    } ?>

    <?= Html::a($caption, ['compose'], ['class' => 'btn btn-success']) ?>

    <?php

    $icon = '<i class="fas fa-inbox"></i> ';
    $caption = Yii::t('message', 'Inbox');

    if ($action == 'inbox') {
        $caption = Html::tag('strong', $caption);
    }

    $inbox_count = Message::find()->where([
        'to' => Yii::$app->user->id,
        'status' => [
            Message::STATUS_READ,
            Message::STATUS_UNREAD,
        ]
    ])->count(); ?>

    <?= Html::a(sprintf('%s %s (%d)', $icon, $caption, $inbox_count),
        ['inbox'],
        ['class' => 'btn btn-success']) ?>

    <?php
    $icon = '<i class="fas fa-share-square"></i> ';
    $caption = Yii::t('message', 'Sent');

    if ($action == 'sent') {
        $caption = Html::tag('strong', $caption);
    }

    $sent_count = Message::find()->where([
        'from' => Yii::$app->user->id,
        'status' => [
            Message::STATUS_READ,
            Message::STATUS_UNREAD,
        ]
    ])->count(); ?>

    <?= Html::a(sprintf('%s %s (%d)', $icon, $caption, $sent_count),
        ['sent'],
        ['class' => 'btn btn-success']) ?>

    <?php
    $icon = '<i class="fab fa-firstdraft"></i> ';
    $caption = Yii::t('message', 'Manage Drafts');

    if ($action == 'drafts') {
        $caption = Html::tag('strong', $caption);
    }

    $draft_count = Message::find()->where([
        'from' => Yii::$app->user->id,
        'status' => Message::STATUS_DRAFT,
    ])->count(); ?>

    <?= Html::a(sprintf('%s %s (%d)', $icon, $caption, $draft_count),
        ['drafts'],
        ['class' => 'btn btn-success']) ?>

    <?php
    $icon = '<i class="fas fa-clone"></i> ';
    $caption = Yii::t('message', 'Manage Templates');

    if ($action == 'templates') {
        $caption = Html::tag('strong', $caption);
    }

    $template_count = Message::find()->where([
        'from' => Yii::$app->user->id,
        'status' => Message::STATUS_TEMPLATE,
    ])->count(); ?>

    <?= Html::a(sprintf('%s %s (%d)', $icon, $caption, $template_count),
        ['templates'],
        ['class' => 'btn btn-success']) ?>

    &nbsp;
    <div class="btn-group">
        <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
            <?= Yii::t('message', 'More'); ?>&nbsp;<span class="caret"></span>
        </button>
        <ul class="dropdown-menu" role="menu">

            <?php
            if ($action == 'manage-draft' && !isset($_GET['hash'])) {
                $caption = '<i class="fa fa-plus"></i> <strong>' . Yii::t('message', 'Create a draft') . '</strong>';
            } else {
                $caption = '<i class="fa fa-plus"></i> ' . Yii::t('message', 'Create a draft');
            } ?>

            <li> <?= Html::a($caption, ['manage-draft'], ['class' => 'btn btn-success']) ?> </li>
            <?php
            if ($action == 'manage-template' && !isset($_GET['hash'])) {
                $caption = '<i class="fa fa-plus"></i> <strong>' . Yii::t('message', 'Create a template') . '</strong>';
            } else {
                $caption = '<i class="fa fa-plus"></i> ' . Yii::t('message', 'Create a template');
            } ?>

            <li> <?= Html::a($caption, ['manage-template'], ['class' => 'btn btn-success']) ?> </li>

            <?php
            if ($action == 'signature') {
                $caption = '<i class="fas fa-pencil-alt"></i> <strong>' . Yii::t('message', 'Manage Signature') . '</strong>';
            } else {
                $caption = '<i class="fas fa-pencil-alt"></i> ' . Yii::t('message', 'Manage Signature');
            } ?>

            <li> <?= Html::a($caption, ['signature'], ['class' => 'btn btn-success']) ?> </li>

            <?php
            if ($action == 'out-of-office') {
                $caption = '<i class="far fa-calendar-times"></i> <strong>' . Yii::t('message', 'Manage Out of Office Message') . '</strong>';
            } else {
                $caption = '<i class="far fa-calendar-times"></i> ' . Yii::t('message', 'Manage Out of Office Message');
            } ?>

            <li> <?= Html::a($caption, ['out-of-office'], ['class' => 'btn btn-success']) ?> </li>

            <?php
            if ($action == 'ignorelist') {
                $caption = '<i class="fas fa-ban"></i> <strong>' . Yii::t('message', 'Manage Ignorelist') . '</strong>';
            } else {
                $caption = '<i class="fas fa-ban"></i> ' . Yii::t('message', 'Manage Ignorelist');
            } ?>

            <li> <?= Html::a($caption, ['ignorelist'], ['class' => 'btn btn-success']) ?> </li>

        </ul>
    </div>

    <?php if ($action == 'inbox') { ?>
        <?= Html::a(
            '<i class="fas fa-flag-checkered"></i> ' . Yii::t('message', 'Mark all messages as read'),
            ['mark-all-as-read'], ['class' => 'btn btn-success pull-right']) ?>
    <?php } ?>

</div>
