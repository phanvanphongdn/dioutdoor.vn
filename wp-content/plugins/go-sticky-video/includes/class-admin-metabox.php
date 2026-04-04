<?php

if (!defined('ABSPATH')) {
    exit;
}

class GoStickyVideo_Admin_Metabox
{
    public function __construct()
    {
        add_action('add_meta_boxes', [$this, 'register_metabox']);
        add_action('save_post', [$this, 'save_metabox']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
    }

    public function register_metabox(): void
    {
        $screens = ['post', 'page'];
        if (post_type_exists('product')) {
            $screens[] = 'product';
        }

        foreach ($screens as $screen) {
            add_meta_box(
                'go_sticky_video_metabox',
                esc_html__('Sticky Video', 'go-sticky-video'),
                [$this, 'render_metabox'],
                $screen,
                'normal',
                'high'
            );
        }
    }

    public function enqueue_admin_assets(string $hook): void
    {
        if (!in_array($hook, ['post.php', 'post-new.php'], true)) {
            return;
        }

        wp_enqueue_media();

        $js = "document.addEventListener('DOMContentLoaded', function () {
            const metabox = document.getElementById('go-sticky-video-metabox');
            if (!metabox) { return; }
            const tabs = metabox.querySelectorAll('.gsv-tab');
            const panes = metabox.querySelectorAll('.gsv-tab-pane');
            tabs.forEach((tab) => {
                tab.addEventListener('click', () => {
                    tabs.forEach((t) => t.classList.remove('is-active'));
                    panes.forEach((p) => p.classList.remove('is-active'));
                    tab.classList.add('is-active');
                    const target = tab.getAttribute('data-target');
                    const pane = metabox.querySelector(target);
                    if (pane) { pane.classList.add('is-active'); }
                    const input = metabox.querySelector('#gsv_video_type');
                    if (input) { input.value = tab.getAttribute('data-type'); }
                });
            });

            const selectBtn = metabox.querySelector('.gsv-select-mp4');
            const removeBtn = metabox.querySelector('.gsv-remove-mp4');
            const inputId = metabox.querySelector('#gsv_video_mp4_id');
            const label = metabox.querySelector('.gsv-mp4-label');

            if (selectBtn && inputId) {
                selectBtn.addEventListener('click', function (event) {
                    event.preventDefault();
                    const frame = wp.media({
                        title: 'Select MP4',
                        button: { text: 'Use MP4' },
                        library: { type: 'video' },
                        multiple: false,
                    });
                    frame.on('select', function () {
                        const attachment = frame.state().get('selection').first().toJSON();
                        inputId.value = attachment.id;
                        if (label) {
                            label.textContent = attachment.filename || attachment.url;
                        }
                    });
                    frame.open();
                });
            }

            if (removeBtn && inputId) {
                removeBtn.addEventListener('click', function (event) {
                    event.preventDefault();
                    inputId.value = '';
                    if (label) {
                        label.textContent = 'No file selected';
                    }
                });
            }
        });";

        wp_register_script('go-sticky-video-admin', '', [], GO_STICKY_VIDEO_VERSION, true);
        wp_enqueue_script('go-sticky-video-admin');
        wp_add_inline_script('go-sticky-video-admin', $js);

        $css = "#go-sticky-video-metabox .gsv-tabs{display:flex;gap:8px;margin-bottom:10px;}
        #go-sticky-video-metabox .gsv-tab{padding:6px 12px;border:1px solid #ccd0d4;border-radius:4px;cursor:pointer;background:#f6f7f7;}
        #go-sticky-video-metabox .gsv-tab.is-active{background:#fff;border-color:#2271b1;color:#2271b1;}
        #go-sticky-video-metabox .gsv-tab-pane{display:none;border:1px solid #ccd0d4;padding:12px;border-radius:6px;background:#fff;}
        #go-sticky-video-metabox .gsv-tab-pane.is-active{display:block;}
        #go-sticky-video-metabox .gsv-field{margin-bottom:12px;}
        #go-sticky-video-metabox .gsv-inline{display:flex;gap:10px;align-items:center;flex-wrap:wrap;}
        #go-sticky-video-metabox .gsv-small{font-size:12px;color:#646970;}";

