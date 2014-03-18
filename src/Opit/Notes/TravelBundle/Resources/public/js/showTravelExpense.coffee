# Where addClass called twice first addClass contains selector(s) and second the actual styles

$('.travel-status-history').click (event) ->
    event.preventDefault()
    id = $(@).data 'id'
    $(document).data('notes').funcs.showTravelStatusHistory id, 'te'

calculateAdvancesPayback = () ->
    spent = []
    amount = 0
    $('.elementContainer .currency-listen').each ->
        $amountEl = $(@).closest('.formFieldsetChild').find '.amount'
        amount = parseInt $amountEl.val()
        amountSpent = $(@).val()
        if spent[amountSpent] is undefined then spent[amountSpent] = amount else spent[amountSpent] += amount
        if isNaN(spent[amountSpent])
            console.warn "Value is not a number (#{$amountEl.attr('id')})"
        
    $('.generalFormFieldset .te-advances-received-currency').each ->
        $closestAdvancesReceived = $(@).closest '.advances-received'
        advancesSpent = spent[$(@).val()]
        advancesReceived = $closestAdvancesReceived.find('.te-advances-received').val()
        advancePayBack = parseInt advancesReceived  - parseInt advancesSpent

        $advancesSpent = $closestAdvancesReceived.find '.te-advances-spent'
        $advancesSpent.html(if advancesSpent is undefined then '0' else if isNaN(advancesSpent) then '0' else advancesSpent)
        
        $closestAdvancesReceived.find('.te-advances-payback').html(
            if advancePayBack
                if advancePayBack < 0 then '0' else advancePayBack
            else if isNaN(advancePayBack)
                if advancesReceived is '' then '0' else advancesReceived
            else '0'
        )

createDeleteButton = ->
    $deleteButton = $('<div>').addClass('form-fieldset-delete-button deleteFormFieldsetChild formFieldsetButton').html '<i class="fa fa-minus-square"></i>Delete'
    $deleteButton.on 'click', ->
        $(@).parent().remove()
        calculateAdvancesPayback()
        
    return $deleteButton

createDeleteExpenseButton = ($parent) ->
    $deleteButton = $('<i>').addClass 'fa fa-minus-square color-red hover-cursor-pointer margin-top-24'
    $deleteButton.on 'click', ->
        $(@).closest('.advances-received').remove()
        setAvailableCurrencies()
        calculateAdvancesPayback()
        
    $parent.append($('<div>').addClass('display-inline-block vertical-align-top margin-right-1-em').append($deleteButton))

validateAllExpenseDates = ->
    isDateValid = true
    $('.formFieldsetChild').each ->
        expenseDateField = $(@).find('input[type=date]')
        validateExpenseDate(expenseDateField)
        if expenseDateField.parent().children('.custom-label-error').length > 0
            isDateValid = false
            return

    return isDateValid

validateExpenseDate = (self) ->
    date = self.val()
    self.addClass 'display-inline-block'
    if date > $('#travelExpense_arrivalDateTime_date').val() or date < $('#travelExpense_departureDateTime_date').val()
        if self.parent().children('.custom-label-error').length < 1
            self.parent().append $('<label>').addClass('custom-label-error').text 'Invalid expense date.'
    else
        self.parent().children().remove('.custom-label-error')

expenseDateChange = (parent) ->
    $dateOfExpenseSpent = parent.find('input[type=date]')
    if $dateOfExpenseSpent.attr('id').indexOf('userPaidExpenses') > -1
        $dateOfExpenseSpent.on 'change', ->
            validateExpenseDate($(@))


reCreateExpenses = (self) ->
    $selectedExpense = $('<span>').addClass('selected-expense').html self.find('.te-expense-type').find(':selected').text()
    $selectedExpense.addClass 'margin-0 color-white display-block align-center background-color-dark-grey'
    
    self.children('label:first').remove()
    
    $container = $('<div>').addClass 'formFieldsetChild padding-10 margin-left-1-em margin-bottom-1-em display-inline-block vertical-align-top'
    $container.append self
    $container.append createDeleteButton()
    $container.prepend $selectedExpense
    $container.find('.amount').parent().append $container.find '.currency'
    $container.find('.amount-listen').on 'change', ->
        calculateAdvancesPayback()
    $container.find('.currency-listen').on 'change', ->
        calculateAdvancesPayback()
    
    expenseDateChange($container)
    
    return $container

