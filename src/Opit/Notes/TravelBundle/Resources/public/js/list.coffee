changeTravelStatus = (statusId, travelRequestId, firstStatusId) ->
    reloadPage = true
    $.ajax
        method: 'POST'
        url: Routing.generate 'OpitNotesTravelBundle_request_state'
        data: {'statusId': statusId, 'travelRequestId': travelRequestId, 'firstStatusId': firstStatusId}
    .done (data) ->
        if data is 'error'
            reloadPage = false
            dialogWidth = 550
            $('<div id="dialog-show-details-tr"></div>').html('You cannot change the status of the travel request because it has been already changed.')
                .dialog
                    open: ->
                        $('.ui-dialog-title').append ('<i class="fa fa-exclamation-triangle"></i> Status cannot be changed')
                width: dialogWidth
                maxHeight: $(window).outerHeight()-100
                modal: on
                buttons:
                    Reload: ->
                        location.reload()
                        return
    .complete () ->
        if reloadPage is true
            location.reload()
    .fail (data) ->
        console.warn 'An error occured while setting new status for the request.'
        
$(document).ready ->
    $('.print-view').on 'click', (event) ->
        event.preventDefault()
        url =  $(@).attr 'href'
        win=window.open url, '_blank'
        win.focus()

    $('.changeState').on 'change', ->
        $(@).addClass 'dropdown-disabled'
        statusId = $(@).val()
        travelRequestId = $(@).closest('tr').find('.clickable').data 'tr-id'
        firstStatusId = $(@).find('option:first-child').val()
        $(document).data('notes').funcs.changeTravelStatus(statusId, travelRequestId, firstStatusId)
            
    $('#searchButton').click (event) ->
        $form = $('#searchFormWrapper').find 'form'
        url = $form.attr 'action'
        event.preventDefault()
        return if not $form.formIsEmpty()

        $.ajax
            method: 'POST'
            url: url
            data: $form.serialize()
        .done (response) ->
            $('#list-table').parent().html response
            $(document).data('notes').funcs.initListPageListeners()
            $(document).data('notes').funcs.initPager()
            return
      return
                
    $('#travel_list').on 'click', '.order-text', ->
        $(document).data('notes').funcs.serverSideListOrdering $(@), $(@).parent().find('i').attr('data-field'), 'OpitNotesTravelBundle_travel_list', 'travel_list'
    $('#travel_list').on 'click', '.fa-sort', ->
        $(document).data('notes').funcs.serverSideListOrdering $(@), $(@).data('field'), 'OpitNotesTravelBundle_travel_list', 'travel_list'
