$.extend true, $(document).data('notes'),
    funcs:
        disableStatusDropdown: ($self) ->
            $spinner = $('<i>').addClass 'fa fa-spinner fa-spin'
            $self.parent().append $spinner
            $self.addClass 'dropdown-disabled'
            
            return $spinner
            
        enableStatusDropdown: ($self) ->
            $self.parent().find('.fa-spinner').remove()
            $self.prop 'selectedIndex', 0
            $self.removeClass 'dropdown-disabled'
                                
        changeTravelExpenseStatus: (data, $spinner) ->
            $.ajax
                method: 'POST'
                url: Routing.generate 'OpitNotesTravelBundle_expense_state'
                data: data
                global: false
            .done (data) ->
                location.reload()
            .complete () ->
                $spinner.remove()
            .fail (data) ->
                $spinner.remove()
                $changeState = $('.changeState[data-tr="' + travelExpenseId + '"]')
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
    
        changeTravelRequestStatus: (data, $spinner) ->
            if $spinner is undefined
                $row = $('tr').find("[data-tr-id=#{ data['status[foreignId]'] }]")
                $dropDown = $row.closest('tr').find '.changeState'
                $spinner = $(document).data('notes').funcs.disableStatusDropdown($dropDown)
            reloadPage = false
            $.ajax
                method: 'POST'
                url: Routing.generate 'OpitNotesTravelBundle_request_state'
                data: data
            .done (data) ->
                if data is 'error'
                    $('<div id="dialog-tr-error"></div>').html('You cannot change the status of the travel request because it has been already changed.')
                        .dialog
                            title: '<i class="fa fa-exclamation-triangle"></i> Status cannot be changed'
                            width: 550
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