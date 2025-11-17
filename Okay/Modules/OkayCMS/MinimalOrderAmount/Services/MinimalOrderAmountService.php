<?php

namespace Okay\Modules\OkayCMS\MinimalOrderAmount\Services;

use Okay\Core\EntityFactory;
use Okay\Core\Languages;
use Okay\Core\Money;
use Okay\Core\ServiceLocator;
use Okay\Core\Settings;
use Okay\Core\Cart;
use Okay\Entities\CategoriesEntity;
use Okay\Entities\CurrenciesEntity;
use Okay\Entities\VariantsEntity;
use Okay\Entities\ProductsEntity;
use Okay\Modules\OkayCMS\MinimalOrderAmount\Entities\RulesEntity;

class MinimalOrderAmountService
{
    private $rulesEntity;
    private $categoriesEntity;
    private $money;
    private $settings;
    private $variantsEntity;
    private $productsEntity;
    private $currenciesEntity;

    private $rulesCache;
    private $warningTemplate;
    private $currencySign;

    public function __construct(
        EntityFactory $entityFactory,
        Money $money,
        Settings $settings
    ) {
        $this->rulesEntity      = $entityFactory->get(RulesEntity::class);
        $this->categoriesEntity = $entityFactory->get(CategoriesEntity::class);
        $this->variantsEntity   = $entityFactory->get(VariantsEntity::class);
        $this->productsEntity   = $entityFactory->get(ProductsEntity::class);
        $this->money            = $money;
        $this->settings         = $settings;
        $this->currenciesEntity = $entityFactory->get(CurrenciesEntity::class);
    }

    public function getRules(): array
    {
        if ($this->rulesCache !== null) {
            return $this->rulesCache;
        }

        $rules = [];
        foreach ($this->rulesEntity->find() as $rule) {
            $category = $this->categoriesEntity->get($rule->category_id);
            if (empty($category)) {
                continue;
            }

            $rules[] = [
                'id'            => (int) $rule->id,
                'category_id'   => (int) $rule->category_id,
                'category_name' => $category->name,
                'children_ids'  => !empty($category->children) ? $category->children : [(int) $rule->category_id],
                'amount'        => (float) $rule->amount,
            ];
        }

        return $this->rulesCache = $rules;
    }

    public function getRulesForJs(): array
    {
        $rules = [];
        foreach ($this->getRules() as $rule) {
            $rules[] = [
                'category_id'   => $rule['category_id'],
                'category_name' => $rule['category_name'],
                'children_ids'  => $rule['children_ids'],
                'amount'        => $rule['amount'],
            ];
        }

        return $rules;
    }

    public function getWarningTemplate(): string
    {
        if ($this->warningTemplate !== null) {
            return $this->warningTemplate;
        }

        $template = $this->getCustomWarningTemplate();
        if ($template !== '') {
            return $this->warningTemplate = $template;
        }

        $translator = ServiceLocator::getInstance()->getService(\Okay\Core\FrontTranslations::class);
        return $this->warningTemplate = (string) $translator->getTranslation('okay_cms__minimal_order_amount__default_warning');
    }

