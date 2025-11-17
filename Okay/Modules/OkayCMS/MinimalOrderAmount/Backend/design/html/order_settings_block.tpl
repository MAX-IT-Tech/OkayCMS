<div class="boxed fn_toggle_wrap">
    <div class="heading_box">
        {$btr->okay_cms__minimal_order_amount__title|escape}
        <div class="box_btn_heading ml-1">
            <button type="button" class="btn btn_mini btn-secondary btn_openSans fn_add_min_order_rule">
                {include file='svg_icon.tpl' svgId='plus'}
                <span>{$btr->okay_cms__minimal_order_amount__add_rule|escape}</span>
            </button>
        </div>
        <div class="toggle_arrow_wrap fn_toggle_card text-primary">
            <a class="btn-minimize" href="javascript:;" ><i class="icon-arrow-down"></i></a>
        </div>
    </div>
    <div class="toggle_body_wrap on fn_card">
        <form method="post" action="{$rootUrl}/backend/index.php?controller=OkayCMS_MinimalOrderAmount_SettingsAdmin">
            <input type="hidden" name="session_id" value="{$smarty.session.id}">
            <div class="table_wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>{$btr->okay_cms__minimal_order_amount__category|escape}</th>
                            <th>{$btr->okay_cms__minimal_order_amount__sum|escape}</th>
                            <th>{$btr->okay_cms__minimal_order_amount__actions|escape}</th>
                        </tr>
                    </thead>
                    <tbody class="fn_min_order_rules">
                        {if $okaycms_min_order_rules}
                            {foreach $okaycms_min_order_rules as $rule}
                                <tr class="fn_min_order_rule">
                                    <td>
                                        <input type="hidden" name="minimal_order_rules[{$rule@iteration}][id]" value="{$rule.id|escape}">
                                        <select name="minimal_order_rules[{$rule@iteration}][category_id]" class="selectpicker form-control" data-live-search="true">
                                            <option value="">{$btr->general_select|escape}</option>
                                            {foreach $okaycms_min_order_categories as $category}
                                                <option value="{$category.id|escape}" {if $category.id == $rule.category_id}selected{/if}>{$category.name|escape}</option>
                                            {/foreach}
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control" name="minimal_order_rules[{$rule@iteration}][amount]" value="{$rule.amount|floatval}" placeholder="{$btr->okay_cms__minimal_order_amount__sum|escape}">
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-danger btn-sm fn_remove_min_order_rule" data-hint="{$btr->okay_cms__minimal_order_amount__delete_rule|escape}">
                                            {include file='svg_icon.tpl' svgId='trash'}
                                        </button>
                                    </td>
                                </tr>
                            {/foreach}
                        {/if}
                        <tr class="fn_min_order_rule fn_min_order_rule_template" style="display: none;">
                            <td>
                                <input type="hidden" name="minimal_order_rules[__INDEX__][id]" value="">
                                <select name="minimal_order_rules[__INDEX__][category_id]" class="selectpicker form-control" data-live-search="true">
                                    <option value="">{$btr->general_select|escape}</option>
                                    {foreach $okaycms_min_order_categories as $category}
                                        <option value="{$category.id|escape}">{$category.name|escape}</option>
                                    {/foreach}
                                </select>
                            </td>
                            <td>
                                <input type="text" class="form-control" name="minimal_order_rules[__INDEX__][amount]" value="">
                            </td>
                            <td>
                                <button type="button" class="btn btn-danger btn-sm fn_remove_min_order_rule" data-hint="{$btr->okay_cms__minimal_order_amount__delete_rule|escape}">
                                    {include file='svg_icon.tpl' svgId='trash'}
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="form-group mt-3">
                <label>{$btr->okay_cms__minimal_order_amount__warning_label|escape}</label>
                {foreach $okaycms_min_order_languages as $language}
                    <div class="mb-3">
                        <div class="text-muted small font-weight-bold">{$language->name|escape}</div>
                        <textarea name="minimal_order_warning_text[{$language->id}]" class="form-control" rows="3">{$okaycms_min_order_warning_texts[$language->id]|escape}</textarea>
                    </div>
                {/foreach}
                <div class="mt-1 text-muted small">{$btr->okay_cms__minimal_order_amount__warning_hint|escape}</div>
            </div>

            <button type="submit" class="btn btn_blue">
                {include file='svg_icon.tpl' svgId='checked'}
                <span>{$btr->okay_cms__minimal_order_amount__save|escape}</span>
            </button>
        </form>
    </div>
</div>

{literal}
<script>
    (function($) {
        $(document).on('click', '.fn_add_min_order_rule', function() {
            const $table = $('.fn_min_order_rules');
            const $template = $table.find('.fn_min_order_rule_template').first();
            const index = Date.now();
            const $clone = $template.clone(true).removeClass('fn_min_order_rule_template').show();
            $clone.html($clone.html().replace(/__INDEX__/g, index));
            $table.append($clone);
            $clone.find('.selectpicker').selectpicker();
        });

        $(document).on('click', '.fn_remove_min_order_rule', function() {
            $(this).closest('.fn_min_order_rule').remove();
        });
    })(jQuery);
</script>
{/literal}
