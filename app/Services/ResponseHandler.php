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

    public function __construct(ResponseStatus $status, string $message, array $data = [], string $channel = "")
    {
        $this->status = $status;
        $this->message = $message;
        $this->data = $data;
        $this->channel = $channel;
        if (count($data) > 0) {
            $logData = [
                'status' => $status->value,
                'description' => $message,
                'data' => $data,
                'log_time' => now()->toIso8601String()
            ];
        } else {
            $logData = [
                'status' => $status->value,
                'description' => $message,
                'log_time' => now()->toIso8601String()
            ];
        }


        // Log in formato JSON sul canale specificato
        if ($channel) {
            Log::channel($channel)->log($this->getLogLevel($status), json_encode($logData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }
    }

    public static function success(string $message, array $data = [], string $channel = 'success_log'): self
    {
        return new self(ResponseStatus::SUCCESS, $message, $data, $channel);
    }

    public static function error(string $message, array $data = [], string $channel = 'error_log'): self
    {
        return new self(ResponseStatus::ERROR, $message, $data, $channel);
    }

    public static function info(string $message, array $data = [], string $channel = 'info_log'): self
    {
        return new self(ResponseStatus::INFO, $message, $data, $channel);
    }

    public static function warning(string $message, array $data = [], string $channel = 'warning_log'): self
    {
        return new self(ResponseStatus::WARNING, $message, $data, $channel);
    }

    private function getLogLevel(ResponseStatus $status): string
    {
        return match ($status) {
            ResponseStatus::SUCCESS => 'info',
            ResponseStatus::ERROR => 'error',
            ResponseStatus::INFO => 'info',
            ResponseStatus::WARNING => 'warning',
        };
    }
}
