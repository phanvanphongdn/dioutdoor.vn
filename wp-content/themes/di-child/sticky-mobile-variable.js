(function($) {
    var modalSelector = '.di-mobile-variation-modal';
    var openButtonSelector = '.di-mobile-sticky-action';
    var closeButtonSelector = '[data-di-mobile-variation-close]';
    var fullscreenButtonSelector = '.di-mobile-variation-modal__image-fullscreen, .di-mobile-variation-modal__image img';
    var formSelector = '.summary-inner form.variations_form.cart, .wd-single-add-cart form.variations_form.cart, .entry-summary form.variations_form.cart';
    var placeholder = null;
    var variationEventsBound = false;
    var lockedScrollTop = 0;
    var $activeSimpleTrigger = $();
    var $activeModalTrigger = $();

    function isMobileViewport() {
        return window.matchMedia('(max-width: 768px)').matches;
    }

    function getModal() {
        return $(modalSelector).first();
    }

    function getFormSlot() {
        return getModal().find('.di-mobile-variation-modal__form-slot');
    }

    function getModalSummary() {
        return getModal().find('.di-mobile-variation-modal__summary');
    }

    function getStickySimpleForm() {
        return $('.wd-sticky-btn-cart.wd-product-type-simple form.cart').first();
    }

    function getMainGalleryTrigger() {
        return $('.woocommerce-product-gallery__image a, .wd-main-gallery a, .product-images a').filter(':visible').first();
    }

    function getSourceForm() {
        return $(formSelector).filter(function() {
            return !$(this).closest(modalSelector).length;
        }).first();
    }

    function ensurePlaceholder($form) {
        if (placeholder && placeholder.length) {
            return placeholder;
        }

        placeholder = $('<div class="di-mobile-variation-modal__placeholder" hidden></div>');
        $form.before(placeholder);

        return placeholder;
    }

    function resetModalSummary() {
        var $summary = getModalSummary();
        var $image = $summary.find('.di-mobile-variation-modal__image');
        var $price = $summary.find('.di-mobile-variation-modal__price');
        var $availability = $summary.find('.di-mobile-variation-modal__availability');

 

        if ($price.length) {
            $price.html($price.attr('data-default-price') || '');
        }

        if ($availability.length) {
            $availability.html($availability.attr('data-default-availability') || '');
        }
    }

    function updateModalSummary(variation) {
        var $summary = getModalSummary();
        var $image = $summary.find('.di-mobile-variation-modal__image');
        var $price = $summary.find('.di-mobile-variation-modal__price');
        var $availability = $summary.find('.di-mobile-variation-modal__availability');
        var imageHtml = '';

        if (!$summary.length || !variation) {
            resetModalSummary();
            return;
        }

        if (variation.image && (variation.image.gallery_thumbnail_src || variation.image.thumb_src || variation.image.src)) {
            imageHtml = '<img src="' + ( variation.image.thumb_src || variation.image.src) + '" alt="' + (variation.image.alt || '') + '">';
        }

        if ($image.length && imageHtml) {
            $image.html(imageHtml);
            $image.attr('data-image-full', variation.image.full_src || variation.image.src || '');
            $image.append('<button type="button" class="di-mobile-variation-modal__image-fullscreen" aria-label="Xem toàn màn hình"></button>');
        }

        if ($price.length && variation.price_html) {
            $price.html(variation.price_html);
        }

        if ($availability.length) {
            $availability.html(variation.availability_html || '');
        }
    }

    function decorateForm($form) {
        var $variationsButton = $form.find('.single_variation_wrap .variations_button').first();
        var $quantity = $variationsButton.children('.quantity').first();

        if ($quantity.length && !$variationsButton.children('.di-mobile-qty-row').length) {
            var $qtyRow = $('<div class="di-mobile-qty-row"><span class="di-mobile-qty-label">Số lượng</span></div>');

            $quantity.before($qtyRow);
            $qtyRow.append($quantity);
        }
    }

    function setButtonLoading($button, isLoading) {
        if (!$button || !$button.length) {
            return;
        }

        $button.toggleClass('loading', isLoading).attr('aria-busy', isLoading ? 'true' : 'false');
    }

    function clearLoadingState() {
        setButtonLoading($activeSimpleTrigger, false);
        setButtonLoading($activeModalTrigger, false);

        $activeSimpleTrigger = $();
        $activeModalTrigger = $();
    }

    function triggerSimpleStickyAction(action) {
        var $form = getStickySimpleForm();
        var $qty = $form.find('.qty').first();
        var $targetButton;

        if (!$form.length) {
            return;
        }

        if ($qty.length) {
            $qty.val(1).trigger('change');
        }

        $targetButton = action === 'buy-now' ? $form.find('.wd-buy-now-btn').first() : $form.find('.single_add_to_cart_button').first();

        if ($targetButton.length) {
            $targetButton.trigger('click');
        }
    }

    function bindVariationEvents($form) {
        if (!$form.length || variationEventsBound) {
            return;
        }

        $form.on('found_variation.diMobileSticky', function(event, variation) {
            updateModalSummary(variation);
        });

        $form.on('reset_data.diMobileSticky hide_variation.diMobileSticky', function() {
            resetModalSummary();
        });

        variationEventsBound = true;
    }

    function highlightPreferredAction($form, action) {
        var $buttons = $form.find('.single_add_to_cart_button, .wd-buy-now-btn');

        $buttons.removeClass('di-mobile-preferred-action');

        if (action === 'buy-now') {
            $form.find('.wd-buy-now-btn').first().addClass('di-mobile-preferred-action');
        } else {
            $form.find('.single_add_to_cart_button').first().addClass('di-mobile-preferred-action');
        }
    }

    function restoreForm() {
        var $slot = getFormSlot();
        var $form = $slot.children('form.variations_form.cart').first();

        if ($form.length && placeholder && placeholder.length) {
            placeholder.before($form);
            placeholder.remove();
        }

        placeholder = null;
        resetModalSummary();
    }

    function lockBodyScroll() {
        lockedScrollTop = $(window).scrollTop();

        $(document.body).css({
            position: 'fixed',
            top: -lockedScrollTop + 'px',
            left: '0',
            right: '0',
            width: '100%'
        });
    }

    function unlockBodyScroll() {
        $(document.body).css({
            position: '',
            top: '',
            left: '',
            right: '',
            width: ''
        });

        $(window).scrollTop(lockedScrollTop);
    }

    function closeModal() {
        var $modal = getModal();

        if (!$modal.length || !$modal.hasClass('is-open')) {
            return;
        }

        $modal.removeClass('is-open').attr('hidden', 'hidden');
        $(document.body).removeClass('di-mobile-variation-modal-open');
        unlockBodyScroll();
        restoreForm();
    }

    function openModal(action) {
        var $modal = getModal();
        var $slot = getFormSlot();
        var $form = getSourceForm();

        if (!isMobileViewport() || !$modal.length || !$slot.length || !$form.length) {
            return;
        }

        ensurePlaceholder($form);
        bindVariationEvents($form);
        decorateForm($form);
        $slot.append($form);
        highlightPreferredAction($form, action);
        resetModalSummary();

        $modal.removeAttr('hidden').addClass('is-open');
        $(document.body).addClass('di-mobile-variation-modal-open');
        lockBodyScroll();

        window.setTimeout(function() {
            var $focusTarget = $form.find('.wd-swatch, select, .qty').filter(':visible').first();

            if ($focusTarget.length) {
                $focusTarget.trigger('focus');
            }
        }, 50);
    }

    $(document).on('click', openButtonSelector, function(event) {
        var action = $(this).data('action') || 'add-to-cart';
        var productType = $(this).data('product-type') || '';
        var $trigger = $(this);

        event.preventDefault();

        if (productType === 'simple') {
            $activeSimpleTrigger = $trigger;
            setButtonLoading($activeSimpleTrigger, true);
            triggerSimpleStickyAction(action);
            return;
        }

        openModal(action);
    });

    $(document).on('click', closeButtonSelector, function(event) {
        event.preventDefault();
        closeModal();
    });

    $(document).on('click', modalSelector + ' .single_add_to_cart_button, ' + modalSelector + ' .wd-buy-now-btn', function() {
        var $form = $(this).closest('form.variations_form.cart');
        var variationId = parseInt($form.find('input[name="variation_id"]').val(), 10) || 0;

        if (!variationId) {
            clearLoadingState();
            return;
        }

        $activeModalTrigger = $(this);
        setButtonLoading($activeModalTrigger, true);
    });

    $(document).on('keydown', function(event) {
        if (event.key === 'Escape') {
            closeModal();
        }
    });

    $(document).on('click', fullscreenButtonSelector, function(event) {
        var imageUrl = $(this).closest('.di-mobile-variation-modal__image').attr('data-image-full') || '';

        event.preventDefault();
        event.stopPropagation();

        if (!imageUrl || !$.magnificPopup) {
            return;
        }

        $.magnificPopup.open({
            items: [
                {
                    src: imageUrl
                }
            ],
            type: 'image',
            closeMarkup: typeof woodmart_settings !== 'undefined' ? woodmart_settings.close_markup : '<button title="%title%" type="button" class="mfp-close">&#215;</button>',
            tLoading: typeof woodmart_settings !== 'undefined' ? woodmart_settings.loading : '',
            fixedContentPos: true,
            mainClass: 'wd-popup-gallery-wrap di-mobile-image-popup',
            closeOnContentClick: false,
            closeOnBgClick: true,
            image: {
                verticalFit: true,
                markup: '<div class="mfp-figure wd-popup wd-popup-gallery">' +
                    (typeof woodmart_settings !== 'undefined' ? woodmart_settings.close_markup : '<button title="%title%" type="button" class="mfp-close">&#215;</button>') +
                    '<figure>' +
                    '<div class="mfp-img"></div>' +
                    '<figcaption>' +
                    '<div class="mfp-bottom-bar">' +
                    '<div class="mfp-title"></div>' +
                    '<div class="mfp-counter"></div>' +
                    '</div>' +
                    '</figcaption>' +
                    '</figure>' +
                    '</div>'
            },
            gallery: {
                enabled: false
            }
        });
    });

    $(document).on('added_to_cart', function() {
        if (getModal().hasClass('is-open')) {
            closeModal();
        }

        clearLoadingState();
    });

    $(window).on('beforeunload', function() {
        if ($activeSimpleTrigger.length || $activeModalTrigger.length) {
            setButtonLoading($activeSimpleTrigger, true);
            setButtonLoading($activeModalTrigger, true);
        }
    });

    $(window).on('resize orientationchange', function() {
        if (!isMobileViewport()) {
            closeModal();
            $('.wd-sticky-btn').removeClass('wd-sticky-btn-shown');
        } else {
            $('.wd-sticky-btn').addClass('wd-sticky-btn-shown');
        }
    });

    $(document).ready(function() {
        $('.di-sticky-out-of-stock').closest('.wd-sticky-btn-cart').addClass('di-sticky-out-of-stock-state');
        $('.di-single-out-of-stock').closest('.summary, .summary-inner, .entry-summary, .wd-single-add-cart').addClass('di-single-out-of-stock-state');

        if (isMobileViewport()) {
            $('.wd-sticky-btn').addClass('wd-sticky-btn-shown');
        }
    });
})(jQuery);
