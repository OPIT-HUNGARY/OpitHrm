$(document).ready ->
    $('#main-wrapper').on 'click', '.status-history', (event) ->
        event.preventDefault()
        $.ajax
            method: 'POST'
            url: Routing.generate 'OpitNotesTravelBundle_travel_states_history'
            data: {'id': $(@).find('.fa-book').data 'id'}
        .done (data) ->
            dialogWidth = 550
            $('<div id="dialog-show-details-tr"></div>').html(data)
                .dialog
                    title: '<i class="fa fa-book"></i> Status history'
                    width: dialogWidth
                    maxHeight: $(window).outerHeight()-100
                    modal: on
                    buttons:
                        Close: ->
                            $('#dialog-show-details-tr').dialog 'destroy'
                            return
            return

    $('#main-wrapper').on 'click', '#travel_list #list-table .clickable', ->
      $changeState = $(@).closest('tr').find('.changeState')
      travelRequestId = $(@).attr 'data-tr-id'
      firstStatusId = $(@).parent().find('option:first-child').val()
      $.ajax
        method: 'POST'
        url: Routing.generate 'OpitNotesTravelBundle_travel_show_details'
        data: 'id': travelRequestId
      .done (data) ->
        dialogWidth = 550
        $('<div id="dialog-show-details-tr"></div>').html(data)
          .dialog
            title: '<i class="fa fa-list-alt"></i> Details'
            width: dialogWidth
            maxHeight: $(window).outerHeight()-100
            modal: on
            if firstStatusId is '1' or firstStatusId is '3'
                buttons:
                  'Send for approval': ->
                     $(document).data('notes').funcs.changeTravelRequestStatus 2, travelRequestId
                     $('#dialog-show-details-tr').dialog 'destroy'
                  Close: ->
                     $('#dialog-show-details-tr').dialog 'destroy'
                     return
            else
                buttons:
                  Close: ->
                     $('#dialog-show-details-tr').dialog 'destroy'
                     return
        return
      return
      
    $('#main-wrapper').on 'click', '.print-view', (event) ->
        event.preventDefault()
        win=window.open $(@).attr('href'), '_blank'
        win.focus()
        
    $travelList = $('#travel_list')
    $travelList.on 'change.tr_status', '.changeState', ->
        travelRequestId = $(@).closest('tr').find('.clickable').data 'tr-id'
        $(document).data('notes').funcs.changeStateDialog $(@), $(document).data('notes').funcs.changeTravelRequestStatus, travelRequestId
            
    $travelList.on 'click', '.order-text', ->
        $(document).data('notes').funcs.serverSideListOrdering $(@), $(@).parent().find('i').attr('data-field'), 'OpitNotesTravelBundle_travel_list', 'travel_list'
        
    $travelList.on 'click', '.fa-sort', ->
        $(document).data('notes').funcs.serverSideListOrdering $(@), $(@).data('field'), 'OpitNotesTravelBundle_travel_list', 'travel_list'
        
    $travelList.on 'click', '.trip-purpose', ->
        $(@).toggleClass 'text-show-all'
