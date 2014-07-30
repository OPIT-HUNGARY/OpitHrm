showTeamDialog = (teamId = '') ->
    $.ajax
        type: 'GET'
        url: Routing.generate url, id: teamId
    .done (data)->
        $selfDialog = $('<div>').html data
        $selfDialog.dialog
            width: 600
            modal: on
            title: 'Edit Team'
            buttons:
                Create: ->
                    $.ajax
                        type: 'POST'
                        url: Routing.generate url, id: teamId
                        data: $selfDialog.find('#show_team_from').serialize()
                    .done (data)->
                        $('.delete-checkbox-form').replaceWith data
                        $selfDialog.dialog 'destroy'
                        $(document).data('opithrm').funcs.initDeleteMultipleListener()
                Close: ->
                    $selfDialog.dialog 'destroy'

$('#main-wrapper').on 'click','.edit-team', ->
    showTeamDialog $(@).data 'id'

$('#main-wrapper #add-team').on 'click', ->
    showTeamDialog()