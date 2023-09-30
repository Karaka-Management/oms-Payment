<?php
/**
 * Jingga
 *
 * PHP Version 8.1
 *
 * @package   Modules\Payment\Models
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\Payment\Models;

use phpOMS\Stdlib\Base\Enum;

/**
 * Type for external references
 *
 * @package Modules\Payment\Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
abstract class PaymentType extends Enum
{
    public const CREDITCARD = 1;

    public const SWIFT = 2;

    public const PAYPAL = 3;
}
