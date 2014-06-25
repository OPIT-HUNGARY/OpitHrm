$.extend true, $(document).data('notes'),
    funcs:
        searchCallback: ($form) ->
            data = {}
            $form.serializeArray().map (x) ->
                data[x.name.replace(/\w+\[(\w+)\]/, "$1")] = x.value
                return

            startDate = new Date(data.startDate) if data.startDate
            endDate = new Date(data.endDate) if data.endDate
            highlightClass = 'background-color-green border-radius-2'

            # Highlight all matching leave dates in the result
            $('ul.leave-dates li').each ->
                container = $(@).children 'span'
                if (startDate and endDate)
                    if startDate <= new Date($(@).data('start-date')) and endDate >= new Date($(@).data('end-date'))
                        container.addClass highlightClass
                else
                    if startDate and startDate <= new Date($(@).data('start-date'))
                        container.addClass highlightClass
                        return true
                    if endDate and endDate >= new Date($(@).data('end-date'))
                        container.addClass highlightClass

            return

$(document).ready ->
    # Init leave history for travel requests
    history = new StatusHistory('OpitNotesLeaveBundle_status_history')
    do history.init

    $('#main-wrapper').on 'click', '.delete-leave-request', (event) ->
        event.preventDefault()
        $deleteButton = $(@)
        leaveRequestId = $deleteButton.data 'request'
        message = "Are you sure you want to delete leave request #{ leaveRequestId }?"
        title = 'Delete leave request'

        # Checking if it is a massive leave request
        if  1 == $deleteButton.data('mlr')
            title = 'Delete group leave request'
            message = 'Are you sure you want to delete this group leave request. ' +
            'Deleting this group leave request will subsequently delete all child leave requests created by it.'

        $('<div id="dialog-show-details-tr"></div>').html(message)
            .dialog
                title: '<i class="fa fa fa-exclamation-triangle"></i> ' + title
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
                            $.ajax
                                type: 'POST'
                                url: Routing.generate 'OpitNotesLeaveBundle_leave_list'
                                data: "resetForm" : 1
                            .done (list)->
                                $('#leave_list').html list
                                $(document).data('notes').funcs.initListPageListeners()
                                $(document).data('notes').funcs.initDeleteMultipleListener()
                            $('#dialog-show-details-tr').dialog 'destroy'
                            return
                    No: ->
                        $('#dialog-show-details-tr').dialog 'destroy'
                        return

    $('#leave_list').on 'click', '.lr-details', ->
        leaveRequestId = $(@).data 'lr-id'
        $.ajax
            method: 'POST'
            url: Routing.generate 'OpitNotesLeaveBundle_leave_show_details'
            data: 'id': leaveRequestId
          .done (data) ->
            dialogWidth = 550
            $('<div id="dialog-show-details-lr"></div>').html(data)
              .dialog
                title: '<i class="fa fa-list-alt"></i> Details'
                width: dialogWidth
                maxHeight: $(window).outerHeight()-100
                modal: on
            return
          return

    $('#leave_list').on 'change', '.changeState', ->
        $(document).data('notes').funcs.changeStateDialog $(@), $(document).data('notes').funcs.changeLeaveRequestStatus, {
            foreignId: $(@).data('lr') # Leave request id
            label: $(@).closest('tr').find('.clickable').text()
            type: 'leave request'
        }
        return

    $('#leave_list').on 'click', '.fa-sort', ->
        $(document).data('notes').funcs.serverSideListOrdering $(@), $(@).data('field'), 'OpitNotesLeaveBundle_leave_list', 'leave_list'

    $('#leave_list').on 'click', '.order-text', ->
        $orderIcon = $(@).parent().find('.fa-sort')
        $(document).data('notes').funcs.serverSideListOrdering $orderIcon, $orderIcon.data('field'), 'OpitNotesLeaveBundle_leave_list', 'leave_list'
