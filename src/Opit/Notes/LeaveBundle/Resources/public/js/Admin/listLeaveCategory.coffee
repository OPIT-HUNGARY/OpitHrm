$("#addLeaveCategory").click ->
    $.ajax
        method: 'GET'
        url: Routing.generate 'OpitNotesLeaveBundle_admin_show_leave_category', id: 0
    .done (data) ->
        $('<div id="dialog-editleavecategory"></div>').html(data)
            .dialog
                title: '<i class="fa fa-list-alt"></i> Create leave category'
                width: 750
                modal: on
                buttons:
                    Create: ->
                        $.ajax
                            type: 'POST'
                            global: false
                            url: Routing.generate 'OpitNotesLeaveBundle_admin_add_leave_category', id: 0
                            data: $('#addleavecategory_frm').serialize()
                        .done (data)->
                            response = data
                            $.ajax
                                type: 'POST'
                                global: false
                                url: Routing.generate 'OpitNotesLeaveBundle_admin_list_leave_categories'
                                data: "showList" : 1
                            .done (data)->
                                $('#form-leavecategory').html data
                                validationResult = $(document).data('notes').funcs.showAlert response, "create", "Leave category created successfully"
                                if validationResult is true
                                    $('#dialog-editleavecategory').dialog "destroy"
                    Close: ->
                        $('#dialog-editleavecategory').dialog "destroy"
                        return
            return
        return

$("#list-table").on "click", ".list-leavecategory", ->
    id = $(@).attr "data-id"
    $.ajax
        method: 'GET'
        url: Routing.generate 'OpitNotesLeaveBundle_admin_show_leave_category', id: id
    .done (data) ->
        $('<div id="dialog-editleavecategory"></div>').html(data)
            .dialog
                title: '<i class="fa fa-list-alt"></i> Edit leave category'
                width: 750
                modal: on
                buttons:
                    Save: ->
                        $.ajax
                            type: 'POST'
                            global: false
                            url: Routing.generate 'OpitNotesLeaveBundle_admin_add_leave_category', id: id
                            data: $('#addleavecategory_frm').serialize()
                        .done (data)->
                            response = data
                            $.ajax
                                type: 'POST'
                                global: false
                                url: Routing.generate 'OpitNotesLeaveBundle_admin_list_leave_categories'
                                data: "showList" : 1
                            .done (data)->
                                $('#form-leavecategory').html data
                                validationResult = $(document).data('notes').funcs.showAlert response, "create", "Leave category modified successfully"
                                if validationResult is true
                                    $('#dialog-editleavecategory').dialog "destroy"
                    Close: ->
                        $('#dialog-editleavecategory').dialog "destroy"
                        return
            return
        return

# Delete button
$('#delete').click ->    
    do deleteLeaveCategory

# Delete icon in the table row
$('#form-leavecategory').on "click", ".delete-single-leavecategory", ->
    event.preventDefault()
    $checkbox = $(@).closest('tr').find ':checkbox'
    $checkbox.prop 'checked', true
    do deleteLeaveCategory

# Call the deleteAction from the app main.js
deleteLeaveCategory = () ->  
    url = Routing.generate 'OpitNotesLeaveBundle_admin_delete_leave_category'
    $(document).data('notes').funcs.deleteAction('Leave category delete', 'leave category(s)', url, '.list-delete-leavecategory')

inverse = false
$('form').on 'click', '.fa-sort', ->
    inverse = $(document).data('notes').funcs.clientSideListOrdering $(@), inverse
    
$('form').on 'click', '.order-text', ->
    inverse = $(document).data('notes').funcs.clientSideListOrdering $(@).parent().find('i'), inverse