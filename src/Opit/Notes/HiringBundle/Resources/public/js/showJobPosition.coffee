$(document).ready ->
    $('#job_position_hiring_manager_ac').autocomplete
        source: (request, response) ->
            $.post Routing.generate('OpitNotesUserBundle_user_search', role: 'role_general_manager'), request, (data) -> response(data)
        minLength: 2
        select: (event, ui) ->
            $('#job_position_hiring_manager').val ui.item.id
            return
        
    $('form.disabled select, .disabled input, form.disabled textarea').each ->
        $(@).attr 'disabled', 'disabled'
        
    $('form.disabled #job_position_create_job_position')
        .addClass 'button-disabled'
        .attr 'disabled', 'disabled'

    $.validator.addMethod 'numberOfPositions', (value, element) ->
        $element = $(element)
        if $element.val() <= 0
            return false
        else
            return true

    , 'Number of positions can not be smaller equal to 0.'

    # method to validate form before preview
    $form = $('#jobPositionForm')

    # assing custom validation rules to arrival date, user, general manager
    $form.validate
        ignore: []
        rules:
            'job_position[numberOfPositions]': 'numberOfPositions'

    $('#job_position_create_job_position').click (event) ->
        event.preventDefault()
        if $form.valid()
            do $form.submit