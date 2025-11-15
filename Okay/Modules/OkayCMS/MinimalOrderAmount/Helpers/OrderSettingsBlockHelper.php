<?php

namespace Okay\Modules\OkayCMS\MinimalOrderAmount\Helpers;

use Okay\Core\Design;
use Okay\Core\EntityFactory;
use Okay\Core\Languages;
use Okay\Core\Settings;
use Okay\Modules\OkayCMS\MinimalOrderAmount\Entities\RulesEntity;
use Okay\Entities\CategoriesEntity;

class OrderSettingsBlockHelper
{
    private $design;
    private $rulesEntity;
    private $categoriesEntity;
    private $settings;
    private $languages;

    public function __construct(
        Design $design,
        EntityFactory $entityFactory,
        Settings $settings,
        Languages $languages
    ) {
        $this->design = $design;
        $this->rulesEntity = $entityFactory->get(RulesEntity::class);
        $this->categoriesEntity = $entityFactory->get(CategoriesEntity::class);
        $this->settings = $settings;
        $this->languages = $languages;
    }

    public function assign(): void
    {
        $this->design->assign('okaycms_min_order_rules', $this->getRules());
        $this->design->assign('okaycms_min_order_categories', $this->getCategoriesOptions());
        $this->design->assign('okaycms_min_order_warning_texts', $this->getWarningTexts());
        $this->design->assign('okaycms_min_order_languages', $this->languages->getAllLanguages());
    }

    private function getRules(): array
    {
        $rules = [];
        foreach ($this->rulesEntity->find() as $rule) {
            $rules[] = [
                'id' => (int) $rule->id,
                'category_id' => (int) $rule->category_id,
                'amount' => (float) $rule->amount,
            ];
        }
        return $rules;
    }

    private function getCategoriesOptions(): array
    {
        $tree = $this->categoriesEntity->getCategoriesTree();
        $options = [];
        $this->collectCategories($tree, 0, $options);
        return $options;
    }

    private function collectCategories($categories, int $level, array &$options): void
    {
        foreach ($categories as $category) {
            $options[] = [
                'id' => (int) $category->id,
                'name' => str_repeat('— ', $level) . $category->name,
            ];
            if (!empty($category->subcategories)) {
                $this->collectCategories($category->subcategories, $level + 1, $options);
            }
        }
    }

    private function getWarningTexts(): array
    {
        $stored = $this->settings->get('okaycms__minimal_order_amount__warning_text');
        if (is_array($stored)) {
            return $stored;
        }

        $default = trim((string) $stored);
        $texts = [];
        foreach ($this->languages->getAllLanguages() as $language) {
            $texts[$language->id] = $default;
        }

        return $texts;
    }
}
