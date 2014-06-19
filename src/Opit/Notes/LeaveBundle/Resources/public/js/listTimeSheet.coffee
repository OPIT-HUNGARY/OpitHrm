$('#list-table').on 'click', '.small-button', ->
    counter = $(@).find('.click-counter')
    counter.html(parseInt(counter.text()) + 1)

inverse = $('#order_dir').val() is 'asc'
$('form').on 'click', '.fa-sort', ->
    inverse = $(document).data('notes').funcs.clientSideListOrdering $(@), inverse

$('form').on 'click', '.order-text', ->
    inverse = $(document).data('notes').funcs.clientSideListOrdering $(@).parent().find('i'), inverse
