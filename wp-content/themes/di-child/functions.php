<?php
/**
 * Enqueue script and styles for child theme
 */
function woodmart_child_enqueue_styles() {
    wp_enqueue_style( 'child-style', get_stylesheet_directory_uri() . '/style.css', array( 'woodmart-style' ), woodmart_get_theme_info( 'Version' ) );
}
add_action( 'wp_enqueue_scripts', 'woodmart_child_enqueue_styles', 10010 );

// add thumbnail category blog
add_image_size( 'category-blog', 500, 280 , true);


//Xóa woocommerce soạn thoải
add_action('admin_head', 'Hide_WooCommerce_Breadcrumb');
function Hide_WooCommerce_Breadcrumb() {
    echo '<style>.woocommerce-layout__header {display: none;}</style>';
}
/** gởi email sau khi checkout thành công
add_filter( 'woocommerce_defer_transactional_emails', '__return_true' );**/
// gửi email order thất bại cho khách hàng
add_action('woocommerce_order_status_changed', 'send_custom_email_notifications', 10, 4 );
function send_custom_email_notifications( $order_id, $old_status, $new_status, $order ){
    if ( $new_status == 'cancelled' || $new_status == 'failed' ){
        $wc_emails = WC()->mailer()->get_emails(); // Get all WC_emails objects instances
        $customer_email = $order->get_billing_email(); // The customer email
    }

    if ( $new_status == 'cancelled' ) {
        // change the recipient of this instance
        $wc_emails['WC_Email_Cancelled_Order']->recipient = $customer_email;
        // Sending the email from this instance
        $wc_emails['WC_Email_Cancelled_Order']->trigger( $order_id );
    }
    elseif ( $new_status == 'failed' ) {
        // change the recipient of this instance
        $wc_emails['WC_Email_Failed_Order']->recipient = $customer_email;
        // Sending the email from this instance
        $wc_emails['WC_Email_Failed_Order']->trigger( $order_id );
    }
}

// xóa filed email checkout
add_filter( 'woocommerce_billing_fields', 'email_optional_field');
function email_optional_field( $fields ) {
    $fields['billing_email']['required'] = false;
    return $fields;
}


//đổi đồng tiền
add_filter('woocommerce_currency_symbol', 'change_existing_currency_symbol', 10, 2);
function change_existing_currency_symbol( $currency_symbol, $currency ) {
    switch( $currency ) {
        case 'VND': $currency_symbol = 'đ'; break;
    }
    return $currency_symbol;
}
// ratemypost
function blazzdev_schemaproduct( $schemaType ) {
    if( is_singular( 'product') ) {
        return '';
    }
    return $schemaType;
}
add_filter( 'rmp_schema_type', 'blazzdev_schemaproduct' );

// xóa sửa block
function example_theme_support() {
    remove_theme_support( 'widgets-block-editor' );
}
add_action( 'after_setup_theme', 'example_theme_support' );

