<?php

if (!defined('ABSPATH')) {
    exit;
}

class GoStickyVideo_Frontend_Render
{
    private array $config = [];

    public function __construct()
    {
        add_action('wp', [$this, 'maybe_prepare']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('wp_footer', [$this, 'render_player']);

        if (class_exists('WooCommerce')) {
            add_action('woocommerce_before_single_product_summary', [$this, 'render_wc_gallery_button'], 5);
            add_action('woocommerce_product_thumbnails', [$this, 'render_wc_gallery_button'], 5);
        }
    }

    public function maybe_prepare(): void
    {
        if (!is_singular()) {
            return;
        }

        $post_id = get_queried_object_id();
        if (!$post_id) {
            return;
        }

        $type = (string) get_post_meta($post_id, GO_STICKY_VIDEO_META_TYPE, true);
        $mp4_id = (int) get_post_meta($post_id, GO_STICKY_VIDEO_META_MP4_ID, true);
        $url = (string) get_post_meta($post_id, GO_STICKY_VIDEO_META_URL, true);
        $autoplay = (bool) get_post_meta($post_id, GO_STICKY_VIDEO_META_AUTOPLAY, true);
        $fit = (string) get_post_meta($post_id, GO_STICKY_VIDEO_META_FIT, true);
        $player_ui = (string) get_post_meta($post_id, GO_STICKY_VIDEO_META_PLAYER_UI, true);
        $side = (string) get_post_meta($post_id, GO_STICKY_VIDEO_META_SIDE, true);
        $muted_default = (bool) get_post_meta($post_id, GO_STICKY_VIDEO_META_MUTED_DEFAULT, true);

        if (!in_array($type, ['mp4', 'youtube', 'vimeo'], true)) {
            return;
        }

        $source = '';
        $aspect_ratio = '';
        if ($type === 'mp4') {
            if ($mp4_id > 0) {
                $source = wp_get_attachment_url($mp4_id) ?: '';
                $meta = wp_get_attachment_metadata($mp4_id);
                if (!empty($meta['width']) && !empty($meta['height'])) {
                    $aspect_ratio = ((float) $meta['height'] / (float) $meta['width']) * 100;
                }
            }
        } else {
            $source = $url;
        }

        if ($source === '') {
            return;
        }

        if (!in_array($fit, ['contain', 'cover'], true)) {
            $fit = 'contain';
        }

        if (!in_array($player_ui, ['simplified', 'native'], true)) {
            $player_ui = 'simplified';
        }

        if (!in_array($side, ['left', 'right'], true)) {
            $side = 'right';
        }

        $this->config = [
            'postId' => $post_id,
            'type' => $type,
            'source' => $source,
            'autoplay' => $autoplay,
            'fit' => $fit,
            'playerUi' => $player_ui,
            'side' => $side,
            'mutedDefault' => $muted_default,
            'aspectRatio' => $aspect_ratio,
            'isProduct' => function_exists('is_product') ? is_product() : false,
        ];
    }

    public function enqueue_assets(): void
    {
        if (empty($this->config)) {
            return;
        }

        wp_enqueue_style(
            'go-sticky-video',
            GO_STICKY_VIDEO_URL . 'assets/css/sticky-video.css',
            [],
            GO_STICKY_VIDEO_VERSION
        );

        wp_enqueue_script(
            'go-sticky-video',
            GO_STICKY_VIDEO_URL . 'assets/js/sticky-video.js',
            [],
            GO_STICKY_VIDEO_VERSION,
            true
        );

        wp_localize_script('go-sticky-video', 'GoStickyVideoConfig', $this->config);
    }

    public function render_player(): void
    {
        if (empty($this->config)) {
            return;
        }
        ?>
        <div class="gsv-sticky-wrapper" data-side="<?php echo esc_attr($this->config['side']); ?>">
            <div class="gsv-sticky-player" data-fit="<?php echo esc_attr($this->config['fit']); ?>" data-ui="<?php echo esc_attr($this->config['playerUi']); ?>">
                <div class="gsv-video-surface" aria-live="polite"></div>
                <div class="gsv-controls">
                    <button type="button" class="gsv-btn gsv-btn-expand" aria-label="<?php esc_attr_e('Expand video', 'go-sticky-video'); ?>">
                        ⤢
                    </button>
                    <button type="button" class="gsv-btn gsv-btn-hide" aria-label="<?php esc_attr_e('Hide video', 'go-sticky-video'); ?>">
                        ⇲
                    </button>
                    <button type="button" class="gsv-btn gsv-btn-side" aria-label="<?php esc_attr_e('Switch side', 'go-sticky-video'); ?>">
                        ⇄
                    </button>
                    <button type="button" class="gsv-btn gsv-btn-mute" aria-label="<?php esc_attr_e('Mute or unmute', 'go-sticky-video'); ?>">
                        🔊
                    </button>
                </div>
                <button type="button" class="gsv-handle" aria-label="<?php esc_attr_e('Show video', 'go-sticky-video'); ?>">
                    ◀
                </button>
            </div>
        </div>

        <div class="gsv-modal" aria-hidden="true">
            <div class="gsv-modal-overlay"></div>
            <div class="gsv-modal-content" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e('Video modal', 'go-sticky-video'); ?>">
                <button type="button" class="gsv-modal-close" aria-label="<?php esc_attr_e('Close modal', 'go-sticky-video'); ?>">×</button>
                <div class="gsv-modal-player"></div>
            </div>
        </div>
        <?php
    }

    public function render_wc_gallery_button(): void
    {
        if (empty($this->config) || !$this->config['isProduct']) {
            return;
        }

        echo '<button type="button" class="gsv-gallery-play" aria-label="' . esc_attr__('Play product video', 'go-sticky-video') . '">▶</button>';
    }
}
