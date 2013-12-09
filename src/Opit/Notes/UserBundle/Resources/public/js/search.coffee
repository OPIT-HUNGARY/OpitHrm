$form = $('#searchForm')
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

$('#searchFormTitle').click ->
  $(@).children('i').toggleClass 'fa-chevron-down'
  $(@).next().slideToggle()