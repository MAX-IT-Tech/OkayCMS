$(document).on('click', '.fn_fast_order_button', function (e) {
    e.preventDefault();

    let variant,
        variantPrice = null,
        amount = 1,
        form_obj = $(this).closest("form.fn_variants");

    $("#fast_order_product_name").html($(this).data('name'));
    if (form_obj.find('input[name=variant]:checked').length > 0) {
        variant = form_obj.find('input[name=variant]:checked').val();
    }

    if (form_obj.find('select[name=variant]').length > 0) {
        const $select = form_obj.find('select[name=variant]');
        variant = $select.val();
        variantPrice = parseFloat($select.find(':selected').data('price-base'));
    }

    if (form_obj.find('input[name=amount]').length > 0) {
        amount = parseFloat(form_obj.find('input[name=amount]').val()) || 1;
    }

    const $fastOrderForm = $("#fn_fast_order");
    $fastOrderForm.find("input[name=amount]").val(amount);
    $fastOrderForm.data('variantPrice', variantPrice);
    $fastOrderForm.data('variantAmount', amount);
    $fastOrderForm.data('categoryId', parseInt($(this).data('category'), 10) || null);

    $("#fast_order_variant_id").val(variant);

    $.fancybox.open({
        src: '#fn_fast_order',
        type : 'inline'
    });

    if (window.okayMinOrder && typeof window.okayMinOrder.updateFastOrderState === 'function') {
        window.okayMinOrder.updateFastOrderState($fastOrderForm);
    }
});

function sendAjaxFastOrderForm() {
    
    let $form      = $("#fn_fast_order"),
        action     = $form.attr('action'),
        $errorBlock = $form.find('.fn_fast_order_errors');

    $.ajax({
        url: action,
        type: 'post',
        data: $form.serialize(),
        dataType: 'json'
    }).done(function(response) {
        if (response.hasOwnProperty('success') && response.hasOwnProperty('redirect_location')) {
            window.location = response.redirect_location;
        } else if (response.hasOwnProperty('errors')) {

            if (typeof resetFastOrderCaptcha === "function") {
                resetFastOrderCaptcha();
            }
            let errorString = '';
            for (let error in response.errors) {
                errorString += '<div>' + response.errors[error] + '</div>';
            }
            $errorBlock.html(errorString).show();
            
        }
    });
    
}


