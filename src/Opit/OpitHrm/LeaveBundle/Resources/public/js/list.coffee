$.extend true, $(document).data('opithrm'),
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
    # Set the active tab using the window hash property
    setActiveTab = () ->
        hash = window.location.hash
        index = if hash != '' then $("[href='#{hash}']").parent().index() else 0
        $('#leave_list.tabs').tabs
            active: index

    $(window).on 'hashchange', () ->
        do setActiveTab

    $('#leave_list.tabs').tabs
        activate: (event, ui) ->
            id = $(ui.newPanel[0]).attr('id')
            # Set the window location hash
            window.location.hash = "##{id}"
            # Set search url and resultSelector
            window.search.setUrl $(ui.newTab[0]).data('url')
            window.search.setResultSelector "#list-table-#{id}"
            return
        create: (event, ui) ->
            id = $(ui.panel[0]).attr('id')
            # Set initial search url and result selector
            window.search.setUrl $(ui.tab[0]).data('url')
            window.search.setResultSelector "#list-table-#{id}"

            do setActiveTab
            return

    # Add item count to awaiting approval tab
    $('.ui-state-default a[href="#awaiting_approval"]').append ' (' + $('#list-table-awaiting_approval').data('count') + ')'

    # Init leave history for travel requests
    history = new StatusHistory('OpitOpitHrmLeaveBundle_status_history')
    do history.init

    # Delete button
    $('#delete').click ->
        do deleteLeaveRequest 'Delete leave request', 'leave request'

    # Delete icon in the row
    $('#leave_list').on 'click', '.delete-leave-request', (event) ->
        event.preventDefault()
        $deleteButton = $(@)
        leaveRequestId = $deleteButton.data 'request'
        message = "leave request #{ leaveRequestId }"
        title = 'Delete leave request'

        # Checking if it is a massive leave request
        if  1 == $deleteButton.data('mlr')
            title = 'Delete group leave request'
            message = 'this group leave request. ' +
            '(Deleting this group leave request will subsequently delete all child leave requests created by it)'

        $checkbox = $(@).closest('tr').find ':checkbox'
        $checkbox.prop 'checked', true
        do deleteLeaveRequest title, message

    # Call the deleteAction from the app main.js
    deleteLeaveRequest = (title = null, message = null) ->
        url = Routing.generate 'OpitOpitHrmLeaveBundle_leaverequest_delete'
        $(document).data('opithrm').funcs.deleteAction(title, message, url, '.deleteMultiple')

    $('#leave_list').on 'click', '.lr-details', ->
        leaveRequestId = $(@).data 'lr-id'
        $.ajax
            method: 'POST'
            url: Routing.generate 'OpitOpitHrmLeaveBundle_leave_show_details'
            data: 'id': leaveRequestId
          .done (data) ->
            dialogWidth = 550
            $('<div id="dialog-show-details-lr"></div>').html(data)
              .dialog
                title: '<i class="fa fa-list-alt"></i> Details'
                width: dialogWidth
                maxHeight: $(window).outerHeight()-100
                modal: on
                buttons:
                    Close: ->
                        $(@).dialog 'destroy'
            return
          return

    $('#leave_list').on 'change', '.changeState', ->
        $(document).data('opithrm').funcs.changeStateDialog $(@), $(document).data('opithrm').funcs.changeLeaveRequestStatus, {
            foreignId: $(@).data('lr') # Leave request id
            label: $(@).closest('tr').find('.clickable').text()
            type: 'leave request'
        }
        return

    $('#leave_list').on 'click', '.fa-sort', ->
        id = $(@).closest('div').attr('id')
        url = Routing.generate 'OpitOpitHrmLeaveBundle_lr_list', type: id
        $(document).data('opithrm').funcs.serverSideListOrdering $(@), $(@).data('field'), url, "leave-#{id}-form"

    $('#leave_list').on 'click', '.order-text', ->
        $orderIcon = $(@).parent().find('.fa-sort')
        id = $orderIcon.closest('div').attr('id')
        url = Routing.generate 'OpitOpitHrmLeaveBundle_lr_list', type: id
        $(document).data('opithrm').funcs.serverSideListOrdering $orderIcon, $orderIcon.data('field'), url, "leave-#{id}-form"
