<?php

namespace Malsoryz\OaiXml\Enums;

use Illuminate\Support\Carbon;

enum Granularity: string 
{
    case Day = 'YYYY-MM-DD';
    case Second = 'YYYY-MM-DDThh:mm:ssZ';

    public function format(Carbon|string $date): string
    {
        $carbon = $date instanceof Carbon ? $date : Carbon::parse($date);
        return $carbon->format(match ($this) {
            self::Day => 'Y-m-d',
            self::Second => 'Y-m-d\TH:i:s\Z',
        });
    }
}