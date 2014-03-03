calculateAdvancesPayback = () ->
    spent = []
    amount = 0
    $('.elementContainer .currency').each ->
        amount = parseInt $(@).closest('.formFieldsetChild').find('.amount').val()
        if spent[$(@).val()] is undefined
            spent[$(@).val()] = amount
        else
            spent[$(@).val()] += amount
            
        if isNaN(spent[$(@).val()])
            console.warn 'is not number'
        
    $('.generalFormFieldset .te-advances-received-currency').each ->
        $closestAdvancesReceived = $(@).closest '.advances-received'
        advancesSpent = spent[$(@).val()]
        $advancesSpent = $closestAdvancesReceived.find '.te-advances-spent'
        advancesReceived = $closestAdvancesReceived.find('.te-advances-received').val()
        advancePayBack = parseInt advancesReceived  - parseInt advancesSpent

        $advancesSpent.html(if advancesSpent is undefined then '0' else if isNaN(advancesSpent) then '0' else advancesSpent)
        $closestAdvancesReceived.find('.te-advances-payback').html(
            if advancePayBack
                if advancePayBack < 0 then '0' else advancePayBack
            else if isNaN(advancePayBack)
                if advancesReceived is '' then '0' else advancesReceived
            else '0'
        )
excludedCurrencies = []
allCurrencies = []
availableCurrencies = []
  
setCurrenciesArray = (arrayToPushTo) ->
    $($('#travelExpense_teAdvancesReceived').data('prototype')).find('option').each ->
        arrayToPushTo.push $(@).val()
  
setAvailableCurrencies = (fillAllCurrenciesArray) ->
    excludedCurrencies = []
    availableCurrencies = []
    # go through all advances received currency selectors
    $('.te-advances-received-currency').each ->
        # go through all options
        $(@).find('option').each ->
            # if option is selected exclude it from the available options that can be selected from other selectors
            if $(@).prop 'selected'
                excludedCurrencies.push $(@).val()
            # if option is not selected remove it
            else
                $(@).remove()
            # if fillAllCurrenciesArray is set that means that all the currencies need to be added to the
            # allCurrencies array to later be used
            if fillAllCurrenciesArray
                # only add currency to array if it has not yet been added to it
                if $.inArray( $(@).val(), allCurrencies ) <= -1 then allCurrencies.push $(@).val()
                
    # loop through all currencies
    for currency in allCurrencies
        # if currency is not in excludedCurrencies array
        if $.inArray( currency, excludedCurrencies ) <= -1
            availableCurrencies.push(currency)
            # add this specific currency to all the selectors
            $('.te-advances-received-currency').each ->
                $option = $('<option>')
                $option.html currency
                $option.attr 'value', currency
                $(@).append $option
                
    calculateAdvancesPayback()

createDeleteButton = ->
    $deleteButton = $('<div>')
    $deleteButton.addClass('deleteFormFieldsetChild formFieldsetButton').html '<i class="fa fa-minus-square"></i>Delete'
    $deleteButton.on 'click', ->
        $(@).parent().remove()
        calculateAdvancesPayback()
        
    return $deleteButton

createDeleteExpenseButton = ($parent) ->
    $inlineElement = $('<div>')
    $inlineElement.addClass 'inlineElements'

    $deleteButton = $('<i>')
    $deleteButton.addClass 'fa fa-minus-square color-red hover-cursor-pointer margin-top-24'
    $deleteButton.on 'mousedown', ->
        $(@).closest('.advances-received').remove()
        setAvailableCurrencies()
        calculateAdvancesPayback()
        
    $inlineElement.append $deleteButton
    $parent.append $inlineElement

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
    
    $container.find('.amount').on 'change', ->
        calculateAdvancesPayback()
        
    $container.find('.currency').on 'change', ->
        calculateAdvancesPayback()
    
    expenseDateChange($container)
    
    return $container

createCustomField = (className, labelText, content) ->
    $customFieldInline = $('<div>')
    $customFieldInline.addClass 'inlineElements'
    $customField = $('<div>')
    $customField.html content
    $customField.addClass className
    $customFieldInline.append $('<label>').html labelText
    $customFieldInline.append $customField
    
    return $customFieldInline

reCreateAdvances = () ->
    collectionIndex = 0
    $teAdvancesReceived = $('#travelExpense_teAdvancesReceived')
    $generalFormFieldset = $('.generalFormFieldset')
    $teAdvancesReceived.parent().children('label').remove()
    $teAdvancesReceived.children().each ->
        $(@).find('label').remove()
        
    $('.te-advances-received').parent().addClass 'inlineElements'
    $('.te-advances-received-currency').parent().addClass 'inlineElements'
    $('.te-advances-received-currency').parent().prepend $('<label>').html 'Currency'
    
    $('.te-advances-received').each (index) ->
        $selfParent = $(@).parent()
        $selfParent.prepend $('<label>').html 'Advances received'
        
        $advancesPayback = createCustomField('te-advances-payback custom-field', 'Advances payback', '0')
        $advancesSpent = createCustomField('te-advances-spent custom-field', 'Advances spent', '0')
        
        $selfParent.after $advancesSpent
        $advancesSpent.after $advancesPayback
        
        collectionIndex++
        
        $teAdvances = $('#travelExpense_teAdvancesReceived_' + index)
        $advancesReceived = $('<div>')
        $advancesReceived.addClass 'advances-received'
        $advancesReceived.append $teAdvances
        $generalFormFieldset.append $advancesReceived
        
        createDeleteExpenseButton($teAdvances)
        
    $teAdvancesReceived.data 'index', collectionIndex
    
    $generalFormFieldset.on 'change', '.te-advances-received', ->
        calculateAdvancesPayback()
    $generalFormFieldset.on 'change', '.te-advances-received-currency', ->
        calculateAdvancesPayback()
    
    calculateAdvancesPayback()

