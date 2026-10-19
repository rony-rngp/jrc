<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class PolyfillTest extends TestCase
{
    public function test_mb_strcut_is_defined_and_functional(): void
    {
        $this->assertTrue(function_exists('mb_strcut'));

        // Short string
        $this->assertEquals('hello', mb_strcut('hello world', 0, 5));

        // Long query string (> 2000 chars as in Laravel's Listener.php)
        $longSql = 'SELECT * FROM test_table WHERE id IN (' . implode(',', range(1, 1000)) . ')';
        $this->assertGreaterThan(2000, strlen($longSql));

        $cut = mb_strcut($longSql, 0, 2000);
        $this->assertLessThanOrEqual(2000, strlen($cut));
        $this->assertStringStartsWith('SELECT * FROM test_table', $cut);

        // UTF-8 multibyte characters
        $utf8 = 'Hello বাংলা World 😊🎉';
        $cutUtf8 = mb_strcut($utf8, 0, 10);
        $this->assertNotEmpty($cutUtf8);

        // Empty string
        $this->assertEquals('', mb_strcut('', 0, 10));
    }

    public function test_mb_strimwidth_is_defined_and_functional(): void
    {
        $this->assertTrue(function_exists('mb_strimwidth'));

        $trimmed = mb_strimwidth('Hello World', 0, 8, '...');
        $this->assertEquals('Hello...', $trimmed);

        $short = mb_strimwidth('Hi', 0, 10, '...');
        $this->assertEquals('Hi', $short);
    }
}
