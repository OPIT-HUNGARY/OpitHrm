createDeleteButton = ->
    $deleteButton = $('<div>')
    $deleteButton.addClass('deleteFormFieldsetChild formFieldsetButton').html '<i class="fa fa-minus-square"></i>Delete'
    $deleteButton.on 'click', ->
        $(@).parent().remove()
        calculateAdvancesPayback()
        
    return $deleteButton

validateAllExpenseDates = ->
    isDateValid = true
    $formFieldsetChilds = $('.formFieldsetChild')
    $formFieldsetChilds.each ->
        expenseDateField = $(@).find('input[type=date]')
        validateExpenseDate(expenseDateField)
        if expenseDateField.parent().children('.custom-error').length > 0
            isDateValid = false
            return

    return isDateValid

validateExpenseDate = (self) ->
    isDateValid = true
    date = self.val()
    self.addClass 'display-inline-block'
    departureDate = $('#travelExpense_departureDateTime_date').val()
    arrivalDate = $('#travelExpense_arrivalDateTime_date').val()
    if date > arrivalDate or date < departureDate
        if self.parent().children('.custom-error').length < 1
            $errorLabel = $('<label>')
            $errorLabel.addClass 'custom-error'
            $errorLabel.text 'Invalid expense date.'
            self.parent().append $errorLabel
    else
        self.parent().children().remove('.custom-error')

expenseDateChange = (parent) ->
    $dateOfExpenseSpent = parent.find('input[type=date]')
    if $dateOfExpenseSpent.attr('id').indexOf('userPaidExpenses') > -1
        $dateOfExpenseSpent.on 'change', ->
            validateExpenseDate($(@))


reCreateExpenses = (self) ->
    $selectedExpense = $('<span>').addClass 'selected-expense'
    $selectedExpense.html self.find('.te-expense-type').find(':selected').text()
    $container = $('<div>').addClass 'formFieldsetChild'
    self.children('label:first').remove()
    $container.append self
    $container.append createDeleteButton()
    $container.prepend $selectedExpense
    
    expenseDateChange($container)
    
    return $container

addNewForm = (collectionHolder, parent) ->
    # get form data from collection holder
    prototype = collectionHolder.data 'prototype'
    
    index = collectionHolder.data 'index'
    
    prototype = prototype.replace '<label class="required">__name__label__</label>', ''
    newForm = prototype.replace /__name__/g, index
    
    $selectedExpense = $('<span>').addClass 'selected-expense'
    $selectedExpense.html 'Expense type'
    
    $formFieldsetChild = $('<div>').addClass 'formFieldsetChild'
    $formFieldsetChild.append newForm
    $formFieldsetChild.append createDeleteButton()
    $formFieldsetChild.prepend $selectedExpense
    
    expenseDateChange($($formFieldsetChild))
    $formFieldsetChild.find('.currency option[value=EUR]').attr('selected','selected')
    collectionHolder.data 'index', index + 1
    
    # for browsers that do not support input type date
    if not Modernizr.inputtypes.date
        $formFieldsetChild.find('input[type=date]').datepicker();

    parent.find('.addFormFieldsetChild').before $formFieldsetChild
    
createTableRow = (text, value, rowTitle) ->
    $row = $('<tr>')
    
    $textColumn = $('<td>')
    $textColumn.addClass 'bgGrey bold'
    $textColumn.html text + ' <i class="fa fa-clock-o" title="'+rowTitle+'"></i>'
    
    $valueColumn = $('<td>')
    $valueColumn.text value + ' EUR'
    
    if text == 'Total'
        $textColumn.html ''
        $valueColumn.html '<strong>Total</strong><br /> ' + value + ' EUR'
        
    $row.append $textColumn
    $row.append $valueColumn
    
    return $row
    
$perDiem = $('<div>').addClass 'display-inline-block vertical-align-top per-diem-details-wrapper'

convertCurrency = (originCode, destinationCode, value) ->
    if originCode is destinationCode
        return value
    else
        return curConverter.convertCurrency(originCode, destinationCode, value).toFixed(2)

