<?php

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

// Source AI
if (! function_exists('matchTemplate')) {
    function matchTemplate(string $template, string $input): ?array
    {
        // cari semua placeholder {name}
        preg_match_all('/\{([a-zA-Z0-9_]+)\}/', $template, $placeholders);

        // ganti placeholder dengan named capture group
        $pattern = preg_replace(
            '/\{([a-zA-Z0-9_]+)\}/',
            '(?P<$1>.+?)',
            preg_quote($template, '#')
        );

        // balikkan escaped \{name\} ke regex capture
        $pattern = preg_replace(
            '/\\\\\{([a-zA-Z0-9_]+)\\\\\}/',
            '(?P<$1>.+?)',
            $pattern
        );

        // bungkus jadi regex utuh
        $pattern = '#^' . $pattern . '$#';

        // cocokkan
        if (preg_match($pattern, $input, $matches)) {
            $result = [];
            foreach ($placeholders[1] as $name) {
                $result[$name] = $matches[$name] ?? null;
            }
            return $result;
        }

        return null;
    }
}
