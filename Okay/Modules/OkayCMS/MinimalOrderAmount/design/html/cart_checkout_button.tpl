{assign var=min_order_state value=$okaycms_min_order_state|default:null}
<button class="form__button button--blick g-recaptcha fn_min_order_checkout" type="submit" name="checkout"
        {if !empty($min_order_state.blocked)}disabled{/if}
        {if $settings->captcha_type == "invisible"}data-sitekey="{$settings->public_recaptcha_invisible}" data-badge='bottomleft' data-callback="onSubmit"{/if}
        value="{$lang->cart_checkout}">
    <span data-language="cart_button">{$lang->cart_button}</span>
</button>
