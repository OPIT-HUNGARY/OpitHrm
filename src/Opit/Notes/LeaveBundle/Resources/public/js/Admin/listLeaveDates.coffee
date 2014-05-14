$("#addHolidayDate").click ->
    $.ajax
        method: 'GET'
        url: Routing.generate 'OpitNotesLeaveBundle_admin_show_leave_date', id: 0
    .done (data) ->
        $('<div id="dialog-editholidaydate"></div>').html(data)
            .dialog
                title: '<i class="fa fa-list-alt"></i> Create Administrative Leave/Working Day'
                width: 750
                modal: on
                open: -> $(document).data('notes').funcs.initDateInputs $('#addholidaydate_frm')
                buttons:
                    Create: ->
                        $.ajax
                            type: 'POST'
                            global: false
                            url: Routing.generate 'OpitNotesLeaveBundle_admin_add_leave_date', id: 0
                            data: $('#addholidaydate_frm').serialize()
                        .done (data)->
                            response = data
                            $.ajax
                                type: 'POST'
                                global: false
                                url: Routing.generate 'OpitNotesLeaveBundle_admin_list_leave_dates'
                                data: "showList" : 1
                            .done (data)->
                                $('#list-table').html data
                                validationResult = $(document).data('notes').funcs.showAlert response, "create", "Administrative Leave/Working Day created successfully"
                                if validationResult is true
                                    $('#dialog-editholidaydate').dialog "destroy"
                    Close: ->
                        $('#dialog-editholidaydate').dialog "destroy"
                        return
            return
        return

$("#list-table").on "click", ".list-holidaydate", (event) ->
    event.preventDefault()
    id = $(@).attr "data-id"
    $.ajax
        method: 'GET'
        url: Routing.generate 'OpitNotesLeaveBundle_admin_show_leave_date', id: id
    .done (data) ->
        $('<div id="dialog-editholidaydate"></div>').html(data)
            .dialog
                title: '<i class="fa fa-list-alt"></i> Edit Administrative Leave/Working Day'
                width: 750
                modal: on
                open: -> $(document).data('notes').funcs.initDateInputs $('#addholidaydate_frm')
                buttons:
                    Save: ->
                        $.ajax
                            type: 'POST'
                            global: false
                            url: Routing.generate 'OpitNotesLeaveBundle_admin_add_leave_date', id: id
                            data: $('#addholidaydate_frm').serialize()
                        .done (data)->
                            response = data
                            $.ajax
                                type: 'POST'
                                global: false
                                url: Routing.generate 'OpitNotesLeaveBundle_admin_list_leave_dates'
                                data: "showList" : 1
                            .done (data)->
                                $('#list-table').html data
                                validationResult = $(document).data('notes').funcs.showAlert response, "create", "Administrative Leave/Working Day modified successfully"
                                if validationResult is true
                                    $('#dialog-editholidaydate').dialog "destroy"
                    Close: ->
                        $('#dialog-editholidaydate').dialog "destroy"
                        return
            return
        return

# Get the dates of the year
$('.year').click (event) ->
    # Set the classes
    $('.year').removeClass 'selected-page'
    $(@).addClass 'selected-page'

    $.ajax
        method: 'POST'
        url: Routing.generate 'OpitNotesLeaveBundle_admin_list_leave_dates'
        data: 'showList' : 1, 'year' : $(@).data('year')
    .done (data) ->
        $('#list-table').html data
        return

# Delete button
$('#delete').click ->
    deleteHolidayDate()

# Delete icon in the table row
$('#list-table').on "click", ".delete-single-holidaydate", (event) ->
    event.preventDefault()
    $checkbox = $(@).closest('tr').find(':checkbox')
    $checkbox.prop 'checked', true
    deleteHolidayDate()

# Call the deleteAction from the app main.js
deleteHolidayDate = () ->
    url = Routing.generate 'OpitNotesLeaveBundle_admin_delete_leave_date'
    $(document).data('notes').funcs.deleteAction('Holiday date delete', 'holiday date(s)', url, '.list-delete-holidaydate')

$('#list-table').on "click", "th .fa-trash-o", ->
    group = $(@).closest('table').attr('id')
    $("##{group} .list-delete-holidaydate").filter(() -> return not @.disabled).checkAll()

inverse = false
$('form').on 'click', '.fa-sort', ->
    inverse = $(document).data('notes').funcs.clientSideListOrdering $(@), inverse

$('form').on 'click', '.order-text', ->
    inverse = $(document).data('notes').funcs.clientSideListOrdering $(@).parent().find('i'), inverse

$(document).ready ->
    # Remove the mousedown event from the year classes.
    $('.year').bind 'mousedown', false
    $('.year:first').addClass 'selected-page'