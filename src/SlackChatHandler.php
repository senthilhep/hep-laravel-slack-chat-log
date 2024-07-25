<?php

namespace Enigma;

use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\LogRecord;

class SlackChatHandler extends AbstractProcessingHandler
{
    /**
     * Writes the record down to the log of the implementing handler.
     *
     * @param LogRecord $record
     *
     * @throws Exception
     */
    protected function write(LogRecord $record): void
    {
        $params = $record->toArray();
        $params['formatted'] = $record->formatted;
        if (Config::get('logging.channels.slack-chat.error_level') >= $params['level']) {
            foreach ($this->getWebhookUrl() as $url) {
                Http::post($url, $this->getRequestBody($params));
            }
        }
    }

    /**
     * Get the webhook url.
     *
     * @return array
     *
     * @throws Exception
     */
    protected function getWebhookUrl(): array
    {
        $url = Config::get('logging.channels.slack-chat.url');
        if (!$url) {
            throw new Exception('Slack chat webhook url is not configured.');
        }

        if (is_array($url)) {
            return $url;
        }

        return array_map(function ($each) {
            return trim($each);
        }, explode(',', $url));
    }

    /**
     * Get the request body content.
     *
     * @param array $recordArr
     * @return array
     */
    protected function getRequestBody(array $recordArr): array
    {
        $timezone = (Config::get('logging.channels.slack-chat.timezone') != null && !empty(Config::get('logging.channels.slack-chat.timezone'))) ? Config::get('logging.channels.slack-chat.timezone') : 'Asia/Kolkata';
        return [
            'blocks' => [
                (object)[
                    'type' => 'rich_text',
                    'elements' => [
                        (object)[
                            'type' => 'rich_text_section',
                            'elements' => [
                                (object)[
                                    'type' => 'text',
                                    'text' => Config::get('app.name') . ": " . $recordArr['level_name'],
                                    'style' => (object)[
                                        'bold' => true
                                    ]
                                ]
                            ]
                        ],
                        (object)[
                            'type' => 'rich_text_section',
                            'elements' => [
                                (object)[
                                    'type' => 'text',
                                    'text' => $recordArr['message'],
                                    'style' => (object)[
                                        'bold' => true
                                    ]
                                ]
                            ]
                        ],
                        (object)[
                            'type' => 'rich_text_preformatted',
                            'elements' => [
                                (object)[
                                    'type' => 'text',
                                    'text' => $this->getLevelContent($recordArr)
                                ]
                            ]
                        ],
                        (object)[
                            'type' => 'rich_text_section',
                            'elements' => [
                                (object)[
                                    'type' => 'text',
                                    'text' => "Date&Time: " . Carbon::parse(strtotime($recordArr['datetime']))->timezone($timezone)->format('Y-m-d h:i: A'),
                                    'style' => (object)[
                                        'bold' => true,
                                        'italic' => true
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * Get the card content.
     *
     * @param array $recordArr
     * @return string
     */
    protected function getLevelContent(array $recordArr): string
    {
        return substr($recordArr['formatted'], 0, 38000);
    }
}
