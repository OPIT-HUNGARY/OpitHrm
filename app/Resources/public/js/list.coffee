$.extend true, $(document).data('opithrm'),
    funcs:
        ###*
         * Helper to submit server side order queries on lists
         *
         * @param object $self The clicked element (icon or label)
         * @param string dataField The entity property to order on
         * @param string url Internal symfony route
         * @param string toReplace A DOM element id selector
         * @param mixed formElement Null or the order form id selector (default: #searchFormWrapper)
        ###
        serverSideListOrdering: ($self, dataField, url, toRelplace, formElement = null) ->
            $form = if formElement then $('#' + formElement) else $('#searchFormWrapper').find 'form'
            order = $form.find('#order_dir').val()
            order = if order is 'desc' then 'asc' else 'desc'
            $form.find('#order_field').val dataField
            $form.find('#order_dir').val order

            try
                url = Routing.generate url

            $.ajax
               method: 'POST'
               url: url
               data: 'showList=1&' + $form.serialize()
             .done (data) ->
                $toReplace = $('#' + toRelplace).html data
                $(document).data('opithrm').funcs.initPager()
                $(document).data('opithrm').funcs.initListPageListeners()
                $(document).data('opithrm').funcs.initDeleteMultipleListener()
                $toReplace.find('th').eq($self.parent().index()).find('i').addClass(if order is 'desc' then 'fa-sort-desc' else 'fa-sort-asc')
        
        changeDeleteButton: (disableInputCheck = false) ->
            $deleteButton = $('#delete').addClass('button-disabled').attr 'disabled', 'disabled'
            $('.list-table tr td input[type=checkbox]').each ->
                if $(@).prop 'checked'
                    $deleteButton.removeClass 'button-disabled'
                    $deleteButton.removeAttr 'disabled'
                    return false
        
        initDeleteMultipleListener: () ->
            $deleteButton = $('#delete')
            $deleteButton.attr 'disabled', 'disabled'
            $deleteButton.addClass 'button-disabled'
            $deleteButton.removeClass 'delete'
            
            $('.list-table input[type="checkbox"]').on 'change', ->
                $(document).data('opithrm').funcs.changeDeleteButton()

        # TODO: Remove any component specific code and make the API fully reusable.
        initListPageListeners: () ->
            $('.icon-disabled').on 'click', (event)->
                event.preventDefault()

            $('.list-table th .fa-trash-o').click ->
                $(this).closest('.list-table').find('.deleteMultiple').filter(() ->
                    return not @.disabled).checkAll $(document).data('opithrm').funcs.changeDeleteButton
                return

        reInitializeListTableListeners: () ->
            $(document).data('opithrm').funcs.initListPageListeners()
            $(document).data('opithrm').funcs.initPager()
            $(document).data('opithrm').funcs.initDeleteMultipleListener()

        initPager: () ->
            $('.list-table').each (key, element) ->
                paginateAction = ($form, requestUrl, requestData) ->
                    $.ajax
                        method: 'POST'
                        url: requestUrl
                        data: requestData
                    .done (data) ->
                        if data.indexOf('error') < 0
                            $element.parent().replaceWith data
                        $(document).data('opithrm').funcs.reInitializeListTableListeners()
                        $field = $element.find('[data-field="' + $form.find('#order_field').val() + '"]')
                        $field.addClass 'fa-sort-' + $form.find('#order_dir').val()

                $element = $(element)
                $pager = $element.next()
                $form = $('#searchFormWrapper form')

                $(document).data('opithrm').funcs.setPagerNumbering $pager

                selectedPageOffset = $pager.data 'offset'
                maxVisiblepages = $pager.data 'max'
                totalNumberOfPages = $pager.data 'pages'
                requestUrl = $pager.data 'url'
                $pager.find('[data-offset="'+selectedPageOffset+'"]').addClass 'selected-page'

                if selectedPageOffset == totalNumberOfPages
                    $pager.find('.fa-caret-right').addClass 'visibility-hidden'
                if selectedPageOffset == 1
                    $pager.find('.fa-caret-left').addClass 'visibility-hidden'

                if totalNumberOfPages < maxVisiblepages
                    $pager.find('.fa-caret-left').addClass 'visibility-hidden'
                    $pager.find('.fa-caret-right').addClass 'visibility-hidden'

                $pager.on 'mousedown', 'span', (event) ->
                    self = $(@)
                    offset = $(@).data 'offset'

                    requestData = "offset=#{ offset - 1 }"
                    requestData = requestData + '&' + $form.serialize()

                    paginateAction $form, requestUrl, requestData

                $pager.find('i').on 'mousedown', (event) ->
                    self = $(@)
                    $selectedPage = $pager.find('i.selected-page')

                    # check which arrow was clicked
                    if $(@).hasClass 'fa-caret-left'
                        offset = selectedPageOffset - 1

                    else if $(@).hasClass 'fa-caret-right'
                        offset = selectedPageOffset + 1

                    requestData = "offset=#{ offset - 1 }"
                    requestData = requestData + '&' + $form.serialize()

                    paginateAction $form, requestUrl, requestData

        resetAndSelectSingle: ($element, container = '.list-table') ->
            # Resetting any prior selections first
            $element.closest(container).find(':checkbox')
                .prop 'checked', false
            # Select the related checkbox for deletion
            $element.closest('tr').find(':checkbox').first()
                .not(':disabled')
                .prop 'checked', true

        setPagerNumbering: ($pager) ->
            offset = $pager.data 'offset'
            pages = $pager.data 'pages'
            maxPages = $pager.data 'max'
            $pager.find('span').remove()

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
                $newPagerItem.insertAfter $pager.find('.fa-caret-left')