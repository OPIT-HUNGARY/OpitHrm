# Get the dates of the year
$('#generate').click (event) ->
    event.preventDefault()
    $(@).attr 'disabled', 'disabled'
    $(@).addClass 'button-disabled'

    $.ajax
        method: 'POST'
        url: Routing.generate 'OpitNotesLeaveBundle_timesheet_list'
        data: 'showList' : 1
    .done (data) ->
        $('#leave_list').html data
        return

inverse = false
$('form').on 'click', '.fa-sort', ->
    inverse = $(document).data('notes').funcs.clientSideListOrdering $(@), inverse

$('form').on 'click', '.order-text', ->
    inverse = $(document).data('notes').funcs.clientSideListOrdering $(@).parent().find('i'), inverse