calculateAdvancesPayback = () ->
    advancesRecieved = parseInt $('#travelExpense_advancesRecieved').val()
    destinationCode = $('#travelExpense_currency').val()
    payback = advancesRecieved
    $('.amount').each ->
        paidInAdvance = $(@).closest('.formFieldsetChild').find('.paid-in-advance').val()
        originCode = $(@).closest('.formFieldsetChild').find('.currency').val()
        if paidInAdvance is '0'
            amount = parseInt $(@).val()
            if not isNaN(amount)
                payback -= convertCurrency originCode, destinationCode, amount
                
    if payback <= advancesRecieved and payback >= 0
        $('#travelExpense_advancesSpent').html((advancesRecieved - payback).toFixed(2))
        $('#travelExpense_advancesPayback').html payback.toFixed(2)
        $('.custom-error').each ->
            $(@).parent().children().remove('br')
            $(@).remove()
        return true
    else
        if $('.formFieldset:nth-child(2)').children('.custom-error').length is 0
            $break = $('<br>')
            $errorLabel = $('<label>')
            $errorLabel.text 'Advance amount spent cannot exceed advances recieved.'
            $errorLabel.addClass 'custom-error'
            $errorLabel.insertAfter $('.formFieldset:nth-child(2) h3')
            $break.insertAfter $errorLabel
        return false
    
calculatePerDiem = (departureDate, departureHour, departureMinute, arrivalDate, arrivalHour, arrivalMinute) ->
    departure = new Date "#{ departureDate } #{ departureHour }:#{ departureMinute }"
    arrival = new Date "#{ arrivalDate } #{ arrivalHour }:#{ arrivalMinute }"
    $.ajax
        method: 'POST'
        url: Routing.generate 'OpitNotesTravelBundle_expense_perdiem'
        data: {arrival: arrival, departure: departure}
    .done (data) ->
        $('.perDiemTable').remove()
        $perDiemTable = $('<table>')
        $perDiemTable.addClass 'perDiemTable bordered'
        
        if data['totalTravelHoursOnSameDay'] > 0
            $perDiemTable.append createTableRow(
                'Travel hours',
                data['totalTravelHoursOnSameDay'],
                ""
            )                 
            
            $perDiemTable.append createTableRow(
                'Total',
                data['totalPerDiem'],
                ""
            )
            
        else
            $perDiemTable.append createTableRow(
                'Departure day',
                data['departurePerDiem'],
                "Number of hours traveled on departure day #{ data['departureHours'] }."
            )
        
            $perDiemTable.append createTableRow(
                "Full days (#{ data['daysBetween'] })",
                data['daysBetweenPerDiem'], 
                "Number of full days #{ data['daysBetween'] }."
            )        
        
            $perDiemTable.append createTableRow(
                'Arrival day',
                data['arrivalPerDiem'],
                "Number of hours traveled on arrival day #{ data['arrivalHours'] }."
            )

            $perDiemTable.append createTableRow('Total', data['totalPerDiem'])
            
            $perDiem.append $perDiemTable

convertCurrency = (originCode, destinationCode, value) ->
    curConverter.convertCurrency originCode, destinationCode, value

