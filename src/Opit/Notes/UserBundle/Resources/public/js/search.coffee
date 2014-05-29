$form = $('#searchFormWrapper').find 'form'
url = $form.attr 'action'
$('#searchButton').click (event) ->
    event.preventDefault()
    return if not $form.formIsEmpty()
  
    $.ajax
        method: 'POST'
        url: url
        data: $form.serialize()
    .done (response) ->
        $('#list-table').parent().html response
        $(document).data('notes').funcs.initDeleteMultipleListener()
        $(document).data('notes').funcs.initListPageListeners()
        $(document).data('notes').funcs.initPager()

        # Trigger optional callback if set
        $(document).data('notes').funcs.searchCallback? $form
        return
    return

$('#resetButton').click ->
    $.ajax
        method: 'POST'
        url: url
        data: 'resetForm': true, 'showList' : 1
    .done (response) ->
        $('#list-table').parent().html response
        $(document).data('notes').funcs.initDeleteMultipleListener()
        $(document).data('notes').funcs.initListPageListeners()
        $(document).data('notes').funcs.initPager()
        return
    return

# method to make search form toggleable
$('#searchFormTitle').click ->
    $nextElement = $(@).next()
    # check if toogle animation is running to prevent a new animation
    if not $nextElement.is(':animated')
        $(@).children('i').toggleClass 'fa-chevron-down'
        $nextElement.slideToggle()
