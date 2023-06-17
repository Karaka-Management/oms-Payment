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

namespace Modules\Payment\tests\Admin;

/**
 * @internal
 */
final class AdminTest extends \PHPUnit\Framework\TestCase
{
    protected const NAME = 'Payment';

    protected const URI_LOAD = 'http://127.0.0.1/en/backend/news';

    use \Modules\tests\ModuleTestTrait;
}
