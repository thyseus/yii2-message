# Yii2-message

System for users to send each other private messages.

## Prerequisites:

You need an User Model that extends from ActiveRecord.
It also should have the 'id' and 'username' attributes.

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

For some url rules, you can copy Module::$urlRules into your 'rules' section of
the URL Manager.

## Contributing to this project

Anyone and everyone is welcome to contribute. Please take a moment to
review the [guidelines for contributing](.github/CONTRIBUTING.md).

## License

Yii2-message is released under the GPLv3 License.
