$(document).ready ->
    $('#add').click ->
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
                          $(document).data('notes').funcs.showAlert response, 'create', 'User created successfully'
                          $('#dialog-edititem').dialog "destroy"
                  .fail (data) ->
                      response = data.responseText
                      if typeof response is 'string'
                          response = $.parseJSON response
                      $(document).data('notes').funcs.showAlert response, 'create','Error'
                 Close: ->
                   $('#dialog-edititem').dialog 'destroy'
                   return
          return
        return

    $('#userlistWrapper').on 'click', '.list-username', ->
        id = $(@).attr 'data-user-id'
        $(document).data('OpitNotesUserBundle').funcs.userEdit id, $(document).data('notes').funcs.showAlert
        return

    deleteUser = () ->
      title = 'User delete'
      message = 'user(s)'
      url = Routing.generate 'OpitNotesUserBundle_user_delete'
      return $(document).data('notes').funcs.deleteAction title, message, url, '.list-delete-user'

    # Delete icon in the table row
    $('#userlistWrapper').on 'click', '.delete-single-user', ->
        $checkbox = $(@).closest('tr').find(':checkbox').not('disabled')
        $checkbox.prop 'checked', true
        deleteUser()

    $('#userlistWrapper').on 'click', '.reset-password', ->
        employeeName = $(@).closest('tr').find('td:nth-child(4)').html()
        userId = $(@).data 'user-id'
        $('<div id="reset-password-dialog"></div>').html(
            "Are you sure you want to reset <b class='underline'>#{ employeeName }'s</b> password ?
            The user will be informed about new password via email."
        )
            .dialog
                open: ->
                  $('.ui-dialog-title').append ('<i class="fa fa-exclamation-triangle"></i> Reset user password')
                dialogClass: 'popup-dialog'
                width: 750
                modal: on
                buttons:
                  Reset: ->
                    $.ajax
                        global: false
                        type: 'POST'
                        url: Routing.generate 'OpitNotesUserBundle_user_password_reset'
                        data: 'id': userId
                    .done (data)->
                        $('#reset-password-dialog').dialog 'destroy'
                    .fail (data) ->
                        console.warn data
                  Close: ->
                      $('#reset-password-dialog').dialog 'destroy'
                      return
        
    $('#userlistWrapper').on 'click', 'th .fa-trash-o', ->
        $('.list-delete-user').filter(() -> return not @.disabled).checkAll()

    $('#list').on 'click', '#list-reply-message', ->
        $(@).hide()

    $('#user-list').on 'click', '.order-text', ->
        $(document).data('notes').funcs.serverSideListOrdering $(@), $(@).parent().find('i').attr('data-field'), 'OpitNotesUserBundle_user_list', 'user-list'
    $('#user-list').on 'click', '.fa-sort', ->
        $(document).data('notes').funcs.serverSideListOrdering $(@), $(@).data('field'), 'OpitNotesUserBundle_user_list', 'user-list'