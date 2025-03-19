<?php

namespace App\Services;

use Monolog\Formatter\JsonFormatter;
use Monolog\LogRecord;
use Illuminate\Support\Facades\Log;

class JsonFormatterEscape extends JsonFormatter
{
    public function format(LogRecord $record): string
    {
        // Decodifica il contenuto JSON presente in message
        $messageData = json_decode($record->message, true);

        // Se il message è JSON valido, lo usiamo, altrimenti costruiamo un array con le informazioni
        $logData = is_array($messageData) ? $messageData : [
            'status' => 'unknown',
            'message' => $record->message,
            'level' => $record->level,
            'timestamp' => $record->datetime->format('c'),
        ];

        return json_encode($logData, JSON_UNESCAPED_UNICODE) . "\n";
    }
}
