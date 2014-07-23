$('#list-table').on 'click', '.small-button', ->
    counter = $(@).find('.click-counter')
    counter.html(parseInt(counter.text()) + 1)

$('#list-table').on 'click', '.order-text', ->
    $(document).data('opithrm').funcs.serverSideListOrdering $(@), $(@).parent().find('i').attr('data-field'), 'OpitOpitHrmLeaveBundle_timesheet_list', 'list-table', 'searchForm'
$('#list-table').on 'click', '.fa-sort', ->
    $(document).data('opithrm').funcs.serverSideListOrdering $(@), $(@).data('field'), 'OpitOpitHrmLeaveBundle_timesheet_list', 'list-table', 'searchForm'