    public function getCartState(Cart $cart): array
    {
        $state = [
            'violations'   => [],
            'blocked'      => false,
            'hash'         => null,
            'template'     => $this->getWarningTemplate(),
        ];

        $rules = $this->getRules();
        if (empty($rules) || $cart->isEmpty) {
            return $state;
        }

        $lineTotals = [];
        $total = 0.0;
        foreach ($cart->purchases as $purchase) {
            $lineTotals[$purchase->variant->id] = max(0, (float) $purchase->meta->total_price);
            $total += $lineTotals[$purchase->variant->id];
        }

        if ($total <= 0) {
            return $state;
        }

        $cartDiscount = max(0, $total - (float) $cart->total_price);
        foreach ($cart->purchases as $purchase) {
            $share = $total > 0 ? $lineTotals[$purchase->variant->id] / $total : 0;
            if ($cartDiscount > 0 && $share > 0) {
                $lineTotals[$purchase->variant->id] = max(0, $lineTotals[$purchase->variant->id] - $cartDiscount * $share);
            }
        }

        foreach ($rules as $rule) {
            $sum = 0.0;
            foreach ($cart->purchases as $purchase) {
                if (in_array($purchase->product->main_category_id, $rule['children_ids'], true)) {
                    $sum += $lineTotals[$purchase->variant->id];
                }
            }

            if ($sum + 0.001 < $rule['amount']) {
                $missing = max(0, $rule['amount'] - $sum);
                $state['violations'][] = [
                    'category_id'   => $rule['category_id'],
                    'category_name' => $rule['category_name'],
                    'required'      => $rule['amount'],
                    'current'       => $sum,
                    'missing'       => $missing,
                    'message'       => $this->buildMessage($rule['category_name'], $rule['amount'], $sum, $missing),
                ];
            }
        }

        if (!empty($state['violations'])) {
            $state['blocked'] = true;
            $state['hash'] = md5(json_encode($state['violations']));
        }

        return $state;
    }

    public function getVariantViolation($variantId, int $amount = 1): ?array
    {
        $variant = $this->variantsEntity->get((int) $variantId);
        if (empty($variant)) {
            return null;
        }
        $product = $this->productsEntity->get($variant->product_id);
        if (empty($product)) {
            return null;
        }

        $rules = $this->getRules();
        if (empty($rules)) {
            return null;
        }

        $price = max(0, (float) $variant->price) * max(1, $amount);
        foreach ($rules as $rule) {
            if (in_array($product->main_category_id, $rule['children_ids'], true)) {
                if ($price + 0.001 < $rule['amount']) {
                    $missing = max(0, $rule['amount'] - $price);
                    return [
                        'category_name' => $rule['category_name'],
                        'required'      => $rule['amount'],
                        'current'       => $price,
                        'missing'       => $missing,
                        'message'       => $this->buildMessage($rule['category_name'], $rule['amount'], $price, $missing),
                    ];
                }
                break;
            }
        }

        return null;
    }

    private function buildMessage(string $categoryName, float $required, float $current, float $missing): string
    {
        $template = $this->getWarningTemplate();
        return strtr($template, [
            '%category%' => $categoryName,
            '%amount%'   => $this->formatCurrency($required),
            '%current%'  => $this->formatCurrency($current),
            '%missing%'  => $this->formatCurrency($missing),
        ]);
    }

    private function getCustomWarningTemplate(): string
    {
        $value = $this->settings->get('okaycms__minimal_order_amount__warning_text');
        if (is_array($value)) {
            $languages = ServiceLocator::getInstance()->getService(Languages::class);
            $langId = $languages->getLangId();
            if (!empty($value[$langId])) {
                return trim((string) $value[$langId]);
            }

            $filtered = array_filter($value, static function ($text) {
                return trim((string) $text) !== '';
            });

            if (!empty($filtered)) {
                return trim((string) reset($filtered));
            }

            return '';
        }

        return trim((string) $value);
    }

    private function formatCurrency(float $amount): string
    {
        $formatted = $this->money->convert($amount);
        $sign = $this->getCurrencySign();

        return trim($formatted . ($sign !== '' ? ' ' . $sign : ''));
    }

    private function getCurrencySign(): string
    {
        if ($this->currencySign !== null) {
            return $this->currencySign;
        }

        $currency = null;
        if (!empty($_SESSION['currency_id'])) {
            $currency = $this->currenciesEntity->get((int) $_SESSION['currency_id']);
        }

        if (empty($currency)) {
            $currency = $this->currenciesEntity->getMainCurrency();
        }

        if (!empty($currency) && !empty($currency->sign)) {
            $this->currencySign = $currency->sign;
        } else {
            $this->currencySign = '';
        }

        return $this->currencySign;
    }
}
