# Yii2-message

System for users to send each other private messages.
- A user configurable ignorelist and admin configurable whitelist (administrators are able to fine-tune 
the definition of which users are able to write to whom) is supported.
- Encryption is not (yet?) supported.

## Prerequisites:

You need an User Model that extends from ActiveRecord.
It also must have the 'id' and 'username' attributes.

I suggest to use https://github.com/dektrium/yii2-user which
works wonderful with this module.

## Installation

```bash
$ composer require thyseus/yii2-message
$ php yii migrate/up --migrationPath=@vendor/thyseus/yii2-message/migrations
```

## Configuration

Add following lines to your main configuration file:

```php
'modules' => [
    'message' => [
        'class' => 'thyseus\message\Module',
        'modelClass' => '\app\models\User', // optional. your User model. Needs to be ActiveRecord.
    ],
],
```

## Sending E-Mails

You can overwrite the default e-mail views and layout by providing an @app/mail/ directory inside your Application.

## Actions

The following Actions are possible:

* inbox: https://your-domain/message/message/inbox
* sent messages: https://your-domain/message/message/sent
* compose a message: https://your-domain/message/message/compose
* delete a message: https://your-domain/message/message/delete/hash/<hash>
* view a message: https://your-domain/message/message/view/hash/<hash>
* manage your ignorelist: https://your-domain/message/message/ignorelist

You can place this code snippet in your layouts/main.php to give your users access
to the message actions:

```php
$messagelabel = '<span class="glyphicon glyphicon-envelope"></span>';
$unread = Message::find()->where(['to' => $user->id, 'status' => 0])->count();
if ($unread > 0)
      $messagelabel .= '(' . $unread . ')';
      
echo Nav::widget([
    'encodeLabels' => false, // important to display HTML-code (glyphicons)
    'items' => [
    // ...
    [
      'label' => $messagelabel,
      'url' => '',
      'visible' => !Yii::$app->user->isGuest, 'items' => [
        ['label' => 'Inbox', 'url' => ['/message/message/inbox']],
        ['label' => 'Sent', 'url' => ['/message/message/sent']],
        ['label' => 'Compose a Message', 'url' => ['/message/message/compose']],
        ['label' => 'Manage your Ignorelist', 'url' => ['/message/message/ignorelist']],
      ]
    ],
    // ...
  ]);
```

Since 0.3.0 you can render the compose view inside an Modal Widget like this:

```php
use kartik\growl\GrowlAsset;
use yii\bootstrap\Modal;
use yii\helpers\Url;

GrowlAsset::register($this);

Modal::begin(['id' => 'compose-message', 'header' => '<h2>Compose new Message</h2>']);
Modal::end();

$recipient_id = 1337; # write an message to user with id 1337

echo Html::a('<span class="glyphicon glyphicon-envelope"></span> Compose Message', '', [
  'class' => 'btn btn-default btn-contact-user',
  'data-recipient' => $recipient_id,
  'data-pjax' => 0
]);

$message_url = Url::to(['//message/message/compose']);

$this->registerJs("
  $('.modal-body').on('click', '.btn-send-message', function(event) {
       if ($('#message-title').val()) {
           $.post('".$message_url."', $('#message-form').serializeArray(), function() {
               $.notify({message: 'Message has been sent successfully.'}, {type: 'success'});
               $('#compose-message').modal('hide');
           });
      } else {
          $('.modal-body').prepend('<div class=\"alert alert-warning\">Please enter a title at least.</div>');
      }

     event.preventDefault();
  });
  
  $('.modal-body').on('submit', '#message-form', function(event) {
    $('.btn-send-message').click(); 
    event.preventDefault();
  });
   

  $('body').on('click', '.btn-contact-user', function(event) {
    $('#compose-message').modal();
    recipient = $(this).data('recipient');
    $.ajax('".$message_url."?to='+recipient+'&add_to_recipient_list=1', {
      'success': function(result) {
           $('.modal-body').html(result);
       }
    });

    event.preventDefault();
    });
");
```

For some url rules, you can copy Module::$urlRules into your 'rules' section of
the URL Manager.

## Contributing to this project

Anyone and everyone is welcome to contribute.

## License

Yii2-message is released under the GPLv3 License.
