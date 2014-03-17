$(document).ready ->
    $('.print-view').on 'click', (event) ->
        event.preventDefault()
        url =  $(@).attr 'href'
        win=window.open url, '_blank'
        win.focus()

    $('#travel_list').on 'change.tr_status', '.changeState', ->
        statusId = $(@).val()
        travelRequestId = $(@).closest('tr').find('.clickable').data 'tr-id'
        $(document).data('notes').funcs.changeStateDialog $(@), $(document).data('notes').funcs.changeTravelRequestStatus, travelRequestId
            
    $('#travel_list').on 'click', '.order-text', ->
        $(document).data('notes').funcs.serverSideListOrdering $(@), $(@).parent().find('i').attr('data-field'), 'OpitNotesTravelBundle_travel_list', 'travel_list'
    $('#travel_list').on 'click', '.fa-sort', ->
        $(document).data('notes').funcs.serverSideListOrdering $(@), $(@).data('field'), 'OpitNotesTravelBundle_travel_list', 'travel_list'
        
    $('#travel_list').on 'click', '.trip-purpose', ->
        $(@).toggleClass 'text-show-all'
