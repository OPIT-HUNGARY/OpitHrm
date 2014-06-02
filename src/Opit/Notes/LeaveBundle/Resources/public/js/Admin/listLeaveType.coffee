$("#addLeaveType").click ->
    $.ajax
        method: 'GET'
        url: Routing.generate 'OpitNotesLeaveBundle_admin_show_leave_type', id: 0
    .done (data) ->
        $('<div id="dialog-editleavetype"></div>').html(data)
            .dialog
                title: '<i class="fa fa-list-alt"></i> Create Administrative Leave/Working Day type'
                width: 750
                modal: on
                buttons:
                    Create: ->
                        $.ajax
                            type: 'POST'
                            global: false
                            url: Routing.generate 'OpitNotesLeaveBundle_admin_add_leave_type', id: 0
                            data: $('#addleavetype_frm').serialize()
                        .done (data)->
                            response = data
                            $.ajax
                                type: 'POST'
                                global: false
                                url: Routing.generate 'OpitNotesLeaveBundle_admin_list_leave_types'
                                data: "showList" : 1
                            .done (data)->
                                $('#form-leavetype').html data
                                $(document).data('notes').funcs.initListPageListeners()
                                $(document).data('notes').funcs.initDeleteMultipleListener()
                                validationResult = $(document).data('notes').funcs.showAlert response, "create", "Leave type created successfully"
                                if validationResult is true
                                    $('#dialog-editleavetype').dialog "destroy"
                    Close: ->
                        $('#dialog-editleavetype').dialog "destroy"
                        return
            return
        return

$("#list-table").on "click", ".list-leavetype", ->
    id = $(@).attr "data-id"
    $.ajax
        method: 'GET'
        url: Routing.generate 'OpitNotesLeaveBundle_admin_show_leave_type', id: id
    .done (data) ->
        $('<div id="dialog-editleavetype"></div>').html(data)
            .dialog
                title: '<i class="fa fa-list-alt"></i> Edit Administrative Leave/Working Day type'
                width: 750
                modal: on
                buttons:
                    Save: ->
                        $.ajax
                            type: 'POST'
                            global: false
                            url: Routing.generate 'OpitNotesLeaveBundle_admin_add_leave_type', id: id
                            data: $('#addleavetype_frm').serialize()
                        .done (data)->
                            response = data
                            $.ajax
                                type: 'POST'
                                global: false
                                url: Routing.generate 'OpitNotesLeaveBundle_admin_list_leave_types'
                                data: "showList" : 1
                            .done (data)->
                                $('#form-leavetype').html data
                                $(document).data('notes').funcs.initListPageListeners()
                                $(document).data('notes').funcs.initDeleteMultipleListener()
                                validationResult = $(document).data('notes').funcs.showAlert response, "create", "Administrative Leave/Working Day type modified successfully"
                                if validationResult is true
                                    $('#dialog-editleavetype').dialog "destroy"
                    Close: ->
                        $('#dialog-editleavetype').dialog "destroy"
                        return
            return
        return

# Delete button
$('#delete').click ->
    do deleteLeaveType

# Delete icon in the table row
$('#form-leavetype').on "click", ".delete-single-leavetype", (event) ->
    event.preventDefault()
    $checkbox = $(@).closest('tr').find(':checkbox')
    $checkbox.prop 'checked', true
    do deleteLeaveType

# Call the deleteAction from the app main.js
deleteLeaveType = () ->
    url = Routing.generate 'OpitNotesLeaveBundle_admin_delete_leave_type'
    $(document).data('notes').funcs.deleteAction('Leave type delete', 'leave type(s)', url, '.list-delete-leavetype')

inverse = false
$('form').on 'click', '.fa-sort', ->
    inverse = $(document).data('notes').funcs.clientSideListOrdering $(@), inverse

$('form').on 'click', '.order-text', ->
    inverse = $(document).data('notes').funcs.clientSideListOrdering $(@).parent().find('i'), inverse