// ẩn biến thế hết hàng
add_filter( 'woocommerce_variation_is_active', 'bbloomer_grey_out_variations_out_of_stock', 10, 2 );
function bbloomer_grey_out_variations_out_of_stock( $is_active, $variation ) {
    if ( ! $variation->is_in_stock() ) return false;
    return $is_active;
}
// Add tình trạng
add_filter( 'woocommerce_get_availability_text', 'custom_backorders_stock_availability_text', 10, 2 );
function custom_backorders_stock_availability_text( $availability, $product ) {
    if( is_product() ){
        if ( ! $product->is_in_stock() ) {
            $availability = __( 'HẾT HÀNG', 'woocommerce' );
        } elseif ( ( $product->managing_stock() && $product->is_on_backorder( 1 ) ) || ( ! $product->managing_stock() && $product->is_on_backorder( 1 ) ) ) {
            $availability = __( 'HÀNG ĐANG VỀ', 'woocommerce' );
        } else {
            $availability = __( 'HÀNG CÓ SẴN', 'woocommerce' );
        }
        return $availability;
    }
    if( is_cart() ){
        if ( ! $product->is_in_stock() ) {
            $availability = __( 'HẾT HÀNG', 'woocommerce' );
        } elseif ( ( $product->managing_stock() && $product->is_on_backorder( 1 ) ) || ( ! $product->managing_stock() && $product->is_on_backorder( 1 ) ) ) {
            $availability = __( '<p class="stock available-on-backorder">ĐẶT HÀNG TRƯỚC</p>', 'woocommerce' );
        } else {
            $availability = __( '<p class="stock in-stock">HÀNG CÓ SẴN</p>', 'woocommerce' );
        }
        return $availability;
    }
    if( is_checkout() ){
        if ( ! $product->is_in_stock() ) {
            $availability = __( 'HẾT HÀNG', 'woocommerce' );
        } elseif ( ( $product->managing_stock() && $product->is_on_backorder( 1 ) ) || ( ! $product->managing_stock() && $product->is_on_backorder( 1 ) ) ) {
            $availability = __( '<div class="cho-hang"><p class="stock available-on-backorder">HÀNG ĐANG VỀ</p></div>', 'woocommerce' );
        } else {
            $availability = __( '<p class="stock in-stock">HÀNG CÓ SẴN</p>', 'woocommerce' );
        }
        return $availability;
    }

}
// Add tình trạng cart & checkout
add_filter( 'woocommerce_cart_item_name', 'add_availability_below_cart_item_name', 10, 3);
function add_availability_below_cart_item_name( $item_name, $cart_item, $cart_item_key ) {
    $availability = $cart_item['data']->get_availability();
    return $item_name . '<br>' . $availability['availability'];
}


/** REMOVE PRELOAD RATE MY POST */
add_filter( 'rmp_font_preload', '__return_false' );
/* lỗi wordpress
// ADD TAG TO TITLE EMAIL WOOCOMMERCE
add_filter( 'woocommerce_email_format_string' , 'filter_email_format_string', 20, 2 );
function filter_email_format_string( $string, $email ) {
    // Get the instance of the WC_Order object
    $order = $email->object;
    // Additional wanted placeholders in the array of find / relace pairs
    $additional_placeholders = array(
        '{order_billing_phone}'   => $order->get_billing_phone(),
        '{order_billing_first_name}'   => $order->get_billing_first_name(),

    );

    // return the clean string with new replacements
    return str_replace( array_keys( $additional_placeholders ), array_values( $additional_placeholders ), $string );
}
*/

// thay đổi timezone woodmart
add_filter( 'woodmart_wp_timezone_element', '__return_true' );

// Cấu hình mặc định khi thêm ảnh
add_action( 'after_setup_theme', 'wnd_default_image_settings' );
function wnd_default_image_settings() {
    update_option( 'image_default_align', 'center' );
    update_option( 'image_default_link_type', 'file' );
    update_option( 'image_default_size', 'large' );
}
/*
//thêm nội dung vào checkout
add_action( 'woocommerce_review_order_after_cart_contents', 'woocommerce_checkout_coupon_form_custom' );
function woocommerce_checkout_coupon_form_custom() {
    echo '<tr class="coupon-form"><td colspan="2"><a href="#giam-gia" class="checkout-coupon">Nhập mã giảm giá <image hei src="https://dioutdoor.vn/media/2023/03/arrow-on-primary-circle.svg" width="18" height="18"></a>';
    if (!is_user_logged_in()) {// vucamp
        echo '<span><strong>LƯU Ý:</strong> Đăng nhập thành viên để nhận ưu đãi nhiều hơn </span></tr></td>' ;
    } else {'</tr></td>';
    }
}//*/
// xóa số đếm danh mục woocommerce
add_filter( 'woocommerce_subcategory_count_html', '__return_null' );
//hiển thị tiết kiệm được
add_action( 'woocommerce_cart_totals_after_order_total', 'bbloomer_show_total_discount_cart_checkout', 9999 );
//add_action( 'woocommerce_review_order_after_order_total', 'bbloomer_show_total_discount_cart_checkout', 9999 );

