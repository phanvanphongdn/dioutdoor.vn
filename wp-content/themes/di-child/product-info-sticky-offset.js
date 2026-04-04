(function($) {
    function hasVisibleScrollUpHeader() {
        var hasScrollUpHeader = false;

        $('.whb-header, .whb-clone').each(function() {
            var $header = $(this);

            if (
                $header.is(':visible') &&
                $header.hasClass('whb-sticked') &&
                $header.hasClass('whb-scroll-up') &&
                !$header.hasClass('whb-scroll-down')
            ) {
                hasScrollUpHeader = true;
                return false;
            }
        });

        return hasScrollUpHeader;
    }

    function syncProductsInfoClass() {
        var $productsInfo = $('#products-info');

        if (!$productsInfo.length) {
            return;
        }

        $productsInfo.toggleClass('di-header-scroll-up', hasVisibleScrollUpHeader());
    }

    function bindStateUpdater() {
        syncProductsInfoClass();

        $(window).on('scroll resize load', syncProductsInfoClass);
        $(document).on('wdHeaderBuilderInited wdHeaderBuilderStickyChanged', syncProductsInfoClass);
        window.addEventListener('wdUpdatedHeader', syncProductsInfoClass);
        window.addEventListener('wdHeaderBuilderCloneCreated', syncProductsInfoClass);

        if ('MutationObserver' in window) {
            $('.whb-header, .whb-clone').each(function() {
                var headerNode = this;

                new MutationObserver(syncProductsInfoClass).observe(headerNode, {
                    attributes: true,
                    attributeFilter: ['class', 'style']
                });
            });
        }
    }

    $(bindStateUpdater);
})(jQuery);
