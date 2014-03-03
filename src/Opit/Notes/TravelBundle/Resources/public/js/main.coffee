$.extend true, $(document).data('notes'),
    funcs:
        changeTravelStatus: (statusId, travelRequestId, $spinner) ->
            reloadPage = false
            dialogWidth = 550
            $.ajax
                method: 'POST'
                url: Routing.generate 'OpitNotesTravelBundle_request_state'
                data: {'statusId': statusId, 'travelRequestId': travelRequestId}
            .done (data) ->
                if data is 'error'
                    $('<div id="dialog-tr-error"></div>').html('You cannot change the status of the travel request because it has been already changed.')
                        .dialog
                            open: ->
                                $('.ui-dialog-title').append ('<i class="fa fa-exclamation-triangle"></i> Status cannot be changed')
                            width: dialogWidth
                            buttons:
                                Reload: ->
                                    location.reload()
                                    return
                else
                    reloadPage = true
            .complete () ->
                $spinner.remove()
                if reloadPage is true
                    location.reload()
            .fail (data) ->
                $spinner.remove()
                $changeState = $('.changeState[data-tr="' + travelRequestId + '"]')
                $changeState.removeClass 'dropdown-disabled'
                $changeState.prop 'selectedIndex', 0
                $('<div id="dialog-tr-error"></div>').html 'Status could not be changed due to an error.'
                    .dialog
                        open: ->
                            $('.ui-dialog-title').append ('<i class="fa fa-exclamation-triangle"></i> An error occurred')
                        width: dialogWidth
                        buttons:
                            Close: ->
                                $(@).dialog 'destroy'
                                return