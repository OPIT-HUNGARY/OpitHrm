$(document).ready ->
    # Init status history for travel requests
    history = new StatusHistory('OpitOpitHrmTravelBundle_status_history')
    do history.init

    $('#main-wrapper').on 'click', '#travel_list .list-table .clickable', ->
      $changeState = $(@).closest('tr').find('.changeState')
      travelRequestId = $(@).attr 'data-tr-id'
      firstStatusId = $(@).parent().find('option:first-child').val()

      buttons = {}
      if firstStatusId is '1' or firstStatusId is '3'
        buttons['Send for approval'] = ->
            $(document).data('opithrm').funcs.changeTravelRequestStatus  'status[id]': 2, 'status[foreignId]': travelRequestId
            $('#dialog-show-details-tr').dialog 'destroy'
            return

      buttons['Close'] = ->
        $('#dialog-show-details-tr').dialog 'destroy'
        return

      $.ajax
        method: 'POST'
        url: Routing.generate 'OpitOpitHrmTravelBundle_travel_show_details'
        data: 'id': travelRequestId
      .done (data) ->
        dialogWidth = 550
        $('<div id="dialog-show-details-tr"></div>').html(data)
          .dialog
            title: '<i class="fa fa-list-alt"></i> Details'
            width: dialogWidth
            maxHeight: $(window).outerHeight()-100
            modal: on
            buttons: buttons
        return
      return

    $('#main-wrapper').on 'click', '.print-view', (event) ->
        event.preventDefault()
        win=window.open $(@).attr('href'), '_blank'
        win.focus()
        
    $travelList = $('#travel_list')
    $travelList.on 'change.tr_status', '.changeState', ->
        travelRequestId = $(@).closest('tr').find('.clickable').data 'tr-id'
        $(document).data('opithrm').funcs.changeStateDialog $(@), $(document).data('opithrm').funcs.changeTravelRequestStatus, {
            foreignId: travelRequestId
            label: $(@).closest('tr').find('.clickable').text()
            type: 'travel request'
        }

    # Delete button
    $('#delete').click ->
        do deleteTravelRequest

    # Delete icon in the table row
    $('#main-wrapper').on "click", ".deleteSingeTravelRequest", ->
        event.preventDefault()
        $checkbox = $(@).closest('tr').find ':checkbox'
        $checkbox.prop 'checked', true
        do deleteTravelRequest

    # Call the deleteAction from the app main.js
    deleteTravelRequest = () ->
        url = Routing.generate 'OpitOpitHrmTravelBundle_travel_delete'
        $(document).data('opithrm').funcs.deleteAction('Travel request delete', 'travel request(s)', url, '.deleteMultiple')

    $travelList.on 'click', '.order-text', ->
        $(document).data('opithrm').funcs.serverSideListOrdering $(@), $(@).parent().find('i').attr('data-field'), 'OpitOpitHrmTravelBundle_travel_list', 'travel_list'
        
    $travelList.on 'click', '.fa-sort', ->
        $(document).data('opithrm').funcs.serverSideListOrdering $(@), $(@).data('field'), 'OpitOpitHrmTravelBundle_travel_list', 'travel_list'
        
    $travelList.on 'click', '.trip-purpose', ->
        $(@).toggleClass 'text-show-all'