function bbloomer_show_total_discount_cart_checkout() {
    $discount_total = 0;

    foreach ( WC()->cart->get_cart() as $cart_item_key => $values ) {
        $product = $values['data'];

        // Sử dụng get_price() thay vì get_sale_price()
        $regular_price = $product->get_regular_price();
        $current_price = $product->get_price();

        if ( $product->is_on_sale() ) {
            $discount = (float) $regular_price - (float) $current_price;
            $discount_total += $discount * (int) $values['quantity'];
        }
    }

    $discount_total += WC()->cart->get_discount_total();

    if ( $discount_total > 0 ) {
        echo '<tr><th>Tiết kiệm</th><td data-title="Tiết kiệm">' . wc_price( $discount_total ) .'</td></tr>';
    }
}


//add custom styles to the WordPress editor
function my_custom_styles( $init_array ) {
    $style_formats = array(
        // These are the custom styles
        array(
            'title' => 'Note URL',
            'selector' => 'p',
            'classes' => 'noteurl',
        ),
    array(
        'title' => 'Note Red',
        'selector' => 'p',
        'classes' => 'notered',
    ),
        array(
            'title' => 'Div URL',
            'block' => 'div',
            'classes' => 'divurl',
            'wrapper' => true,
        ),
        array(
            'title' => 'Div Red',
            'block' => 'div',
            'classes' => 'divred',
            'wrapper' => true,
        ),
    );
    // Insert the array, JSON ENCODED, into 'style_formats'
    $init_array['style_formats'] = json_encode( $style_formats );

    return $init_array;

}
// Attach callback to 'tiny_mce_before_init'
add_filter( 'tiny_mce_before_init', 'my_custom_styles' );
// thêm style vào admin
// thay đổi slug vị trí cửa hàng
register_post_type('rank_math_locations', array('has_archive' => false));
add_action('init', function () {
    add_rewrite_rule('cua-hang/?$','index.php?pagename=cua-hang', 'top');
}, 1000);

function custom_admin_styles() {
    wp_enqueue_style('custom-admin-css', get_stylesheet_directory_uri(). '/admin.css');
}
add_action('admin_enqueue_scripts', 'custom_admin_styles');


add_filter( 'loop_shop_per_page', 'bbloomer_redefine_products_per_page', 9999 );
//số lượng sản phẩm trên 1 page
function bbloomer_redefine_products_per_page( $per_page ) {
    $per_page = 16;
    return $per_page;
}

// show stock sku trong REST API
add_filter('woocommerce_rest_prepare_product_object', 'add_variations_to_product_response', 10, 3);

function add_variations_to_product_response($response, $product, $request) {
    // Kiểm tra xem sản phẩm có biến thể hay không
    if ($product->is_type('variable')) {
        $variations = array();

        // Lấy tất cả các biến thể
        foreach ($product->get_children() as $variation_id) {
            $variation = wc_get_product($variation_id);

            // Lấy dữ liệu SKU và stock của từng biến thể
            $variations[] = array(
                'id' => $variation_id,
                'sku' => $variation->get_sku(),
                'stock_quantity' => $variation->get_stock_quantity(),
                'stock_status' => $variation->get_stock_status()
            );
        }

        // Thêm thông tin biến thể vào phản hồi của sản phẩm cha
        $response->data['variations_data'] = $variations;
    }

    return $response;
}

// ADD Colum Thông số kỹ thuật
add_action('add_meta_boxes', 'add_product_parameters_meta_box');

