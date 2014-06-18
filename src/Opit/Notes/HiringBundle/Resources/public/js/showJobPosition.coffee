$(document).ready ->
    $('#job_position_hiring_manager_ac').autocomplete
        source: (request, response) ->
            $.post Routing.generate('OpitNotesUserBundle_user_search', role: 'role_general_manager'), request, (data) -> response(data)
        minLength: 2
        select: (event, ui) ->
            $('#job_position_hiring_manager').val ui.item.id
            return

    # method to validate form before preview
    $form = $('#jobPositionForm')

    # assing custom validation rules to arrival date, user, general manager
    $form.validate
        ignore: []

    $( '#job_position_create_job_position' ).click (event) ->
        event.preventDefault()
        if $form.valid()
            do $form.submit