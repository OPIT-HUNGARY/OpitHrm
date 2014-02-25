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

    $('#userlistWrapper').on 'click', '.list-change-password', ->
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
                width: 500
                modal: on
                buttons:
                    Save: ->
                        $.ajax
                            type: 'POST'
                            global: false
                            url: Routing.generate 'OpitNotesUserBundle_user_update_password', id: id
                            data: $('#changePassword_frm').serialize()
                        .done (data)->
                            $(document).data('notes').funcs.showAlert data, 'update','Password reset successfully'
                            $('#dialog-edititem').dialog "destroy"
                        .fail (data) ->
                            data = $.parseJSON data.responseText
                            $(document).data('notes').funcs.showAlert data, 'update','Password reset successfully'
                    Close: ->
                        $('#dialog-edititem').dialog 'destroy'

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

    $('#userlistWrapper').on 'click', 'th .fa-trash-o', ->
        $('.list-delete-user').filter(() -> return not @.disabled).checkAll()

    $('#list').on 'click', '#list-reply-message', ->
        $(@).hide()

    # Ordering.
    $('#user-list').on 'click', '.fa-sort', ->
        indexOfTh = $(@).parent().index()
        field = $(@).attr('data-field')
        $form = $('#searchFormWrapper').find 'form'
        order = $form.find('#order_dir').val()
        order = if order is 'desc' then 'asc' else 'desc'
        $form.find('#order_field').val field
        $form.find('#order_dir').val order
        searchData = $form.serialize()

        $.ajax
           method: 'POST'
           url: Routing.generate 'OpitNotesUserBundle_user_list'
           data: 'showList=1&' + searchData
         .done (data) ->
            $('#user-list').html(data)
            $(document).data('notes').funcs.initPager()
            if order is 'desc'
                $('#user-list').find('th').eq(indexOfTh).children().addClass 'fa-sort-desc'
            else
                $('#user-list').find('th').eq(indexOfTh).children().addClass 'fa-sort-asc'