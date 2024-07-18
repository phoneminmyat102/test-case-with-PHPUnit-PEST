<?php

namespace Tests\Unit;

use App\Services\CurrencyService;
use PHPUnit\Framework\TestCase;

class CurrencyTest extends TestCase
{
    /**
     * A basic unit test example.
     */
    public function test_currency_convert_successfully(): void
    {
        $this->assertEquals(98, (new CurrencyService())->convert(100, 'usd', 'euro'));
    }
}
