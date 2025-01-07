<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use App\Enums\ResponseStatus;

class ResponseHandler
{
    public ResponseStatus $status;
    public string $message;
    public array $data;
    public string $channel;

    public function __construct(ResponseStatus $status, string $message, array $data = [], string $channel = "") {
        $this->status = $status;
        $this->message = $message;
        $this->data = $data;
        $this->channel = $channel;

        // Log the message to the specified channel
        if ($channel) {
            Log::channel($channel)->log($this->getLogLevel($status), $message, $data);
        }
    }

    public static function success(string $message, array $data = [], string $channel = 'success_log'): self {
        return new self(ResponseStatus::SUCCESS, $message, $data, $channel);
    }

    public static function error(string $message, array $data = [], string $channel = 'error_log'): self {
        return new self(ResponseStatus::ERROR, $message, $data, $channel);
    }

    public static function info(string $message, array $data = [], string $channel = 'info_log'): self {
        return new self(ResponseStatus::INFO, $message, $data, $channel);
    }

    private function getLogLevel(ResponseStatus $status): string {
        return match ($status) {
            ResponseStatus::SUCCESS => 'info',
            ResponseStatus::ERROR => 'error',
            ResponseStatus::INFO => 'info',
        };
    }
}
