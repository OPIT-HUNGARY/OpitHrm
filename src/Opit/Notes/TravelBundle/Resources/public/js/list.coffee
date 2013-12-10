$('#list-table').on 'click', '.clickable', ->
  id = $(@).attr 'data-tr-id'
  $.ajax
    method: 'POST'
    url: $(document).data('OpitNotesTravelBundle').urls.OpitNotesTravelBundle_travel_show_details
    data: 'id': id
  .done (data) ->
    dialogWidth = 550
    $('<div id="dialog-show-details-tr"></div>').html(data)
      .dialog
        open: ->
          $('.ui-dialog-title').append ('<i class="fa fa-list-alt"></i> Details')
        width: dialogWidth
        maxHeight: $(window).outerHeight()-100
        modal: on
        buttons:
          Close: ->
             $('#dialog-show-details-tr').dialog 'destroy'
             return
    return
  return

$('#list-table th i').click ->
    $('.deleteMultipleTravelRequest').checkAll()

$('.deleteSingeTravelRequest').click ->
    event.preventDefault()
    self = $(@)
#    empName = self.closest('tr').children('td.employee-name').text()
    # Set checkbox
    $checkbox = self.closest('tr').find(':checkbox')
    $checkbox.prop 'checked', true
    # TODO: Add travel request ID to the dialog body text.
    #$('<div></div>').html("Are you sure you want to delete the travel request \"#{travel-request-id}\"?").dialog
    $('<div></div>').html('Are you sure you want to delete the travel request?').dialog
        title: 'Travel request removal'
        buttons:
            Yes: ->
                $.ajax
                  method: 'POST'
                  url: self.attr 'href'
                  data: 'id': self.data 'id'
                .done (data) ->
                    if data is '0' then self.parent().parent().remove()
                    return
                .fail () ->
                    $('<div></div>').html('The travel request could not be deleted due to an error.').dialog
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
        
$('#delete').click ->
    travelRequests = []
    selectedTravelRequestRow = []
    $('.deleteMultipleTravelRequest').each ->
        if $(@).is ':checked'
            travelRequests.push $(@).val()
            selectedTravelRequestRow.push $(@).parent().parent()
            
    $('<div></div>').html('Are you sure you want to delete the selected travel requests?').dialog
        title: 'Travel request removal'
        buttons:
            Yes: ->
                $.ajax
                  method: 'POST'
                  url: $('.deleteSingeTravelRequest').attr 'href'
                  data: 'id': travelRequests
                .done (data) ->
                    $(selectedTravelRequestRow).each ->
                        $(@).remove()
                    return
                .fail () ->
                    $('<div></div>').html('The travel request could not be deleted due to an error.').dialog
                        title: 'Error'                    
                $(@).dialog 'close'
                return
            No: ->
                $(@).dialog 'close'
                return
        close: ->
            $(@).dialog 'destroy'
            return
    return