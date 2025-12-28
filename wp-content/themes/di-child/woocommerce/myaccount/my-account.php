<?php
/**
 * My Account page
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/my-account.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.5.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * My Account navigation.
 *
 * @since 2.6.0
 */
do_action( 'woocommerce_account_navigation' ); ?>

<div class="woocommerce-MyAccount-content">
    <?php
    $current_user = wp_get_current_user();//vucamp
    if ($current_user->ID === 0) {
        return;
    }
    $user_roles = $current_user->roles;
    $user_role = array_shift($user_roles);

    function displayMembership($role, $imagePath, $memberClass, $memberName) {
        echo '<div class="member-ship text-center">
        <div class="wd-image text-center"><img decoding="async" width="100" height="100" src="' . $imagePath . '" alt="Thành viên ' . $memberName . '"></div>
        <span>Bạn đang là Thành viên <span class="' . $memberClass . '">' . $memberName . '</span> - <a href="https://dioutdoor.vn/chinh-sach-thanh-vien-uu-dai-khach-hang">Tìm hiểu quyền lợi của bạn <span class="wd-icon-warning-sign"></span></a></span>
<a href="#" class="update-tk" style=" font-weight: bold; text-transform: uppercase; display: block;">Yêu cầu cập nhật thông tin.</a></div>';
    }

    switch ($user_role) {
        case 'customer':
            displayMembership($user_role, 'https://dioutdoor.vn/media/2023/12/member-customer-65x65.png', 'member-customer', 'CUSTOMER');
            break;
        case 'iron':
            displayMembership($user_role, 'https://dioutdoor.vn/media/2023/12/member-iron-65x65.png', 'member-iron', 'IRON');
            break;
        case 'stainless':
            displayMembership($user_role, 'https://dioutdoor.vn/media/2023/12/member-stainless-65x65.png', 'member-stainless', 'STAINLESS STEEL');
            break;
        case 'aluminum':
            displayMembership($user_role, 'https://dioutdoor.vn/media/2023/12/member-aluminum-65x65.png', 'member-aluminum', 'ALUMINUM');
            break;
        case 'titanium':
            displayMembership($user_role, 'https://dioutdoor.vn/media/2023/12/member-titanium-65x65.png', 'member-titanium', 'TITANIUM');
            break;
        case 'administrator':
            displayMembership($user_role, 'https://dioutdoor.vn/media/2023/12/member-titanium-65x65.png', 'member-titanium', 'TITANIUM');
            break;
        default:
            echo '<strong>' . $user_role . '</strong>';
            break;
    }
    ?>
	<?php
		/**
		 * My Account content.
		 *
		 * @since 2.6.0
		 */
		do_action( 'woocommerce_account_content' );
	?>
</div>
