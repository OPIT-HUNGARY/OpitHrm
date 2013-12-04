$("#list-table").on "click", ".clickableTableRow", ->
  event.preventDefault()
  id = $(@).attr "data-tr-id"
  $.ajax
    method: 'POST'
    url: $(document).data('OpitNotesTravelBundle').urls.OpitNotesTravelBundle_travel_show_details
    data: 'trId': id
  .done (data) ->
    dialogWidth = 550
    $('<div id="dialog-show-details-tr"></div>')
      .dialog
        open: ->
          $('.ui-dialog-title').append ('<i class="fa fa-list-alt"></i> Details')
          $(@).html(data)
        dialogClass: 'popup-dialog'
        width: dialogWidth
        position: [($(window).width()-dialogWidth)/2,$(window).height()/20]
        modal: on
        buttons:
          Close: ->
             $('#dialog-show-details-tr').dialog "destroy"
             return
    return
  return