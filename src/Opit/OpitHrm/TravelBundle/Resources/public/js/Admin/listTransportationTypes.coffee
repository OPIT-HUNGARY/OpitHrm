# Save transportation type method
saveTransportationType = (options = {}) ->
    if options.id?
        formUrl = Routing.generate 'OpitOpitHrmTravelBundle_admin_transportationtype_show', id: options.id
    else
        formUrl = Routing.generate 'OpitOpitHrmTravelBundle_admin_transportationtype_show'

    $.ajax
        type: 'POST'
        global: false
        url: formUrl
        data: $('#transportationtype_frm').serialize()
    .done (data) ->
        response = data
        $.ajax
            type: 'GET'
            url: Routing.generate 'OpitOpitHrmTravelBundle_admin_transportationtype_list'
        .done (data) ->
            $('#form-transportation-types').html data
            $(document).data('opithrm').funcs.initListPageListeners()
            $(document).data('opithrm').funcs.initDeleteMultipleListener()

            $(document).data('opithrm').funcs.showAlert $('#dialog-transportation-type-form'), response, "create", options.response?.message
            $('#dialog-transportation-type-form').dialog "destroy"
    .fail (jqXHR, textStatus, errorThrown) ->
        $(document).data('opithrm').funcs.showAlert $('#dialog-transportation-type-form'), $.parseJSON(jqXHR.responseText), "create"

    return true

# Add transportation type event handler
$("#list-controls-table button#add").click ->
    $.ajax
        method: 'GET'
        url: Routing.generate 'OpitOpitHrmTravelBundle_admin_transportationtype_show'
    .done (data) ->
        $('<div id="dialog-transportation-type-form"></div>').html(data)
            .dialog
                title: '<i class="fa fa-list-alt"></i> Create transportation type'
                width: 500
                modal: on
                buttons:
                    Create: ->
                        saveTransportationType 'response': { 'message': 'Transportation type successfully created' }
                    Close: ->
                        $(@).dialog "destroy"
                        return
            return
        return

# Edit transportation type event handler
$("#transportation-type-list").on "click", ".list-type", ->
    id = $(@).data "id"
    $.ajax
        method: 'GET'
        url: Routing.generate 'OpitOpitHrmTravelBundle_admin_transportationtype_show', id: id
    .done (data) ->
        $('<div id="dialog-transportation-type-form"></div>').html(data)
            .dialog
                title: '<i class="fa fa-list-alt"></i> Edit transportation type'
                width: 500
                modal: on
                buttons:
                    Edit: ->
                        saveTransportationType 'id': id, 'response': { 'message': 'Transportation type successfully modified' }
                    Close: ->
                        $(@).dialog "destroy"
                        return
            return
        return

# Delete button event
$('#list-controls-table button#delete').click ->
    do deleteTransportationType

# Register delete icon event (single delete)
$('#transportation-type-list').on "click", ".delete-single-type", ->
    $(document).data('opithrm').funcs.resetAndSelectSingle $(@)
    do deleteTransportationType

# Delete action (calls generic function from app main.coffee)
deleteTransportationType = () ->
    $(document).data('opithrm').funcs.deleteAction(
        'Transportation type delete',
        'Transportation type(s)',
        Routing.generate('OpitOpitHrmTravelBundle_admin_transportationtype_delete'),
        '.list-delete-type'
    )

# Register server side ordering events
$('form#form-transportation-types').on 'click', '.order-text', ->
    $(document).data('opithrm').funcs.serverSideListOrdering $(@), $(@).parent().find('i').attr('data-field'), 'OpitOpitHrmTravelBundle_admin_transportationtype_list', 'list-table', 'searchForm'
$('form#form-transportation-types').on 'click', '.fa-sort', ->
    $(document).data('opithrm').funcs.serverSideListOrdering $(@), $(@).data('field'), 'OpitOpitHrmTravelBundle_admin_transportationtype_list', 'list-table', 'searchForm'