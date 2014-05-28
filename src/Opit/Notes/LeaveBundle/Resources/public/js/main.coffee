$.extend true, $(document).data('notes'),
    funcs:
        changeTimeSheetStatus: (statusId, timeSheetStatusId, $spinner) ->
            $.ajax
                method: 'POST'
                url: Routing.generate 'OpitNotesTravelBundle_timesheet_state'
                data: {'statusId': statusId, 'timeSheetStatusId': timeSheetStatusId}
                global: false
            .done (data) ->
                location.reload()
            .complete () ->
                $spinner.remove()
            .fail (data) ->
                $spinner.remove()
                $changeState = $('.changeState[data-tr="' + timeSheetStatusId + '"]')
                                .removeClass('dropdown-disabled')
                                .prop 'selectedIndex', 0
                $('<div id="dialog-tr-error"></div>').html 'Status could not be changed due to an error.'
                    .dialog
                        title: '<i class="fa fa-exclamation-triangle"></i> An error occurred'
                        width: 550
                        buttons:
                            Close: ->
                                $(@).dialog 'destroy'
                                return
