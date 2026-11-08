<?php

namespace Tests\Unit;

use App\Support\Money;
use PHPUnit\Framework\TestCase;

class MoneyTest extends TestCase
{
    public function test_formats_taka_with_thousands_separators(): void
    {
        $this->assertSame('৳1,500', Money::bdt(1500));
        $this->assertSame('৳800', Money::bdt('800.00'));
    }

    public function test_formats_a_price_range(): void
    {
        $this->assertSame('৳800 – ৳1,500', Money::range(800, 1500));
        $this->assertSame('৳500', Money::range(500, 500));
        $this->assertSame('Price on request', Money::range(null, null));
    }
}
