<?php
/**
 * Jingga
 *
 * PHP Version 8.1
 *
 * @package   Modules\Payment
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\Payment\Controller;

use phpOMS\Asset\AssetType;
use phpOMS\Contract\RenderableInterface;
use phpOMS\Message\RequestAbstract;
use phpOMS\Message\ResponseAbstract;
use phpOMS\Views\View;

/**
 * Payment controller class.
 *
 * @package Modules\Payment
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
final class BackendController extends Controller
{
    /**
     * Routing end-point for application behaviour.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param array            $data     Generic data
     *
     * @return RenderableInterface Returns a renderable object
     *
     * @since 1.0.0
     * @codeCoverageIgnore
     */
    public function viewCheckoutStripe(RequestAbstract $request, ResponseAbstract $response, array $data = []) : RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);
        $view->setTemplate('/Modules/Payment/Theme/Backend/checkout-stripe');

        $head = $response->data['Content']->head;
        $head->addAsset(AssetType::CSS, 'Modules/Payment/Theme/Backend/css/styles.css');
        $head->addAsset(AssetType::JS, 'Modules/Payment/Theme/Backend/css/styles.css');

        return $view;
    }
}
