<?php

namespace Okay\Modules\OkayCMS\MinimalOrderAmount\Backend\Controllers;

use Okay\Admin\Controllers\IndexAdmin;
use Okay\Core\BackendPostRedirectGet;
use Okay\Core\BackendTranslations;
use Okay\Core\Languages;
use Okay\Core\Request;
use Okay\Core\Response;
use Okay\Core\Settings;
use Okay\Modules\OkayCMS\MinimalOrderAmount\Entities\RulesEntity;
use Okay\Entities\CategoriesEntity;

class SettingsAdmin extends IndexAdmin
{
    public function fetch(
        Request $request,
        Response $response,
        BackendPostRedirectGet $postRedirectGet,
        BackendTranslations $backendTranslations,
        RulesEntity $rulesEntity,
        CategoriesEntity $categoriesEntity,
        Settings $settings,
        Languages $languages
    ) {
        if (!$request->method('post')) {
            $response->redirectTo($request->getRootUrl() . '/backend/index.php?controller=OrderSettingsAdmin');
        }

        if (!$request->checkSession()) {
            $postRedirectGet->storeMessageError($backendTranslations->getTranslation('okay_cms__minimal_order_amount__session_error'));
            $postRedirectGet->redirect($request->getRootUrl() . '/backend/index.php?controller=OrderSettingsAdmin');
        }

        $rawRules = (array) $request->post('minimal_order_rules');
        $normalized = [];
        foreach ($rawRules as $rule) {
            $categoryId = isset($rule['category_id']) ? (int) $rule['category_id'] : 0;
            $amountRaw = isset($rule['amount']) ? str_replace(',', '.', trim((string) $rule['amount'])) : '';
            $amount = (float) $amountRaw;

            if ($categoryId <= 0 || $amount <= 0) {
                continue;
            }

            if (!$categoriesEntity->get($categoryId)) {
                continue;
            }

            $normalized[] = [
                'category_id' => $categoryId,
                'amount' => $amount,
            ];
        }

        $existingIds = $rulesEntity->cols(['id'])->find();
        if (!empty($existingIds)) {
            $rulesEntity->delete($existingIds);
        }

        foreach ($normalized as $ruleData) {
            $rulesEntity->add($ruleData);
        }

        $warningTextsRaw = (array) $request->post('minimal_order_warning_text');
        $warningTexts = [];
        foreach ($languages->getAllLanguages() as $language) {
            $langId = (int) $language->id;
            $value = $warningTextsRaw[$langId] ?? '';
            $warningTexts[$langId] = trim((string) $value);
        }

        $settings->set('okaycms__minimal_order_amount__warning_text', $warningTexts);

        $postRedirectGet->storeMessageSuccess($backendTranslations->getTranslation('okay_cms__minimal_order_amount__settings_saved'));
        $postRedirectGet->redirect($request->getRootUrl() . '/backend/index.php?controller=OrderSettingsAdmin');
    }
}
