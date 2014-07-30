$('article h3').on 'click', ->
    $control = $(@).children('i')
    $area = $(@).parent().children('ul')

    # Check if toogle animation is running to prevent a new animation
    if not $area.is(':animated')
        $control.toggleClass('fa-chevron-down')
        $area.slideToggle()

    return