{assign var=min_order_state value=$cart->minimum_order_amount|default:null}
{if $min_order_state === null}
    {assign var=min_order_state value=['blocked'=>false,'violations'=>[], 'hash'=>'', 'template'=>'']}
{/if}
{assign var=is_blocked value=(!empty($min_order_state.blocked))}
<div class="min-order-warning fn_min_order_warning" data-min-order-blocked="{if $is_blocked}1{else}0{/if}" data-min-order-hash="{$min_order_state.hash|default:''|escape}" {if !$is_blocked}style="display:none;"{/if}>
    <div class="min-order-warning__title">{$lang->okay_cms__minimal_order_amount__warning_title|escape}</div>
    <div class="min-order-warning__body">
        {if $min_order_state.violations}
            <ul class="min-order-warning__list">
                {foreach $min_order_state.violations as $violation}
                    <li>{$violation.message|escape}</li>
                {/foreach}
            </ul>
        {elseif $is_blocked}
            <div class="min-order-warning__message">{$min_order_state.template|escape}</div>
        {/if}
    </div>
</div>
