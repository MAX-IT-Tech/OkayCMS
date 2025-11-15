<?php

namespace Okay\Modules\OkayCMS\MinimalOrderAmount\Extenders;

use Okay\Core\Cart;
use Okay\Core\Design;
use Okay\Core\Modules\Extender\ExtensionInterface;
use Okay\Core\FrontTranslations;
use Okay\Modules\OkayCMS\MinimalOrderAmount\Services\MinimalOrderAmountService;

class FrontendExtender implements ExtensionInterface
{
    private $service;
    private $design;
    private $cart;
    private $translations;

    public function __construct(
        MinimalOrderAmountService $service,
        Design $design,
        Cart $cart,
        FrontTranslations $translations
    ) {
        $this->service      = $service;
        $this->design       = $design;
        $this->cart         = $cart;
        $this->translations = $translations;
    }

    public function attachCartState($cart)
    {
        $cart->minimum_order_amount = $this->service->getCartState($cart);
        return $cart;
    }

    public function extendCartAjaxResult($result, $cart)
    {
        $state = $cart->minimum_order_amount ?? null;
        $result['minimum_order_state'] = $state;
        $result['minimum_order_warning'] = $this->design->fetch('minimal_order_warning.tpl');
        return $result;
    }

    public function validateCart($error)
    {
        if (!empty($error)) {
            return $error;
        }

        $cart = $this->cart->get();
        $state = $cart->minimum_order_amount ?? $this->service->getCartState($cart);
        if (!empty($state['violations'][0]['message'])) {
            return $state['violations'][0]['message'];
        }

        return $error;
    }

    public function assignDesignData()
    {
        $cart = $this->cart->get();
        $state = $cart->minimum_order_amount ?? $this->service->getCartState($cart);
        $this->design->assignJsVar('min_order_rules', $this->service->getRulesForJs());
        $this->design->assignJsVar('min_order_warning_template', $this->service->getWarningTemplate());
        $this->design->assignJsVar(
            'min_order_fast_order_error',
            $this->translations->getTranslation('okay_cms__minimal_order_amount__fast_order_error')
        );
        $this->design->assignJsVar('min_order_initial_state', $state);
    }

    public function validateFastOrder($errors, $order, $variantId)
    {
        if (!empty($errors)) {
            return $errors;
        }

        $amount = isset($order->amount) ? (int) $order->amount : 1;
        $violation = $this->service->getVariantViolation($variantId, max(1, $amount));
        if (!empty($violation['message'])) {
            $errors[] = $violation['message'];
        }

        return $errors;
    }
}
