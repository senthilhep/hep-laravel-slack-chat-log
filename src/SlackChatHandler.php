<?php

namespace Enigma;

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
        foreach ($this->getWebhookUrl() as $url) {
            Http::post($url, $this->getRequestBody($params));
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
            throw new Exception('Google chat webhook url is not configured.');
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
        //$recordArr['formatted'] = substr($recordArr['formatted'], 34);
        return [
            'text' => "*".Config::get('app.name') . ": " . $recordArr['level_name'] . "* \n" . $recordArr['message'] . "\n" . $this->getLevelContent($recordArr),
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
