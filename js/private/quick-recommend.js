// wait until the page and jQuery have loaded before running the code below
jQuery(document).ready(function ($) {

    // stop our admin menus from collapsing
    if ($('body[class*=" qrec_"]').length || $('body[class*=" post-type-qrec_"]').length) {

        $qrec_menu_li = $('#toplevel_page_qrec_dashboard_admin_page');
        $qrec_menu_li = $('#toplevel_page_qrec_dashboard_admin_page');

        $qrec_menu_li
            .removeClass('wp-not-current-submenu')
            .addClass('wp-has-current-submenu')
            .addClass('wp-menu-open');

        $('a:first', $qrec_menu_li)
            .removeClass('wp-not-current-submenu')
            .addClass('wp-has-submenu')
            .addClass('wp-has-current-submenu')
            .addClass('wp-menu-open');

    }

});
