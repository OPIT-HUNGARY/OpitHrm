$(document).data 'OpitNotesUserBundle', {}

# Declare any generic user bundle functions here
$.extend true, $(document).data('OpitNotesUserBundle'),
    funcs:
        userEdit: (userId, successCallback) ->
          $.ajax
            method: 'GET'
            url: Routing.generate 'OpitNotesUserBundle_user_show', id: userId
          .done (data) ->
            $('<div id="dialog-edititem"></div>').html(data)
              .dialog
                  title: '<i class="fa fa-list-alt"></i> Edit User'
                  modal: on
                  width: 710
                  maxHeight: 600
                  open: ->
                    $(document).data('notes').funcs.initDateInputs $(@)
                  buttons:
                    Save: ->
                      $.ajax
                        type: 'POST'
                        global: off
                        url: Routing.generate 'OpitNotesUserBundle_user_add', id: userId
                        data: $('#adduser_frm').serialize()
                      .done (data, textStatus, jqXHR)->
                          url = Routing.generate 'OpitNotesUserBundle_user_list'
                          offset = $('.selected-page').data('offset')
                          # Check if current page matches user list and update content (edit user can be triggered from several pages)
                          if url is window.location.pathname and jqXHR.getResponseHeader("content-type").indexOf('html')
                            response = data
                            $.ajax
                              type: 'POST'
                              url: url
                              data: 'offset' : (offset - 1), 'incrementOffset': false
                            .done (data)->
                              $('#user-list').html data
                              $(document).data('notes').funcs.initListPageListeners()
                              $(document).data('notes').funcs.initPager()
                              $(document).data('notes').funcs.initDeleteMultipleListener()
                              $('.selected-page').each ->
                                  $(@).removeClass 'selected-page'
                              $('[data-offset="'+offset+'"]').addClass 'selected-page'
                              postActions = successCallback $('#dialog-edititem'), response, "update", "User modified successfully" if successCallback?
                              $('#dialog-edititem').dialog 'destroy' if postActions or postActions is undefined
                          else
                            $('#dialog-edititem').dialog 'destroy'
                          return
                      .fail (jqXHR, textStatus, errorThrown) ->
                        successCallback $('#dialog-edititem'), $.parseJSON(jqXHR.responseText), "update", "Error"
                    Close: ->
                       $('#dialog-edititem').dialog "destroy"
                       return
              return
            return
        isLdapUser: (userId) ->
            df = $.Deferred();
            $.ajax
                type: 'POST'
                url: Routing.generate 'OpitNotesUserBundle_user_ldap_enabled'
                data: 'id': userId
            .done (data)->
                if data.ldap_enabled is on
                    $('<div id="ldap-password-dialog"></div>').html("This feature is not supported for LDAP users. Please, kindly turn to your system administrator for help.")
                        .dialog
                            width: 500
                            title: '<i class="fa fa-exclamation-triangle"></i> Unsupported feature'
                            close: ->
                                $(@).dialog 'destroy'
                                return
                    df.fail()
                else
                    df.resolve()
                return

            return df

getAllNotifications = ($notificationsContent) ->
    changeStatus = (el, callback) ->
        if el.closest('.notification').hasClass 'unread'
            $.ajax
                method: 'POST'
                url: Routing.generate 'OpitNotesTravelBundle_notifications_state_change'
                data: "id" : el.data('id')
            .complete ->
                # if ajax request is completed, remove unread class
                el.closest('.notification').removeClass 'unread'
                callback() if callback?
                return
        else
            callback() if callback?
        return
    # post an AJAX request to get all notifications
    $.ajax
        method: 'POST'
        url: Routing.generate 'OpitNotesTravelBundle_notifications_all'
    .done (data) ->
        # fill up wrapper with AJAX result
        $notificationsContent.html data
        # add listener to trash icon
        $('.notification-header-delete i').on 'click', ->
            $self = $(@)
            # if delete icon clicked send an AJAX request to delete notification
            $.ajax
                method: 'POST'
                url: Routing.generate 'OpitNotesTravelBundle_notification_delete'
                data: "id" : $self.data('id')
            .done (data) ->
                # if item was deleted remove row from wrapper
                $self.closest('.notification').remove()
                
        # add listener to message container
        $('.notification-message').on 'click', (event) ->
            # if clicked prevent propagation
            event.stopPropagation()
            # Change notification status
            changeStatus $(@)
            return
      
        # prevent propagation for details links
        $('.notification-details').on 'click.notifications', (event) ->
            # stop event bubbling
            event.preventDefault()
            event.stopPropagation()
            $self = $(@)
            # Change notification status
            changeStatus $self.parent(), ->
                window.location.href = $self.attr 'href'
                return
            return
                
        # show notifications wrapper
        $notificationsContent.removeClass 'display-none'
        $('#notifications').removeClass 'right-m312-important'
        
        # update scrollbar for notifications wrapper
        $('#notifications-wrapper').mCustomScrollbar 'update'
               
