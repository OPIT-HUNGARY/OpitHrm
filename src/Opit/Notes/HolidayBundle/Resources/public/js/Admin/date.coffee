$("#addHolidayDate").click ->
    $.ajax
        method: 'GET'
        url: Routing.generate 'OpitNotesHolidayBundle_admin_show_holiday_date', id: 0
    .done (data) ->
        $('<div id="dialog-editholidaydate"></div>').html(data)
            .dialog
                title: '<i class="fa fa-list-alt"></i> Create holiday date'
                width: 750
                modal: on
                buttons:
                    Create: ->
                        $.ajax
                            type: 'POST'
                            global: false
                            url: Routing.generate 'OpitNotesHolidayBundle_admin_add_holiday_date', id: 0
                            data: $('#addholidaydate_frm').serialize()
                        .done (data)->
                            response = data
                            $.ajax
                                type: 'POST'
                                global: false
                                url: Routing.generate 'OpitNotesHolidayBundle_admin_list_holiday_dates'
                                data: "showList" : 1
                            .done (data)->
                                $('#list-table').html data
                                validationResult = $(document).data('notes').funcs.showAlert response, "create", "Holiday date created successfully"
                                if validationResult is true
                                    $('#dialog-editholidaydate').dialog "destroy"
                    Close: ->
                        $('#dialog-editholidaydate').dialog "destroy"
                        return
            return
        return

$("#list-table").on "click", ".list-holidaydate", ->
    id = $(@).attr "data-id"
    $.ajax
        method: 'GET'
        url: Routing.generate 'OpitNotesHolidayBundle_admin_show_holiday_date', id: id
    .done (data) ->
        $('<div id="dialog-editholidaydate"></div>').html(data)
            .dialog
                title: '<i class="fa fa-list-alt"></i> Edit holiday date'
                width: 750
                modal: on
                buttons:
                    Save: ->
                        $.ajax
                            type: 'POST'
                            global: false
                            url: Routing.generate 'OpitNotesHolidayBundle_admin_add_holiday_date', id: id
                            data: $('#addholidaydate_frm').serialize()
                        .done (data)->
                            response = data
                            $.ajax
                                type: 'POST'
                                global: false
                                url: Routing.generate 'OpitNotesHolidayBundle_admin_list_holiday_dates'
                                data: "showList" : 1
                            .done (data)->
                                $('#list-table').html data
                                validationResult = $(document).data('notes').funcs.showAlert response, "create", "Holiday date modified successfully"
                                if validationResult is true
                                    $('#dialog-editholidaydate').dialog "destroy"
                    Close: ->
                        $('#dialog-editholidaydate').dialog "destroy"
                        return
            return
        return

# Delete button
$('#delete').click ->
    deleteHolidayDate()

# Delete icon in the table row
$('#list-table').on "click", ".delete-single-holidaydate", ->
    $checkbox = $(@).closest('tr').find(':checkbox')
    $checkbox.prop 'checked', true
    deleteHolidayDate()

# Call the deleteAction from the app main.js
deleteHolidayDate = () ->
    url = Routing.generate 'OpitNotesHolidayBundle_admin_delete_holiday_date'
    $(document).data('notes').funcs.deleteAction('Holiday date delete', 'holiday date(s)', url, '.list-delete-holidaydate')

$('#list-table').on "click", "th .fa-trash-o", ->
      $('.list-delete-holidaydate').filter(() -> return not @.disabled).checkAll()

inverse = false
$('form').on 'click', '.fa-sort', ->
    inverse = $(document).data('notes').funcs.clientSideListOrdering $(@), inverse

$('form').on 'click', '.order-text', ->
    inverse = $(document).data('notes').funcs.clientSideListOrdering $(@).parent().find('i'), inverse