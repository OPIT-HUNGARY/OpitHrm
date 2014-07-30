$("#addLeaveDate").click ->
    $.ajax
        method: 'GET'
        url: Routing.generate 'OpitOpitHrmLeaveBundle_admin_show_leave_date', id: 0
    .done (data) ->
        $('<div id="dialog-editleavedate"></div>').html(data)
            .dialog
                title: '<i class="fa fa-list-alt"></i> Create Administrative Leave/Working Day'
                width: 750
                modal: on
                open: -> $(document).data('opithrm').funcs.initDateInputs $('#addleavedate_frm')
                buttons:
                    Create: ->
                        $.ajax
                            type: 'POST'
                            global: false
                            url: Routing.generate 'OpitOpitHrmLeaveBundle_admin_add_leave_date', id: 0
                            data: $('#addleavedate_frm').serialize()
                        .done (data)->
                            response = data
                            $.ajax
                                type: 'POST'
                                global: false
                                url: Routing.generate 'OpitOpitHrmLeaveBundle_admin_list_leave_dates'
                                data: "showList" : 1
                            .done (data)->
                                $('#form-leavedate').html data
                                $(document).data('opithrm').funcs.initListPageListeners()
                                $(document).data('opithrm').funcs.initDeleteMultipleListener()
                                $(document).data('opithrm').funcs.showAlert $('#dialog-editleavedate'), response, "create", "Administrative Leave/Working Day created successfully"
                                $('#dialog-editleavedate').dialog "destroy"
                        .fail (jqXHR, textStatus, errorThrown) ->
                            $(document).data('opithrm').funcs.showAlert $('#dialog-editleavedate'), $.parseJSON(jqXHR.responseText), "create", "The leave date must be in the future."
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
        url: Routing.generate 'OpitOpitHrmLeaveBundle_admin_show_leave_date', id: id
    .done (data) ->
        $('<div id="dialog-editleavedate"></div>').html(data)
            .dialog
                title: '<i class="fa fa-list-alt"></i> Edit Administrative Leave/Working Day'
                width: 750
                modal: on
                open: -> $(document).data('opithrm').funcs.initDateInputs $('#addleavedate_frm')
                buttons:
                    Edit: ->
                        $.ajax
                            type: 'POST'
                            global: false
                            url: Routing.generate 'OpitOpitHrmLeaveBundle_admin_add_leave_date', id: id
                            data: $('#addleavedate_frm').serialize()
                        .done (data)->
                            response = data
                            $.ajax
                                type: 'POST'
                                global: false
                                url: Routing.generate 'OpitOpitHrmLeaveBundle_admin_list_leave_dates'
                                data: "showList" : 1
                            .done (data)->
                                $('#form-leavedate').html data
                                $(document).data('opithrm').funcs.initListPageListeners()
                                $(document).data('opithrm').funcs.initDeleteMultipleListener()
                                $(document).data('opithrm').funcs.showAlert $('#dialog-editleavedate'), response, "create", "Administrative Leave/Working Day modified successfully"
                                $('#dialog-editleavedate').dialog "destroy"
                        .fail (jqXHR, textStatus, errorThrown) ->
                            $(document).data('opithrm').funcs.showAlert $('#dialog-editleavedate'), $.parseJSON(jqXHR.responseText), "create", "The leave date must be in the future."
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
    url = Routing.generate 'OpitOpitHrmLeaveBundle_admin_delete_leave_date'
    $(document).data('opithrm').funcs.deleteAction 'Leave date delete', 'leave date(s)', url, '.list-delete-leavedate'

$('#form-leavedate').on 'click', '.fa-sort', ->
    $(document).data('opithrm').funcs.serverSideListOrdering $(@), $(@).data('field'), 'OpitOpitHrmLeaveBundle_admin_list_leave_dates', 'form-leavedate'

$('#form-leavedate').on 'click', '.order-text', ->
    $orderIcon = $(@).parent().find('.fa-sort')
    $(document).data('opithrm').funcs.serverSideListOrdering $orderIcon, $orderIcon.data('field'), 'OpitOpitHrmLeaveBundle_admin_list_leave_dates', 'form-leavedate'
