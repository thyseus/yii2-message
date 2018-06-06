# ATTENTION:

github has been bought by Microsoft. This repository is orphaned and has been moved to:

https://gitlab.com/thyseus/yii2-message

Thanks a lot for your understanding and blame Microsoft.

# Yii2-message

System for users to send each other private messages.
- A user configurable ignorelist and admin configurable whitelist (administrators are able to fine-tune 
the definition of which users are able to write to whom) is supported.
- Encryption is not (yet?) supported.
- Uses Font Awesome (http://fontawesome.io/) for some icons
- Every message sent inside the messaging system can be forwarded to the recipient be e-mail automatically.
- Since 0.4.0 you can save drafts and use signatures

## Prerequisites:

You need a Model with the 'id' and 'username' attributes. This needs to be an ActiveRecord or Model instance.

I suggest to use https://github.com/dektrium/yii2-user which works wonderful with this module.
Note that dektrium is no longer maintained, for future projects you should take a look at:
https://github.com/2amigos/yii2-usuario

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
        'userModelClass' => '\app\models\User', // your User model. Needs to be ActiveRecord.
    ],
],
```

## Sending E-Mails

If you want the system to automatically send E-Mails via Yii::$app->mailer you only need to provide an
'email' column in your ActiveRecord Model.

Use the $mailMessages module option to define which users are getting E-Mails. For Example:

```php
        'mailMessages' => function ($user) {
            return $user->profile->receive_emails === true;
        },
```

You can overwrite the default e-mail views and layout by providing an @app/mail/ directory inside your Application.

## Mailqueue

From version 0.4 and above you can use yii2-queue (https://github.com/yiisoft/yii2-queue) to send messages via a
mail queue. Once you have yii2-queue configured in your application you can set

```php
        'useMailQueue' => true,
```

to let yii2-message push an EmailJob on to your queue instead of sending the E-Mail directly.

If you want to use an mailqueue like https://github.com/nterms/yii2-mailqueue you can override the 'mailer' 
configuration option in the module configuration. Since yii2-queue is stable and an official extension i personally
prefer to use yii2-queue instead of 3rd party extensions.

## Ignore List and Recipients Filter

The user can manage his own ignore list using the message/message/ignorelist route. You can place a callback that
defines which users should be able to be messaged. For example, if you do not want your users to be able to write
to admin users, do this:

```php
        'recipientsFilterCallback' => function ($users) {
            return array_filter($users, function ($user) {
                return !$user->isAdmin;
            });
        },
```

The recipients filter is applied after the ignore list.

## Actions

The following Actions are possible:

* inbox: https://your-domain/message/message/inbox
* drafts: https://your-domain/message/message/drafts
* signature: https://your-domain/message/message/signature
* out-of-office: https://your-domain/message/message/out-of-office
* sent messages: https://your-domain/message/message/sent
* compose a message: https://your-domain/message/message/compose
* delete a message: https://your-domain/message/message/delete/hash/<hash>
* view a message: https://your-domain/message/message/view/hash/<hash>
* manage your ignorelist: https://your-domain/message/message/ignorelist

You can place this code snippet in your layouts/main.php to give your users access
to the message actions:

```php
$messagelabel = '<span class="fas fa-envelope"></span>';
$unread = Message::find()->where(['to' => $user->id, 'status' => 0])->count();
if ($unread > 0)
      $messagelabel .= '(' . $unread . ')';
      
echo Nav::widget([
    'encodeLabels' => false, // important to display HTML-code (fontawesome icons)
    'items' => [
    // ...
    [
      'label' => $messagelabel,
      'url' => '',
      'visible' => !Yii::$app->user->isGuest, 'items' => [
        ['label' => '<i class="fas fa-inbox"></i> Inbox', 'url' => ['/message/message/inbox']],
        ['label' => '<i class="fas fa-share-square"></i> Sent', 'url' => ['/message/message/sent']],
        '<hr>',
        ['label' => '<i class="fas fa-firstdraft"></i> Drafts', 'url' => ['/message/message/drafts']],
        ['label' => '<i class="fas fa-clone"></i> Signature', 'url' => ['/message/message/signature']],
        ['label' => '<i class="fas fa-calendar-times"></i> Out of Office', 'url' => ['/message/message/out-of-office']],
        ['label' => '<i class="fas fa-ban"></i> Manage your Ignorelist', 'url' => ['/message/message/ignorelist']],
        '<hr>',
        ['label' => '<i class="fas fa-plus"></i> Compose a Message', 'url' => ['/message/message/compose']],
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

echo Html::a('<span class="fas fa-envelope"></span> Compose Message', '', [
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

For some common url rules, you can copy Module::$urlRules into your 'rules' section of the URL Manager.

## Contributing to this project

Anyone and everyone is welcome to contribute.

## License

Yii2-message is released under the GPLv3 License.
