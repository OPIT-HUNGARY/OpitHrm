$("#addHolidayCategory").click ->
    $.ajax
        method: 'GET'
        url: Routing.generate 'OpitNotesHolidayBundle_admin_show_holiday_category', id: 0
    .done (data) ->
        $('<div id="dialog-editholidaycategory"></div>').html(data)
            .dialog
                title: '<i class="fa fa-list-alt"></i> Create holiday category'
                width: 750
                modal: on
                buttons:
                    Create: ->
                        $.ajax
                            type: 'POST'
                            global: false
                            url: Routing.generate 'OpitNotesHolidayBundle_admin_add_holiday_category', id: 0
                            data: $('#addholidaycategory_frm').serialize()
                        .done (data)->
                            response = data
                            $.ajax
                                type: 'POST'
                                global: false
                                url: Routing.generate 'OpitNotesHolidayBundle_admin_list_holiday_categories'
                                data: "showList" : 1
                            .done (data)->
                                $('#list-table').html data
                                validationResult = $(document).data('notes').funcs.showAlert response, "create", "Holiday category created successfully"
                                if validationResult is true
                                    $('#dialog-editholidaycategory').dialog "destroy"
                    Close: ->
                        $('#dialog-editholidaycategory').dialog "destroy"
                        return
            return
        return

$("#list-table").on "click", ".list-holidaycategory", ->
    id = $(@).attr "data-id"
    $.ajax
        method: 'GET'
        url: Routing.generate 'OpitNotesHolidayBundle_admin_show_holiday_category', id: id
    .done (data) ->
        $('<div id="dialog-editholidaycategory"></div>').html(data)
            .dialog
                title: '<i class="fa fa-list-alt"></i> Edit holiday category'
                width: 750
                modal: on
                buttons:
                    Save: ->
                        $.ajax
                            type: 'POST'
                            global: false
                            url: Routing.generate 'OpitNotesHolidayBundle_admin_add_holiday_category', id: id
                            data: $('#addholidaycategory_frm').serialize()
                        .done (data)->
                            response = data
                            $.ajax
                                type: 'POST'
                                global: false
                                url: Routing.generate 'OpitNotesHolidayBundle_admin_list_holiday_categories'
                                data: "showList" : 1
                            .done (data)->
                                $('#list-table').html data
                                validationResult = $(document).data('notes').funcs.showAlert response, "create", "Holiday category modified successfully"
                                if validationResult is true
                                    $('#dialog-editholidaycategory').dialog "destroy"
                    Close: ->
                        $('#dialog-editholidaycategory').dialog "destroy"
                        return
            return
        return

# Delete button
$('#delete').click ->    
    deleteHolidayCategory()

# Delete icon in the table row
$('#list-table').on "click", ".delete-single-holidaycategory", ->
    $checkbox = $(@).closest('tr').find(':checkbox')
    $checkbox.prop 'checked', true
    deleteHolidayCategory()

# Call the deleteAction from the app main.js
deleteHolidayCategory = () ->  
    url = Routing.generate 'OpitNotesHolidayBundle_admin_delete_holiday_category'
    $(document).data('notes').funcs.deleteAction('Holiday category delete', 'holiday category(s)', url, '.list-delete-holidaycategory')

$('#list-table').on "click", "th .fa-trash-o", ->
      $('.list-delete-holidaycategory').filter(() -> return not @.disabled).checkAll()

inverse = false
$('form').on 'click', '.fa-sort', ->
    inverse = $(document).data('notes').funcs.clientSideListOrdering $(@), inverse
    
$('form').on 'click', '.order-text', ->
    inverse = $(document).data('notes').funcs.clientSideListOrdering $(@).parent().find('i'), inverse