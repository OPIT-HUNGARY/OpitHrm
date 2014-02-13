order = 'desc'

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
        changeTravelStatus(statusId, travelRequestId, firstStatusId)

            
    $('.status-history').click (event) ->
        event.preventDefault()
        $.ajax
            method: 'POST'
            url: Routing.generate 'OpitNotesTravelBundle_travel_states_history'
            data: {'id': $(@).find('.fa-book').data 'id'}
        .done (data) ->
            dialogWidth = 550
            $('<div id="dialog-show-details-tr"></div>').html(data)
              .dialog
                open: ->
                  $('.ui-dialog-title').append ('<i class="fa fa-book"></i> Status history')
                width: dialogWidth
                maxHeight: $(window).outerHeight()-100
                modal: on
                buttons:
                  Close: ->
                     $('#dialog-show-details-tr').dialog 'destroy'
                     return
            return
            
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
        $(document).data('notes').funcs.initTravelRequestListListeners()
        $(document).data('notes').funcs.initPager()
        return
      return

# type = expense or request
deleteSingleRequest = (type, self) ->
    $checkbox = self.closest('tr').find(':checkbox')
    $checkbox.prop 'checked', true
    # TODO: Add travel request ID to the dialog body text.
    #$('<div></div>').html("Are you sure you want to delete the travel request \"#{travel-request-id}\"?").dialog
    $('<div></div>').html("Are you sure you want to delete the travel #{ type }?").dialog
        title: 'Travel request removal'
        buttons:
            Yes: ->
                $.ajax
                  method: 'POST'
                  url: if type is 'expense' then Routing.generate 'OpitNotesTravelBundle_expense_delete' else Routing.generate 'OpitNotesTravelBundle_travel_delete'
                  data: 'id': self.data 'id'
                .done (data) ->
                    if data is '0' then self.parent().parent().remove()
                    return
                .fail () ->
                    $('<div></div>').html("The travel #{ type } could not be deleted due to an error.").dialog
                        title: 'Error'
                $(@).dialog 'close'
                return
            No: ->
                # Unset checkbox
                $checkbox.prop 'checked', false
                $(@).dialog 'close'
                return
        close: ->
            $(@).dialog 'destroy'
            return
    return
# Ordering.
$('#list-table').on 'click', 'th .fa-sort', ->
    field = $(@).attr('data-field')
    if order is 'desc'
        order = 'asc'
    else
        order = 'desc'
    $.ajax
       method: 'POST'
       url: Routing.generate 'OpitNotesTravelBundle_travel_list'
       data: 'field': field, 'order': order, 'showList': 1
     .done (data) ->
        $('#list-table').html(data)