$(document).ready ->
    $buttonParent = $('#travelExpense_add_travel_expense').parent()
    $(document).data('notes').funcs.createButton 'Cancel', 'button display-inline-block', '', $buttonParent, 'OpitNotesTravelBundle_travel_list'
    $(document).data('notes').funcs.makeElementToggleAble 'h3', $('.formFieldset'), '.elementContainer'

    arrivalDate = $('#travelExpense_arrivalDateTime_date')
    arrivalTime = $('#travelExpense_arrivalDateTime_time')
    departureDate = $('#travelExpense_departureDateTime_date')
    departureTime = $('#travelExpense_departureDateTime_time')
    
    # for browsers that do not support input type date
    if not Modernizr.inputtypes.date
        arrivalDate.datepicker( "destroy" );
        departureDate.datepicker( "destroy" );
        # change date format so that symfony will accept it
        $('input[type=date]').each ->
            dateVal = $(@).val()
            $(@).val $(@).val().replace(/(\d{4})-(\d{2})-(\d{2})/, "$2/$3/$1")
    else
        arrivalDate.attr 'readonly', 'readonly'
        departureDate.attr 'readonly', 'readonly'
    
    arrivalTime.addClass 'inlineElements time-picker'
    departureTime.addClass 'inlineElements time-picker'
    
    arrivalDate.css display: 'inline-block'
    departureDate.css display: 'inline-block'

    $secondFormFieldset = $('#travelExpense').children('.formFieldset:nth-child(2)')
    $thirdFormFieldset = $('#travelExpense').children('.formFieldset:nth-child(3)')

    $secondFormFieldset.append $('<div>').addClass 'elementContainer'
    $thirdFormFieldset.append $('<div>').addClass 'elementContainer'
    $secondFormFieldset.find('.elementContainer').append $addUserTagLink
    $thirdFormFieldset.find('.elementContainer').append $addCompanyTagLink
    companyPaidExpensesIndex = 0;
    userPaidExpensesIndex = 0;
    
    if $('#travelExpense_companyPaidExpenses').children('div').length > 0
        $('#travelExpense_companyPaidExpenses').children('div').each ->
            $container = reCreateExpenses($(@))
            $('#travelExpense').children('.formFieldset:nth-child(3)').find('.addFormFieldsetChild').before $container
            companyPaidExpensesIndex++
        
    if $('#travelExpense_userPaidExpenses').children('div').length > 0
        $('#travelExpense_userPaidExpenses').children('div').each ->
            $container = reCreateExpenses($(@))
            $('#travelExpense').children('.formFieldset:nth-child(2)').find('.addFormFieldsetChild').before $container
            userPaidExpensesIndex++
    
    $('#travelExpense_companyPaidExpenses').data 'index', companyPaidExpensesIndex
    $('#travelExpense_userPaidExpenses').data 'index', userPaidExpensesIndex
    $('#travelExpense_companyPaidExpenses').parent().children('label').remove()
    $('#travelExpense_userPaidExpenses').parent().children('label').remove()
    
    $('#travelExpense').css display: 'block'
    
    $perDiemAmountsTable = $('<table>')
    $perDiemAmountsTable.addClass 'per-diem-amounts-slab bordered'
    $.ajax
        method: 'POST'
        url: Routing.generate 'OpitNotesTravelBundle_expense_perdiemvalues'
    .done (data) ->
        for key, value of data
            $tr = $('<tr>')
            $tdHours = $('<td>')
            $tdHours.attr 'width', '100px'
            $tdHours.text "Over #{ key } hours"
            $tdAmount = $('<td>')
            $tdAmount.text value + ' EUR'
            
            $tr.append $tdHours
            $tr.append $tdAmount
            
            $perDiemAmountsTable.append $tr
    $tr = $('<tr>')
    $td = $('<td>')
    $td.attr 'colspan', 2
    $td.html 'Per diem is calculated considering the following slab.'
    $tr.append $td
    $perDiemAmountsTable.prepend $tr
    $perDiem.append $perDiemAmountsTable

    $('.generalFormFieldset').find('br').last().remove()
    $perDiem.append $perDiemAmountsTable
    $('.generalFormFieldset').append $perDiem
    
    $('.fa-question-circle').on 'mouseover', ->
        $description = $(@).parent().parent().find('.formFieldsetDescription')
        $description.removeClass 'display-none'
    $('.fa-question-circle').on 'mouseout', ->
        $('.formFieldsetDescription').addClass 'display-none'
    
    $departureHour = $('#travelExpense_departureDateTime_time_hour')
    $departureMinute = $('#travelExpense_departureDateTime_time_minute')
    $arrivalHour = $('#travelExpense_arrivalDateTime_time_hour')
    $arrivalMinute = $('#travelExpense_arrivalDateTime_time_minute')
    
    departureDateVal = departureDate.val()
    departureHourVal = $departureHour.val()
    departureMinuteVal = $departureMinute.val()
    arrivalDateVal = arrivalDate.val()
    arrivalHourVal = $arrivalHour.val()
    arrivalMinuteVal = $arrivalMinute.val()
    
    if not isNewTravelExpense
        calculatePerDiem(departureDateVal, departureHourVal, departureMinuteVal, arrivalDateVal, arrivalHourVal, arrivalMinuteVal)
    
    $departureHour.on 'change', ->
        departureHourVal = $departureHour.val()
        calculatePerDiem(departureDateVal, departureHourVal, departureMinuteVal, arrivalDateVal, arrivalHourVal, arrivalMinuteVal)
    $departureMinute.on 'change', ->
        departureMinuteVal = $departureMinute.val()
        calculatePerDiem(departureDateVal, departureHourVal, departureMinuteVal, arrivalDateVal, arrivalHourVal, arrivalMinuteVal)
    $arrivalHour.on 'change', ->
        arrivalHourVal = $arrivalHour.val()
        calculatePerDiem(departureDateVal, departureHourVal, departureMinuteVal, arrivalDateVal, arrivalHourVal, arrivalMinuteVal)
    $arrivalMinute.on 'change', ->
        arrivalMinuteVal = $arrivalMinute.val()
        calculatePerDiem(departureDateVal, departureHourVal, departureMinuteVal, arrivalDateVal, arrivalHourVal, arrivalMinuteVal)
       
    $advancesRecieved = $('#travelExpense_advancesRecieved')
    if $advancesRecieved.val() is ''
        $advancesRecieved.val(0)
        
    $advancesPayback = $('<div>')
    $advancesPayback.addClass 'inlineElements'
    $advancesPaybackLabel = $('<label>')
    $advancesPaybackLabel.html 'Advances payback'
    $advancesPaybackText = $('<div>')
    $advancesPaybackText.html '0'
    $advancesPaybackText.addClass 'custom-field'
    $advancesPaybackText.attr 'id', 'travelExpense_advancesPayback'
    $advancesPayback.append $advancesPaybackLabel
    $advancesPayback.append $advancesPaybackText
    
    $toSettle = $('<div>')
    $toSettle.addClass 'inlineElements'
    $toSettleLabel = $('<label>')
    $toSettleLabel.html 'Advances spent'
    $toSettleText = $('<div>')
    $toSettleText.html '0'
    $toSettleText.addClass 'custom-field'
    $toSettleText.attr 'id', 'travelExpense_advancesSpent'
    $toSettle.append $toSettleLabel
    $toSettle.append $toSettleText
    
    $('#travelExpense_advancesRecieved').parent().after $toSettle
    $toSettle.after $advancesPayback
    
    calculateAdvancesPayback()
    
    $('#travelExpense_advancesRecieved').on 'change', ->
        calculateAdvancesPayback()
    $('.formFieldset').on 'change', '.amount, .currency, .paid-in-advance', ->
        calculateAdvancesPayback()
        
    $('.changeState').on 'change', ->
        statusId = $(@).val()
        parameters = window.location.pathname.split('/')
        travelExpenseId = parameters[parameters.length - 1]
        $.ajax
            method: 'POST'
            url: Routing.generate 'OpitNotesTravelBundle_expense_state'
            data: {'statusId': statusId, 'travelExpenseId': travelExpenseId}
        .done (data) ->
            window.location.href = Routing.generate 'OpitNotesTravelBundle_travel_list'
        .fail (data) ->
            console.warn 'Error occured while saving state for travel expense.'


