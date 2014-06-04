$(document).ready ->
    $('#add').click ->
      $.ajax
        method: 'GET'
        url: Routing.generate 'OpitNotesUserBundle_user_show', id: 0
      .done (data) ->
        $('<div id="dialog-edititem"></div>').html(data)
          .dialog
              title: '<i class="fa fa-list-alt"></i> Create User'
              dialogClass: 'popup-dialog'
              width: 750
              maxHeight: 600
              open: ->
                $(document).data('notes').funcs.initDateInputs $(@)
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
                          $(document).data('notes').funcs.showAlert $('#dialog-edititem'), response, 'create', 'User created successfully'
                          $('#dialog-edititem').dialog "destroy"
                  .fail (data) ->
                      response = data.responseText
                      if typeof response is 'string'
                          response = $.parseJSON response
                      $(document).data('notes').funcs.showAlert $('#dialog-edititem'), response, 'create', 'Error'
                Close: ->
                    $('#dialog-edititem').dialog 'destroy'
                    return
          return
        return

    $('#userlistWrapper').on 'click', '.list-username', ->
        $(document).data('OpitNotesUserBundle').funcs.userEdit($(@).attr('data-user-id'), $(document).data('notes').funcs.showAlert)
        return

    deleteUser = () ->
      url = Routing.generate 'OpitNotesUserBundle_user_delete'
      return $(document).data('notes').funcs.deleteAction 'User delete', 'user(s)', url, '.list-delete-user'

    # Delete icon in the table row
    $('#userlistWrapper').on 'click', '.delete-single-user', ->
        $(@).closest('tr').find(':checkbox').not('disabled').prop 'checked', true
        deleteUser()

    $('#userlistWrapper').on 'click', '.reset-password', ->
        _self = $(@)
        userId = _self.data('user-id');

        # Only allow password changes for local users
        $(document).data('OpitNotesUserBundle').funcs.isLdapUser(userId).done ->
            $('<div id="reset-password-dialog"></div>').html(
                "Are you sure you want to reset <b class='underline'>#{ _self.closest('tr').find('.list-username').html() }'s</b> password ?
                The user will be informed about new password via email."
            )
                .dialog
                    title: '<i class="fa fa-exclamation-triangle"></i> Reset user password'
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
            return
        return
        
    $('#userlistWrapper').on 'click', 'th .fa-trash-o', ->
        $('.list-delete-user').filter(() -> return not @.disabled).checkAll()

    $('#list').on 'click', '#list-reply-message', ->
        $(@).hide()

    $('#user-list').on 'click', '.order-text', ->
        $(document).data('notes').funcs.serverSideListOrdering $(@), $(@).parent().find('i').attr('data-field'), 'OpitNotesUserBundle_user_list', 'user-list'
    $('#user-list').on 'click', '.fa-sort', ->
        $(document).data('notes').funcs.serverSideListOrdering $(@), $(@).data('field'), 'OpitNotesUserBundle_user_list', 'user-list'