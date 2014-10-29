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

$('#calendar-team-selector').on 'change', ->
    teamId = $(@).val()
    url = Routing.generate('OpitOpitHrmLeaveBundle_calendar_team_employees') + '?team=' + teamId
    $teamLeavesCalendar = $('#team-leaves-calendar')

    $(@).after $('<i>').addClass('fa fa-spinner fa-spin margin-left-5 margin-top-5 team-load-indicator')
    # Disable dropdown
    $(@).attr 'disabled', 'disabled'
    $(@).addClass 'dropdown-disabled'
    # Hide all employees
    $('.team-employee').addClass 'display-none-important'

    # Remove all events from calendar
    $teamLeavesCalendar.fullCalendar 'removeEvents'
    # Remove event source from calendar event sources
    $teamLeavesCalendar.fullCalendar 'removeEventSource', $teamLeavesCalendar.data('url')
    # Add new event source to calendar (autofetches events)
    $teamLeavesCalendar.data 'url', url
    $teamLeavesCalendar.fullCalendar 'addEventSource', url

$('#toggle-calendar-size').on 'click', ->
    $calendarContainer = $('#leave-calendar-container')
    calendarWidth = window.innerHeight

    if window.innerHeight > window.innerWidth
        calendarWidth = window.innerWidth

    if $calendarContainer.data('isFullScreen') is true
        $calendarContainer.data 'isFullScreen', false
        $calendarContainer.css
            'width': ''
            'margin-left': ''

        $(document.body).css
            'overflow': ''
    else
        $calendarContainer.data 'isFullScreen', true
        $calendarContainer.css
            'width': calendarWidth
            'margin-left': -(calendarWidth/2) + 'px'

        # Disable body scroll in case screen is smaller than menu width
        $(document.body).css
            'overflow': 'hidden'

    # Rotate the toggle fullscreen icon
    $(@).toggleClass 'fa-rotate-180'
    # Toggle calendar container to be fullscreen
    $calendarContainer.toggleClass 'fullscreen'
    # Show or hide overlay
    $('#leave-calendar-overlay').toggleClass 'display-none'

    # Trigger window resize for fullcalendar to resize calendar to new size
    $(window).trigger 'resize'

$(document).ready ->
    url = Routing.generate('OpitOpitHrmLeaveBundle_calendar_team_employees')
    $teamLeaveCalendar = $('#team-leaves-calendar')
    $teamLeaveCalendar.data 'url', url

    $teamLeaveCalendar.fullCalendar
        editable: false
        selectable: false
        firstDay: 1
        events:
            url: url
            data:
                team: 0
        eventAfterRender: (event, element, view) ->
            teamId = $('#calendar-team-selector').val()
            if teamId > 0
                # Show employees that are in the selected team
                $(".team-employee.team-#{teamId}").removeClass 'display-none-important'
            else
                # Show all employees
                $('.team-employee').removeClass 'display-none-important'

            # Set holiday event background color
            if event.className.length > 1
                date = event.className[2].split('_')[1]
                $('#leave-calendar-container').find("[data-date='" + date + "']").addClass 'background-color-default-red'

            $('.team-load-indicator').remove()

            # Enable dropdown
            $('#calendar-team-selector').removeAttr 'disabled'
            $('#calendar-team-selector').removeClass 'dropdown-disabled'

    $('.team-employee').bgPainter()

    $('#leave-calendar-container').data 'isFullScreen', false
    $('.fc-header-right').prepend $('#calendar-button-group')