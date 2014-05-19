$.fn.bgPainter = (options = {}, container) ->
    defaultOptions =
        bgColors: [200, 200, 200]
        substractor1: 50
        substractor2: 20
        
    # merge passed options
    $.extend true, defaultOptions, options
    $container = if container then $(container) else $(@)
    
    styles = ''
    colorIndex = 0
    bgColors = defaultOptions.bgColors.slice 0
    # loop through all elements with class team-employee
    $container.each ->
        elementClass = $(@).data 'class'
        # if current working color leq 0
        if bgColors[colorIndex] <= 0
            # if colors are still left in array
            if colorIndex < (bgColors.length - 1)
                colorIndex++
            else
                colorIndex = 0
            bgColors = defaultOptions.bgColors.slice 0
            
        # loop through all colors
        x = 0
        while x < bgColors.length
            if x is colorIndex
                bgColors[colorIndex] = bgColors[colorIndex] - defaultOptions.substractor1
            else
                bgColors[x] = bgColors[x] - defaultOptions.substractor2
            x++
            
        styles += ".#{ elementClass } { background: rgb(#{ bgColors[0] }, #{ bgColors[1] }, #{ bgColors[2] }) !important; }"
        
    $( "<style>#{ styles }</style>" ).appendTo 'head'
    return

$(document).ready ->
    $("#team-leaves-calendar").fullCalendar
        editable: false,
        selectable: false,
        events: Routing.generate('OpitNotesLeaveBundle_calendar_team_employees')
        
    $('.team-employee').bgPainter()