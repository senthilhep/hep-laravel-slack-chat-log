<p align="center"><code>&hearts; Made with &lt;love/&gt; And I love &lt;code/&gt;</code></p>

# Laravel/Lumen Slack Chat Log

Brings up the option for sending the logs to Slack chat from [Laravel](https://laravel.com)/[Lumen](https://lumen.laravel.com).

## Installation
### Composer install
```shell
composer require senthilhep/hep-laravel-slack-chat-log
```

Add the following code to the channels array in `config/logging.php` in your laravel/lumen application.
```
'slack-chat' => [
    'driver' => 'monolog',
    'url' => env('LOG_SLACK_CHAT_WEBHOOK_URL'),
    'handler' => \Enigma\SlackChatHandler::class,
],
```

You can provide the eight logging levels defined in the [RFC 5424 specification](https://tools.ietf.org/html/rfc5424): `emergency`, `alert`, `critical`, `error`, `warning`, `notice`, `info`, and `debug`

<b>Note*:</b> Make sure to set the <b>LOG_SLACK_CHAT_WEBHOOK_URL</b> env variable.

Here, you can set multiple slack chat webhook url as comma separated value for the <b>LOG_SLACK_CHAT_WEBHOOK_URL</b> env variable.

In order to notify different users for different log levels, you can set the corresponding env keys mentioned to configure in the `logging.php` file. 

## License

Copyright © Senthil Prabu

Laravel Slack Chat Log is open-sourced software licensed under the [MIT license](LICENSE).
