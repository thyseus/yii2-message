<?php

use yii\helpers\Html;
use yii\helpers\Url;

?>

<p> You have received the following message
    through <?= isset(Yii::$app->name) ? Yii::$app->name : 'Yii2-message'; ?>: </p>


<?php if ($content) { ?>
    <?= $content; ?>
<?php } else { ?>
    <p> <?= Yii::t('message', 'No message content has been given'); ?> </p>
<?php } ?>

<?php $url_inbox = Url::to(['//message/message/inbox'], true); ?>

<p> <?= Yii::t('message', 'Use this link to get to your inbox'); ?>: <?= Html::a($url_inbox, $url_inbox); ?> </p>
