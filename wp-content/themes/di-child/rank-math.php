<?php
/**
 * Filter to enable/disable SearchAction JSON-LD data
 */
add_filter( 'rank_math/json_ld/disable_search', '__return_false' );



add_filter( 'rank_math/json_ld', function( $data, $jsonld ) {
    if ( is_product_category() || is_tax('pa_brands')) {
        $category = get_queried_object();
        // Kiểm tra xem đang ở trang lưu trữ brand hay không
        if (is_tax('pa_brands')) {
            // Lấy danh sách sản phẩm theo thuộc tính "brand"
            $products = wc_get_products(array(
                'tax_query' => array(
                    array(
                        'taxonomy' => 'pa_brands', // Thay bằng taxonomy của thuộc tính "brand" trong sản phẩm của bạn
                        'field'    => 'slug',
                        'terms'    => $category->slug,
                        'posts_per_page'=> -1,
                    ),
                ),
            ));
        } else {
            // Đang ở trang lưu trữ danh mục sản phẩm
            $products = wc_get_products(array(
                'category' => array($category->slug),
                'posts_per_page'=> -1,
            ));
        }
        $raiting_value = get_field('rating_value', $category);
        $raiting = get_field('rating_count', $category);
        $product_images = array(); // Mảng lưu trữ URL ảnh đại diện sản phẩm
        $limit_images = 20;
        $counter = 0;

        foreach ($products as $product) {
            $all_prices[] = $product->get_price();
            $product_image_id = $product->get_image_id();

            if ($product_image_id) {
                $product_images[] = wp_get_attachment_url($product_image_id);
                $counter++;

                // Kiểm tra nếu đã đạt đến giới hạn ảnh
                if ($counter >= $limit_images) {
                    break;
                }
            }
        }
        $category_url = get_term_link($category);
        $term_title = get_term_meta( get_queried_object_id(), 'rank_math_title', true );
        $name = ! empty( $term_title ) ? RankMath\Helper::replace_vars($term_title) : single_term_title( '', false );
        $term_description = get_term_meta( get_queried_object_id(), 'rank_math_description', true );
        $seo_description = ! empty($term_description ) ? RankMath\Helper::replace_vars($term_description) : wp_strip_all_tags(term_description('',false));

        $all_prices = array();
        foreach ($products as $product) {
            $all_prices[] = $product->get_price();
        }
        $product_count = count($products);
        $min_price = min($all_prices);
        $max_price = max($all_prices);
        $data['product'] = [
            '@type' => 'Product',
            'name'  => $name,
            'description'=>  $seo_description,
            'mainEntityOfPage' => ['@id' => esc_url($category_url) . '#webpage'],
            'aggregateRating' => [
                '@type'=>'AggregateRating',
                'ratingValue'=>$raiting_value,
                'bestRating'=>'5',
                'reviewCount'=>$raiting,
            ],
            'offers' => [
                '@type'         => 'AggregateOffer',
                'lowPrice'      => $min_price,
                'highPrice'      => $max_price,
                'offerCount'=> $product_count,
                'priceCurrency'=> 'VND',
                'availability'=> 'http://schema.org/InStock',
                'itemCondition'=> 'https://schema.org/NewCondition',
                'seller'=>[
                    '@type'=>'Organization',
                    '@id'=>'https://dioutdoor.vn/',
                    'name'=>'Đi Outdoor',
                    'url'=>'https://dioutdoor.vn',
                    'logo'=>'https://dioutdoor.vn/media/2020/08/logo-dioutdoor-v.jpg',
                ],
                'image'         => $product_images,
            ],
        ];
        // Thêm thông tin về thuộc tính sản phẩm "brand"
        if (is_tax('pa_brands')) {
            $data['product']['brand'] = get_queried_object()->name;
        }
    }
    return $data;
}, 20, 2 );


//edit .htaccess 
add_filter( 'rank_math/can_edit_file', '__return_true' );

//xóa cache sitemap rankmath
add_filter( 'rank_math/sitemap/enable_caching', '__return_false');

/** chuyển url trang đính kèm sang url hình ảnh
 * Redirect Attachments to media URL
 */
add_filter('rank_math/frontend/attachment/redirect_url', function ($redirect, $post) {
    return $post->guid;
}, 10, 2);

/**
Remote author product
 */
add_filter( 'rank_math/json_ld', function( $entities, $jsonld ) {
    if ( ! is_singular( 'product' ) ) {
        return $entities;
    }
    if ( isset( $entities['ProfilePage'] ) ) {
        $id = $entities['ProfilePage']['@id'];
        foreach ( $entities as $key => $entity ) {
            if ( isset( $entity['author' ]['@id'] ) && $id === $entity['author' ]['@id'] ) {
                unset( $entities[ $key ]['author'] );
            }
        }
        unset( $entities['ProfilePage'] );
    }
    return $entities;
}, 999, 2 );
/**
REMOVE NOTE COMMENT RANKMATH
 */
