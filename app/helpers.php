<?php

if (! function_exists('format_inr')) {
    function format_inr(float|int|string|null $amount, int $decimals = 2): string
    {
        $symbol = config('shop.currency_symbol', '₹');

        return $symbol . number_format((float) $amount, $decimals);
    }
}
