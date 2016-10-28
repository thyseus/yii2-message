# Yii2-message 

System for users to send each other private messages.

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
        'class' => 'thyseus\yii2-message\Module',
        'modelClass' => '\app\models\User', // optional. your User model
    ],
],
```

Your User model should have a 'id' and 'username' attribute.

## Contributing to this project

Anyone and everyone is welcome to contribute. Please take a moment to
review the [guidelines for contributing](.github/CONTRIBUTING.md).

## License

Yii2-message is released under the GPLv3 License.
