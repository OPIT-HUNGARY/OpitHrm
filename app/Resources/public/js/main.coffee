$(document).data 'notes', {}
$.extend true, $(document).data('notes'),
    funcs:
        clientSideListOrdering: ($self, inverse) ->
            header = $self.parent()
            index = header.index()
            header
                .closest('table')
                .find('td')
                .filter () ->
                    return $(@).index() == index
                .sort(
                    (a,b) ->
                        a = $(a).text()
                        b = $(b).text()
                        return if (if isNaN(a) or isNaN(b) then a > b else +a > +b) then (if inverse then -1 else 1) else (if inverse then 1 else -1)
                    () ->
                        return @.parentNode
                )
            inverse = not inverse
            $('#list-table').find('.fa-sort').removeClass('fa-sort-desc').removeClass('fa-sort-asc')
            $self.removeClass('fa-sort-asc').addClass if inverse then 'fa-sort-desc' else 'fa-sort-asc'
            
            return inverse
            
        serverSideListOrdering: ($self, dataField, url, toRelplace) ->
            index = $self.parent().index()
            $form = $('#searchFormWrapper').find 'form'
            order = $form.find('#order_dir').val()
            order = if order is 'desc' then 'asc' else 'desc'
            $form.find('#order_field').val(dataField)
            $form.find('#order_dir').val order

            $.ajax
               method: 'POST'
               url: Routing.generate url
               data: 'showList=1&' + $form.serialize()
             .done (data) ->
                $('#' + toRelplace).html(data)
                $(document).data('notes').funcs.initPager()
                $('#' + toRelplace).find('th').eq(index).find('i').addClass(if order is 'desc' then 'fa-sort-desc' else 'fa-sort-asc')
            
        deleteSingleRequest: (type, self) ->
            $checkbox = self.closest('tr').find(':checkbox')
            $checkbox.prop 'checked', true
            # TODO: Add travel request ID to the dialog body text.
            #$('<div></div>').html("Are you sure you want to delete the travel request \"#{travel-request-id}\"?").dialog
            $('<div></div>').html("Are you sure you want to delete the travel #{ type }?").dialog
                title: 'Travel request removal'
                buttons:
                    Yes: ->
                        $.ajax
                          method: 'POST'
                          url: if type is 'expense' then Routing.generate 'OpitNotesTravelBundle_expense_delete' else Routing.generate 'OpitNotesTravelBundle_travel_delete'
                          data: 'id': self.data 'id'
                        .done (data) ->
                            if data is '0' then self.parent().parent().remove()
                            return
                        .fail () ->
                            $('<div></div>').html("The travel #{ type } could not be deleted due to an error.").dialog
                                title: 'Error'
                        $(@).dialog 'close'
                        return
                    No: ->
                        # Unset checkbox
                        $checkbox.prop 'checked', false
                        $(@).dialog 'close'
                        return
                close: ->
                    $(@).dialog 'destroy'
                    return
            return
            
        deleteAction: (title, message, url, identifier) ->
          if $(identifier+':checked').length > 0
            $('<div></div>').html('Are you sure you want to delete the '+message+'?').dialog
              title: title
              buttons:
                  Yes: ->
                      $.ajax
                        method: 'POST'
                        url: url
                        data: $(identifier).serialize()
                      .done (data) ->
                        if data[0].userRelated
                            $(document).data('notes').funcs.showAlert data, 'create', 'Deletion not allowed for roles with relations', true
                        else
                          $(identifier+':checked').closest('tr').remove()
                          return
                      .fail () ->
                          $('<div></div>').html('The '+message+' could not be deleted due to an error.').dialog
                              title: 'Error'
                      $(@).dialog 'close'
                      return
                  No: ->
                      $(identifier + ':checkbox').attr 'checked', false
                      $(@).dialog 'close'
                      return
              close: ->
                  $(@).dialog 'destroy'
                  return
                
        showAlert: (response, actionType, message, forceClass) ->
            $('#reply-message').addClass "alert-message"
            
            if typeof response is not "string"
                response = $.parseJSON response
                
            if response[0]? and response[0].response == 'error'
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
                $('#reply-message')
                  .removeClass('alert-message error-message')
                  .empty()
                returnVal = on
              
              if forceClass
                $('#list-reply-message').removeClass('success-message').addClass('error-message')

            return returnVal
            
        createButton: (text, classes, id, $parent = '', redirectAction = '') ->
            $button = $('<div>')
            $button.html text
            $button.addClass classes
            $button.attr 'id', id
               
            if '' != redirectAction
                $button.on 'click', ->
                    window.location.href = Routing.generate redirectAction
               
            if '' != parent
                $parent.append $button
                
            return $button
            
        makeElementToggleAble: (parent, $toggleItems, elementToToggle = '') ->
            $toggleItems.each ->
                $parent = $(@).find(parent)
                self = $(@)
                $toggleIcon = $('<i>')
                $toggleIcon.addClass 'fa fa-chevron-up toggle-icon'
                $toggleIcon.addClass 'color-white background-color-orange border-radius-5 cursor-pointer float-right'
                $toggleIcon.on 'click', ->
                    if '' != elementToToggle
                        $elementToToggle = self.find elementToToggle
                        if not $elementToToggle.is(':animated')
                            $toggleIcon.toggleClass 'fa-chevron-down'
                            $elementToToggle.slideToggle()
                    else
                        if not $parent.next().is(':animated')
                            $toggleIcon.toggleClass 'fa-chevron-down'
                            $parent.next().slideToggle()
                $parent.append $toggleIcon
                
        changeDeleteButton: (disableInputCheck = false) ->
            $deleteButton = $('#delete')
            $deleteButton.attr 'disabled', 'disabled'
            $deleteButton.addClass 'button-disabled'
            if disableInputCheck is false
                $('#list-table tr td input[type=checkbox]').each ->
                    if $(@).prop 'checked'
                        $deleteButton.removeClass 'button-disabled'
                        $deleteButton.removeAttr 'disabled'
                        return false
                
        initDeleteMultipleListener: () ->
            $deleteButton = $('#delete')
            $deleteButton.attr 'disabled', 'disabled'
            $deleteButton.addClass 'button-disabled'
            $deleteButton.removeClass 'delete'
            
            $('#list-table input[type="checkbox"]').on 'change', ->
                $(document).data('notes').funcs.changeDeleteButton()
                
        initListPageListeners: () ->
            $('.status-history').click (event) ->
                event.preventDefault()
                $.ajax
                    method: 'POST'
                    url: Routing.generate 'OpitNotesTravelBundle_travel_states_history'
                    data: {'id': $(@).find('.fa-book').data 'id'}
                .done (data) ->
                    dialogWidth = 550
                    $('<div id="dialog-show-details-tr"></div>').html(data)
                        .dialog
                            open: ->
                                $('.ui-dialog-title').append ('<i class="fa fa-book"></i> Status history')
                            width: dialogWidth
                            maxHeight: $(window).outerHeight()-100
                            modal: on
                            buttons:
                                Close: ->
                                    $('#dialog-show-details-tr').dialog 'destroy'
                                    return
                    return
        
            $('#travel_list #list-table').on 'click', '.clickable', ->
              $changeState = $(@).closest('tr').find('.changeState')
              travelRequestId = $(@).attr 'data-tr-id'
              firstStatusId = $(@).parent().find('option:first-child').val()
              $.ajax
                method: 'POST'
                url: Routing.generate 'OpitNotesTravelBundle_travel_show_details'
                data: 'id': travelRequestId
              .done (data) ->
                dialogWidth = 550
                $('<div id="dialog-show-details-tr"></div>').html(data)
                  .dialog
                    open: ->
                      $('.ui-dialog-title').append ('<i class="fa fa-list-alt"></i> Details')
                    width: dialogWidth
                    maxHeight: $(window).outerHeight()-100
                    modal: on
                    if firstStatusId is '1' or firstStatusId is '3'
                        buttons:
                          'Send for approval': ->
                             $changeState.addClass 'dropdown-disabled'
                             $(document).data('notes').funcs.changeTravelStatus 2, travelRequestId
                             $('#dialog-show-details-tr').dialog 'destroy'
                          Close: ->
                             $('#dialog-show-details-tr').dialog 'destroy'
                             return
                    else
                        buttons:
                          Close: ->
                             $('#dialog-show-details-tr').dialog 'destroy'
                             return
                return
              return

            $('.icon-disabled').on 'click', (event)->
                event.preventDefault()

            $('#list-table th .fa-trash-o').click ->
                $('.deleteMultipleTravelRequest').checkAll()
                $('.deleteMultipleTravelExpense').checkAll()

            $('#list-table .deleteSingeTravelRequest').click (event) ->
                event.preventDefault()
                $(document).data('notes').funcs.deleteSingleRequest 'request', $(@)

            $('#delete').click ->
                if $('#userlistWrapper').length is 1
                    title = 'User delete'
                    message = 'user(s)'
                    url = Routing.generate 'OpitNotesUserBundle_user_delete'
                    $(document).data('notes').funcs.deleteAction(title, message, url, '.list-delete-user')
                    return false
                else if $('#travel_list').length is 1
                    warningMessage = 'Are you sure you want to delete the selected travel requests?'
                    checkBoxClass = '.deleteMultipleTravelRequest'
                    url = Routing.generate 'OpitNotesTravelBundle_travel_delete'
                    title = 'Travel request removal'
                    errorText = 'The travel request could not be deleted due to an error.'
                else
                    return false

                travelRequests = []
                selectedTravelRequestRow = []
                $(checkBoxClass).each ->
                    if $(@).is ':checked'
                        travelRequests.push $(@).val()
                        selectedTravelRequestRow.push $(@).parent().parent()

                $('<div></div>').html(warningMessage).dialog
                    title: title
                    buttons:
                        Yes: ->
                            $.ajax
                              method: 'POST'
                              url: url
                              data: 'id': travelRequests
                            .done (data) ->
                                $(selectedTravelRequestRow).each ->
                                    $(@).remove()
                                return
                            .fail () ->
                                $('<div></div>').html(errorText).dialog
                                    title: 'Error'
                            $(@).dialog 'close'
                            return
                        No: ->
                            $(@).dialog 'close'
                            return
                    close: ->
                        $(@).dialog 'destroy'
                        return
                return
                
        initPager: () ->
            selectedPageOffset = $('#pager').data 'offset'
            maxVisiblepages = $('#pager').data 'max'
            newSelectedPage = $('#pager').find('[data-offset="'+selectedPageOffset+'"]')
            newSelectedPage.addClass 'selected-page'
            totalNumberOfPages = $('#pager').data 'pages'
            requestUrl = $('#pager').data 'url'
            
            if selectedPageOffset == totalNumberOfPages
                $('.fa-caret-right').addClass 'visibility-hidden'
            if selectedPageOffset == 1
                $('.fa-caret-left').addClass 'visibility-hidden'
            
            if totalNumberOfPages < maxVisiblepages
                $('.fa-caret-left').addClass 'visibility-hidden'
                $('.fa-caret-right').addClass 'visibility-hidden'

            $('#pager').on 'mousedown', 'span', (event) ->
                self = $(@)
                offset = $(@).data 'offset'
                
                $form = $('#searchFormWrapper').find 'form'
                requestData = "offset=#{ offset - 1 }"
                
                if $form.formIsEmpty() is yes
                    requestData = requestData + '&' + $form.serialize()
               
                $.ajax
                    method: 'POST'
                    url: requestUrl
                    data: requestData
                .done (data) ->
                    if data.indexOf('error') < 0
                        $('#list-table').parent().replaceWith data
                        $(document).data('notes').funcs.initListPageListeners()
                        $(document).data('notes').funcs.initPager()

            $('#pager i').on 'mousedown', (event) ->
                self = $(@)
                $selectedPage = $('.selected-page')

                if $(@).hasClass 'fa-caret-left'
                    offset = selectedPageOffset - 1

                else if $(@).hasClass 'fa-caret-right'
                    offset = selectedPageOffset + 1

                $form = $('#searchFormWrapper').find 'form'
                requestData = "offset=#{ offset - 1 }"

                if $form.formIsEmpty() is yes
                    requestData = requestData + '&' + $form.serialize()

                $.ajax
                    method: 'POST'
                    url: requestUrl
                    data: requestData
                .done (data) ->
                    $selectedPage.removeClass 'selected-page'
                    $('#pager span').each ->
                        $(@).removeClass 'selected-page'
                    $pager = $('#pager')
                    $('#list-table').parent().replaceWith data
                    
                    offset = $('#pager').data 'offset'
                    pages = $('#pager').data 'pages'
                    max = $('#pager').data 'max'
                    
                    if offset > max
                        if self.hasClass 'fa-caret-right'
                            $('#pager span').remove()
                            for num in [0..max-1]
                                $newPagerItem = $('<span>')
                                $newPagerItem.html (offset - num)
                                $newPagerItem.attr 'data-offset', (offset - num)
                                $newPagerItem.insertAfter $('#pager .fa-caret-left')
                                
                        if self.hasClass 'fa-caret-left'
                            if offset < $('#pager').first().data 'offset'
                                return false
                            else
                                $('#pager').html $pager.html()
                        
                    $(document).data('notes').funcs.initListPageListeners()
                    $(document).data('notes').funcs.initPager()
                    
