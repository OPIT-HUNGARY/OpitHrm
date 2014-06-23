$(document).ready ->
    $('#main-wrapper').on 'click', '.delete-applicant', (event) ->
            event.preventDefault()
            $deleteButton = $(@)
            applicantName = $deleteButton.data 'request'
            message = "Are you sure you want to delete applicantion of  #{ applicantName }?"
            title = 'Delete applicant'

            $('<div id="dialog-show-details-applicant"></div>').html(message)
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
                                    url: Routing.generate 'OpitNotesHiringBundle_applicant_list'
                                    data: "resetForm" : 1
                                .done (list)->
                                    $('#applicant_list').html list
                                    $(document).data('notes').funcs.initListPageListeners()
                                    $(document).data('notes').funcs.initDeleteMultipleListener()
                                $('#dialog-show-details-applicant').dialog 'destroy'
                                return
                        No: ->
                            $('#dialog-show-details-applicant').dialog 'destroy'
                            return

    $('#applicant_list').on 'click', '.fa-sort', ->
        $(document).data('notes').funcs.serverSideListOrdering $(@), $(@).data('field'), 'OpitNotesHiringBundle_applicant_list', 'applicant_list'

    $('#applicant_list').on 'click', '.order-text', ->
        $orderIcon = $(@).parent().find('.fa-sort')
        $(document).data('notes').funcs.serverSideListOrdering $orderIcon, $orderIcon.data('field'), 'OpitNotesHiringBundle_applicant_list', 'applicant_list'    