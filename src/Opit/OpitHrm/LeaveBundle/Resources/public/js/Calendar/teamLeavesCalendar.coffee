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

# Register visual export button event listeners
$('#leave-calendar-container #export-button').hover(
    -> $(@).addClass('fc-state-hover'),
    -> $(@).removeClass('fc-state-hover')
)

# Register calendar export event listener
$('#leave-calendar-container #export-button').on 'click.export', ->
    # Fetch current calendar view object
    $calendarView = $("#team-leaves-calendar").fullCalendar 'getView'
    # We want to send data as attachment, traditional form submission is used
    url = Routing.generate 'OpitOpitHrmLeaveBundle_calendar_team_leaves_export'
    data =
        start: $.datepicker.formatDate($.datepicker.ISO_8601, new Date($calendarView.visStart)),
        end: $.datepicker.formatDate($.datepicker.ISO_8601, new Date($calendarView.visEnd)),
        title: $calendarView.title
        team: $('#calendar-team-selector').val()

    # Call form submission helper
    $.fn.download(url, data)

    return

renderCalendar = (teamId) ->
    $teamLeaveCalendar = $('#team-leaves-calendar')
    $teamLeaveCalendar.html('')
    $teamLeaveCalendar.fullCalendar
        editable: false
        selectable: false
        firstDay: 1
        events:
            url: Routing.generate('OpitOpitHrmLeaveBundle_calendar_team_employees')
            data:
                team: teamId
        eventAfterRender: (event, element, view) ->
            if teamId > 0
                # Show employees that are in the selected team
                className = event.className[0]
                $(".team-employee.team-#{teamId}").removeClass 'display-none-important'
            else
                # Show all employees
                $('.team-employee').removeClass 'display-none-important'

            if event.className.length > 1
                date = event.className[2].split('_')[1]
                $('#leave-calendar-container').find("[data-date='" + date + "']").addClass 'background-color-default-red'

            $('.team-load-indicator').remove()
            $('#calendar-team-selector').removeAttr 'disabled'
            $('#calendar-team-selector').removeClass 'dropdown-disabled'

    $('.team-employee').bgPainter()

$('#calendar-team-selector').on 'change', ->
    $(@).after $('<i>').addClass('fa fa-spinner fa-spin margin-left-5 margin-top-5 team-load-indicator')
    $(@).attr 'disabled', 'disabled'
    $(@).addClass 'dropdown-disabled'
    # Hide all employees
    $('.team-employee').addClass 'display-none-important'
    renderCalendar $(@).val()

$(document).ready ->
    renderCalendar 0