function add_product_parameters_meta_box() {
    add_meta_box(
        'product_parameters_meta_box',       // ID của meta box
        'Thông số sản phẩm',                  // Tiêu đề
        'render_product_parameters_meta_box', // Hàm hiển thị nội dung
        'product',                            // Loại bài viết (product)
        'normal',                             // Vị trí
        'high'                                // Độ ưu tiên
    );
}
// Hiển thị nội dung của meta box
function render_product_parameters_meta_box($post) {
    // Lấy dữ liệu JSON đã lưu
    $json_data = get_post_meta($post->ID, 'info_custom', true);
    $parameters = !empty($json_data) ? json_decode($json_data, true) : [];
    wp_nonce_field('save_product_parameters', 'product_parameters_nonce');
    ?>
    <div id="product-parameters-wrapper">
        <table class="form-table">
            <thead>
            <tr>
                <th style="width: 10px;"><input type="checkbox" id="select-all" /> All</th>
                <th>Tên thông số</th>
                <th>Giá trị</th>
                <th style="width: 10px;">Thao tác</th>
                <th style="width: 10px;">Move</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($parameters as $index => $parameter) : ?>
                <tr data-index="<?php echo esc_attr($index); ?>">
                    <td><input type="checkbox" class="delete-row" /></td>
                    <td><input style="width: 100%;" type="text" name="parameter_name[]" value="<?php echo esc_attr($parameter['n']); ?>" /></td>
                    <td><input style="width: 100%;" type="text" name="parameter_value[]" value="<?php echo esc_attr($parameter['v']); ?>" /></td>
                    <td>
                        <button type="button" class="copy-row button-icon"><i class="dashicons dashicons-admin-page"></i></button>
                        <button type="button" class="remove-row button-icon"><i class="dashicons dashicons-trash"></i></button>
                    </td>
                    <td>
                        <button type="button" class="move-up button-icon"><i class="dashicons dashicons-arrow-up-alt2"></i></button>
                        <button type="button" class="move-down button-icon"><i class="dashicons dashicons-arrow-down-alt2"></i></button>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 10px;">
            <button type="button" id="delete-selected" class="button button-danger">Xóa hàng đã chọn</button>
            <div>
                <button type="button" id="add-single-parameter" class="button button-primary">Thêm 1 dòng</button>
                <button type="button" id="add-parameter" class="button">Thêm 3 dòng</button>
            </div>
        </div>
    </div>

    <script>
        // Chọn hoặc bỏ chọn tất cả các hàng
        document.getElementById('select-all').addEventListener('change', function() {
            var checked = this.checked;
            document.querySelectorAll('#product-parameters-wrapper tbody .delete-row').forEach(function(checkbox) {
                checkbox.checked = checked;
            });
        });
        document.getElementById('add-single-parameter').addEventListener('click', function() {
            var wrapper = document.getElementById('product-parameters-wrapper').querySelector('tbody');
            var newRow = document.createElement('tr');
            newRow.innerHTML = '<td><input type="checkbox" class="delete-row" /></td><td><input type="text" name="parameter_name[]" /></td><td><input type="text" name="parameter_value[]" /></td><td><button type="button" class="copy-row button-icon"><i class="dashicons dashicons-admin-page"></i></button><button type="button" class="remove-row button-icon"><i class="dashicons dashicons-trash"></i></button></td><td><button type="button" class="move-up button-icon"><i class="dashicons dashicons-arrow-up-alt2"></i></button><button type="button" class="move-down button-icon"><i class="dashicons dashicons-arrow-down-alt2"></i></button></td>';
            wrapper.appendChild(newRow);
        });
        // Thêm 3 thông số
        document.getElementById('add-parameter').addEventListener('click', function() {
            var wrapper = document.getElementById('product-parameters-wrapper').querySelector('tbody');
            for (let i = 0; i < 3; i++) {
                var newRow = document.createElement('tr');
                newRow.innerHTML = '<td><input type="checkbox" class="delete-row" /></td><td><input type="text" name="parameter_name[]" /></td><td><input type="text" name="parameter_value[]" /></td><td><button type="button" class="copy-row button-icon"><i class="dashicons dashicons-admin-page"></i></button><button type="button" class="remove-row button-icon"><i class="dashicons dashicons-trash"></i></button></td><td><button type="button" class="move-up button-icon"><i class="dashicons dashicons-arrow-up-alt2"></i></button><button type="button" class="move-down button-icon"><i class="dashicons dashicons-arrow-down-alt2"></i></button></td>';
                wrapper.appendChild(newRow);
            }
        });

        // Sao chép, xóa và di chuyển hàng
        document.addEventListener('click', function(e) {
            if (e.target.closest('.copy-row')) {
                var row = e.target.closest('tr');
                var name = row.querySelector('input[name="parameter_name[]"]').value;
                var value = row.querySelector('input[name="parameter_value[]"]').value;

                var newRow = document.createElement('tr');
                newRow.innerHTML = '<td><input type="checkbox" class="delete-row" /></td><td><input type="text" name="parameter_name[]" value="' + name + '" /></td><td><input type="text" name="parameter_value[]" value="' + value + '" /></td><td><button type="button" class="copy-row button-icon"><i class="dashicons dashicons-admin-page"></i></button><button type="button" class="remove-row button-icon"><i class="dashicons dashicons-trash"></i></button></td><td><button type="button" class="move-up button-icon"><i class="dashicons dashicons-arrow-up-alt2"></i></button><button type="button" class="move-down button-icon"><i class="dashicons dashicons-arrow-down-alt2"></i></button></td>';
                row.parentNode.appendChild(newRow);
            }
            if (e.target.closest('.remove-row')) {
                e.target.closest('tr').remove();
            }
            if (e.target.closest('.move-up')) {
                var row = e.target.closest('tr');
                var previousRow = row.previousElementSibling;
                if (previousRow) {
                    row.parentNode.insertBefore(row, previousRow);
                    row.classList.add('highlight-move');
                    setTimeout(() => row.classList.remove('highlight-move'), 2000);
                }
            }
            if (e.target.closest('.move-down')) {
                var row = e.target.closest('tr');
                var nextRow = row.nextElementSibling;
                if (nextRow) {
                    row.parentNode.insertBefore(nextRow, row);
                    row.classList.add('highlight-move');
                    setTimeout(() => row.classList.remove('highlight-move'), 2000);
                }
            }
        });
        // Xóa hàng đã chọn
        document.getElementById('delete-selected').addEventListener('click', function() {
            document.querySelectorAll('#product-parameters-wrapper tbody .delete-row:checked').forEach(function(checkbox) {
                checkbox.closest('tr').remove();
            });
        });
    </script>
    <?php
}
add_action('save_post', 'save_product_parameters_meta');
function save_product_parameters_meta($post_id) {
    // Kiểm tra xem đây có phải là sản phẩm không
    if (get_post_type($post_id) != 'product') {
        return;
    }
    // Kiểm tra quyền chỉnh sửa
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    // Kiểm tra nonce để đảm bảo yêu cầu lưu đến từ giao diện quản trị của bạn
    if (!isset($_POST['product_parameters_nonce']) || !wp_verify_nonce($_POST['product_parameters_nonce'], 'save_product_parameters')) {
        return;
    }

    // Kiểm tra dữ liệu và lưu lại
    if (isset($_POST['parameter_name']) && isset($_POST['parameter_value'])) {
        $parameters = [];
        $names = $_POST['parameter_name'];
        $values = $_POST['parameter_value'];
        for ($i = 0; $i < count($names); $i++) {
            $parameters[] = [
                'n' => sanitize_text_field($names[$i]),
                'v' => sanitize_text_field($values[$i])
            ];
        }
        // Lưu dữ liệu dưới dạng JSON
        update_post_meta($post_id, 'info_custom', wp_json_encode($parameters, JSON_UNESCAPED_UNICODE));
    } else {
        // Nếu không có dữ liệu mới, không làm gì cả để bảo toàn dữ liệu cũ
       
    }
}

