$(document).data 'notes', {}
$.extend true, $(document).data('notes'),
    funcs:
        deleteAction: (title, message, url, identifier) ->
          if $(identifier).find(':checked').length > 0
            $('<div></div>').html('Are you sure you want to delete the '+message+'?').dialog
              title: title
              buttons:
                  Yes: ->
                      $.ajax
                        method: 'POST'
                        url: url
                        data: $(identifier).serialize()
                      .done (data) ->
                          $(identifier).find(':checked').parent().parent().parent().remove()
                          return
                      .fail () ->
                          $('<div></div>').html('The '+message+' could not be deleted due to an error.').dialog
                              title: 'Error'
                      $(@).dialog 'close'
                      return
                  No: ->
                      $(identifier + ' input:checkbox').attr('checked', false)
                      $(@).dialog 'close'
                      return
              close: ->
                  $(@).dialog 'destroy'
                  return

