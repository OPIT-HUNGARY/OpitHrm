$("#add").click ->
  $.ajax
    method: 'GET'
    url: Routing.generate 'OpitNotesUserBundle_user_show', id: 0
  .done (data) ->
    $('<div id="dialog-edititem"></div>').html(data)
      .dialog
          open: ->
            $('.ui-dialog-title').append ('<i class="fa fa-list-alt"></i> Create User')
          dialogClass: 'popup-dialog'
          width: 750
          modal: on
          buttons:
            Create: ->
              $.ajax
                type: 'POST'
                global: false
                url: Routing.generate 'OpitNotesUserBundle_user_add', id: 0
                data: $('#adduser_frm').serialize()
              .done (data)->
                  response = data
                  $.ajax
                    type: 'POST'
                    global: false
                    url: Routing.generate 'OpitNotesUserBundle_user_list'
                    data: "showList" : 1
                  .done (data)->
                    $('#list-table').html data
                    showAlert response, "create", "User created successfully"
             Close: ->
               $('#dialog-edititem').dialog "destroy"
               return
      return
    return

$("#list-table").on "click", ".list-username", ->
  event.preventDefault()
  id = $(@).attr "data-user-id"
  $.ajax
    method: 'GET'
    url: Routing.generate 'OpitNotesUserBundle_user_show', id: id 
  .done (data) ->
    $('<div id="dialog-edititem"></div>').html(data)
      .dialog
          open: ->
            $('.ui-dialog-title').append ('<i class="fa fa-list-alt"></i> Edit User')
          dialogClass: 'popup-dialog'
          width: 750
          modal: on
          buttons:
            Save: ->
              $.ajax
                type: 'POST'
                global: false
                url: Routing.generate 'OpitNotesUserBundle_user_add', id: id 
                data: $('#adduser_frm').serialize()
              .done (data)->
                  response = data
                  $.ajax
                    type: 'POST'
                    global: false
                    url: Routing.generate 'OpitNotesUserBundle_user_list'
                    data: "showList" : 1
                  .done (data)->
                    $('#list-table').html data
                    showAlert response, "update","User modified successfully"
            Close: ->
               $('#dialog-edititem').dialog "destroy"
               return
      return
    return

$("#list-table").on "click", ".list-change-password", ->
  event.preventDefault()
  id = $(@).attr "data-user-id"
  $.ajax
    method: 'GET'
    url: Routing.generate 'OpitNotesUserBundle_user_show_password', id: id 
  .done (data) ->
    $('<div id="dialog-edititem"></div>').html(data)
      .dialog
          open: ->
            $('.ui-dialog-title').append ('<i class="fa fa-list-alt"></i> Reset Password')
            $(@).html(data)
          dialogClass: 'popup-dialog-change-password'
          width: 750
          modal: on
          buttons:
            Save: ->
              $.ajax
                type: 'POST'
                global: false
                url: Routing.generate 'OpitNotesUserBundle_user_update_password', id: id
                data: $('#changePassword_frm').serialize()
              .done (data)->
                  response = data
                  showAlert response, "update","Password reset successfully"
            Close: ->
               $('#dialog-edititem').dialog "destroy"
               return
      return
    return

$('#delete').click ->
  users = []
  $('.list-delete-user').each ->
    if(@.checked)
      users.push $(@).val()
  if users.length > 0
    $('<div></div>').html('Are you sure you want to delete the travel request?').dialog
        title: 'Travel request removal'
        buttons:
            Yes: ->
              $.ajax
                type: 'POST'
                global: false
                url: Routing.generate 'OpitNotesUserBundle_user_delete'
                data: "userIds" : users
              .done (data)->
                response = data
                $.ajax
                  type: 'POST'
                  global: false
                  url: Routing.generate 'OpitNotesUserBundle_user_list'
                  data: "showList" : 1
                .done (data)->
                  $('#list-table').html data
                  showAlert response, "delete","The user(s) deleted successfully"
               $(@).dialog 'destroy'
               return
             No: ->
                $('input:checkbox').removeAttr('checked')
                $(@).dialog 'destroy'
                return
        close: ->
            $(@).dialog 'destroy'
            return
    return

showAlert = (response, actionType, message) ->
  $('#reply-message').addClass "alert-message"
  if response[0].response == 'error'
    if "update" == actionType or "create" == actionType
      errorString = "<ul>"
      for i in response[0].errorMessage
        errorString += "<li>"+i+"</li>"
      errorString += "</ul>"
      $('#reply-message')
        .html("<i class='fa fa-exclamation-triangle'></i> <strong>Error messages:</strong>"+errorString)
        .removeClass('success-message')
        .addClass('error-message')
    else if "delete" == actionType
      $('#list-reply-message')
        .html("<i class='fa fa-exclamation-triangle'></i> Error, while tried to delete the user(s)! <i class='float-right fa fa-chevron-circle-up'></i> ")
        .removeClass('success-message')
        .addClass('error-message')
        .fadeIn(200)
        .delay(5000)
        .slideUp(1000)
  else
    $('#list-reply-message')
      .html("<i class='fa fa-check-square'></i> "+message+"! <i class='float-right fa fa-chevron-circle-up'></i> ")
      .addClass("alert-message")
      .addClass('success-message')
      .fadeIn(200)
      .delay(2000)
      .slideUp(1000)
    $('#dialog-edititem').dialog 'destroy'
    
closeAlert = () ->
  console.log "click"
  $('#list-reply-message')
    .hide

$(document).ready ->
  $('#list-reply-message').click ->
    closeAlert()
