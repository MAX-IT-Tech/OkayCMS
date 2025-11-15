<?php

namespace Okay\Modules\OkayCMS\MinimalOrderAmount;

use Okay\Core\Cart;
use Okay\Core\Design;
use Okay\Core\EntityFactory;
use Okay\Core\FrontTranslations;
use Okay\Core\Languages;
use Okay\Core\Money;
use Okay\Core\OkayContainer\Reference\ServiceReference as SR;
use Okay\Core\Settings;
use Okay\Modules\OkayCMS\MinimalOrderAmount\Extenders\FrontendExtender;
use Okay\Modules\OkayCMS\MinimalOrderAmount\Helpers\OrderSettingsBlockHelper;
use Okay\Modules\OkayCMS\MinimalOrderAmount\Services\MinimalOrderAmountService;

return [
    MinimalOrderAmountService::class => [
        'class' => MinimalOrderAmountService::class,
        'arguments' => [
            new SR(EntityFactory::class),
            new SR(Money::class),
            new SR(Settings::class),
        ],
    ],
    OrderSettingsBlockHelper::class => [
        'class' => OrderSettingsBlockHelper::class,
        'arguments' => [
            new SR(Design::class),
            new SR(EntityFactory::class),
            new SR(Settings::class),
            new SR(Languages::class),
        ],
    ],
    FrontendExtender::class => [
        'class' => FrontendExtender::class,
        'arguments' => [
            new SR(MinimalOrderAmountService::class),
            new SR(Design::class),
            new SR(Cart::class),
            new SR(FrontTranslations::class),
        ],
    ],
];
