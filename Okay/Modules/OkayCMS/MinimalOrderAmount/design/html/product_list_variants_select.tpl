<select name="variant" class="fn_variant  variant_select {if $product->variants|count == 1}hidden{else}fn_select2{/if}">
    {foreach $product->variants as $v}
        <option value="{$v->id}" data-price="{$v->price|convert}" data-price-base="{$v->price}" data-stock="{$v->stock}"{if $v->compare_price > 0}
                data-cprice="{$v->compare_price|convert}"{if $v->compare_price>$v->price && $v->price>0}
                data-discount="{round((($v->price-$v->compare_price)/$v->compare_price)*100, 2)}&nbsp;%"{/if}{/if}{if $v->sku}
                data-sku="{$v->sku|escape}"{/if}>{if $v->name}{$v->name|escape}{else}{$product->name|escape}{/if}</option>
    {/foreach}
</select>