$formFieldset = $('<div>')
$formFieldset.addClass 'formFieldset'

$generalFormFieldset = $formFieldset.clone().addClass 'generalFormFieldset'
$expensesPaidByMe = $formFieldset.clone().append $('<h3>').html 'Expenses paid by me <i class="fa fa-question-circle"></i>'
$expensesPaidByOpit = $formFieldset.clone().append $('<h3>').html 'Expenses paid by opit <i class="fa fa-question-circle"></i>'

$('#travelExpense').prepend $expensesPaidByOpit
$('#travelExpense').prepend $expensesPaidByMe
$('#travelExpense').prepend $generalFormFieldset
$('#travelExpense').addClass 'travelForm'

$generalFormFields = $('<div>').addClass 'display-inline-block'
$generalFormFieldset.append $generalFormFields
        
$expensesPaidByOpitDesc = $('<div>')
$expensesPaidByOpitDesc.html 'Expenses paid by OPIT (already paid by OPIT).'
$expensesPaidByOpitDesc.addClass 'formFieldsetDescription short-description display-none'

$expensesPaidByMeDesc = $('<div>')
$expensesPaidByMeDesc.html 'Expenses paid by employee (payable to your own bank account).'
$expensesPaidByMeDesc.addClass 'formFieldsetDescription short-description display-none'

$expensesPaidByOpit.append $expensesPaidByOpitDesc
$expensesPaidByMe.append $expensesPaidByMeDesc
        
