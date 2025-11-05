<?php

namespace Tarsana\Syntax;

class Text
{
    public static function split(string $text, string $separator, string $surrounders = '""[](){}', string $wrappers = '""'): array
    {
        // Let's assume some values to understand how this function works
        // surrounders = '""{}()'
        // unwrap = '""'
        // separator = ' '
        // $text = 'foo ("bar baz" alpha) beta'
        $counters = [
            'values'   => [], // each item of this array refers to the number
                              // of closings needed for an opening
            'openings' => [], // an associative array where the key is an opening
                              // and the value is the index of corresponding cell
                              // in the 'values' field
            'closings' => [], // associative array for closings like the previous one
            'total'    => 0   // the total number of needed closings
        ];
        foreach (str_split($surrounders) as $key => $char) {
            $index = intdiv($key, 2);
            $counters['values'][$index] = 0;
            if ($key % 2 == 0) {
                $counters['openings'][$char] = $index;
            } else {
                $counters['closings'][$char] = $index;
            }
        }
        // $counters = [
        //   'values'   => [0, 0, 0],
        //   'openings' => ['"' => 0, '{' => 1, '(' => 2],
        //   'closings' => ['"' => 0, '}' => 1, ')' => 2],
        //   'total'    => 0
        // ]
        $result = [];
        $length = strlen($text);
        $separatorLength = strlen($separator);
        $characters = str_split($text);
        $index = 0;
        $buffer = '';
        while ($index < $length) {
            if (substr($text, $index, $separatorLength) == $separator && $counters['total'] == 0) {
                $result[] = self::unwrap($buffer, $wrappers);
                $buffer = '';
                $index += $separatorLength;
            } else {
                $c = $characters[$index];
                $isOpening = array_key_exists($c, $counters['openings']);
                $isClosing = array_key_exists($c, $counters['closings']);
                if ($isOpening && $isClosing) { // when $c == '"' for example
                    $value = $counters['values'][$counters['openings'][$c]];
                    if ($value == 0) {
                        $counters['values'][$counters['openings'][$c]] = 1;
                        $counters['total']++;
                    } else {
                        $counters['values'][$counters['openings'][$c]] = 0;
                        $counters['total']--;
                    }
                } else {
                    if ($isOpening) {
                        $counters['values'][$counters['openings'][$c]]++;
                        $counters['total']++;
                    }
                    if ($isClosing) {
                        $counters['values'][$counters['closings'][$c]]--;
                        $counters['total']--;
                    }
                }
                $buffer .= $c;
                $index++;
            }
        }
        if ($buffer != '') {
            $result[] = self::unwrap($buffer, $wrappers);
        }

        return $result;
    }

    /**
     * ('"Hello"', '""') => 'Hello'
     * ('(Hey)', '()') => 'Hey'
     * ('(Hey)', '""()') => 'Hey'
     */
    public static function unwrap(string $text, string $wrappers): string
    {
        $size = strlen($wrappers);
        for ($i = 0; $i < $size; $i += 2) {
            if (
                substr($text, 0, 1) == substr($wrappers, $i, 1)
                && substr($text, -1) == substr($wrappers, $i + 1, 1)
            ) {
                return substr($text, 1, strlen($text) - 2);
            }
        }
        return $text;
    }

    public static function join(array $items, string $separator): string
    {
        return implode($separator, array_map(fn(string $item) => (str_contains($item, $separator)) ? "\"{$item}\"" : $item, $items));
    }
}
