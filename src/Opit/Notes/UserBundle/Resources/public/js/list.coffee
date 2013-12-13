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
    id = $(@).attr "data-user-id"
    $(document).data('OpitNotesUserBundle').funcs.userEdit id, $(document).data('OpitNotesUserBundle').funcs.showAlert
    return

$("#list-table").on "click", ".list-change-password", ->
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

# Delete button
$('#delete').click ->
  deleteUser()

# Delete icon in the table row
$('#list-table').on "click", ".delete-single-user", ->
  $checkbox = $(@).closest('tr').find(':checkbox')
  $checkbox.prop 'checked', true
  deleteUser()

# Call the deleteAction from the app main.js
deleteUser = () ->
  title = 'User delete'
  message = 'user(s)'
  url = Routing.generate 'OpitNotesUserBundle_user_delete'
  $(document).data('notes').funcs.deleteAction(title, message, url, '.delete-checkbox-form')
    
$.extend true, $(document).data('OpitNotesUserBundle').funcs,
    showAlert: (response, actionType, message) ->
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
          returnVal = off
        else
          $('#list-reply-message')
            .html("<i class='fa fa-check-square'></i> "+message+"! <i class='float-right fa fa-chevron-circle-up'></i> ")
            .addClass("alert-message")
            .addClass('success-message')
            .fadeIn(200)
            .delay(2000)
            .slideUp(1000)
          returnVal = on

        return returnVal
        
$('#list-table').on "click", "th i", ->
  $('.list-delete-user').checkAll()
    
$('#list').on "click", "#list-reply-message", ->
  console.log $(@)
  $(@).hide()
