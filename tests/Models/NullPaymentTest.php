<?php
/**
 * Jingga
 *
 * PHP Version 8.1
 *
 * @package   tests
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\Payment\tests\Models;

use Modules\Payment\Models\NullPayment;

/**
 * @internal
 */
final class NullPaymentTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @covers Modules\Payment\Models\NullPayment
     * @group module
     */
    public function testNull() : void
    {
        self::assertInstanceOf('\Modules\Payment\Models\Payment', new NullPayment());
    }

    /**
     * @covers Modules\Payment\Models\NullPayment
     * @group module
     */
    public function testId() : void
    {
        $null = new NullPayment(2);
        self::assertEquals(2, $null->id);
    }

    /**
     * @covers Modules\Payment\Models\NullPayment
     * @group module
     */
    public function testJsonSerialize() : void
    {
        $null = new NullPayment(2);
        self::assertEquals(['id' => 2], $null->jsonSerialize());
    }
}
