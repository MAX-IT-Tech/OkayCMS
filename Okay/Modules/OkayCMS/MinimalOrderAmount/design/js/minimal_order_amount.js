(function (window, $) {
    'use strict';

    const module = {
        state: null,
        rules: [],
        template: '',
        fastOrderError: '',
        $container: null,

        init() {
            if (typeof window.okay === 'undefined') {
                window.okay = {};
            }

            this.rules = window.okay.min_order_rules || [];
            this.template = window.okay.min_order_warning_template || '';
            this.fastOrderError = window.okay.min_order_fast_order_error || '';
            this.state = window.okay.min_order_initial_state || null;
            this.$container = $('.fn_min_order_container');
            this.applyCartState(this.state);
            this.bindAjaxListeners();
        },

        applyCartState(state, warningHtml) {
            this.state = state || null;

            if (typeof warningHtml !== 'undefined') {
                this.$container = $('.fn_min_order_container');
                if (this.$container.length) {
                    this.$container.html(warningHtml || '');
                }
            } else if (!this.$container || !this.$container.length) {
                this.$container = $('.fn_min_order_container');
            }

            const blocked = !!(this.state && this.state.blocked);
            this.toggleCheckout(blocked);
            this.toggleCartWarning(blocked);
        },

        toggleCheckout(blocked) {
            $('.fn_min_order_checkout').prop('disabled', blocked);
        },

        toggleCartWarning(blocked) {
            if (!this.$container || !this.$container.length) {
                return;
            }
            const $warning = this.$container.find('.fn_min_order_warning');
            if (!$warning.length) {
                return;
            }
            if (blocked) {
                $warning.show();
            } else {
                $warning.hide();
            }
        },

        updateFastOrderState($form) {
            if (!$form || !$form.length) {
                return;
            }
            const categoryId = parseInt($form.data('categoryId'), 10);
            if (!categoryId) {
                this.hideFastOrderWarning($form);
                return;
            }
            const rule = this.findRule(categoryId);
            if (!rule) {
                this.hideFastOrderWarning($form);
                return;
            }
            const price = parseFloat($form.data('variantPrice'));
            const amount = parseFloat($form.data('variantAmount')) || 1;
            if (!price || price <= 0) {
                this.hideFastOrderWarning($form);
                return;
            }
            const current = price * Math.max(1, amount);
            if (current + 0.001 >= rule.amount) {
                this.hideFastOrderWarning($form);
                return;
            }
            const missing = rule.amount - current;
            const message = this.buildMessage(rule.category_name, rule.amount, current, missing);
            if (!message) {
                this.hideFastOrderWarning($form);
                return;
            }
            this.showFastOrderWarning($form, message);
        },

        showFastOrderWarning($form, message) {
            const $warning = $form.find('.fn_fast_order_min_warning');
            $warning.html(message).show();
            $form.find('.fn_fast_order_submit').prop('disabled', true);
        },

        hideFastOrderWarning($form) {
            const $warning = $form.find('.fn_fast_order_min_warning');
            $warning.hide().empty();
            $form.find('.fn_fast_order_submit').prop('disabled', false);
        },

        findRule(categoryId) {
            if (!Array.isArray(this.rules)) {
                return null;
            }
            for (let i = 0; i < this.rules.length; i += 1) {
                const rule = this.rules[i];
                if (!Array.isArray(rule.children_ids)) {
                    continue;
                }
                if (rule.children_ids.includes(categoryId)) {
                    return rule;
                }
            }
            return null;
        },

        buildMessage(category, required, current, missing) {
            const template = this.template || this.fastOrderError;
            if (!template) {
                return '';
            }
            const replacements = {
                '%category%': category,
                '%amount%': this.formatMoney(required),
                '%current%': this.formatMoney(current),
                '%missing%': this.formatMoney(missing),
            };
            return template.replace(/%category%|%amount%|%current%|%missing%/g, (match) => {
                return Object.prototype.hasOwnProperty.call(replacements, match) ? replacements[match] : match;
            });
        },

        formatMoney(value) {
            if (typeof window.okay !== 'undefined' && typeof window.okay.convert === 'function') {
                return window.okay.convert(value, null, true, true);
            }
            return value;
        },

        bindAjaxListeners() {
            $(document).ajaxSuccess((event, xhr) => {
                const data = this.extractResponseJson(xhr);
                if (data && Object.prototype.hasOwnProperty.call(data, 'minimum_order_state')) {
                    this.applyCartState(data.minimum_order_state, data.minimum_order_warning);
                }
            });
        },

        extractResponseJson(xhr) {
            if (!xhr) {
                return null;
            }
            if (xhr.responseJSON && typeof xhr.responseJSON === 'object') {
                return xhr.responseJSON;
            }
            let contentType = '';
            if (typeof xhr.getResponseHeader === 'function') {
                contentType = xhr.getResponseHeader('Content-Type') || '';
            }
            const isJson = contentType.indexOf('application/json') !== -1;
            let responseText = xhr.responseText || '';
            if (!isJson && (!responseText || (responseText.trim()[0] !== '{' && responseText.trim()[0] !== '['))) {
                return null;
            }
            if (!responseText) {
                return null;
            }
            try {
                return JSON.parse(responseText);
            } catch (e) {
                return null;
            }
        },
    };

    $(function () {
        module.init();
    });

    window.okayMinOrder = module;
})(window, jQuery);