// Loại trừ content ra khỏi tìm kiếm
add_filter( 'relevanssi_index_content', '__return_false' );

// Lưu lần đăng nhập cuối cùng khi người dùng đăng nhập đăng ký lần đầu 15/11/2024
function save_last_login_time($user_login, $user) {
    update_user_meta($user->ID, 'last_login', current_time('mysql'));
}
add_action('wp_login', 'save_last_login_time', 10, 2);

add_action( 'woocommerce_after_shop_loop_item', 'remove_add_to_cart_buttons', 1 );

function remove_add_to_cart_buttons() {
    if( is_product_category() || is_shop()) {
        remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_simple_add_to_cart' );
    }
}

// thêm single-product vào trang product
function add_custom_css_to_single_product() {
    if (is_product()) {
        wp_enqueue_style('custom-single-product-css', get_stylesheet_directory_uri() . '/single-product.css');
    }
}
add_action('wp_enqueue_scripts', 'add_custom_css_to_single_product');


//


// thêm note vào hình thức vận chuyển
function action_woocommerce_after_shipping_rate( $method, $index ) {

    // Compare (adjust as needed)
    if (in_array($method->get_id(), ['flat_rate:13','flat_rate:18'])) {
        echo '<a href="#" class="woocommerce-shipping-destination-2 open-ship"> <span class="wd-icon-warning-sign"></span> Phí SHIP cụ thể sẽ được thông báo khi Đi Outdoor gọi điện xác nhận đơn hàng.</a>';
    }
    if (in_array($method->get_id(), ['local_pickup:15', 'local_pickup:19'])){
        echo '<a href="#" class="woocommerce-shipping-destination-3 open-ship"><span class="wd-icon-warning-sign"></span> Chỉ hỗ trợ tại Hồ Chí Minh, Đà Nẵng, Hà Nội. </a>';
    }
    if (in_array($method->get_id(), ['free_shipping:12' , 'free_shipping:17'])) {
        echo '<a href="#" class="woocommerce-shipping-destination-2 open-ship"><span class="wd-icon-warning-sign"></span> Đơn hàng được miễn phí vận chuyển (<10kg).</a>';
    }
}
add_action( 'woocommerce_after_shipping_rate', 'action_woocommerce_after_shipping_rate', 10, 2 );