add_filter( 'rank_math/frontend/remove_credit_notice', '__return_true' );
// add "thương hiệu breadcrumb"
add_filter( 'rank_math/frontend/breadcrumb/items', function( $crumbs, $class ) {
    if ( is_tax('pa_brands') ) {
        $last_item = $crumbs[1];
        $crumbs[1] = ['Thương Hiệu',
            'https://dioutdoor.vn/brands',];
        $crumbs[2] = $last_item;}
    if ( is_tax('pa_series') ) {
        $last_item = $crumbs[1];
        $crumbs[1] = ['Thương Hiệu',
            'https://dioutdoor.vn/brands',];
        $crumbs[2] = ['Leatherman',
            'https://dioutdoor.vn/brands/leatherman',];
        $crumbs[3] = $last_item;}
    return $crumbs;
}, 10, 2 );
// Xóa Local Business ra all
add_filter( 'rank_math/json_ld', function( $data, $jsonld ) {
    if ( is_front_page() || ! isset( $data['publisher'] ) ) {
        return $data;
    }	unset( $data['publisher'] );
    unset( $data['place'] );	return $data;
}, 99, 2);
// chỉnh sửa url post
function custom_post_type_args( $args, $post_type ) {
    if ( $post_type == 'rank_math_locations' ) {
        $args['rewrite'] = array(
            'slug' => 'cua-hang',
            'with_front' => false
        );
    }

    return $args;
}
add_filter( 'register_post_type_args', 'custom_post_type_args', 999, 2 );



// remove index
add_filter( 'rank_math/frontend/robots', function( $robots ) {
    $url = home_url( $_SERVER['REQUEST_URI'] );
    if (strpos($url,'?attribute_') !== false || strpos($url,'?v=') || strpos($url,'?add_to_cart=') || strpos($url,'?filter_')|| strpos($url,'?feed_')|| strpos($url,'?gclid')|| strpos($url,'?orderby')|| strpos($url,'?PageSpeed')|| strpos($url,'?fbclid')) {
        $robots['index'] = 'noindex';
        $robots['follow'] = 'nofollow';
        return $robots;
    }
    return $robots;
});
add_action( 'wp_head', function() {
    if ( is_product_category() || is_shop() ) {
        global $wp;
        $current_url = home_url( add_query_arg( array(), $wp->request ) );

        // Kiểm tra nếu URL có query filter, orderby, attribute, page...
        $blacklist = ['filter_', 'orderby', 'v=', 'remove', 'PageSpeed', 'p=', 'attribute_pa_'];
        $url_string = $_SERVER['QUERY_STRING'] ?? '';

        foreach ( $blacklist as $param ) {
            if ( strpos( $url_string, $param ) !== false ) {
                echo '<link rel="canonical" href="' . esc_url( $current_url ) . "\" />\n";
                return;
            }
        }
    }
}, 999 );
/* thêm chính sách vận chuyển vào rankmath
add_filter( 'rank_math/json_ld', function( $data, $jsonld ) {
    if ( is_product() ) {
        global $product;
        if ( $product->is_type( 'variable' ) ) {
            // Lấy giá nhỏ nhất từ các biến thể
            $prices = $product->get_variation_prices( true );
            $min_price = $prices['price'] ? min( $prices['price'] ) : 0;
            $formatted_price = wc_format_decimal( $min_price, wc_get_price_decimals() );
        } else {
            // Nếu không phải sản phẩm biến thể, dùng giá thông thường
            $price = $product->get_price(); // vucamp
            $formatted_price = wc_format_decimal( $price, wc_get_price_decimals() );
        }
        $shipping_rate = ( $formatted_price > 299999 ) ? 0 : 20000;
        // Tạo schema shippingDetails
        $shipping_details = [
            '@context'=> 'https://schema.org/',
            '@type'               => 'OfferShippingDetails',
            'id'=>'#shipping_policy',
            'shippingRate'        => [
                '@type'    => 'MonetaryAmount',
                'value'    => $shipping_rate, // Adjust the shipping rate accordingly
                'currency' => 'VND'
            ],
            'shippingDestination' => [
                '@type'           => 'DefinedRegion',
                'addressCountry' => 'VN'
            ],
            'deliveryTime'        => [
                '@type'         => 'ShippingDeliveryTime',
                'handlingTime'  => [
                    '@type'   => 'QuantitativeValue',
                    'minValue' => 0,
                    'maxValue' => 1,
                    'unitCode' => 'DAY'
                ],
                'transitTime'  => [
                    '@type'   => 'QuantitativeValue',
                    'minValue' => 1,
                    'maxValue' => 4,
                    'unitCode' => 'DAY'
                ]
            ]
        ];
        $return_policy=[
            '@context'=> 'https://schema.org/',
                '@type'                => 'MerchantReturnPolicy',
            '@id'=> '#return_policy',
                'applicableCountry'    => 'VN',
                'returnPolicyCategory' => 'https://schema.org/MerchantReturnFiniteReturnWindow',
                'merchantReturnDays'   => 14,
                'returnMethod'         => 'https://schema.org/ReturnByMail',
                'returnFees'           => 'https://schema.org/FreeReturn'

        ];
        $offer=[
            'shippingDetails'=> [ '@id'=> '#shipping_policy' ],
            'hasMerchantReturnPolicy'=> [ '@id'=>'#return_policy' ],
            ];
        $data[] = $shipping_details;
        $data[] = $return_policy;
        foreach ( $data as $key => $schema ) {
            if ( isset( $schema['@type'] ) && $schema['@type'] === 'Product' ) {
                if ( isset( $schema['offers'] ) && is_array( $schema['offers'] ) ) {
                    // Thêm vào offers
                    $data[$key]['offers'] = array_merge( $data[$key]['offers'], $offer );
                }
                break;
            }
        }
    }

    return $data;
}, 90, 2 );
*/
//hiển thị meta giá
add_filter( 'rank_math/woocommerce/og_price', '__return_true' );