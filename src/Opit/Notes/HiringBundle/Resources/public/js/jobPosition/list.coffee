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

    $('#main-wrapper').on 'click', '.delete-job-position', (event) ->
            event.preventDefault()
            $deleteButton = $(@)
            jobPositionId = $deleteButton.data 'request'
            message = "Are you sure you want to delete job position #{ jobPositionId }?"
            title = 'Delete job positon'

            $('<div id="dialog-show-details-jp"></div>').html(message)
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
                                    url: Routing.generate 'OpitNotesHiringBundle_job_position_list'
                                    data: "resetForm" : 1
                                .done (list)->
                                    $('#job_position_list').html list
                                    $(document).data('notes').funcs.initListPageListeners()
                                    $(document).data('notes').funcs.initDeleteMultipleListener()
                                $('#dialog-show-details-jp').dialog 'destroy'
                                return
                        No: ->
                            $('#dialog-show-details-jp').dialog 'destroy'
                            return

    $('#job_position_list').on 'click', '.fa-sort', ->
        $(document).data('notes').funcs.serverSideListOrdering $(@), $(@).data('field'), 'OpitNotesHiringBundle_job_position_list', 'job_position_list'

    $('#job_position_list').on 'click', '.order-text', ->
        $orderIcon = $(@).parent().find('.fa-sort')
        $(document).data('notes').funcs.serverSideListOrdering $orderIcon, $orderIcon.data('field'), 'OpitNotesHiringBundle_job_position_list', 'job_position_list'    