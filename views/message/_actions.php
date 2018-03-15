<?php

use yii\helpers\Html;

$action = Yii::$app->controller->action->id;

?>

<?php
if ($action == 'inbox') {
    $caption = '<i class="fa fa-inbox"></i> <strong>' . Yii::t('message', 'Inbox') . '</strong>';
} else {
    $caption = '<i class="fa fa-inbox"></i> ' . Yii::t('message', 'Inbox');
} ?>

<?= Html::a($caption, ['inbox'], ['class' => 'btn btn-success']) ?>

<?php
if ($action == 'sent') {
    $caption = '<i class="fa fa-paper-plane-o"></i> <strong>' . Yii::t('message', 'Sent') . '</strong>';
} else {
    $caption = '<i class="fa fa-paper-plane-o"></i> ' . Yii::t('message', 'Sent');
} ?>

<?= Html::a($caption, ['sent'], ['class' => 'btn btn-success']) ?>

<div class="btn-group">
    <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
        <?= Yii::t('message', 'More'); ?>&nbsp;<span class="caret"></span>
    </button>
    <ul class="dropdown-menu" role="menu">
        <?php
        if ($action == 'drafts' || isset($_GET['hash'])) {
            $caption = '<i class="fa fa-file-text-o"></i> <strong>' . Yii::t('message', 'Manage Drafts') . '</strong>';
        } else {
            $caption = '<i class="fa fa-file-text-o"></i> ' . Yii::t('message', 'Manage Drafts');
        } ?>

        <li> <?= Html::a($caption, ['drafts'], ['class' => 'btn btn-success']) ?> </li>

        <?php
        if ($action == 'templates' || isset($_GET['hash'])) {
            $caption = '<i class="fa fa-file-text-o"></i> <strong>' . Yii::t('message', 'Manage Templates') . '</strong>';
        } else {
            $caption = '<i class="fa fa-file-text-o"></i> ' . Yii::t('message', 'Manage Templates');
        } ?>

        <li> <?= Html::a($caption, ['templates'], ['class' => 'btn btn-success']) ?> </li>

        <?php
        if ($action == 'signature') {
            $caption = '<i class="fa fa-pencil-square-o"></i> <strong>' . Yii::t('message', 'Manage Signature') . '</strong>';
        } else {
            $caption = '<i class="fa fa-pencil-square-o"></i> ' . Yii::t('message', 'Manage Signature');
        } ?>

        <li> <?= Html::a($caption, ['signature'], ['class' => 'btn btn-success']) ?> </li>

        <?php
        if ($action == 'ignorelist') {
            $caption = '<i class="fa fa-ban"></i> <strong>' . Yii::t('message', 'Manage Ignorelist') . '</strong>';
        } else {
            $caption = '<i class="fa fa-ban"></i> ' . Yii::t('message', 'Manage Ignorelist');
        } ?>

        <li> <?= Html::a($caption, ['ignorelist'], ['class' => 'btn btn-success']) ?> </li>

    </ul>
</div>

<div class="btn-group pull-right">
    <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
        <i class="fa fa-plus"> </i><span class="caret"></span>
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
        if ($action == 'compose') {
            $caption = '<i class="fa fa-plus"></i> <strong>' . Yii::t('message', 'Write a message') . '</strong>';
        } else {
            $caption = '<i class="fa fa-plus"></i> ' . Yii::t('message', 'Write a message');
        } ?>

        <li> <?= Html::a($caption, ['compose'], ['class' => 'btn btn-success']) ?> </li>

</div>

<?php if ($action == 'inbox') { ?>
    <?= Html::a(
        '<i class="fa fa-flag-checkered"></i> ' . Yii::t('message', 'Mark all messages as read'),
        ['mark-all-as-read'], ['class' => 'btn btn-success pull-right']) ?>
<?php } ?>

