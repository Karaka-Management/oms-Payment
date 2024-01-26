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

use Modules\Admin\Models\AccountExternal;
use phpOMS\DataStorage\Database\Mapper\DataMapperFactory;

/**
 * Account mapper class.
 *
 * @package Modules\Payment\Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 *
 * @template T of Payment
 * @extends DataMapperFactory<T>
 */
class PaymentMapper extends DataMapperFactory
{
    /**
     * Columns.
     *
     * @var array<string, array{name:string, type:string, internal:string, autocomplete?:bool, readonly?:bool, writeonly?:bool, annotations?:array}>
     * @since 1.0.0
     */
    public const COLUMNS = [
        'payment_id'         => ['name' => 'payment_id',           'type' => 'int',      'internal' => 'id'],
        'payment_status'     => ['name' => 'payment_status',       'type' => 'int',      'internal' => 'status'],
        'payment_type'       => ['name' => 'payment_type',         'type' => 'int',      'internal' => 'type'],
        'payment_content1'   => ['name' => 'payment_content1',        'type' => 'string',      'internal' => 'content1'],
        'payment_content2'   => ['name' => 'payment_content2',        'type' => 'string',      'internal' => 'content2'],
        'payment_content3'   => ['name' => 'payment_content3',        'type' => 'string',      'internal' => 'content3'],
        'payment_content4'   => ['name' => 'payment_content4',        'type' => 'string',      'internal' => 'content4'],
        'payment_content5'   => ['name' => 'payment_content5',        'type' => 'string',      'internal' => 'content5'],
        'payment_created_at' => ['name' => 'payment_created_at',   'type' => 'DateTimeImmutable', 'internal' => 'createdAt', 'readonly' => true],
        'payment_account'    => ['name' => 'payment_account',   'type' => 'int', 'internal' => 'account'],
        'payment_external'   => ['name' => 'payment_external',   'type' => 'int', 'internal' => 'externalRef'],
    ];

    /**
     * Has one relation.
     *
     * @var array<string, array{mapper:class-string, external:string, by?:string, column?:string, conditional?:bool}>
     * @since 1.0.0
     */
    public const OWNS_ONE = [
        'external' => [
            'mapper'   => AccountExternal::class,
            'external' => 'account_localization',
        ],
    ];

    /**
     * Model to use by the mapper.
     *
     * @var class-string<T>
     * @since 1.0.0
     */
    public const MODEL = Payment::class;

    /**
     * Primary table.
     *
     * @var string
     * @since 1.0.0
     */
    public const TABLE = 'payment';

    /**
     * Primary field name.
     *
     * @var string
     * @since 1.0.0
     */
    public const PRIMARYFIELD = 'payment_id';

    /**
     * Created at column
     *
     * @var string
     * @since 1.0.0
     */
    public const CREATED_AT = 'payment_created_at';
}
