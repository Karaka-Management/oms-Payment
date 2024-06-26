<?php
/**
 * Jingga
 *
 * PHP Version 8.2
 *
 * @package   Modules\Payment\Models
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\Payment\Models;

use Modules\Admin\Models\Account;
use Modules\Admin\Models\AccountExternal;
use Modules\Admin\Models\NullAccount;

/**
 * Payment article class.
 *
 * @package Modules\Payment\Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
class Payment implements \JsonSerializable
{
    /**
     * ID.
     *
     * @var int
     * @since 1.0.0
     */
    public int $id = 0;

    /**
     * External type.
     *
     * @var int
     * @since 1.0.0
     */
    public int $type = PaymentType::CREDITCARD;

    /**
     * External status.
     *
     * @var int
     * @since 1.0.0
     */
    public int $status = PaymentStatus::ACTIVATE;

    /**
     * Content.
     *
     * @var string
     * @since 1.0.0
     */
    public string $content1 = '';

    /**
     * Content.
     *
     * @var string
     * @since 1.0.0
     */
    public string $content2 = '';

    /**
     * Content.
     *
     * @var string
     * @since 1.0.0
     */
    public string $content3 = '';

    /**
     * Content.
     *
     * @var string
     * @since 1.0.0
     */
    public string $content4 = '';

    /**
     * Content.
     *
     * @var string
     * @since 1.0.0
     */
    public string $content5 = '';

    /**
     * Created.
     *
     * @var \DateTimeImmutable
     * @since 1.0.0
     */
    public \DateTimeImmutable $createdAt;

    /**
     * Creator.
     *
     * @var Account
     * @since 1.0.0
     */
    public Account $account;

    /**
     * Creator.
     *
     * @var null|AccountExternal
     * @since 1.0.0
     */
    public ?AccountExternal $externalRef = null;

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        $this->account   = new NullAccount();
        $this->createdAt = new \DateTimeImmutable('now');
    }

    /**
     * {@inheritdoc}
     */
    public function toArray() : array
    {
        return [
            'id' => $this->id,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize() : mixed
    {
        return $this->toArray();
    }
}
