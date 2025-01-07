<?php

namespace App\Enums;

enum ResponseStatus: string
{
    case SUCCESS = 'success';
    case ERROR = 'error';
    case INFO = 'info';
}
