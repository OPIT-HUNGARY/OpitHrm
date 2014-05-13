$(document).ready ->
    $('#main-wrapper').on 'click', '.delete-leave-request', (event) ->
        event.preventDefault()
        $deleteButton = $(@)
        leaveRequestId = $deleteButton.data 'request'
        $('<div id="dialog-show-details-tr"></div>').html("Are you sure you want to delete leave request #{ leaveRequestId }?")
            .dialog
                title: '<i class="fa fa fa-exclamation-triangle"></i> Delete leave request'
                width: 550
                maxHeight: $(window).outerHeight()-100
                modal: on
                buttons:
                    Yes: ->
                        $.ajax
                            method: 'POST'
                            url: $deleteButton.attr('href')
                            data: 'id': $deleteButton.data('id')
                        .done (data) ->
                            $('#dialog-show-details-tr').dialog 'destroy'
                            $deleteButton.closest('tr').remove()
                            return
                    No: ->
                        $('#dialog-show-details-tr').dialog 'destroy'
                        return

inverse = false
$('form').on 'click', '.fa-sort', ->
    inverse = $(document).data('notes').funcs.clientSideListOrdering $(@), inverse
    
$('form').on 'click', '.order-text', ->
    inverse = $(document).data('notes').funcs.clientSideListOrdering $(@).parent().find('i'), inverse