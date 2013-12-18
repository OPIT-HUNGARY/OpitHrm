$form = $('#searchFormWrapper').find 'form'
url = $form.attr 'action'
$('#searchButton').click ->
  event.preventDefault()
  return if not $form.formIsEmpty()
  
  $.ajax
      method: 'POST'
      url: url
      data: $form.serialize()
  .done (response) ->
    $('#list-table').html response
    return
  return

$('#resetButton').click ->
  $.ajax
      method: 'POST'
      url: url
      data: 'resetForm': true, 'showList' : 1
  .done (response) ->
    $('#list-table').html response
    return
  return

# method to make search form toggleable
$('#searchFormTitle').click ->
    # check if toogle animation is running to prevent a new animation
    if not $(@).next().is(':animated')
        $(@).children('i').toggleClass 'fa-chevron-down'
        $(@).next().slideToggle()