/////////// teeem

add_action( 'woocommerce_cart_calculate_fees', 'custom_handling_fee' );
function custom_handling_fee( $cart ) {
    if ( is_admin() && ! defined( 'DOING_AJAX' ) )
        return;

    $chosen_payment_id = WC()->session->get( 'chosen_payment_method' );
    $chosen_shipping_method = WC()->session->get( 'chosen_shipping_methods' )[0] ?? '';

    if ( empty( $chosen_payment_id ) || empty( $chosen_shipping_method ) )
        return;

    $subtotal = $cart->subtotal;

    // Cấu hình phí theo từng hình thức thanh toán và giao hàng
    if ( strpos( $chosen_shipping_method, 'local_pick' ) !== false ) {
        $cart->add_fee( __( 'Phí Giao Hàng', 'woocommerce' ), 0, true ); // Phí "thông báo sau" không tính phí.
    } else {
        $targeted_payment_ids = array(
            'payos' => ( $subtotal > 300000 ) ? 0 : 25000, // Điều kiện cho payos
            'cod'   => 25000, // Phí cố định
        );

        foreach ( $targeted_payment_ids as $payment_id => $fee_cost ) {
            if ( $chosen_payment_id === $payment_id ) {
                $cart->add_fee( __( 'Phí Giao Hàng', 'woocommerce' ), $fee_cost, true );
            }
        }
    }
}