# check for new notifications
getUnreadNotifications = () ->
    # send an AJAX request to get the number of unread notifications
    $.ajax
        method: 'POST'
        url: Routing.generate 'OpitNotesTravelBundle_notifications_unread_count'
        global: off
    .done (data) ->
        $unreadNotificationsCount = $('#unread-notifications-count')
        $notificationsIcon = $('#notifications i')
        # if number of unread notifications and data returned from the server are not the same
        if $('#unread-notifications').html() !=  data
            # if returned number of notifications is not zero
            if '0' != data
                # show number of unread notifications indicator
                $unreadNotificationsCount.removeClass 'display-none'
                # set globe to active
                $notificationsIcon.addClass 'color-light-green'
                # replace the number in the indicator
                $unreadNotificationsCount.html data
                $('#notifications').addClass 'right-m312-important'
            
        if '0' == $unreadNotificationsCount.html()
            $unreadNotificationsCount.addClass 'display-none'
            $notificationsIcon.removeClass 'color-light-green'
            $('#notifications').removeClass 'right-m312-important'
            
        # check for new notifications every 10 seconds
        setTimeout getUnreadNotifications, 10000

# Place any jQuery/helper plugins in here.

$.fn.extend
    formIsEmpty: (element) ->
        $el = if element then $(element) else $(@)
        exists = no
        $el.find(':input').each ->
            if $(@).attr('type') != 'hidden'
                exists = yes if($(@).val())
        return exists

initMenu = ->
    $menu = $('#menu:first')
    $clone = $('#menu.clone')

    if $menu.length > 0 and $clone.length is 0
        $clone = $menu.clone()
        $clone.attr
            class: 'clone'
        .css
            top: 0
        
        $('body').append($clone);
        
    startHeight = parseInt($menu.offset().top) + parseInt($menu.outerHeight()) + 25
    
    # scroll method for sticky header
    $(window).scroll ->
        # if page scroll is below submenu top show submenu clone
        if $menu.length > 0 and $clone.length > 0
            if startHeight < parseInt($(window).scrollTop())
                $('#menu.clone').slideDown('fast')
            else
                $('#menu.clone').slideUp('fast')

$(document).ready ->
    # init the menu
    do initMenu if $('#menu').length > 0

    $(document).data('notes').funcs.initDeleteMultipleListener()
    $(document).data('notes').funcs.initListPageListeners()
    $(document).data('notes').funcs.initPager()

    # add scrollbar to notifications
    $('#notifications-wrapper').mCustomScrollbar()

    $('#notifications > i.fa-bell-o').on 'click.notifications', (event) ->
        # stop event bubbling
        event.stopPropagation()
        $container = $(@).parent()
        if !$container.hasClass 'right-m15-important'
            $container.addClass 'right-m15-important'
            # remove classes that make the notifications tab active
            $(@).removeClass 'color-light-green'
            $('#unread-notifications-count').addClass 'display-none'
            # call get all notifications function
            getAllNotifications $('#notifications-content')

            # prevent event propagation for elements inside notifications container
            $('#notifications-wrapper').on 'click.notifications', (event) ->
                event.stopPropagation()

            # register hide listener clicking outside of the notifications boundaries
            $('body').on 'click.notifications', (event) ->
                if $('#notifications').hasClass 'right-m15-important'
                    $('#notifications').removeClass 'right-m15-important'

                    # detach event listener if notifications are hidden
                    $('body, #notifications-wrapper').off 'click.notifications'
        else
            $container.removeClass 'right-m15-important'

            # detach event listener if notifications are hidden
            $('body, #notifications-wrapper').off 'click.notifications'

    # start checking for new notifications
    getUnreadNotifications() if $('#notifications').length > 0

    $('#loggedInUser.edit').click ->
        $(document).data('OpitNotesUserBundle').funcs.userEdit $(@).children('span').data('user-id'), $(document).data('notes').funcs?.showAlert

    $(document).on 'click', '.ui-button-text', ->
        buttonText = $(@).html()
        if buttonText == 'Yes' or buttonText == 'Continue'
            $(document).data('notes').funcs.changeDeleteButton true

    $('#changePassword').on 'click', ->
        id = $(@).attr "data-user-id"

        # Only allow password changes for local users
        $(document).data('OpitNotesUserBundle').funcs.isLdapUser(id).done ->
            $.ajax
                method: 'GET'
                url: Routing.generate 'OpitNotesUserBundle_user_show_password', id: id
            .done (data) ->
                $('<div id="password-dialog"></div>').html(data)
                .dialog
                    title: '<i class="fa fa-list-alt"></i> Reset Password'
                    open: ->
                        $(@).html(data)
                    width: 600
                    modal: on
                    buttons:
                        Save: ->
                            $.ajax
                                type: 'POST'
                                global: false
                                url: Routing.generate 'OpitNotesUserBundle_user_update_password', id: id
                                data: $('#changePassword_frm').serialize()
                            .done (data)->
                                $('#password-dialog').dialog 'destroy'
                                $(document).data('notes').funcs.showAlert $('#password-dialog'), data, 'update', 'Password successfully changed'
                            .fail (jqXHR, textStatus, errorThrown) ->
                                $(document).data('notes').funcs.showAlert $('#password-dialog'), $.parseJSON(jqXHR.responseText), 'update', 'Password reset successfully'
                        Close: ->
                            $('#password-dialog').dialog 'destroy'
                            return
            return
        return
                


