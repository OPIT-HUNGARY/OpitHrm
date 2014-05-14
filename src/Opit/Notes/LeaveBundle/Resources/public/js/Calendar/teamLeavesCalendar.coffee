$(document).ready ->
    $("#team-leaves-calendar").fullCalendar
        editable: false,
        selectable: false,
        events: Routing.generate('OpitNotesLeaveBundle_calendar_team_employees')
        
    styles = ''
    # loop through all elements with class team-employee
    $('.team-employee').each ->
        elementClass = $(@).data 'class'
        backgroundColor = "rgb(#{ Math.floor(Math.random() * 150) }, #{ Math.floor(Math.random() * 150) }, #{ Math.floor(Math.random() * 150) })"
        styles += ".#{ elementClass } { background: #{ backgroundColor } !important; }"
    $( "<style>#{ styles }</style>" ).appendTo 'head'