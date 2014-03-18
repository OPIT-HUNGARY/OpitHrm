$("#addJobTitle").click ->
    $.ajax
        method: 'GET'
        url: Routing.generate 'OpitNotesUserBundle_admin_show_jobtitle', id: 0
    .done (data) ->
        $('<div id="dialog-editjobtitle"></div>').html(data)
            .dialog
                open: ->
                    $('.ui-dialog-title').append '<i class="fa fa-list-alt"></i> Create job title'
                width: 750
                modal: on
                buttons:
                    Create: ->
                        $.ajax
                            type: 'POST'
                            global: false
                            url: Routing.generate 'OpitNotesUserBundle_admin_add_jobtitle', id: 0
                            data: $('#addjobtitle_frm').serialize()
                        .done (data)->
                            response = data
                            $.ajax
                                type: 'POST'
                                global: false
                                url: Routing.generate 'OpitNotesUserBundle_admin_list_jobtitle'
                                data: "showList" : 1
                            .done (data)->
                                $('#list-table').html data
                                validationResult = $(document).data('notes').funcs.showAlert response, "create", "Job title created successfully"
                                if validationResult is true
                                    $('#dialog-editjobtitle').dialog "destroy"
                    Close: ->
                        $('#dialog-editjobtitle').dialog "destroy"
                        return
            return
        return

$("#list-table").on "click", ".list-jobtitle", ->
    id = $(@).attr "data-id"
    $.ajax
        method: 'GET'
        url: Routing.generate 'OpitNotesUserBundle_admin_show_jobtitle', id: id
    .done (data) ->
        $('<div id="dialog-editjobtitle"></div>').html(data)
            .dialog
                open: ->
                    $('.ui-dialog-title').append '<i class="fa fa-list-alt"></i> Edit job title'
                width: 750
                modal: on
                buttons:
                    Save: ->
                        $.ajax
                            type: 'POST'
                            global: false
                            url: Routing.generate 'OpitNotesUserBundle_admin_add_jobtitle', id: id
                            data: $('#addjobtitle_frm').serialize()
                        .done (data)->
                            response = data
                            $.ajax
                                type: 'POST'
                                global: false
                                url: Routing.generate 'OpitNotesUserBundle_admin_list_jobtitle'
                                data: "showList" : 1
                            .done (data)->
                                $('#list-table').html data
                                validationResult = $(document).data('notes').funcs.showAlert response, "create", "Job title modified successfully"
                                if validationResult is true
                                    $('#dialog-editjobtitle').dialog "destroy"
                    Close: ->
                        $('#dialog-editjobtitle').dialog "destroy"
                        return
            return
        return

# Delete button
$('#delete').click ->    
    deleteJobTitle()

# Delete icon in the table row
$('#list-table').on "click", ".delete-single-jobtitle", ->
    $checkbox = $(@).closest('tr').find(':checkbox')
    $checkbox.prop 'checked', true
    deleteJobTitle()

# Call the deleteAction from the app main.js
deleteJobTitle = () ->  
    url = Routing.generate 'OpitNotesUserBundle_admin_delete_jobtitle'
    $(document).data('notes').funcs.deleteAction('Job title delete', 'job title(s)', url, '.list-delete-jobtitle')

$('#list-table').on "click", "th .fa-trash-o", ->
      $('.list-delete-jobtitle').filter(() -> return not @.disabled).checkAll()

inverse = false
$('form').on 'click', '.fa-sort', ->
    inverse = $(document).data('notes').funcs.clientSideListOrdering $(@), inverse
    
$('form').on 'click', '.order-text', ->
    inverse = $(document).data('notes').funcs.clientSideListOrdering $(@).parent().find('i'), inverse