<?php
/**
 * Filter to enable/disable SearchAction JSON-LD data
 */
add_filter( 'rank_math/json_ld/disable_search', '__return_false' );



/*add_filter( 'rank_math/json_ld', function( $data, $jsonld ) {
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
        if (is_tax('product_brand')) {
            $data['product']['brand'] = get_queried_object()->name;
        }
    }
    return $data;
}, 20, 2 );*/
add_filter('rank_math/json_ld', function ($data, $jsonld) {

    if (!is_product_category() && !is_tax('product_brand')) {
        return $data;
    }

    $term = get_queried_object();
    if (!($term instanceof WP_Term)) {
        return $data;
    }

    global $wp_query;

    $term_id  = $term->term_id;
    $page_url = get_term_link($term);

    // Title/Description: ưu tiên Rank Math term meta
    $rm_title = get_term_meta($term_id, 'rank_math_title', true);
    $name     = (!empty($rm_title) && class_exists('\RankMath\Helper'))
        ? \RankMath\Helper::replace_vars($rm_title)
        : single_term_title('', false);

    $rm_desc = get_term_meta($term_id, 'rank_math_description', true);
    $desc    = (!empty($rm_desc) && class_exists('\RankMath\Helper'))
        ? \RankMath\Helper::replace_vars($rm_desc)
        : wp_strip_all_tags(term_description('', false));

 // Lấy tối đa 10 sản phẩm đang hiển thị trên trang hiện tại để đưa vào schema
$schema_limit = 10;
$product_ids  = [];

if (!empty($wp_query->posts)) {
    foreach ($wp_query->posts as $p) {
        if (isset($p->ID) && get_post_type($p->ID) === 'product') {
            $product_ids[] = (int) $p->ID;

            if (count($product_ids) >= $schema_limit) {
                break;
            }
        }
    }
}


    // Tổng số sản phẩm (phản ánh toàn bộ term, không phải chỉ page hiện tại)
    $total_products = isset($wp_query->found_posts) ? (int) $wp_query->found_posts : (int) ($term->count ?? 0);

    // Nếu là brand page: tạo Brand entity
    if (is_tax('product_brand')) {
        $data['brand'] = [
            '@type' => 'Brand',
            '@id'   => esc_url($page_url) . '#brand',
            'name'  => $term->name,
            'url'   => esc_url($page_url),
        ];
    }

    // Build ItemList: mỗi ListItem chứa Product có offers + aggregateRating (nếu có)
    $item_list = [
        '@type'           => 'ItemList',
        'name'            => $name,
        'itemListOrder'   => 'https://schema.org/ItemListOrderAscending',
        'numberOfItems'   => $total_products,
        'itemListElement' => [],
    ];

    $pos = 1;
    foreach ($product_ids as $pid) {
        $product = wc_get_product($pid);
        if (!$product) continue;

        $p_url  = get_permalink($pid);
        $p_name = $product->get_name();

        // Image (1 ảnh đại diện)
        $img = '';
        $img_id = $product->get_image_id();
        if ($img_id) {
            $img = wp_get_attachment_url($img_id);
        }

        // Offers
        // - Simple: Offer (price)
        // - Variable: AggregateOffer (low/high)
        $offers = null;

        if ($product->is_type('variable')) {
            $min_price = $product->get_variation_price('min', true);
            $max_price = $product->get_variation_price('max', true);

            $offers = [
                '@type'         => 'AggregateOffer',
                'priceCurrency' => get_woocommerce_currency(),
                'lowPrice'      => (float) $min_price,
                'highPrice'     => (float) $max_price,
                'offerCount'    => (int) $product->get_children() ? count($product->get_children()) : 0,
                'availability'  => $product->is_in_stock() ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
                'url'           => $p_url,
            ];
        } else {
            $price = $product->get_price();
            $offers = [
                '@type'         => 'Offer',
                'priceCurrency' => get_woocommerce_currency(),
                'price'         => is_numeric($price) ? (float) $price : 0,
                'availability'  => $product->is_in_stock() ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
                'url'           => $p_url,
            ];
        }

        // AggregateRating (chỉ khi có ratingCount > 0)
        $rating_count = (int) rmp_get_vote_count( $product->get_id() );
        $avg_rating   = rmp_get_avg_rating( $product->get_id()  ); // string "4.7"...

        $product_node = [
            '@type'  => 'Product',
            '@id'    => esc_url($p_url) . '#product',
            'name'   => $p_name,
            'url'    => $p_url,
            'offers' => $offers,
        ];

        if (!empty($img)) {
            $product_node['image'] = $img;
        }

        if ($rating_count > 0 && is_numeric($avg_rating)) {
            $product_node['aggregateRating'] = [
                '@type'       => 'AggregateRating',
                'ratingValue' => (float) $avg_rating,
                'bestRating'  => 5,
                'ratingCount' => $rating_count,
            ];
        }

        // Nếu đang ở trang brand: gắn brand cho Product
        if (is_tax('product_brand')) {
            $product_node['brand'] = [
                '@type' => 'Brand',
                'name'  => $term->name,
            ];
        }

        $item_list['itemListElement'][] = [
            '@type'    => 'ListItem',
            'position' => $pos++,
            'url'      => $p_url,
            'item'     => $product_node,
        ];
    }

    // CollectionPage
    $collection_page = [
        '@type'            => 'CollectionPage',
        '@id'              => esc_url($page_url) . '#collectionpage',
        'url'              => esc_url($page_url),
        'name'             => $name,
        'description'      => $desc,
        'mainEntity'       => $item_list,
        'isPartOf'         => ['@id' => home_url('/') . '#website'],
        'mainEntityOfPage' => ['@id' => esc_url($page_url) . '#webpage'],
    ];

    // Nếu là brand page: liên kết about -> Brand
    if (is_tax('product_brand')) {
        $collection_page['about'] = ['@id' => esc_url($page_url) . '#brand'];
    }

    // Gắn vào graph của Rank Math
    $data['collection_page'] = $collection_page;

    return $data;

}, 20, 2);




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
    if ( is_tax('product_brand') ) {
        $last_item = $crumbs[1];
        $crumbs[1] = ['Thương Hiệu',
            'https://dioutdoor.vn/brands',];
        $crumbs[2] = $last_item;}
        /*
    if ( is_tax('pa_series') ) {
        $last_item = $crumbs[1];
        $crumbs[1] = ['Thương Hiệu',
            'https://dioutdoor.vn/brands',];
        $crumbs[2] = ['Leatherman',
            'https://dioutdoor.vn/brands/leatherman',];
        $crumbs[3] = $last_item;}*/
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

    $query = $_SERVER['QUERY_STRING'] ?? '';
    if ( empty($query) ) {
        return $robots;
    }

    $blocked = [
        'attribute_',
        'filter_',
        'add_to_cart',
        'orderby',
        'gclid',
        'fbclid',
        'PageSpeed',
        'feed',
        'v=',
        'min_price',
        'max_price',
    ];

    foreach ( $blocked as $key ) {
        if ( strpos( $query, $key ) !== false ) {
            $robots['index']  = 'noindex';
            $robots['follow'] = 'follow';
            break;
        }
    }

    return $robots;
});
add_action('wp_head', function () {

    // chỉ trên category / brand
    if ( ! is_product_category() && ! is_tax('product_brand') ) {
        return;
    }

    $query = $_SERVER['QUERY_STRING'] ?? '';
    if ( empty($query) ) {
        return;
    }

    $blocked = [
        'attribute_',
        'filter_',
        'min_price',
        'max_price',
        'orderby',
        'gclid',
        'fbclid',
        'PageSpeed',
        'add_to_cart',
        'feed',
        'v=',
    ];

    foreach ($blocked as $key) {
        if (strpos($query, $key) !== false) {

            $term = get_queried_object();
            if ($term instanceof WP_Term) {
                $canonical = get_term_link($term);
                echo '<link rel="canonical" href="' . esc_url($canonical) . "\" />\n";
            }

            break;
        }
    }
}, 1);
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