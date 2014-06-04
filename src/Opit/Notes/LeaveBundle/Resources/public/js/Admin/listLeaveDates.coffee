$("#addLeaveDate").click ->
    $.ajax
        method: 'GET'
        url: Routing.generate 'OpitNotesLeaveBundle_admin_show_leave_date', id: 0
    .done (data) ->
        $('<div id="dialog-editleavedate"></div>').html(data)
            .dialog
                title: '<i class="fa fa-list-alt"></i> Create Administrative Leave/Working Day'
                width: 750
                modal: on
                open: -> $(document).data('notes').funcs.initDateInputs $('#addleavedate_frm')
                buttons:
                    Create: ->
                        $.ajax
                            type: 'POST'
                            global: false
                            url: Routing.generate 'OpitNotesLeaveBundle_admin_add_leave_date', id: 0
                            data: $('#addleavedate_frm').serialize()
                        .done (data)->
                            response = data
                            $.ajax
                                type: 'POST'
                                global: false
                                url: Routing.generate 'OpitNotesLeaveBundle_admin_list_leave_dates'
                                data: "showList" : 1
                            .done (data)->
                                $('#form-leavedate').html data
                                $(document).data('notes').funcs.initListPageListeners()
                                $(document).data('notes').funcs.initDeleteMultipleListener()
                                validationResult = $(document).data('notes').funcs.showAlert $('#dialog-editleavedate'), response, "create", "Administrative Leave/Working Day created successfully"
                                if validationResult is true
                                    $('#dialog-editleavedate').dialog "destroy"
                    Close: ->
                        $('#dialog-editleavedate').dialog "destroy"
                        return
            return
        return

$("#form-leavedate").on "click", ".list-leavedate", (event) ->
    event.preventDefault()
    id = $(@).attr "data-id"
    $.ajax
        method: 'GET'
        url: Routing.generate 'OpitNotesLeaveBundle_admin_show_leave_date', id: id
    .done (data) ->
        $('<div id="dialog-editleavedate"></div>').html(data)
            .dialog
                title: '<i class="fa fa-list-alt"></i> Edit Administrative Leave/Working Day'
                width: 750
                modal: on
                open: -> $(document).data('notes').funcs.initDateInputs $('#addleavedate_frm')
                buttons:
                    Save: ->
                        $.ajax
                            type: 'POST'
                            global: false
                            url: Routing.generate 'OpitNotesLeaveBundle_admin_add_leave_date', id: id
                            data: $('#addleavedate_frm').serialize()
                        .done (data)->
                            response = data
                            $.ajax
                                type: 'POST'
                                global: false
                                url: Routing.generate 'OpitNotesLeaveBundle_admin_list_leave_dates'
                                data: "showList" : 1
                            .done (data)->
                                $('#form-leavedate').html data
                                $(document).data('notes').funcs.initListPageListeners()
                                $(document).data('notes').funcs.initDeleteMultipleListener()
                                validationResult = $(document).data('notes').funcs.showAlert $('#dialog-editleavedate'), response, "create", "Administrative Leave/Working Day modified successfully"
                                if validationResult is true
                                    $('#dialog-editleavedate').dialog "destroy"
                    Close: ->
                        $('#dialog-editleavedate').dialog "destroy"
                        return
            return
        return

# Init search scrollbar plugin
$('ul.scrollbar').mCustomScrollbar()

# Delete button
$('#delete').click ->
    do deleteLeaveDate

# Delete icon in the table row
$('#form-leavedate').on "click", ".delete-single-leavedate", (event) ->
    event.preventDefault()
    $checkbox = $(@).closest('tr').find ':checkbox'
    $checkbox.prop 'checked', true
    do deleteLeaveDate

# Call the deleteAction from the app main.js
deleteLeaveDate = () ->
    url = Routing.generate 'OpitNotesLeaveBundle_admin_delete_leave_date'
    $(document).data('notes').funcs.deleteAction 'Leave date delete', 'leave date(s)', url, '.list-delete-leavedate'

inverse = false
$('#form-leavedate').on 'click', '.fa-sort', ->
    inverse = $(document).data('notes').funcs.clientSideListOrdering $(@), inverse

$('#form-leavedate').on 'click', '.order-text', ->
    inverse = $(document).data('notes').funcs.clientSideListOrdering $(@).parent().find('i'), inverse