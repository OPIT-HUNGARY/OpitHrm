$("#add").click ->
  $.ajax
    method: 'POST'
    url: $(document).data('OpitNotesUserBundle').urls.OpitNotesUserBundle_user_show
    data: 'edit': 0
  .done (data) ->
    $('<div id="dialog-edititem"></div>')
      .dialog
          open: ->
            $('.ui-dialog-title').append ('<i class="fa fa-list-alt"></i> Create User')
            $(@).html(data)
          dialogClass: 'popup-dialog'
          width: 750
          modal: on
          buttons:
            Create: ->
              $.ajax
                type: 'POST'
                global: false
                url: $(document).data('OpitNotesUserBundle').urls.OpitNotesUserBundle_user_add
                data: $('#adduser_frm').serialize()
              .done (data)->
                  response = data
                  $.ajax
                    type: 'POST'
                    global: false
                    url: $(document).data('OpitNotesUserBundle').urls.OpitNotesUserBundle_user_list
                    data: "showList" : 1
                  .done (data)->
                    $('#list-table').html data
                    $('<div id="dialog-success"></div>')
                      .dialog
                        open: ->
                          $('#dialog-success .ui-dialog-title').append ('<i class="fa fa-check-square-o"></i> Message')
                          if response[0].response == 'error'
                            $(@).html(response[0].errorMessage)
                          else
                            $(@).html("Successfully added the new user!")
                        title: 'Message'
                        width: 400
                        buttons:
                          Close: ->
                            $('#dialog-success').dialog "destroy"
                            return
                        close: ->
                          $(@).dialog "destroy"
                          return
                      return
                    $('#dialog-edititem').dialog "destroy"
                    return
            Close: ->
               $('#dialog-edititem').dialog "destroy"
               return
      return
    return

$("#list-table").on "click", ".list-username", ->
  event.preventDefault()
  id = $(@).attr "data-user-id"
  $.ajax
    method: 'POST'
    url: $(document).data('OpitNotesUserBundle').urls.OpitNotesUserBundle_user_show
    data: 'edit': 1, 'userId': id
  .done (data) ->
    $('<div id="dialog-edititem"></div>')
      .dialog
          open: ->
            $('.ui-dialog-title').append ('<i class="fa fa-list-alt"></i> Edit User')
            $(@).html(data)
          dialogClass: 'popup-dialog'
          width: 750
          modal: on
          buttons:
            Save: ->
              $.ajax
                type: 'POST'
                global: false
                url: $(document).data('OpitNotesUserBundle').urls.OpitNotesUserBundle_user_add
                data: $('#adduser_frm').serialize()
              .done (data)->
                  response = data
                  $.ajax
                    type: 'POST'
                    global: false
                    url: $(document).data('OpitNotesUserBundle').urls.OpitNotesUserBundle_user_list
                    data: "showList" : 1
                  .done (data)->
                    $('#list-table').html data
                    $('<div id="dialog-success"></div>')
                            .dialog
                              open: ->
                                $('#dialog-success .ui-dialog-title').append ('<i class="fa fa-check-square-o"></i> Message')
                                if response[0].response == 'error'
                                  $(@).html(response[0].errorMessage)
                                else
                                  $(@).html("Successfully modified the user!")
                              title: 'Message'
                              width: 400
                              buttons:
                                Close: ->
                                  $('#dialog-success').dialog "destroy"
                                  return
                              close: ->
                                $(@).dialog "destroy"
                                return
                            return
                    $('#dialog-edititem').dialog "destroy"
                    return
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
  $.ajax
    type: 'POST'
    global: false
    url: $(document).data('OpitNotesUserBundle').urls.OpitNotesUserBundle_user_delete
    data: "userIds" : users
  .done (data)->
    response = data
    $.ajax
      type: 'POST'
      global: false
      url: $(document).data('OpitNotesUserBundle').urls.OpitNotesUserBundle_user_list
      data: "showList" : 1
    .done (data)->
      $('#list-table').html data
      return