createCustomField = (className, labelText, content) ->
    $customFieldInline = $('<div>').addClass 'display-inline-block vertical-align-top margin-right-1-em'
    $customFieldInline.append $('<label>').html labelText
    $customFieldInline.append $('<div>').addClass(className).html(content)
    
    return $customFieldInline

reCreateAdvances = () ->
    collectionIndex = 0
    $teAdvancesReceived = $('#travelExpense_advancesReceived')
    $generalFormFieldset = $('.generalFormFieldset')
    $teAdvancesReceived.parent().children('label').remove()
    $teAdvancesReceived.children().each ->
        $(@).find('label').remove()
        
    $('.te-advances-received').parent().addClass 'display-inline-block vertical-align-top margin-right-1-em'
    $advancesReceivedCurrencyParent = $('.te-advances-received-currency').parent()
                                        .addClass('display-inline-block vertical-align-top margin-right-1-em')
                                        .prepend($('<label>').html('Currency'))
    
    $('.te-advances-received').each (index) ->
        $advancesSpent = createCustomField('te-advances-spent custom-field', 'Advances spent', '0')
        
        $selfParent = $(@).parent()
                        .prepend($('<label>').html('Advances received'))
                        .after($advancesSpent)
        
        $advancesSpent.after(createCustomField('te-advances-payback custom-field', 'Advances payback', '0'))
        
        collectionIndex++
        
        $teAdvances = $('#travelExpense_advancesReceived_' + index)
        $advancesReceived = $('<div>').addClass 'advances-received'
                                .addClass('margin-top-5 margin-bottom-5')
                                .append $teAdvances
        $generalFormFieldset.append $advancesReceived
        
        createDeleteExpenseButton($teAdvances)
        
    $teAdvancesReceived.data 'index', collectionIndex
    
    $generalFormFieldset.on 'change', '.te-advances-received', ->
        calculateAdvancesPayback()
    $generalFormFieldset.on 'change', '.te-advances-received-currency', ->
        calculateAdvancesPayback()
    
    calculateAdvancesPayback()


setCurrenciesArray = (array) ->
    $($('#travelExpense_advancesReceived').data('prototype')).find('option').each ->
        array.push $(@).val()
  
setAvailableCurrencies = (doSplice) ->
    # array for all currencies
    availableCurrencies = []
    # array for currencies that are selected
    selectedCurrencies = []
    # array for currencies that can be selected
    selectAbleCurrencies = []
    setCurrenciesArray availableCurrencies
    
    if $('.te-advances-received-currency').length > 0
        $('.te-advances-received-currency').each ->
            selectedCurrencies.push($(@).find('option:selected').val())

        # loop though selected currencies
        selectedCurrencies.forEach (selectedCurrency) ->
            # loop through available currencies
            availableCurrencies.forEach (availableCurrency) ->
                # if available currency is the same as selected currency
                if availableCurrency == selectedCurrency
                    # if available currency is in availableCurrencies
                    if availableCurrencies.indexOf(availableCurrency) > -1
                        # remove available currency from available currencies
                        availableCurrencies.splice availableCurrencies.indexOf(availableCurrency), 1
        
        # remove all not selected currencies
        $('.te-advances-received-currency option').not(':selected').each ->
            $(@).remove()

        # make copy of availableCurrencies
        selectAbleCurrencies = availableCurrencies.slice()
        # decide to remove first element
        if not doSplice
            selectAbleCurrencies.splice 0, 1

        # loop thourgh all currency pickers
        $('.te-advances-received-currency').each ->
            $self = $(@)
            # loop through all currencies that can be selected
            selectAbleCurrencies.forEach (selectAbleCurrency) ->
                option = $('<option>')
                option.attr 'value', selectAbleCurrency
                option.html selectAbleCurrency
                $self.append option
                
        calculateAdvancesPayback()
    return availableCurrencies

