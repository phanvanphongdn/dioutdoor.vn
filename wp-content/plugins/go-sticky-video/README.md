# Go Sticky Video

Theme-independent sticky video player for WordPress posts, pages, and WooCommerce products. All configuration is stored per-content via a metabox.

## Usage
1. Activate the plugin.
2. Edit a post, page, or product.
3. Choose MP4/YouTube/Vimeo in the Sticky Video metabox.
4. Configure autoplay, mute, fit, UI style, and default side.
5. View the content on the front-end to see the sticky player and modal.

## Notes
- Scripts and styles only load on singular views that have video configuration enabled.
- If WooCommerce is installed, a play button appears in the product gallery and opens the same modal.
- If the theme overrides WooCommerce galleries, JS will attempt to inject the play button into `.woocommerce-product-gallery`.

## Test Checklist
- Create a post with MP4 source, verify sticky player and modal.
- Create a page with YouTube source, verify mute toggle and expand modal.
- Create a product with Vimeo source, verify gallery play button and modal.
- Verify hide/show handle pauses playback.
- Verify switch side animation and handle placement.