// Thêm ghi chú sau các hình thức thanh toán
add_filter( 'woocommerce_gateway_title', 'add_custom_payment_notes', 20, 2 );
function add_custom_payment_notes( $description, $payment_id ) {
    // Ngăn không chạy trong trang quản trị
    if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
        return $description;
    }
    $chosen_shipping_method = WC()->session->get( 'chosen_shipping_methods' )[0] ?? '';

    // Ghi chú cho tất cả hình thức thanh toán
    if ( strpos( $chosen_shipping_method, 'local_pick' ) !== false ) {
        $description .= '<span class="woocommerce-shipping-destination-2"><strong> (Phí giao hàng Thông báo sau)</strong></span>';
    } else {
        if ( 'cod' === $payment_id ) {
            $description .= '<span class="woocommerce-shipping-destination-2"><strong> (Phí giao hàng +25.000đ)</strong></span>';
        } elseif ( 'payos' === $payment_id ) {
            $cart = WC()->cart;
            $subtotal = $cart ? $cart->subtotal : 0;
            if ( $subtotal >= 300000 ) {
                $description .= '<span class="woocommerce-shipping-destination-2"> <strong> (Giao hàng miễn phí)</strong></span>';
            } else {
                $description .= '<span class="woocommerce-shipping-destination-2"><strong> (Phí giao hàng +25.000đ)</strong></span>';
            }
        }
    }
    return $description;
}


// jQuery - Cập nhật lại checkout khi thay đổi phương thức thanh toán
add_action( 'woocommerce_checkout_init', 'payment_methods_refresh_checkout' );
function payment_methods_refresh_checkout() {
    wc_enqueue_js( "jQuery( function($){
        $('form.checkout').on('change', 'input[name=payment_method]', function(){
            $(document.body).trigger('update_checkout');
        });
    });");
}

// [product_faqs title="Câu hỏi thường gặp" state="all_closed"]
add_action('wp_enqueue_scripts', function () {
    $base_css = trailingslashit( get_template_directory_uri() ) . 'css/parts/';
    $base_js  = trailingslashit( get_template_directory_uri() ) . 'js/scripts/elements/';

    wp_register_style( 'wd-accordion',      $base_css . 'el-accordion.min.css',          [], '8.0.1' );
    wp_register_style( 'wd-accordion-elem', $base_css . 'el-accordion-wpb-elem.min.css', [], '8.0.1' );
    wp_register_script('wd-accordion-js',   $base_js  . 'accordion.min.js',              ['jquery'], '8.0.1', true);
}, 15);
// state: all_closed | first_open | all_open (mặc định: all_closed)
// [product_faqs title="Câu hỏi thường gặp" state="all_closed"]
add_shortcode('product_faqs', function($atts){
    if ( ! is_singular('product') ) return '';

    $atts = shortcode_atts([
        'title' => 'Câu hỏi thường gặp',
        'state' => 'all_closed', // all_closed | first_open | all_open
    ], $atts, 'product_faqs');

    if ( ! function_exists('get_field') ) return '';
    $faqs = get_field('product_faqs', get_the_ID());
    if ( empty($faqs) || !is_array($faqs) ) return '';

    /** ====== NẠP ASSETS THEO CƠ CHẾ WOODMART (ƯU TIÊN) ====== */
    if ( function_exists('woodmart_enqueue_js_script') ) {
        // JS accordion của theme (tương đương di/js/scripts/elements/accordion.min.js)
        woodmart_enqueue_js_script( 'accordion-element' );
    }
    if ( function_exists('woodmart_enqueue_inline_style') ) {
        // CSS accordion của theme (tương đương 2 file el-accordion*.css)
        woodmart_enqueue_inline_style( 'accordion' );
        woodmart_enqueue_inline_style( 'accordion-elem-wpb' );
    }

    // Wrapper
    ob_start(); ?>
    <section class="dioutdoor-product-faqs">
        <?php if ( !empty($atts['title']) ) : ?>
            <h3 class="woodmart-title-container title wd-fontsize-l"><?php echo esc_html($atts['title']); ?></h3>
        <?php endif; ?>

        <div class="wd-accordion wd-style-shadow wd-titles-left wd-opener-pos-right wd-opener-style-arrow"
             data-state="<?php echo esc_attr($atts['state']); ?>">
            <?php
            $i = 0;
            foreach ( $faqs as $row ) :
                $q = isset($row['question']) ? trim(wp_strip_all_tags($row['question'])) : '';
                $a = isset($row['answer']) ? trim($row['answer']) : '';
                if ( $q === '' || $a === '' ) continue;

                $is_open = false;
                if ( $atts['state'] === 'all_open' ) $is_open = true;
                if ( $atts['state'] === 'first_open' && $i === 0 ) $is_open = true;

                // Một số bản WoodMart thêm class khi mở; nếu JS của theme đã lo, có thể không cần.
                $item_classes = 'wd-accordion-item' . ( $is_open ? ' wd-opened' : '' );
                ?>
                <div class="<?php echo esc_attr($item_classes); ?>">
                    <div class="wd-accordion-title" data-accordion-index="<?php echo esc_attr($i); ?>">
                        <div class="wd-accordion-title-text">
                            <span><?php echo esc_html($q); ?></span>
                        </div>
                        <span class="wd-accordion-opener"></span>
                    </div>

                    <div class="wd-accordion-content wd-entry-content"
                         data-accordion-index="<?php echo esc_attr($i); ?>"
                        <?php echo $is_open ? '' : 'style="display:none"'; ?>>
                        <?php
                        // Cho phép format cơ bản + shortcode trong câu trả lời
                        echo do_shortcode( wp_kses_post( $a ) );
                        ?>
                    </div>
                </div>
                <?php
                $i++;
            endforeach; ?>
        </div>
    </section>
    <?php
    return ob_get_clean();
});

