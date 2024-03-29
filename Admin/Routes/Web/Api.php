<?php
/**
 * Jingga
 *
 * PHP Version 8.2
 *
 * @package   Modules
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

use phpOMS\Router\RouteVerb;

return [
    '^.*/payment/webhook(\?.*|$)' => [
        [
            'dest'       => '\Modules\Payment\Controller\ApiController:apiWebhook',
            'verb'       => RouteVerb::ANY,
            'csrf'       => true,
            'permission' => [
            ],
        ],
    ],
];
