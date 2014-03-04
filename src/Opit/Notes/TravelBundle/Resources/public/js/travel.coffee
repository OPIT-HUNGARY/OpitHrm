$(document).data 'notes_travel', {}
$.extend true, $(document).data('notes_travel'),
    funcs:
        showTravelStatusHistory: (id, mode) ->
            $.ajax
                method: 'POST'
                url: Routing.generate 'OpitNotesTravelBundle_travel_states_history', mode: mode
                data: {'id': id}
            .done (data) ->
                dialogWidth = 550
                $('<div id="dialog-show-details-tr"></div>').html(data)
                    .dialog
                        open: ->
                            $('.ui-dialog-title').append ('<i class="fa fa-book"></i> Status history')
                        width: dialogWidth
                        maxHeight: $(window).outerHeight()-100
                        modal: on
                        buttons:
                            Close: ->
                                $('#dialog-show-details-tr').dialog 'destroy'
                                return
                    return