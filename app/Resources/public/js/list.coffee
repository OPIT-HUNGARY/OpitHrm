$.extend true, $(document).data('notes'),
    funcs:
        clientSideListOrdering: ($self, inverse) ->
            $form = $('#searchFormWrapper').find 'form'
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
                
            order = if inverse then 'desc' else 'asc'
            $('#list-table').find('.fa-sort').removeClass('fa-sort-desc').removeClass('fa-sort-asc')
            $self.removeClass('fa-sort-asc').addClass "fa-sort-#{order}"

            $form.find('#order_field').val $self.data('field')
            $form.find('#order_dir').val order
            
            return not inverse
        
        serverSideListOrdering: ($self, dataField, url, toRelplace) ->
            $form = $('#searchFormWrapper').find 'form'
            order = $form.find('#order_dir').val()
            order = if order is 'desc' then 'asc' else 'desc'
            $form.find('#order_field').val dataField
            $form.find('#order_dir').val order

            $.ajax
               method: 'POST'
               url: Routing.generate url
               data: 'showList=1&' + $form.serialize()
             .done (data) ->
                $toReplace = $('#' + toRelplace).html data
                $(document).data('notes').funcs.initPager()
                $(document).data('notes').funcs.initListPageListeners()
                $(document).data('notes').funcs.initDeleteMultipleListener()
                $toReplace.find('th').eq($self.parent().index()).find('i').addClass(if order is 'desc' then 'fa-sort-desc' else 'fa-sort-asc')
        
        changeDeleteButton: (disableInputCheck = false) ->
            $deleteButton = $('#delete').addClass('button-disabled').attr 'disabled', 'disabled'
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
        
        # TODO: Remove any component specific code and make the API fully reusable.
        initListPageListeners: () ->
            $('.icon-disabled').on 'click', (event)->
                event.preventDefault()

            $('#list-table th .fa-trash-o').click ->
                $('.deleteMultiple').filter(() ->
                    return not @.disabled).checkAll $(document).data('notes').funcs.changeDeleteButton
                return

            $('#list-table .deleteSingeTravelRequest').click (event) ->
                event.preventDefault()
                $(document).data('notes').funcs.deleteSingleRequest 'request', $(@)

            $('#delete').off('click.deleteList').on 'click.deleteList', ->
                if $('#userlistWrapper').length is 1
                    title = 'User delete'
                    message = 'user(s)'
                    url = Routing.generate 'OpitNotesUserBundle_user_delete'
                    $(document).data('notes').funcs.deleteAction(title, message, url, '.deleteMultiple')
                    return false
                else if $('#travel_list').length is 1
                    warningMessage = 'Are you sure you want to delete the selected travel requests?'
                    checkBoxClass = '.deleteMultiple'
                    url = Routing.generate 'OpitNotesTravelBundle_travel_delete'
                    title = 'Travel request removal'
                    errorText = 'The travel request could not be deleted due to an error.'
                else if $('#leave_list').length is 1
                    warningMessage = 'Are you sure you want to delete the selected leave requests?'
                    checkBoxClass = '.deleteMultiple'
                    url = Routing.generate 'OpitNotesLeaveBundle_leaverequest_delete'
                    title = 'Leave request removal'
                    errorText = 'The leave request could not be deleted due to an error.'
                else
                    return false

                requests = []
                selectedRequests = []
                $(checkBoxClass).each ->
                    if $(@).is ':checked'
                        requests.push $(@).val()
                        selectedRequests.push $(@).parent().parent()

                $('<div></div>').html(warningMessage).dialog
                    title: title
                    buttons:
                        Yes: ->
                            $.ajax
                              method: 'POST'
                              url: url
                              data: 'id': requests
                            .done (data) ->
                                $(selectedRequests).each ->
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
        
        setPagerNumbering: () ->
            offset = $('#pager').data 'offset'
            pages = $('#pager').data 'pages'
            maxPages = $('#pager').data 'max'
            $('#pager span').remove()

            pagesOnOneSide = Math.floor(maxPages/2)
            min = offset - pagesOnOneSide
            max = offset + pagesOnOneSide

            if 0 >= min || offset < maxPages
                min = 1
                max = maxPages

            if max > pages
                max = pages
                # Increase the minimum if offset is reached the max pages.
                if offset >= maxPages
                    min = pages - maxPages + 1

            # If the max pages is an odd value
            if maxPages % 2 == 0
                diff = max - min
                if diff < maxPages - 1 and offset >= maxPages
                    # set the max value of interval
                    max = Math.abs(max - (maxPages - diff))
                if diff >= maxPages and max >= maxPages
                    # set the min value of interval.
                    min = Math.abs(min + 1)

            for num in [max..min]
                $newPagerItem = $('<span>')
                                    .html (num)
                                    .addClass('cursor-pointer')
                                    .attr('data-offset', (num))
                $newPagerItem.insertAfter $('#pager .fa-caret-left')        
        
        reInitializeListTableListeners: () ->
            $(document).data('notes').funcs.setPagerNumbering()
            $(document).data('notes').funcs.initListPageListeners()
            $(document).data('notes').funcs.initPager()
            $(document).data('notes').funcs.initDeleteMultipleListener()
        
        initPager: () ->
            $pager = $('#pager')
            selectedPageOffset = $pager.data 'offset'
            maxVisiblepages = $pager.data 'max'
            $pager.find('[data-offset="'+selectedPageOffset+'"]').addClass 'selected-page'
            totalNumberOfPages = $pager.data 'pages'
            requestUrl = $pager.data 'url'
            
            if selectedPageOffset == totalNumberOfPages
                $('.fa-caret-right').addClass 'visibility-hidden'
            if selectedPageOffset == 1
                $('.fa-caret-left').addClass 'visibility-hidden'
            
            if totalNumberOfPages < maxVisiblepages
                $('.fa-caret-left').addClass 'visibility-hidden'
                $('.fa-caret-right').addClass 'visibility-hidden'

            $pager.on 'mousedown', 'span', (event) ->
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
                    $(document).data('notes').funcs.reInitializeListTableListeners()

            $('#pager i').on 'mousedown', (event) ->
                self = $(@)
                $selectedPage = $('.selected-page')

                # check which arrow was clicked
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
                    $('#list-table').parent().replaceWith data
                    $(document).data('notes').funcs.reInitializeListTableListeners()