<?php
/**
 * Karaka
 *
 * PHP Version 8.1
 *
 * @package   Modules
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

use Modules\Payment\Controller\ApiController;
use Modules\Payment\Models\PermissionCategory;
use phpOMS\Account\PermissionType;
use phpOMS\Router\RouteVerb;

return [
    '^.*/payment/webhook(\?.*|$)' => [
        [
            'dest'       => '\Modules\Payment\Controller\ApiController:apiWebhook',
            'verb'       => RouteVerb::SET,
            'permission' => [
            ],
        ],
    ],
];