###
 * jQuery datepicker extension
 * Datepicker extended by custom rendering possibility
 *
 * @author Sven Henneböle <henneboele@opit.hu>
 * @version 1.0
 * @depends jQuery
 *
 * @param object  options List of options
###
__picker = $.fn.datepicker

$.fn.datepicker = (options) ->
    __picker.apply this, [options]
    $self = @

    options = options or {}
    defaultOptions =
        wrapper: '<div></div>'
        indicatorIcon: $('<i>')
    # Merge passed options
    $.extend true, defaultOptions, options
    
    if options.showOn isnt 'button'
        $self.attr
            readonly: 'readonly'
        .addClass 'icon-prefix-indent'
        defaultOptions.indicatorIcon.addClass 'fa fa-calendar position-absolute input-prefix-position cursor-pointer'
        defaultOptions.indicatorIcon.click ->
            $(@).parent().parent().children('input').focus()
        $self.before defaultOptions.wrapper
        $self.prev().append defaultOptions.indicatorIcon
    return $self


if not Modernizr.inputtypes.date
    $('input[type=date]').each ->
        name = $(@).attr 'name'
        id = $(@).attr('id')
        $(@).after '<input type="hidden" name="'+name+'" id="altDate'+id+'" />'
        $(@).datepicker {altField:'#altDate'+id, altFormat: 'yy-mm-dd'}
        
$(document).ajaxComplete (event, XMLHttpRequest, ajaxOptions) ->
    id = XMLHttpRequest.responseText.match(/id="([\w|-]+)"/)
    $("##{id[1]} *[title]").tipsy() if id?[1]?
    
$(document).ajaxError (event, request, settings) ->
    if window.location.href.indexOf('login') < -1
        if settings.url.indexOf('unread') > -1
            $sessionTimeout = $('<div id="dialog-travelrequest-preview"></div>').html 'Your session has timed out please login again.'
            $sessionTimeout.dialog
                open: ->
                    $('.ui-dialog-title').append '<i class="fa fa-exclamation-circle"></i> Session timeout'
                width: 550
                maxHeight: $(window).outerHeight()-100
                modal: on
                buttons:
                    Login: ->
                        window.location.href = Routing.generate 'OpitNotesUserBundle_security_login'
    
$(document).ready ->
    $('[title]').each ->
        $(@).tipsy()