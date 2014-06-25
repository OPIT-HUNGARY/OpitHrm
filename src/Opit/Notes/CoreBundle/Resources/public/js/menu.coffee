###*
 *  This file is part of the {Bundle}.
 *
 *  (c) Opit Consulting Kft. <info@opit.hu>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 *
 *  Menu scripts
 *
 *  This file is intended to run any main menu related scripts.
 *  The related partial can be found under views -> Navigation.
 *
 *  @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 *  @version 1.0
 *  @package Opit
 *  @subpackage Notes
###

initMenu = ->
    $menu = $('#menu:first')
    $clone = $('#menu.clone')

    if $menu.length > 0 and $clone.length is 0
        $clone = $menu.clone()
        $clone.attr
            class: 'clone'
        .css
            top: 0

        $('body').append($clone);

    startHeight = parseInt($menu.offset().top) + parseInt($menu.outerHeight()) + 25

    # Scroll method for sticky header
    $(window).scroll ->
        # If page scroll is below submenu top show submenu clone
        if $menu.length > 0 and $clone.length > 0
            if startHeight < parseInt($(window).scrollTop())
                $('#menu.clone').slideDown('fast')
            else
                $('#menu.clone').slideUp('fast')

$(document).ready ->
    # Init the menu
    do initMenu if $('#menu').length > 0