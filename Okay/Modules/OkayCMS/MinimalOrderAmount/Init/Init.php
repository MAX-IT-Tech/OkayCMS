<?php

namespace Okay\Modules\OkayCMS\MinimalOrderAmount\Init;

use Okay\Core\Cart;
use Okay\Core\Modules\AbstractInit;
use Okay\Core\Modules\EntityField;
use Okay\Helpers\CartHelper;
use Okay\Helpers\MainHelper;
use Okay\Helpers\ValidateHelper;
use Okay\Modules\OkayCMS\FastOrder\Helpers\ValidateHelper as FastOrderValidateHelper;
use Okay\Modules\OkayCMS\MinimalOrderAmount\Entities\RulesEntity;
use Okay\Modules\OkayCMS\MinimalOrderAmount\Extenders\FrontendExtender;
use Okay\Modules\OkayCMS\MinimalOrderAmount\Helpers\OrderSettingsBlockHelper;

class Init extends AbstractInit
{
    public function install()
    {
        $this->migrateEntityTable(RulesEntity::class, [
            (new EntityField('id'))
                ->setTypeInt(11, false)
                ->setAutoIncrement()
                ->setIndexPrimaryKey(),
            (new EntityField('category_id'))
                ->setTypeInt(11, false)
                ->setIndex(),
            (new EntityField('amount'))
                ->setTypeDecimal('14,2')
                ->setDefault(0),
        ]);
    }

    public function init()
    {
        $this->registerBackendController('SettingsAdmin');
        $this->addBackendControllerPermission('SettingsAdmin', 'order_settings');

        $this->addBackendBlock('order_settings_custom_block', 'order_settings_block.tpl', function (OrderSettingsBlockHelper $helper) {
            $helper->assign();
        });

        $this->registerChainExtension(
            [Cart::class, 'applyDiscounts'],
            [FrontendExtender::class, 'attachCartState']
        );

        $this->registerChainExtension(
            [CartHelper::class, 'getAjaxCartResult'],
            [FrontendExtender::class, 'extendCartAjaxResult']
        );

        $this->registerChainExtension(
            [ValidateHelper::class, 'getCartValidateError'],
            [FrontendExtender::class, 'validateCart']
        );

        $this->registerChainExtension(
            [MainHelper::class, 'setDesignDataProcedure'],
            [FrontendExtender::class, 'assignDesignData']
        );

        $this->registerChainExtension(
            [FastOrderValidateHelper::class, 'validateFastOrderHeler'],
            [FrontendExtender::class, 'validateFastOrder']
        );
    }
}
