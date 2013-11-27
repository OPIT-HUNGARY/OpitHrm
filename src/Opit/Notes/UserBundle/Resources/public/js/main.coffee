$(document)
  .ready ->
    $(".menu .mainMenu")
      .click ->
        $(".menu .mainMenu").removeClass "active"
        $(@).addClass "active"

# Place any jQuery/helper plugins in here.

$.fn.extend
  formIsEmpty: (element) ->
    $el = if element then $(element) else $(@)
    exists = no
    $el.find(':input').each ->
      exists = yes if($(@).val())
    return exists