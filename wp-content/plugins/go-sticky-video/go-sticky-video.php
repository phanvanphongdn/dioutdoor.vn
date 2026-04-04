<?php
/**
 * Plugin Name: Go Sticky Video
 * Description: Adds a theme-independent sticky video player with per-content configuration.
 * Version: 1.0.0
 * Author: Go Sticky Video
 * License: GPL-2.0+
 */

if (!defined('ABSPATH')) {
    exit;
}

define('GO_STICKY_VIDEO_VERSION', '1.0.0');
define('GO_STICKY_VIDEO_PATH', plugin_dir_path(__FILE__));
define('GO_STICKY_VIDEO_URL', plugin_dir_url(__FILE__));

define('GO_STICKY_VIDEO_MIN_PHP', '7.4.0');

define('GO_STICKY_VIDEO_META_ENABLED', '_gosticky_video_enabled');
define('GO_STICKY_VIDEO_META_TYPE', '_gosticky_video_type');
define('GO_STICKY_VIDEO_META_MP4_ID', '_gosticky_video_mp4_id');
define('GO_STICKY_VIDEO_META_URL', '_gosticky_video_url');
define('GO_STICKY_VIDEO_META_AUTOPLAY', '_gosticky_video_autoplay');
define('GO_STICKY_VIDEO_META_FIT', '_gosticky_video_fit');
define('GO_STICKY_VIDEO_META_PLAYER_UI', '_gosticky_video_player_ui');
define('GO_STICKY_VIDEO_META_SIDE', '_gosticky_video_side');
define('GO_STICKY_VIDEO_META_MUTED_DEFAULT', '_gosticky_video_muted_default');

register_activation_hook(__FILE__, 'go_sticky_video_activate');

function go_sticky_video_activate(): void
{
    if (version_compare(PHP_VERSION, GO_STICKY_VIDEO_MIN_PHP, '<')) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die(
            esc_html__('Go Sticky Video requires PHP 7.4 or higher.', 'go-sticky-video'),
            esc_html__('Plugin Activation Error', 'go-sticky-video'),
            ['back_link' => true]
        );
    }
}

require_once GO_STICKY_VIDEO_PATH . 'includes/class-admin-metabox.php';
require_once GO_STICKY_VIDEO_PATH . 'includes/class-frontend-render.php';

add_action('plugins_loaded', static function (): void {
    new GoStickyVideo_Admin_Metabox();
    new GoStickyVideo_Frontend_Render();
});