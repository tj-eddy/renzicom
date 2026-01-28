// public/js/distribution-form.js

$(function () {
    const $warehouseSelect = $('[data-warehouse-select]');
    const $productSelect = $('[data-product-select]');
    const $quantityInput = $('[data-quantity-input]');

    if (!$warehouseSelect.length || !$productSelect.length || !$quantityInput.length) {
        return;
    }

    let selectedWarehouseId = null;
    let selectedProductId = null;
    let availableStock = {};

    // ========================================
    // ÉTAPE 1: Sélection de l'entrepôt
    // ========================================

    $warehouseSelect.on('change', function () {
        selectedWarehouseId = $(this).val();

        if (!selectedWarehouseId) {
            $productSelect
                .html('<option value="">' + trans('distribution.select_warehouse_first') + '</option>')
                .prop('disabled', true);

            $quantityInput.val('').attr('max', '');
            return;
        }

        // Loader
        $productSelect
            .html('<option value="">' + trans('distribution.loading') + '</option>')
            .prop('disabled', true);

        $.ajax({
            url: `/${window.app_locale}/warehouse/${selectedWarehouseId}/products`,
            method: 'GET',
            dataType: 'json'
        })
            .done(function (products) {
                $productSelect.html('<option value="">' + trans('distribution.select_product') + '</option>');

                if (!products.length) {
                    $productSelect
                        .html('<option value="">' + trans('distribution.no_products') + '</option>')
                        .prop('disabled', true);

                    showNotification(trans('distribution.no_products_notification'), 'warning');
                    return;
                }

                availableStock = {};

                $.each(products, function (_, product) {
                    $('<option>', {
                        value: product.id,
                        text: product.label,
                        'data-quantity': product.quantity
                    }).appendTo($productSelect);

                    availableStock[product.id] = product.quantity;
                });

                $productSelect.prop('disabled', false);
                showNotification(trans('distribution.products_count_notification', { count: products.length }), 'success');
            })
            .fail(function () {
                $productSelect
                    .html('<option value="">' + trans('distribution.load_error') + '</option>')
                    .prop('disabled', true);

                showNotification(trans('distribution.load_error_notification'), 'danger');
            });
    });

    // ========================================
    // ÉTAPE 2: Sélection du produit
    // ========================================

    $productSelect.on('change', function () {
        selectedProductId = $(this).val();

        if (!selectedProductId) {
            $quantityInput.val('').attr('max', '');
            removeStockInfo();
            return;
        }

        const $selectedOption = $(this).find(':selected');
        const availableQuantity = parseInt($selectedOption.data('quantity') || 0);

        $quantityInput
            .attr('max', availableQuantity)
            .val('')
            .focus();

        showStockInfo(availableQuantity);
    });

    // ========================================
    // ÉTAPE 3: Validation de la quantité
    // ========================================

    $quantityInput.on('input', function () {
        if (!selectedProductId) return;

        const requestedQuantity = parseInt($(this).val() || 0);
        const availableQuantity = availableStock[selectedProductId] || 0;

        if (requestedQuantity > availableQuantity) {
            $(this)
                .addClass('is-invalid')
                .removeClass('is-valid');

            updateStockInfo(availableQuantity, requestedQuantity, false);
        } else if (requestedQuantity > 0) {
            $(this)
                .addClass('is-valid')
                .removeClass('is-invalid');

            updateStockInfo(availableQuantity, requestedQuantity, true);
        } else {
            $(this).removeClass('is-valid is-invalid');
        }
    });

    // Validation avant soumission
    const $form = $quantityInput.closest('form');

    if ($form.length) {
        $form.on('submit', function (e) {
            if (!selectedProductId) {
                e.preventDefault();
                showNotification(trans('distribution.select_product_notification'), 'danger');
                return false;
            }

            const requestedQuantity = parseInt($quantityInput.val() || 0);
            const availableQuantity = availableStock[selectedProductId] || 0;

            if (requestedQuantity > availableQuantity) {
                e.preventDefault();
                showNotification(
                    trans('distribution.insufficient_stock_notification', { available: availableQuantity, requested: requestedQuantity }),
                    'danger'
                );
                $quantityInput.focus();
                return false;
            }

            if (requestedQuantity <= 0) {
                e.preventDefault();
                showNotification(trans('distribution.quantity_greater_than_zero'), 'danger');
                $quantityInput.focus();
                return false;
            }
        });
    }

    // ========================================
    // FONCTIONS UTILITAIRES
    // ========================================

    function showStockInfo(availableQuantity) {
        removeStockInfo();

        const $info = $(`
            <div class="alert alert-info mt-2 stock-info">
                <i class="bi bi-info-circle"></i>
                <strong>${trans('distribution.available_stock_info')}</strong> ${availableQuantity} ${trans('distribution.units')}
            </div>
        `);

        $quantityInput.parent().append($info);
    }

    function updateStockInfo(available, requested, sufficient) {
        const $stockInfo = $('.stock-info');

        if (!$stockInfo.length) return;

        if (sufficient) {
            $stockInfo
                .attr('class', 'alert alert-success mt-2 stock-info')
                .html(`
                    <i class="bi bi-check-circle"></i>
                    <strong>${trans('distribution.sufficient_stock_info')}</strong> ${available} ${trans('distribution.available')}, ${requested} ${trans('distribution.requested')}
                `);
        } else {
            $stockInfo
                .attr('class', 'alert alert-danger mt-2 stock-info')
                .html(`
                    <i class="bi bi-exclamation-triangle"></i>
                    <strong>${trans('distribution.insufficient_stock_info')}</strong> ${available} ${trans('distribution.available')}, ${requested} ${trans('distribution.requested')}
                `);
        }
    }

    function removeStockInfo() {
        $('.stock-info').remove();
    }

    function showNotification(message, type = 'info') {
        const $toast = $(`
            <div class="alert alert-${type} alert-dismissible fade show position-fixed"
                 style="top:20px; right:20px; z-index:9999; min-width:300px;">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `);

        $('body').append($toast);

        setTimeout(() => {
            $toast.alert('close');
        }, 5000);
    }
});