$('.formFieldset').on 'change', '.te-expense-type', ->
    $(@).closest('.formFieldsetChild').children('.selected-expense').html $("##{ $(@).attr 'id' } :selected").text()

# move all element with specified class into form fieldset
$('.te-claim').each (index) ->
    $(@).parent().addClass 'inlineElements'
    $generalFormFields.append $(@).parent()
    
    # if element has class display-none add class to elements parent node
    if $(@).hasClass 'display-none'
        $(@).removeClass 'display-none'
        $(@).parent().addClass 'display-none'
    # allow two properties in a row
    if index % 2
        $generalFormFields.append $('<br>')
        
# create add expenses button, and add on click listeners to them
$addCompanyTagLink = $('<div class="addFormFieldsetChild formFieldsetButton"><i class="fa fa-plus-square"></i>Add company expense</div>')
$addCompanyTagLink.on 'click', ->
    addNewForm($('#travelExpense_companyPaidExpenses'), $('#travelExpense').children('.formFieldset:nth-child(3)'))    
    
$addUserTagLink = $('<div class="addFormFieldsetChild formFieldsetButton"><i class="fa fa-plus-square"></i>Add user expense</div>')
$addUserTagLink.on 'click', ->
    addNewForm($('#travelExpense_userPaidExpenses'), $('#travelExpense').children('.formFieldset:nth-child(2)'))
    
    
$form = $('#travelExpenseForm')    
    
$.validator.addMethod 'compare', (value, element) ->
    departureDate = $('#travelExpense_departureDateTime_date').val()
    arrivalDate = $('#travelExpense_arrivalDateTime_date').val()
    
#    # if date contains dash change date format for validation
    if departureDate.indexOf('-')
        arrivalDate = arrivalDate.replace(/(\d{4})-(\d{2})-(\d{2})/, "$2/$3/$1")
        departureDate = departureDate.replace(/(\d{4})-(\d{2})-(\d{2})/, "$2/$3/$1")

    departureTimeHour = $('#travelExpense_departureDateTime_time_hour').val()
    arrivalTimeHour = $('#travelExpense_arrivalDateTime_time_hour').val()

    departureTimeMinute = $('#travelExpense_departureDateTime_time_minute').val()
    arrivalTimeMinute = $('#travelExpense_arrivalDateTime_time_minute').val()

    departure = departureDate+' '+departureTimeHour+':'+departureTimeMinute
    arrival = arrivalDate+' '+arrivalTimeHour+':'+arrivalTimeMinute

    departure = new Date(departure)
    arrival = new Date(arrival)
    $('#travelExpense_arrivalDateTime_time_minute').css border: 'solid 1px rgb(170, 170, 170)'

    return departure < arrival
, 'Arrival date should not be smaller than departure date.'
    
$form.validate
    ignore: []
    rules:
        'travelExpense[arrivalDateTime][time][minute]': 'compare',
        'travelExpense[taxIdentification]': {maxlength: 11},
        'travelExpense[toSettle]': {digits: true}
 
$('#travelExpense_add_travel_expense').on 'click', (event) ->
    event.preventDefault()
    if not $(@).hasClass 'button-disabled'
        if $form.valid() and calculateAdvancesPayback() and validateAllExpenseDates()
            # for browsers that do not support input type date
            if not Modernizr.inputtypes.date
                $('input[type=date]').each ->
                    $(@).parent().find('input[type=hidden]').val $(@).val().replace(/(\d{2})\/(\d{2})\/(\d{4})/, "$3-$1-$2")
            $.ajax
                method: 'POST'
                url: Routing.generate 'OpitNotesTravelBundle_expense_show_details'
                data: 'preview=1&' + $form.serialize()
            .done (data) ->
                $preview = $('<div id="dialog-travelrequest-preview"></div>').html data
                $preview.dialog
                    open: ->
                        $('.ui-dialog-title').append '<i class="fa fa-list-alt"></i> Details'
                    close: ->
                        $preview.dialog "destroy"
                    width: 550
                    maxHeight: $(window).outerHeight()-100
                    modal: on
                    buttons:
                        Cancel: ->
                            $preview.dialog "destroy"
                            return
                        Save: ->
                            $form.submit()
                            $preview.dialog "destroy"
                            return  
            .fail () ->
                $('<div></div>').html('The travel expense could not be saved due to an error.').dialog
                    title: 'Error'                        