addNewAdvanceReceived = (collectionHolder) ->
    if availableCurrencies.length > 0
        prototype = collectionHolder.data 'prototype'
        index = collectionHolder.data 'index'

        prototype = prototype.replace '<label class="required">__name__label__</label>', ''
        newAdvancesReceived = prototype.replace /__name__/g, index
        $newAdvancesReceived = $(newAdvancesReceived)
        $newAdvancesReceived.addClass 'advances-received'
        $newAdvancesReceived.children('div').children('div').each ->
            $(@).addClass 'inlineElements'

        $advancesPayback = createCustomField('te-advances-payback custom-field', 'Advances payback', '0')
        $advancesSpent = createCustomField('te-advances-spent custom-field', 'Advances spent', '0')
        $newAdvancesReceived.find('.te-advances-received').parent().after $advancesSpent
        $advancesSpent.after $advancesPayback

        createDeleteExpenseButton($newAdvancesReceived.children('div'))

        $('.generalFormFieldset .addFormFieldsetChild').before $newAdvancesReceived

        collectionHolder.data 'index', index + 1

        if allCurrencies.length is 0 then setCurrenciesArray(allCurrencies)

        $newAdvancesReceived.find('.te-advances-received-currency').find('option').remove()
        $defaultOption = $('<option>')
        $defaultOption.html availableCurrencies[0]
        $defaultOption.val availableCurrencies[0]
        $newAdvancesReceived.find('.te-advances-received-currency').append $defaultOption

        setAvailableCurrencies()
        calculateAdvancesPayback()
    else
        if setCurrenciesArray(availableCurrencies) is true
            if availableCurrencies.length != $('.te-advances-received-currency').length
                addNewAdvanceReceived(collectionHolder)
            else
                availableCurrencies = []

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
    
    $formFieldsetChild.find('.amount').on 'change', ->
        calculateAdvancesPayback()
    $formFieldsetChild.find('.currency').on 'change', ->
        calculateAdvancesPayback()
    
    # for browsers that do not support input type date
    if not Modernizr.inputtypes.date
        $datePicker = $formFieldsetChild.find 'input[type=date]'
        id = $datePicker.attr 'id'
        name = $datePicker.attr 'name'
        $datePicker.after '<input type="hidden" name="'+name+'" id="altDate'+id+'" />'
        $datePicker.datepicker {altField:'#altDate'+id, altFormat: 'yy-mm-dd'}

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
    
calculatePerDiem = (departureDate, departureHour, departureMinute, arrivalDate, arrivalHour, arrivalMinute) ->
    departure = new Date "#{ departureDate } #{ departureHour }:#{ departureMinute }"
    arrival = new Date "#{ arrivalDate } #{ arrivalHour }:#{ arrivalMinute }"
    if arrival > departure
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
                    'One day trip',
                    data['totalPerDiem'],
                    "Hours traveled #{ data['totalTravelHoursOnSameDay'] }."
                )

            else
                $perDiemTable.append createTableRow(
                    'Departure day',
                    data['departurePerDiem'],
                    "Hours traveled on departure day #{ data['departureHours'] }."
                )

                $perDiemTable.append createTableRow(
                    "Full days (#{ data['daysBetween'] })",
                    data['daysBetweenPerDiem'],
                    "Number of full days #{ data['daysBetween'] }."
                )

                $perDiemTable.append createTableRow(
                    'Arrival day',
                    data['arrivalPerDiem'],
                    "Hours traveled on arrival day #{ data['arrivalHours'] }."
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
        arrivalDate.datepicker 'destroy'
        departureDate.datepicker 'destroy'
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

    reCreateAdvances()
    $advancesReceived = $('#travelExpense_teAdvancesReceived')

    $addNewAdvance = $('<div>')
    $addNewAdvance.addClass 'addFormFieldsetChild formFieldsetButton margin-left-0'
    $addNewAdvance.html '<i class="fa fa-plus-square"></i>Add advances received'
    $('.generalFormFieldset').append $addNewAdvance
    $addNewAdvance.on 'mousedown', ->
        addNewAdvanceReceived($advancesReceived) 
        
    setAvailableCurrencies(true)
    $('.generalFormFieldset').on 'change', '.te-advances-received-currency', ->
        setAvailableCurrencies()
        
    if $('#travelExpense_add_travel_expense').hasClass 'button-disabled'
        $('.addFormFieldsetChild').each ->
            $(@).remove()
        $('.deleteFormFieldsetChild').each ->
            $(@).remove()
        $('.fa-minus-square').each ->
            $(@).remove()
        
    $('.changeState').on 'change', ->
        statusId = $(@).val()
        travelExpenseId = $(@).data 'te'
        $spinner = $(document).data('notes').funcs.disableStatusDropdown $(@)
        $.ajax
            method: 'POST'
            url: Routing.generate 'OpitNotesTravelBundle_expense_state'
            data: {'statusId': statusId, 'travelExpenseId': travelExpenseId}
        .done (data) ->
            window.location.href = Routing.generate 'OpitNotesTravelBundle_travel_list'
        .complete () ->
            $spinner.remove()
        .fail (data) ->
            console.warn 'Error occured while saving state for travel expense.'


$formFieldset = $('<div>')
$formFieldset.addClass 'formFieldset'

$generalFormFieldset = $formFieldset.clone().addClass 'generalFormFieldset clearfix'
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
        if $form.valid() and validateAllExpenseDates()
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