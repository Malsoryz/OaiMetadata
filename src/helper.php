<?php

use Closure;
use Exception;

if (! function_exists('when')) {
    function when($condition, Closure $handle, ?Closure $default = null)
    {
        if ($condition) {
            return $handle($condition);
        } else {
            return $default instanceof Closure ? $default($condition) : $default;
        }
    }
}