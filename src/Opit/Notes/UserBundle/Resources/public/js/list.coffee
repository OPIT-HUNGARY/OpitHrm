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
                global: false
                type: 'POST'
                url: Routing.generate 'OpitNotesUserBundle_user_add', id: 0
                data: $('#adduser_frm').serialize()
              .done (data)->
                  response = data
                  $.ajax
                    type: 'POST'
                    url: Routing.generate 'OpitNotesUserBundle_user_list'
                    data: "showList" : 1
                  .done (data)->
                    $('#user-list').html data
                    $(document).data('notes').funcs.showAlert response, "create", "User created successfully"
                    $('#dialog-edititem').dialog "destroy"
              .fail (data) ->
                response = data.responseText
                if typeof response is 'string'
                    response = $.parseJSON response
                $(document).data('notes').funcs.showAlert response, "create","Error"
             Close: ->
               $('#dialog-edititem').dialog "destroy"
               return
      return
    return

$("#userlistWrapper").on "click", ".list-username", ->
    id = $(@).attr "data-user-id"
    $(document).data('OpitNotesUserBundle').funcs.userEdit id, $(document).data('notes').funcs.showAlert
    return

$("#userlistWrapper").on "click", ".list-change-password", ->
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
                  $(document).data('notes').funcs.showAlert data, "update","Password reset successfully"
                  $('#dialog-edititem').dialog "destroy"
              .fail (data) ->
                  data = $.parseJSON data.responseText
                  $(document).data('notes').funcs.showAlert data, "update","Password reset successfully"
            Close: ->
               $('#dialog-edititem').dialog "destroy"
               return
      return
    return

# Delete button
$('#delete').click ->
    deleteUser()

# Delete icon in the table row
$('#userlistWrapper').on "click", ".delete-single-user", ->
    $checkbox = $(@).closest('tr').find(':checkbox')
    $checkbox.prop 'checked', true
    deleteUser()

# Call the deleteAction from the app main.js
deleteUser = () ->
    title = 'User delete'
    message = 'user(s)'
    url = Routing.generate 'OpitNotesUserBundle_user_delete'
    $(document).data('notes').funcs.deleteAction(title, message, url, '.list-delete-user')
        
$('#userlistWrapper').on "click", "th i", ->
    $('.list-delete-user').checkAll()
    
$('#list').on "click", "#list-reply-message", ->
    $(@).hide()