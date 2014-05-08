$("#addHolidayType").click ->
    $.ajax
        method: 'GET'
        url: Routing.generate 'OpitNotesHolidayBundle_admin_show_holiday_type', id: 0
    .done (data) ->
        $('<div id="dialog-editholidaytype"></div>').html(data)
            .dialog
                title: '<i class="fa fa-list-alt"></i> Create Administrative Leave/Working Day type'
                width: 750
                modal: on
                buttons:
                    Create: ->
                        $.ajax
                            type: 'POST'
                            global: false
                            url: Routing.generate 'OpitNotesHolidayBundle_admin_add_holiday_type', id: 0
                            data: $('#addholidaytype_frm').serialize()
                        .done (data)->
                            response = data
                            $.ajax
                                type: 'POST'
                                global: false
                                url: Routing.generate 'OpitNotesHolidayBundle_admin_list_holiday_types'
                                data: "showList" : 1
                            .done (data)->
                                $('#list-table').html data
                                validationResult = $(document).data('notes').funcs.showAlert response, "create", "Holiday type created successfully"
                                if validationResult is true
                                    $('#dialog-editholidaytype').dialog "destroy"
                    Close: ->
                        $('#dialog-editholidaytype').dialog "destroy"
                        return
            return
        return

$("#list-table").on "click", ".list-holidaytype", ->
    id = $(@).attr "data-id"
    $.ajax
        method: 'GET'
        url: Routing.generate 'OpitNotesHolidayBundle_admin_show_holiday_type', id: id
    .done (data) ->
        $('<div id="dialog-editholidaytype"></div>').html(data)
            .dialog
                title: '<i class="fa fa-list-alt"></i> Edit Administrative Leave/Working Day type'
                width: 750
                modal: on
                buttons:
                    Save: ->
                        $.ajax
                            type: 'POST'
                            global: false
                            url: Routing.generate 'OpitNotesHolidayBundle_admin_add_holiday_type', id: id
                            data: $('#addholidaytype_frm').serialize()
                        .done (data)->
                            response = data
                            $.ajax
                                type: 'POST'
                                global: false
                                url: Routing.generate 'OpitNotesHolidayBundle_admin_list_holiday_types'
                                data: "showList" : 1
                            .done (data)->
                                $('#list-table').html data
                                validationResult = $(document).data('notes').funcs.showAlert response, "create", "Administrative Leave/Working Day type modified successfully"
                                if validationResult is true
                                    $('#dialog-editholidaytype').dialog "destroy"
                    Close: ->
                        $('#dialog-editholidaytype').dialog "destroy"
                        return
            return
        return

# Delete button
$('#delete').click ->
    deleteHolidayType()

# Delete icon in the table row
$('#list-table').on "click", ".delete-single-holidaytype", ->
    $checkbox = $(@).closest('tr').find(':checkbox')
    $checkbox.prop 'checked', true
    deleteHolidayType()

# Call the deleteAction from the app main.js
deleteHolidayType = () ->
    url = Routing.generate 'OpitNotesHolidayBundle_admin_delete_holiday_type'
    $(document).data('notes').funcs.deleteAction('Holiday type delete', 'holiday type(s)', url, '.list-delete-holidaytype')

$('#list-table').on "click", "th .fa-trash-o", ->
      $('.list-delete-holidaytype').filter(() -> return not @.disabled).checkAll()

inverse = false
$('form').on 'click', '.fa-sort', ->
    inverse = $(document).data('notes').funcs.clientSideListOrdering $(@), inverse

$('form').on 'click', '.order-text', ->
    inverse = $(document).data('notes').funcs.clientSideListOrdering $(@).parent().find('i'), inverse