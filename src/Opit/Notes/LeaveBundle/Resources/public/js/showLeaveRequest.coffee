$(document).ready ->
    $('#leave_request').find('label:first').remove()
    $collectionHolder = $('#leave_request_leaves')
    $collectionHolder.data 'index', 0
    prototype = $collectionHolder.data 'prototype'
    prototype = prototype.replace '<label class="required">__name__label__</label>', ''
    $form = $collectionHolder.closest 'form'
    $form.prepend $('.formFieldset')
    $form.find('#leave_request_create_leave_request').parent().append $('#cancel-button')
    
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

    $( '#leave_request_create_leave_request' ).on 'click', (event) ->
        event.preventDefault()
        if validateDates() is yes
            $form.submit()
        