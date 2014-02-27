$(document).ready ->
    $('.print-view').on 'click', (event) ->
        event.preventDefault()
        url =  $(@).attr 'href'
        win=window.open url, '_blank'
        win.focus()

    $('#travel_list').on 'change.tr_status', '.changeState', ->
        $(@).addClass 'dropdown-disabled'
        statusId = $(@).val()
        travelRequestId = $(@).closest('tr').find('.clickable').data 'tr-id'
        firstStatusId = $(@).find('option:first-child').val()
        $(document).data('notes').funcs.changeTravelStatus statusId, travelRequestId
            
    $('#travel_list').on 'click', '.order-text', ->
        $(document).data('notes').funcs.serverSideListOrdering $(@), $(@).parent().find('i').attr('data-field'), 'OpitNotesTravelBundle_travel_list', 'travel_list'
    $('#travel_list').on 'click', '.fa-sort', ->
        $(document).data('notes').funcs.serverSideListOrdering $(@), $(@).data('field'), 'OpitNotesTravelBundle_travel_list', 'travel_list'
