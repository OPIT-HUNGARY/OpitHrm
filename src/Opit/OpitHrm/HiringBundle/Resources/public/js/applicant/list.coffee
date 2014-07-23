$(document).ready ->
    # Delete button
    $('#delete').click ->
        do deleteApplicant

    # Delete icon in the table row
    $('#main-wrapper').on "click", ".delete-applicant", ->
        event.preventDefault()
        $checkbox = $(@).closest('tr').find ':checkbox'
        $checkbox.prop 'checked', true
        do deleteApplicant

    # Call the deleteAction from the app main.js
    deleteApplicant = () ->
        url = Routing.generate 'OpitOpitHrmHiringBundle_applicant_delete'
        $(document).data('opithrm').funcs.deleteAction('Applicant delete', 'applicant(s)', url, '.deleteMultiple')

    $('#applicant_list').on 'change', '.changeState', ->
        $(document).data('opithrm').funcs.changeStateDialog $(@), $(document).data('opithrm').funcs.changeApplicantStatus, {
            foreignId: $(@).data('applicant') # applicant id
            label: $(@).closest('tr').find('.applicant-name').text()
        }
        return

    history = new StatusHistory('OpitOpitHrmHiringBundle_status_history')
    do history.init

    $('#applicant_list').on 'click', '.fa-sort', ->
        $(document).data('opithrm').funcs.serverSideListOrdering $(@), $(@).data('field'), 'OpitOpitHrmHiringBundle_applicant_list', 'applicant_list'

    $('#applicant_list').on 'click', '.order-text', ->
        $orderIcon = $(@).parent().find('.fa-sort')
        $(document).data('opithrm').funcs.serverSideListOrdering $orderIcon, $orderIcon.data('field'), 'OpitOpitHrmHiringBundle_applicant_list', 'applicant_list'    