$(document).ready ->
    $('.changeState').on 'change', ->
        $(document).data('notes').funcs.changeStateDialog $(@), $(document).data('notes').funcs.changeLeaveRequestStatus, $(@).data('lr'), 'leave'
        
    $('#leave_request_team_manager_ac').autocomplete
        source: Routing.generate 'OpitNotesUserBundle_user_search', role: 'team_manager'
        minLength: 2
        select: (event, ui) ->
            $('#leave_request_team_manager').val ui.item.id
            return

    $('#leave_request_general_manager_ac').autocomplete
        source: Routing.generate 'OpitNotesUserBundle_user_search', role: 'general_manager'
        minLength: 2
        select: (event, ui) ->
            $('#leave_request_general_manager').val ui.item.id
            return        

    $('#leave_request').find('label:first').remove()
    $collectionHolder = $('#leave_request_leaves')
    $collectionHolder.data 'index', 0
    
    prototype = $collectionHolder.data 'prototype'
    $prototype = $(prototype)
    $prototype.find('.start-date').parent().addClass('display-inline-block')
    $prototype.find('.end-date').parent().addClass('display-inline-block margin-left-5')
    prototype = $prototype.html()
    prototype = prototype.replace '<label class="required">__name__label__</label>', ''
    
    $form = $collectionHolder.closest 'form'
    $form.prepend $('.formFieldset')
    $form.find('#leave_request_create_leave_request').parent().append $('#cancel-button')
    
    $requiredApprovals = $('#required-approvals')
    $requiredApprovals.append($('#leave_request_team_manager_ac').parent().addClass('display-inline-block vertical-align-top margin-right-1-em'))
    $requiredApprovals.append($('#leave_request_general_manager_ac').parent().addClass('display-inline-block vertical-align-top margin-right-1-em'))
    
    $employeeError = $('#leave_request').find('ul')
    if $employeeError.length > 0
        $alertMessage = $('.alert-message')
        $alertMessage.removeClass 'display-none'
        $alertMessage.append $employeeError
    
    createDeleteButton = () ->
        $deleteButtonWrapper = $('<div>').addClass 'deleteFormFieldsetChild formFieldsetButton form-fieldset-delete-button'
        $deleteButtonWrapper.html '<i class="fa fa-minus-square"></i>Delete'
        $deleteButtonWrapper.on 'click', ->
            $(@).closest('.formFieldsetChild').remove()
            
        return $deleteButtonWrapper
        
    createHolidayRequest = ($holidayRequest) ->
        if typeof $holidayRequest is 'object'
            $holidayRequest.find('.start-date').parent().addClass 'display-inline-block'
            $holidayRequest.find('.end-date').parent().addClass 'display-inline-block margin-left-5'
            
        index = $collectionHolder.data 'index'
        $requestContainer = $('<div>').addClass 'formFieldsetChild padding-10 margin-left-1-em margin-bottom-1-em display-inline-block vertical-align-top'
        if $holidayRequest is undefined
            $holidayRequest = $(prototype.replace /__name__/g, index)
            $requestContainer.append $holidayRequest

            # init datepicker plugin
            $(document).data('notes').funcs.initDateInputs $holidayRequest
        else
            $requestContainer.append $holidayRequest

        $holidayRequest.append createDeleteButton()
        
        $errorList = $holidayRequest.find('ul')
        $errorListParent = $errorList.parent()
        if $errorList.length > 0
            $errorListParent.append $('<label>').addClass('error').html($errorList.find('li:first').html())
            $input = $errorListParent.find 'input'
            $input.addClass 'error'
            
            $errorList.remove()
            
        $holidayRequest.find('.start-date').removeAttr 'required'
        $holidayRequest.find('.end-date').removeAttr 'required'
            
        
        $requestContainer.insertBefore $('.addFormFieldsetChild')
        $collectionHolder.data('index', index + 1)
        return        
        
    validateDates = () ->
        valid = yes
        $('.formFieldsetChild').each (index) ->
            $startDate = $(@).find('.start-date')
            $startDateParent = $startDate.parent()
            startDateVal = $startDate.val()
            
            $endDate = $(@).find('.end-date')
            $endDateParent = $endDate.parent()
            endDateVal = $endDate.val()
            
            $startDate.removeClass 'error'
            $startDateParent.find('label.error').remove()
            
            $endDate.removeClass 'error'
            $endDateParent.find('label.error').remove()            
            
            if startDateVal == ''
                $errorLabel = $('<label>').addClass('error').html 'Start date cannot be empty'
                $startDate.addClass 'error'
                $startDateParent.append $errorLabel
                valid = no
                
            if endDateVal == ''
                $errorLabel = $('<label>').addClass('error').html 'End date cannot be empty'
                $endDate.addClass 'error'
                $endDateParent.append $errorLabel                
                valid = no
            
            if valid is yes
                if startDateVal > endDateVal
                    if $startDate.hasClass('error') is no
                        $startDate.addClass 'error'
                        $errorLabel = $('<label>').addClass('error').html 'Start date should be bigger than end date.'
                        $startDateParent.append $errorLabel
                    valid = no
        return valid
    
    $collectionHolder.children().each (index) ->
        $(@).find('label:first').remove()
        createHolidayRequest($(@))
    
    $('.addFormFieldsetChild').on 'click', ->    
        createHolidayRequest()
        
    $('.disabled .deleteFormFieldsetChild').each ->
        $(@).remove()
        
    $('.disabled select, .disabled input, .disabled textarea').each ->
        $(@).attr 'disabled', 'disabled'
        
    $('.disabled #leave_request_create_leave_request').addClass 'button-disabled'
    $('.disabled #leave_request_create_leave_request').attr 'disabled', 'disabled'
    $('.disabled').find('.addFormFieldsetChild').remove()        

    $( '#leave_request_create_leave_request' ).on 'click', (event) ->
        event.preventDefault()
        isValid = yes
        if $('.formFieldsetChild').length <= 0
            isValid = no
            $errorContainer = $('#reply-message')
            $errorMessage = $('<ul>').addClass('leave-error').append $('<li>').html('No leave date added.')
            $errorContainer.append $errorMessage
            $errorContainer.removeClass 'display-none'
        else
            $('#reply-message').addClass 'display-none'
            $('.leave-error').remove()
            
        if validateDates() is no
            isValid = no
            
        if isValid is yes
            $form.submit()
        