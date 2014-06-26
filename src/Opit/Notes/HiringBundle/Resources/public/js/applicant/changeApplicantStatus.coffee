$.extend true, $(document).data('notes'),
    funcs:
        changeApplicantStatus: (data, $spinner) ->
            $.ajax
                method: 'POST'
                url: Routing.generate 'OpitNotesHiringBundle_applicant_state'
                data: data
                global: false
            .done (data) ->
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