addNewAdvanceReceived = (collectionHolder) ->
    availableCurrencies = setAvailableCurrencies()
    if availableCurrencies.length > 0
        prototype = collectionHolder.data 'prototype'
        index = collectionHolder.data 'index'

        prototype = prototype.replace '<label class="required">__name__label__</label>', ''
        $newAdvancesReceived = $(prototype.replace(/__name__/g, index))
                                .addClass('advances-received')
                                .addClass('margin-top-5 margin-bottom-5')
        $newAdvancesReceived.children('div').children('div').each ->
            $(@).addClass 'display-inline-block vertical-align-top margin-right-1-em'

        $advancesPayback = createCustomField('te-advances-payback custom-field', 'Advances payback', '0')
        $advancesSpent = createCustomField('te-advances-spent custom-field', 'Advances spent', '0')
        $newAdvancesReceived.find('.te-advances-received').parent().after $advancesSpent
        $advancesSpent.after $advancesPayback

        createDeleteExpenseButton($newAdvancesReceived.children('div'))

        $('.generalFormFieldset .addFormFieldsetChild').before $newAdvancesReceived

        collectionHolder.data 'index', index + 1

        $availableCurrencies = $newAdvancesReceived.find('.te-advances-received-currency')
        # remove all currencies element from created element
        $availableCurrencies.children().remove()
        # add all available currencies to element
        availableCurrencies.forEach (availableCurrency) ->
            option = $('<option>')
            option.attr 'value', availableCurrency
            option.html availableCurrency
            $availableCurrencies.append option

        $availableCurrencies.on 'change', ->
            setAvailableCurrencies(true)
        
    calculateAdvancesPayback()

addNewForm = (collectionHolder, parent) ->
    # get form data from collection holder
    index = collectionHolder.data 'index'
    prototype = collectionHolder.data 'prototype'
    prototype = prototype.replace '<label class="required">__name__label__</label>', ''
    newForm = prototype.replace /__name__/g, index
    
    $selectedExpense = $('<span>').addClass('selected-expense').html 'Expense type'
    $selectedExpense.addClass 'margin-0 color-white display-block align-center background-color-dark-grey'
    
    $formFieldsetChild = $('<div>')
                            .addClass('formFieldsetChild')
                            .addClass 'padding-10 margin-left-1-em margin-bottom-1-em display-inline-block vertical-align-top'
    $formFieldsetChild.append newForm
    $formFieldsetChild.append createDeleteButton()
    $formFieldsetChild.prepend $selectedExpense
    
    expenseDateChange($($formFieldsetChild))
    $formFieldsetChild.find('.currency option[value=EUR]').attr('selected','selected')
    collectionHolder.data 'index', index + 1
    $formFieldsetChild.find('.amount').parent().append($formFieldsetChild.find('.currency'))
    
    $formFieldsetChild.find('.amount-listen, .currency-listen').on 'change', ->
        calculateAdvancesPayback()
    
    # for browsers that do not support input type date
    if not Modernizr.inputtypes.date
        $datePicker = $formFieldsetChild.find 'input[type=date]'
        id = $datePicker.attr 'id'
        $datePicker.after '<input type="hidden" name="' + $datePicker.attr('name') + '" id="altDate'+id+'" />'
        $datePicker.datepicker {altField:'#altDate'+id, altFormat: 'yy-mm-dd'}

    parent.find('.addFormFieldsetChild').before $formFieldsetChild
    
createTableRow = (text, value, rowTitle) ->
    $row = $('<tr>')
    $textColumn = $('<td>')
                    .addClass('bgGrey bold')
                    .html text + ' <i class="fa fa-clock-o" title="'+rowTitle+'"></i>'
    $valueColumn = $('<td>').text value + ' EUR'
    
    if text == 'Total'
        $textColumn.html ''
        $valueColumn.html '<strong>Total</strong><br /> ' + value + ' EUR'
        
    $row.append $textColumn
    $row.append $valueColumn
    
    return $row
    
$perDiem = $('<div>').addClass 'per-diem-details-wrapper'
$perDiem.addClass 'display-inline-block vertical-align-top float-right margin-top-5 margin-bottom-10'

convertCurrency = (originCode, destinationCode, value) ->
    if originCode is destinationCode
        return value
    else
        return curConverter.convertCurrency(originCode, destinationCode, value).toFixed(2)
    
