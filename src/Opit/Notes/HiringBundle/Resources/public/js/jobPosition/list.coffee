$(document).ready ->
    $('#job_position_list').on 'click', '.jp-details', ->
        jobPositionId = $(@).data 'jp-id'
        $.ajax
            method: 'POST'
            url: Routing.generate 'OpitNotesHiringBundle_job_show_details'
            data: 'id': jobPositionId
          .done (data) ->
            dialogWidth = 550
            $('<div id="dialog-show-details-job-position"></div>').html(data)
              .dialog
                title: '<i class="fa fa-list-alt"></i> Details'
                width: dialogWidth
                maxHeight: $(window).outerHeight()-100
                modal: on
            return
          return

    # Delete button
    $('#delete').click ->
        do deleteJobPosition

    # Delete icon in the table row
    $('#main-wrapper').on "click", ".delete-job-position", ->
        event.preventDefault()
        $checkbox = $(@).closest('tr').find ':checkbox'
        $checkbox.prop 'checked', true
        do deleteJobPosition

    # Call the deleteAction from the app main.js
    deleteJobPosition = () ->
        url = Routing.generate 'OpitNotesHiringBundle_job_position_delete'
        $(document).data('notes').funcs.deleteAction('Job position delete', 'job position(s)', url, '.deleteMultiple')

    $('#job_position_list').on 'click', '.fa-sort', ->
            $(document).data('notes').funcs.serverSideListOrdering $(@), $(@).data('field'), 'OpitNotesHiringBundle_job_position_list', 'job_position_list'

    $('#job_position_list').on 'click', '.order-text', ->
        $orderIcon = $(@).parent().find('.fa-sort')
        $(document).data('notes').funcs.serverSideListOrdering $orderIcon, $orderIcon.data('field'), 'OpitNotesHiringBundle_job_position_list', 'job_position_list'    