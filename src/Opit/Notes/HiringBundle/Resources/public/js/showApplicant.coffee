$(document).ready ->
    $('#applicant_jobPositionAc').autocomplete
        source: (request, response) ->
            $.post Routing.generate('OpitNotesHiringBundle_job_search'), request, (data) -> response(data)
        minLength: 2
        select: (event, ui) ->
            $('#applicant_jobPosition').val ui.item.id
            return

    $('form.disabled select, .disabled input, form.disabled textarea').each ->
        $(@).attr 'disabled', 'disabled'

    $('form.disabled #applicant_create_applicant')
        .addClass 'button-disabled'
        .attr 'disabled', 'disabled'

    # method to validate form before preview
    $form = $('#applicantForm')

    # assing custom validation rules to arrival date, user, general manager
    $form.validate
        ignore: []
        rules:
            'applicant[jobPositionAc]': 'required'

    $('#applicant_create_applicant').click (event) ->
        event.preventDefault()
        if $form.valid()
            do $form.submit