/**
 * Thêm FAQPage schema từ ACF vào Rank Math (đúng chữ ký filter).
 * - Gắn cho trang sản phẩm.
 * - Không ghi đè schema khác của Rank Math.
 * - Tránh thêm trùng nếu đã có FAQPage.
 */
add_filter( 'rank_math/json_ld', function( $data, $jsonld ) {

    if ( ! is_singular('product') ) {
        return $data;
    }

    // ACF chưa có thì thôi
    if ( ! function_exists('get_field') ) {
        return $data;
    }

    $post_id = get_the_ID();
    $faqs    = get_field('product_faqs', $post_id);

    if ( empty($faqs) || ! is_array($faqs) ) {
        return $data;
    }

    // Nếu đã có FAQPage do nơi khác thêm (Rank Math / thủ công) thì bỏ qua để tránh trùng
    foreach ( $data as $entity ) {
        $types = (array) ( $entity['@type'] ?? [] );
        if ( in_array( 'FAQPage', $types, true ) ) {
            return $data;
        }
    }

    $entities = [];
    foreach ( $faqs as $row ) {
        $q = isset($row['question']) ? trim( wp_strip_all_tags( $row['question'] ) ) : '';
        $a = isset($row['answer'])   ? trim( $row['answer'] ) : '';
        if ( $q === '' || $a === '' ) { continue; }

        $entities[] = [
            '@type' => 'Question',
            'name'  => $q,
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text'  => wp_kses_post( $a ),
            ],
        ];
    }

    if ( ! $entities ) {
        return $data;
    }

    $data['di_faq'] = [
        '@context'   => 'https://schema.org',
        '@type'      => 'FAQPage',
        '@id'        => trailingslashit( get_permalink( $post_id ) ) . '#faq',
        'url'        => get_permalink( $post_id ),
        'mainEntity' => $entities,
    ];

    return $data;
}, 99, 2 );



