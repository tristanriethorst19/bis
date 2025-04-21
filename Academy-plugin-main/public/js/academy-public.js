jQuery(document).ready(function($) {
    // Event listener for clicks on .elementor-nav-menu--toggle
    $('.elementor-nav-menu--toggle').on('click', function() {
        var $menu = $('.nav-menu-shortcode-class');
        if ($menu.length) {
            var screenWidth = $(window).width();
            var menuOffsetLeft = $menu.offset().left;
            var leftPosition = -menuOffsetLeft;

            $menu.css('width', screenWidth + 'px');
            $menu.css('left', leftPosition + 'px');
        }
    });
});