        wp_register_style('go-sticky-video-admin', false, [], GO_STICKY_VIDEO_VERSION);
        wp_enqueue_style('go-sticky-video-admin');
        wp_add_inline_style('go-sticky-video-admin', $css);
    }

    public function render_metabox(
        WP_Post $post
    ): void {
        $type = (string) get_post_meta($post->ID, GO_STICKY_VIDEO_META_TYPE, true);
        $mp4_id = (int) get_post_meta($post->ID, GO_STICKY_VIDEO_META_MP4_ID, true);
        $url = (string) get_post_meta($post->ID, GO_STICKY_VIDEO_META_URL, true);
        $autoplay = (bool) get_post_meta($post->ID, GO_STICKY_VIDEO_META_AUTOPLAY, true);
        $fit = (string) get_post_meta($post->ID, GO_STICKY_VIDEO_META_FIT, true);
        $player_ui = (string) get_post_meta($post->ID, GO_STICKY_VIDEO_META_PLAYER_UI, true);
        $side = (string) get_post_meta($post->ID, GO_STICKY_VIDEO_META_SIDE, true);
        $muted_default = (bool) get_post_meta($post->ID, GO_STICKY_VIDEO_META_MUTED_DEFAULT, true);

        if (!in_array($type, ['mp4', 'youtube', 'vimeo'], true)) {
            $type = 'mp4';
        }

        if (!in_array($fit, ['contain', 'cover'], true)) {
            $fit = 'contain';
        }

        if (!in_array($player_ui, ['simplified', 'native'], true)) {
            $player_ui = 'simplified';
        }

        if (!in_array($side, ['left', 'right'], true)) {
            $side = '';
        }

        $mp4_label = $mp4_id ? basename(get_attached_file($mp4_id)) : esc_html__('No file selected', 'go-sticky-video');

        wp_nonce_field('go_sticky_video_save', 'go_sticky_video_nonce');
        ?>
        <div id="go-sticky-video-metabox">
            <input type="hidden" name="gsv_video_type" id="gsv_video_type" value="<?php echo esc_attr($type); ?>" />

            <div class="gsv-tabs">
                <button type="button" class="gsv-tab <?php echo $type === 'mp4' ? 'is-active' : ''; ?>" data-target="#gsv-pane-mp4" data-type="mp4">
                    <?php esc_html_e('MP4', 'go-sticky-video'); ?>
                </button>
                <button type="button" class="gsv-tab <?php echo $type === 'youtube' ? 'is-active' : ''; ?>" data-target="#gsv-pane-youtube" data-type="youtube">
                    <?php esc_html_e('YouTube', 'go-sticky-video'); ?>
                </button>
                <button type="button" class="gsv-tab <?php echo $type === 'vimeo' ? 'is-active' : ''; ?>" data-target="#gsv-pane-vimeo" data-type="vimeo">
                    <?php esc_html_e('Vimeo', 'go-sticky-video'); ?>
                </button>
            </div>

            <div id="gsv-pane-mp4" class="gsv-tab-pane <?php echo $type === 'mp4' ? 'is-active' : ''; ?>">
                <div class="gsv-field">
                    <input type="hidden" name="gsv_video_mp4_id" id="gsv_video_mp4_id" value="<?php echo esc_attr((string) $mp4_id); ?>" />
                    <div class="gsv-inline">
                        <button type="button" class="button gsv-select-mp4"><?php esc_html_e('Select MP4', 'go-sticky-video'); ?></button>
                        <button type="button" class="button gsv-remove-mp4"><?php esc_html_e('Remove', 'go-sticky-video'); ?></button>
                        <span class="gsv-mp4-label"><?php echo esc_html($mp4_label); ?></span>
                    </div>
                    <p class="gsv-small"><?php esc_html_e('Upload or choose an MP4 from the Media Library.', 'go-sticky-video'); ?></p>
                </div>
            </div>

            <div id="gsv-pane-youtube" class="gsv-tab-pane <?php echo $type === 'youtube' ? 'is-active' : ''; ?>">
                <div class="gsv-field">
                    <label>
                        <?php esc_html_e('YouTube URL', 'go-sticky-video'); ?><br />
                        <input type="url" class="widefat" name="gsv_video_url_youtube" value="<?php echo esc_attr($type === 'youtube' ? $url : ''); ?>" placeholder="https://www.youtube.com/watch?v=..." />
                    </label>
                     <p class="gsv-small"><?php esc_html_e('https://youtu.be/LXb3EKWsInQ.', 'go-sticky-video'); ?></p>
                </div>
                
            </div>

            <div id="gsv-pane-vimeo" class="gsv-tab-pane <?php echo $type === 'vimeo' ? 'is-active' : ''; ?>">
                <div class="gsv-field">
                    <label>
                        <?php esc_html_e('Vimeo URL', 'go-sticky-video'); ?><br />
                        <input type="url" class="widefat" name="gsv_video_url_vimeo" value="<?php echo esc_attr($type === 'vimeo' ? $url : ''); ?>" placeholder="https://vimeo.com/..." />
                    </label>
                     <p class="gsv-small"><?php esc_html_e('https://vimeo.com/259400046.', 'go-sticky-video'); ?></p>
                </div>
            </div>

            <div class="gsv-field gsv-inline">
                <label class="gsv-inline">
                    <input type="checkbox" name="gsv_video_autoplay" value="1" <?php checked($autoplay); ?> />
                    <span><?php esc_html_e('Autoplay', 'go-sticky-video'); ?></span>
                </label>
                <label class="gsv-inline">
                    <input type="checkbox" name="gsv_video_muted_default" value="1" <?php checked($muted_default); ?> />
                    <span><?php esc_html_e('Start muted', 'go-sticky-video'); ?></span>
                </label>
            </div>

            <div class="gsv-field gsv-inline">
                <label>
                    <?php esc_html_e('Video size mode', 'go-sticky-video'); ?><br />
                    <select name="gsv_video_fit">
                        <option value="contain" <?php selected($fit, 'contain'); ?>><?php esc_html_e('Contain', 'go-sticky-video'); ?></option>
                        <option value="cover" <?php selected($fit, 'cover'); ?>><?php esc_html_e('Cover', 'go-sticky-video'); ?></option>
                    </select>
                </label>
                <label>
                    <?php esc_html_e('Player UI', 'go-sticky-video'); ?><br />
                    <select name="gsv_video_player_ui">
                        <option value="simplified" <?php selected($player_ui, 'simplified'); ?>><?php esc_html_e('Simplified', 'go-sticky-video'); ?></option>
                        <option value="native" <?php selected($player_ui, 'native'); ?>><?php esc_html_e('Native player', 'go-sticky-video'); ?></option>
                    </select>
                </label>
                <label>
                    <?php esc_html_e('Default side', 'go-sticky-video'); ?><br />
                    <select name="gsv_video_side">
                        <option value="" <?php selected($side, ''); ?>><?php esc_html_e('Auto (right)', 'go-sticky-video'); ?></option>
                        <option value="left" <?php selected($side, 'left'); ?>><?php esc_html_e('Left', 'go-sticky-video'); ?></option>
                        <option value="right" <?php selected($side, 'right'); ?>><?php esc_html_e('Right', 'go-sticky-video'); ?></option>
                    </select>
                </label>
            </div>
        </div>
        <?php
    }

    public function save_metabox(int $post_id): void
    {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!isset($_POST['go_sticky_video_nonce']) || !wp_verify_nonce($_POST['go_sticky_video_nonce'], 'go_sticky_video_save')) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        $type = isset($_POST['gsv_video_type']) ? sanitize_key($_POST['gsv_video_type']) : '';
        $mp4_id = isset($_POST['gsv_video_mp4_id']) ? absint($_POST['gsv_video_mp4_id']) : 0;
        $url_youtube = isset($_POST['gsv_video_url_youtube']) ? esc_url_raw($_POST['gsv_video_url_youtube']) : '';
        $url_vimeo = isset($_POST['gsv_video_url_vimeo']) ? esc_url_raw($_POST['gsv_video_url_vimeo']) : '';
        $autoplay = isset($_POST['gsv_video_autoplay']) ? '1' : '';
        $fit = isset($_POST['gsv_video_fit']) ? sanitize_key($_POST['gsv_video_fit']) : 'contain';
        $player_ui = isset($_POST['gsv_video_player_ui']) ? sanitize_key($_POST['gsv_video_player_ui']) : 'simplified';
        $side = isset($_POST['gsv_video_side']) ? sanitize_key($_POST['gsv_video_side']) : '';
        $muted_default = isset($_POST['gsv_video_muted_default']) ? '1' : '';

        if (!in_array($type, ['mp4', 'youtube', 'vimeo'], true)) {
            $type = 'mp4';
        }

        if (!in_array($fit, ['contain', 'cover'], true)) {
            $fit = 'contain';
        }

        if (!in_array($player_ui, ['simplified', 'native'], true)) {
            $player_ui = 'simplified';
        }

        if (!in_array($side, ['left', 'right'], true)) {
            $side = '';
        }

        if ($mp4_id > 0) {
            $mime = get_post_mime_type($mp4_id);
            if ($mime !== 'video/mp4') {
                $mp4_id = 0;
            }
        }

        update_post_meta($post_id, GO_STICKY_VIDEO_META_TYPE, $type);
        if ($type === 'mp4') {
            update_post_meta($post_id, GO_STICKY_VIDEO_META_MP4_ID, $mp4_id);
            delete_post_meta($post_id, GO_STICKY_VIDEO_META_URL);
        }

        if ($type === 'youtube') {
            update_post_meta($post_id, GO_STICKY_VIDEO_META_URL, $url_youtube);
            delete_post_meta($post_id, GO_STICKY_VIDEO_META_MP4_ID);
        }

        if ($type === 'vimeo') {
            update_post_meta($post_id, GO_STICKY_VIDEO_META_URL, $url_vimeo);
            delete_post_meta($post_id, GO_STICKY_VIDEO_META_MP4_ID);
        }

        if ($type === 'mp4' && $mp4_id === 0) {
            delete_post_meta($post_id, GO_STICKY_VIDEO_META_MP4_ID);
        }

        if (in_array($type, ['youtube', 'vimeo'], true) && $url_youtube === '' && $url_vimeo === '') {
            delete_post_meta($post_id, GO_STICKY_VIDEO_META_URL);
        }
        update_post_meta($post_id, GO_STICKY_VIDEO_META_AUTOPLAY, $autoplay);
        update_post_meta($post_id, GO_STICKY_VIDEO_META_FIT, $fit);
        update_post_meta($post_id, GO_STICKY_VIDEO_META_PLAYER_UI, $player_ui);
        update_post_meta($post_id, GO_STICKY_VIDEO_META_SIDE, $side);
        update_post_meta($post_id, GO_STICKY_VIDEO_META_MUTED_DEFAULT, $muted_default);
    }
}