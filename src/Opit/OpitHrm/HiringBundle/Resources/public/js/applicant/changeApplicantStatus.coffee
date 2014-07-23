$.extend true, $(document).data('opithrm'),
    funcs:
        changeApplicantStatus: (data, $spinner) ->
            $.ajax
                method: 'POST'
                url: Routing.generate 'OpitOpitHrmHiringBundle_applicant_state'
                data: data
                global: false
            .done (data) ->
                nextStates = data.nextStates
                $changeState = $('[data-applicant="' + data.applicant + '"]')
                $changeState
                    .removeClass 'dropdown-disabled'
                    .html ''
                Object.keys(nextStates).forEach (key) ->
                    $option = $('<option>').val(nextStates[key]).text(key)
                    $changeState.append $option
                $spinner.remove()
            .complete () ->
                $spinner.remove()
            .fail (data) ->
                response = data.responseJSON
                error = if response.error isnt undefined then response.error else 'Status could not be changed due to an error.'
                $spinner.remove()
                $changeState = $('.changeState[data-applicant="' + response.id + '"]')
                                .removeClass('dropdown-disabled')
                                .prop 'selectedIndex', 0
                $('<div id="dialog-tr-error"></div>').html error
                    .dialog
                        title: '<i class="fa fa-exclamation-triangle"></i> An error occurred'
                        width: 550
                        buttons:
                            Close: ->
                                $(@).dialog 'destroy'
                                return
