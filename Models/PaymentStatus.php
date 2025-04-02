<?php
/**
 * Jingga
 *
 * PHP Version 8.2
 *
 * @package   Modules\Payment\Models
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.2
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\Payment\Models;

use phpOMS\Stdlib\Base\Enum;

/**
 * Status for external references
 *
 * @package Modules\Payment\Models
 * @license OMS License 2.2
 * @link    https://jingga.app
 * @since   1.0.0
 */
abstract class PaymentStatus extends Enum
{
    public const ACTIVATE = 1;

    public const INACTIVE = 2;
}