calculatePerDiem = (departureDate, arrivalDate) ->
    departureHour = $('#travelExpense_departureDateTime_time_hour').val()
    departureMinute = $('#travelExpense_departureDateTime_time_minute').val()
    arrivalHour = $('#travelExpense_arrivalDateTime_time_hour').val()
    arrivalMinute = $('#travelExpense_arrivalDateTime_time_minute').val()
    departure = new Date "#{ departureDate } #{ departureHour }:#{ departureMinute }"
    arrival = new Date "#{ arrivalDate } #{ arrivalHour }:#{ arrivalMinute }"
    if arrival > departure
        $.ajax
            method: 'POST'
            url: Routing.generate 'OpitNotesTravelBundle_expense_perdiem'
            data: {arrival: arrival, departure: departure}
        .done (data) ->
            $('.perDiemTable').remove()
            $perDiemTable = $('<table>').addClass 'perDiemTable bordered margin-top-10'

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
    # Initialize all and available currencies
    setAvailableCurrencies()
    
    $buttonParent = $('#travelExpense_add_travel_expense').parent()
    $(document).data('notes').funcs.createButton 'Cancel', 'button display-inline-block', '', $buttonParent, 'OpitNotesTravelBundle_travel_list'
    $(document).data('notes').funcs.makeElementToggleAble 'h3', $('.formFieldset'), '.elementContainer'

    arrivalDate = $('#travelExpense_arrivalDateTime_date').css display: 'inline-block'
    arrivalTime = $('#travelExpense_arrivalDateTime_time').addClass 'display-inline-block vertical-align-top margin-right-1-em time-picker'
    departureDate = $('#travelExpense_departureDateTime_date').css display: 'inline-block'
    departureTime = $('#travelExpense_departureDateTime_time').addClass 'display-inline-block vertical-align-top margin-right-1-em time-picker'
    
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

    $secondFormFieldset = $('#travelExpense').children('.formFieldset:nth-child(2)')
    $thirdFormFieldset = $('#travelExpense').children('.formFieldset:nth-child(3)')

    $addCompanyTagLink = $('<div class="addFormFieldsetChild"><i class="fa fa-plus-square"></i>Add company expense</div>')
                            .addClass 'formFieldsetButton form-fieldset-add-button'
    $addCompanyTagLink.on 'click', ->
        addNewForm $('#travelExpense_companyPaidExpenses'), $('#travelExpense').children('.formFieldset:nth-child(3)')

    $addUserTagLink = $('<div class="addFormFieldsetChild"><i class="fa fa-plus-square"></i>Add user expense</div>')
                        .addClass 'formFieldsetButton form-fieldset-add-button'
    $addUserTagLink.on 'click', ->
        addNewForm $('#travelExpense_userPaidExpenses'), $('#travelExpense').children('.formFieldset:nth-child(2)')

    $secondFormFieldset.append($('<div>').addClass('elementContainer').append $addUserTagLink)
    $thirdFormFieldset.append($('<div>').addClass('elementContainer').append $addCompanyTagLink)
    
    companyPaidExpensesIndex = 0
    userPaidExpensesIndex = 0
    paidExpenses = [$('#travelExpense_companyPaidExpenses'), $('#travelExpense_userPaidExpenses')]
    paidExpenses.forEach (paidExpense) ->
        paidExpenseChildren = paidExpense.children('div')
        if paidExpenseChildren.length > 0
            paidExpenseChildren.each ->
                if 'travelExpense_companyPaidExpenses' == paidExpense.attr('id')
                    $thirdFormFieldset.find('.addFormFieldsetChild').before(reCreateExpenses($(@)))
                    companyPaidExpensesIndex++
                else if 'travelExpense_userPaidExpenses' == paidExpense.attr('id')
                    $secondFormFieldset.find('.addFormFieldsetChild').before(reCreateExpenses($(@)))
                    userPaidExpensesIndex++
    
    $('#travelExpense_companyPaidExpenses').data 'index', companyPaidExpensesIndex
    $('#travelExpense_userPaidExpenses').data 'index', userPaidExpensesIndex
    $('#travelExpense_companyPaidExpenses').parent().children('label').remove()
    $('#travelExpense_userPaidExpenses').parent().children('label').remove()
    
    $('#travelExpense').css display: 'block'
    
    $perDiemAmountsTable = $('<table>').addClass 'per-diem-amounts-slab bordered width-100'
    $.ajax
        method: 'POST'
        url: Routing.generate 'OpitNotesTravelBundle_expense_perdiemvalues'
    .done (data) ->
        for key, value of data
            $tr = $('<tr>')
            $tdHours = $('<td>').attr 'width', '100px'
            $tdHours.text "Over #{ key } hours"
            $tdAmount = $('<td>').text value + ' EUR'
            
            $tr.append $tdHours
            $tr.append $tdAmount
            
            $perDiemAmountsTable.append $tr
            
    $perDiemAmountsTable.prepend $('<tr>').append $('<td>').attr('colspan', 2).html('Per diem is calculated considering the following slab.')
    $perDiem.append $perDiemAmountsTable

    $('.generalFormFieldset').find('br').last().remove()
    $perDiem.append $perDiemAmountsTable
    $('.generalFormFieldset').append $perDiem
    
    $('.fa-question-circle').on 'mouseover', ->
        $(@).parent().parent().find('.formFieldsetDescription').removeClass('display-none')
    $('.fa-question-circle').on 'mouseout', ->
        $('.formFieldsetDescription').addClass 'display-none'
    
    if not isNewTravelExpense
        calculatePerDiem(departureDate.val(), arrivalDate.val())
    
    $('#travelExpense_departureDateTime_time_hour,
        #travelExpense_departureDateTime_time_minute,
        #travelExpense_arrivalDateTime_time_hour,
        #travelExpense_arrivalDateTime_time_minute').on 'change', ->
            calculatePerDiem(departureDate.val(), arrivalDate.val())

    reCreateAdvances()
    $advancesReceived = $('#travelExpense_advancesReceived')

    $addNewAdvance = $('<div>').addClass('addFormFieldsetChild formFieldsetButton').html '<i class="fa fa-plus-square"></i>Add advances received'
    $addNewAdvance.addClass 'form-fieldset-add-button margin-left-0-important'
    $('.generalFormFieldset').append $addNewAdvance
    $addNewAdvance.on 'click', ->
        addNewAdvanceReceived($advancesReceived) 
        
    if $('#travelExpense_add_travel_expense').hasClass 'button-disabled'
        $('.addFormFieldsetChild').each ->
            $(@).remove()
        $('.deleteFormFieldsetChild').each ->
            $(@).remove()
        $('.fa-minus-square').each ->
            $(@).remove()
        
    $('.changeState').on 'change', ->
        $(document).data('notes').funcs.changeStateDialog($(@), $(document).data('notes').funcs.changeTravelExpenseStatus, $(@).data('te'))


$formFieldset = $('<div>').addClass 'formFieldset padding-bottom-5 margin-top-20 margin-bottom-20'

$generalFormFieldset = $formFieldset.clone().addClass 'generalFormFieldset clearfix padding-10'
$expensesPaidByMe = $formFieldset.clone().append $('<h3>').addClass('background-color-orange color-white padding-top-2 padding-bottom-2 padding-left-1-em cursor-pointer').html 'Expenses paid by me <i class="fa fa-question-circle"></i>'
$expensesPaidByOpit = $formFieldset.clone().append $('<h3>').addClass('background-color-orange color-white padding-top-2 padding-bottom-2 padding-left-1-em cursor-pointer').html 'Expenses paid by opit <i class="fa fa-question-circle"></i>'

$('#travelExpense').prepend $expensesPaidByOpit
$('#travelExpense').prepend $expensesPaidByMe
$('#travelExpense').prepend $generalFormFieldset
$('#travelExpense').addClass 'travelForm'

$generalFormFields = $('<div>').addClass 'display-inline-block'
$generalFormFieldset.append $generalFormFields
        
$expensesPaidByOpitDesc = $('<div>')
                            .addClass('formFieldsetDescription')
                            .addClass('short-description display-none position-absolute padding-5')
                            .html 'Expenses paid by OPIT (already paid by OPIT).'

$expensesPaidByMeDesc = $('<div>')
                            .addClass('formFieldsetDescription')
                            .addClass('short-description display-none position-absolute padding-5')
                            .html 'Expenses paid by employee (payable to your own bank account).'

$expensesPaidByOpit.append $expensesPaidByOpitDesc
$expensesPaidByMe.append $expensesPaidByMeDesc
        
$('.formFieldset').on 'change', '.te-expense-type', ->
    $(@).closest('.formFieldsetChild').children('.selected-expense').html $("##{ $(@).attr 'id' } :selected").text()

# move all element with specified class into form fieldset
$('.te-claim').each (index) ->
    $(@).parent().addClass 'display-inline-block vertical-align-top margin-right-1-em'
    $generalFormFields.append $(@).parent()
    
    # if element has class display-none add class to elements parent node
    if $(@).hasClass 'display-none'
        $(@).removeClass 'display-none'
        $(@).parent().addClass 'display-none'
    # allow two properties in a row
    if index % 2
        $generalFormFields.append $('<br>')
  
#create add expenses button, and add on click listeners to them
$addCompanyTagLink = $('<div class="addFormFieldsetChild"><i class="fa fa-plus-square"></i>Add company expense</div>')
                        .addClass 'formFieldsetButton form-fieldset-add-button'
$addCompanyTagLink.on 'click', ->
    addNewForm($('#travelExpense_companyPaidExpenses'), $('#travelExpense').children('.formFieldset:nth-child(3)'))    
    
$addUserTagLink = $('<div class="addFormFieldsetChild"><i class="fa fa-plus-square"></i>Add user expense</div>')
                    .addClass 'formFieldsetButton form-fieldset-add-button'
$addUserTagLink.on 'click', ->
    addNewForm($('#travelExpense_userPaidExpenses'), $('#travelExpense').children('.formFieldset:nth-child(2)'))
    
    
$form = $('#travelExpenseForm')    
    
$.validator.addMethod 'compare', (value, element) ->
    departureDate = $('#travelExpense_departureDateTime_date').val()
    arrivalDate = $('#travelExpense_arrivalDateTime_date').val()
    
    # if date contains dash change date format for validation
    if departureDate.indexOf('-')
        arrivalDate = arrivalDate.replace(/(\d{4})-(\d{2})-(\d{2})/, "$2/$3/$1")
        departureDate = departureDate.replace(/(\d{4})-(\d{2})-(\d{2})/, "$2/$3/$1")

    departure = departureDate+' ' + $('#travelExpense_departureDateTime_time_hour').val() + ':' + $('#travelExpense_departureDateTime_time_minute').val()
    arrival = arrivalDate+' ' + $('#travelExpense_arrivalDateTime_time_hour').val() + ':' + $('#travelExpense_arrivalDateTime_time_minute').val()

    $('#travelExpense_arrivalDateTime_time_minute').css border: 'solid 1px rgb(170, 170, 170)'

    return new Date(departure) < new Date(arrival)
, 'Arrival date should not be earlier than departure date.'
    
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
                        Save: ->
                            $form.submit()
                            $preview.dialog "destroy"
                            return
                        Cancel: ->
                            $preview.dialog "destroy"
                            return
            .fail () ->
                $('<div></div>').html('The travel expense could not be saved due to an error.').dialog
                    title: 'Error'
        else
            $('.hasDatepicker').each ->
                if $(@).hasClass 'error'
                    $(@).parent().find('.fa-calendar').addClass 'margin-top-12'

$('#travelRequestPreview').on 'click', ->
    $.ajax
        method: 'POST'
        url: Routing.generate 'OpitNotesTravelBundle_travel_show_details'
        data: 'id': $(@).attr 'data-tr-id'
    .done (data) ->
        $previewTr = $('<div id="dialog-show-details-tr"></div>');
        $previewTr.html(data)
            .dialog
                open: ->
                    $('.ui-dialog-title').append ('<i class="fa fa-list-alt"></i> Details')
                close: ->
                    $previewTr.dialog "destroy"
                width: 550
                maxHeight: $(window).outerHeight()-100
                modal: on
        return
    return