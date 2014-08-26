# Where addClass called twice first addClass contains selector(s) and second the actual styles

# Init status history for travel expenses
history = new StatusHistory('OpitOpitHrmTravelBundle_status_history', {mode: 'te'})
do history.init

calculateAdvancesPayback = () ->
    spent = []
    amount = 0
    $('#travelExpense_userPaidExpenses .currency-listen').each ->
        $amountEl = $(@).closest('.formFieldsetChild').find '.amount'
        amount = parseFloat($amountEl.val().replace ',', '.') # replace comma with dot so js can calculate with number
        amountSpent = $(@).val()
        if spent[amountSpent] is undefined then spent[amountSpent] = amount else spent[amountSpent] += amount
        if isNaN(spent[amountSpent])
            if $amountEl.closest('div').children('.custom-label-error').length < 1
                $amountEl.closest('div').append $('<label>').addClass('custom-label-error').text 'Amount must be a valid number.'
                $amountEl.css 'border': 'solid 2px #aa0000'
        else
            $amountEl.css 'border': '0'
            $amountEl.closest('div').find('.custom-label-error').remove()
        
    $('.generalFormFieldset .te-advances-received-currency').each ->
        $closestAdvancesReceived = $(@).closest '.advances-received'
        amountSpent = spent[$(@).val()]
        amountSpent = if amountSpent then amountSpent else 0
        advancesReceived = $closestAdvancesReceived.find('.te-advances-received').val().replace ',', '.' # replace comma with dot so js can calculate with number
        amount = advancesReceived - amountSpent

        $amountSpent = $closestAdvancesReceived.find '.te-amount-spent'
        $amountSpent.html(if amountSpent is undefined then '0' else if isNaN(amountSpent) then '0' else Number(amountSpent).toFixed(2))
        
        amountPayable = 0
        
        if amount
            if amount < 0
                advancesReceived = '0'
                # get number absolute value and set to display 2 decimals
                amountPayable = Number(Math.abs(amount)).toFixed 2
            else
                # set number to display 2 decimals
                advancesReceived = Number(amount).toFixed 2
                amountPayable = '0'
        else
            advancesReceived = '0'
            amountPayable = '0'
            
        $closestAdvancesReceived.find('.te-advances-payback').html advancesReceived
        $closestAdvancesReceived.find('.te-amount-payable').html amountPayable

createDeleteButton = ->
    $deleteButton = $('<div>').addClass('form-fieldset-delete-button deleteFormFieldsetChild formFieldsetButton').html '<i class="fa fa-minus-square"></i>Delete'
    $deleteButton.on 'click', ->
        $(@).parent().remove()
        calculateAdvancesPayback()
        
    return $deleteButton

createDeleteExpenseButton = ($parent) ->
    $deleteButton = $('<i>').addClass 'fa fa-minus-square color-red cursor-pointer margin-top-24'
    $deleteButton.on 'click', ->
        $(@).closest('.advances-received').remove()
        setAvailableCurrencies(true)
        calculateAdvancesPayback()
        
    $parent.append $deleteButton

validateAllExpenseDates = ->
    isDateValid = true
    $('.formFieldsetChild').each ->
        expenseDateField = $(@).find('input[type=date]')
        if expenseDateField.attr('id').indexOf('userPaidExpenses') > -1
            validateExpenseDate(expenseDateField)
            if expenseDateField.closest('div').children('.custom-label-error').length > 0
                isDateValid = false
                return

    return isDateValid

validateExpenseDate = (self) ->
    date = self.val()
    self.addClass 'display-inline-block'
    $('#travelExpenseForm').valid()
    $selfClosestDiv = self.closest('div')
    if (date > $('#travelExpense_arrivalDateTime_date').val() or date < $('#travelExpense_departureDateTime_date').val()) and date != ''
        if $selfClosestDiv.children('.custom-label-error').length < 1
            $selfClosestDiv.append $('<label>').addClass('custom-label-error').text 'Invalid expense date.'
    else
        $selfClosestDiv.children().remove('.custom-label-error')

expenseDateChange = (parent) ->
    $dateOfExpenseSpent = parent.find('input[type=date]')
    if $dateOfExpenseSpent.attr('id').indexOf('userPaidExpenses') > -1
        $dateOfExpenseSpent.on 'change', ->
            validateExpenseDate($(@))

createCustomField = (className, labelText, content) ->
    $customFieldInline = $('<div>').addClass 'display-inline-block vertical-align-top margin-right-1-em'
    $customFieldInline.append $('<label>').html labelText
    $customFieldInline.append $('<div>').addClass(className).html(content)
    
    return $customFieldInline

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
            selectedCurrencies.push $(@).find('option:selected').val()

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

addNewAdvanceReceived = () ->
    availableCurrencies = setAvailableCurrencies()
    $collectionHolder = $('#travelExpense_advancesReceived')
    if availableCurrencies.length > 0
        if $collectionHolder.data('index') is undefined
            $collectionHolder.data 'index', $collectionHolder.find('.advances-received').length

        prototype = $collectionHolder.data 'prototype'
        index = $collectionHolder.data 'index'

        prototype = prototype.replace '<label class="required">__name__label__</label>', ''
        $newAdvancesReceived = $(prototype.replace(/__name__/g, index)).addClass 'advances-received'

        $newAdvancesReceived.find('#travelExpense_advancesReceived_' + index + '_advancesReceived').addClass 'te-advances-received'
        $newAdvancesReceived
            .find('#travelExpense_advancesReceived_' + index + '_advancesReceived')
            .parent()
            .addClass 'display-inline-block vertical-align-top margin-right-1-em'

        $advancesPayback = createCustomField('te-advances-payback custom-field', 'Advances to be returned', '0')
        $amountSpent = createCustomField('te-amount-spent custom-field', 'Amount spent', '0')
        $amountPayable = createCustomField('te-amount-payable custom-field', 'Amount payable', '0')
        $newAdvancesReceived.find('.te-advances-received').parent().after $amountSpent
        $amountSpent.after $advancesPayback
        $advancesPayback.after $amountPayable

        $collectionHolder.append $newAdvancesReceived

        $collectionHolder.data 'index', index + 1

        $availableCurrencies = $newAdvancesReceived.find('.te-advances-received-currency')
        $availableCurrencies.parent().addClass 'display-inline-block vertical-align-top margin-right-1-em'
        # remove all currencies element from created element
        $availableCurrencies.children().remove()
        # add all available currencies to element
        availableCurrencies.forEach (availableCurrency) ->
            option = $('<option>')
            option.attr 'value', availableCurrency
            option.html availableCurrency
            $availableCurrencies.append option

        $availableCurrencies.on 'change', ->
            calculateAdvancesPayback()
            setAvailableCurrencies(true)

        $newAdvancesReceived.find('.te-advances-received').on 'change', ->
            calculateAdvancesPayback()

        createDeleteExpenseButton $newAdvancesReceived
        
    calculateAdvancesPayback()

addNewForm = ($collectionHolder) ->
    if $collectionHolder.data('index') is undefined
        $collectionHolder.data 'index', $collectionHolder.find('.formFieldsetChild').length

    # get form data from collection holder
    index = $collectionHolder.data 'index'
    prototype = $collectionHolder.data 'prototype'
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
    $collectionHolder.data 'index', index + 1
    $formFieldsetChild.find('.amount').parent().append($formFieldsetChild.find('.currency'))
    
    $formFieldsetChild.find('.amount-listen').on 'change', ->
        calculateAdvancesPayback()

    $formFieldsetChild.find('.currency-listen').on 'change', ->
        calculateAdvancesPayback()

    $collectionHolder.append $formFieldsetChild

    # init datepicker plugin
    $(document).data('opithrm').funcs.initDateInputs $formFieldsetChild
    $('.hasDatepicker').css 'padding-right', '0'
    return
    
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
$perDiem.addClass 'display-inline-block vertical-align-top margin-top-5 margin-bottom-10'

convertCurrency = (originCode, destinationCode, value) ->
    if originCode is destinationCode
        return value
    else
        return curConverter.convertCurrency(originCode, destinationCode, value).toFixed(2)
    
calculatePerDiem = () ->
    departureDate = $('#travelExpense_departureDateTime_date').val()
    departureHour = $('#travelExpense_departureDateTime_time_hour').val()
    departureMinute = $('#travelExpense_departureDateTime_time_minute').val()
    departure = "#{ departureDate } #{ departureHour }:#{ departureMinute }"

    departureDate = departureDate.split('-')
    departureYear = departureDate[0]
    departureMonth = departureDate[1]
    departureDay = departureDate[1]

    departureObj = new Date departureYear, departureMonth, departureDay, departureHour, departureMinute

    arrivalDate = $('#travelExpense_arrivalDateTime_date').val()
    arrivalHour = $('#travelExpense_arrivalDateTime_time_hour').val()
    arrivalMinute = $('#travelExpense_arrivalDateTime_time_minute').val()
    arrival = "#{ arrivalDate } #{ arrivalHour }:#{ arrivalMinute }"

    arrivalDate = arrivalDate.split('-')
    arrivalYear = arrivalDate[0]
    arrivalMonth = arrivalDate[1]
    arrivalDay = arrivalDate[1]

    arrivalObj = new Date arrivalYear, arrivalMonth, arrivalDay, arrivalHour, arrivalMinute

    if arrivalObj > departureObj
        $.ajax
            method: 'POST'
            url: Routing.generate 'OpitOpitHrmTravelBundle_expense_perdiem'
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

# Call the change state dialog function from main.
changeStateModal = ($self) ->
    $(document).data('opithrm').funcs.changeStateDialog $self, $(document).data('opithrm').funcs.changeTravelExpenseStatus,  {
        foreignId: $self.data('te')
        type: 'travel expense'
    }
    return

payrollAutocomplete = ->
    # Autocompleting the input text field.
    $(this).find('#email_to_payroll_ac').autocomplete
        source: (request, response) ->
            $.post Routing.generate('OpitOpitHrmUserBundle_user_search', role: 'role_payroll'), request, (data) -> response(data)
        minLength: 2
        select: (event, ui) ->
            $('#email_to_payroll').val ui.item.id
            return

# Sending email to payroll when the TE is approved.
emailToPayrollDialog = ($self) ->
    $(document).data('opithrm').funcs.changeStateDialog $self, $(document).data('opithrm').funcs.changeTravelExpenseStatus, {
        foreignId: $self.data('te')
        type: 'travel expense',
        template: 'OpitOpitHrmTravelBundle:Expense:changeStatusApproved.html.twig'
    }, payrollAutocomplete

    return

$(document).ready ->
    # Initialize all and available currencies
    setAvailableCurrencies()
    $(document).data('opithrm').funcs.makeElementToggleAble 'h3', $('.formFieldset')

    $('#add_user_expense').on 'click', ->
        addNewForm $('#travelExpense_userPaidExpenses')

    $('#add_company_expense').on 'click', ->
        addNewForm $('#travelExpense_companyPaidExpenses')

    $perDiemAmountsTable = $('<table>').addClass 'per-diem-amounts-slab bordered width-100'
    $.ajax
        method: 'POST'
        url: Routing.generate 'OpitOpitHrmTravelBundle_expense_perdiemvalues'
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
    $perDiem.insertBefore $('.generalFormFieldset .clear-both')
    
    $('.fa-question-circle').on 'mouseover', ->
        $(@).parent().parent().find('.formFieldsetDescription').removeClass('display-none')
    $('.fa-question-circle').on 'mouseout', ->
        $('.formFieldsetDescription').addClass 'display-none'

    $amountListen = $('#travelExpense_userPaidExpenses .amount-listen')
    if $amountListen.length > 0
        $amountListen.on 'change', ->
            calculateAdvancesPayback()

        $('#travelExpense_userPaidExpenses .currency-listen').on 'change', ->
            calculateAdvancesPayback()

        $('.formFieldsetChild .fa-minus-square').on 'click', ->
            $(@).parent().remove()
            calculateAdvancesPayback()

    $advancesReceived = $('.generalFormFieldset .te-advances-received')
    if $advancesReceived.length > 0
        $advancesReceived.on 'change', ->
            calculateAdvancesPayback()

        $('.generalFormFieldset .te-advances-received-currency').on 'change', ->
            calculateAdvancesPayback()
            setAvailableCurrencies(true)

        $('.advances-received .fa-minus-square').on 'click', ->
            $(@).closest('.advances-received').remove()
            setAvailableCurrencies(true)
            calculateAdvancesPayback()

    $('#travelExpense_departureDateTime_date, #travelExpense_arrivalDateTime_date').attr 'readonly', 'readonly'

    $('#travelExpense_departureDateTime_time_hour,
        #travelExpense_departureDateTime_time_minute,
        #travelExpense_arrivalDateTime_time_hour,
        #travelExpense_arrivalDateTime_time_minute').on 'change', ->
            calculatePerDiem()

    calculatePerDiem()

    calculateAdvancesPayback()
    setAvailableCurrencies(true)

    $('#add_advances_recieved').on 'click', ->
        addNewAdvanceReceived()
        
    if $('#travelExpense_add_travel_expense').hasClass 'button-disabled'
        $('.addFormFieldsetChild').each ->
            $(@).remove()
        $('.deleteFormFieldsetChild').each ->
            $(@).remove()
        $('.fa-minus-square').each ->
            $(@).remove()

    $('.changeState').on 'change', ->
        # If the status is approved send an email to payroll
        if parseInt($(@).val()) == 4
            emailToPayrollDialog $(@)
        else
            changeStateModal $(@)
        # Disable the select
        $(@).attr 'disabled', 'disabled'
        $(@).addClass 'dropdown-disabled'

    $('.formFieldset').on 'change', '.te-expense-type', ->
        $(@).closest('.formFieldsetChild').children('.selected-expense').html $("##{ $(@).attr 'id' } :selected").text()
    
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
, 'Arrival date can not be earlier than departure date.'
    
$form.validate
    ignore: []
    rules:
        'travelExpense[arrivalDateTime][time][minute]': 'compare',
        'travelExpense[taxIdentification]': {maxlength: 11},
        'travelExpense[toSettle]': {digits: true}
    errorPlacement: ($error, $element) ->
        $element.closest('div').append $error
 
$('#travelExpense_add_travel_expense').on 'click', (event) ->
    event.preventDefault()
    saveText = 'Create'
    sendForApprovalText = 'Create & send for approval'

    if not isNewTravelExpense
        saveText = 'Edit'
        sendForApprovalText = 'Edit & send for approval'

    if not $(@).hasClass 'button-disabled'
        if $form.valid() and validateAllExpenseDates()
            # for browsers that do not support input type date
            if not Modernizr.inputtypes.date
                $('input[type=date]').each ->
                    $(@).parent().find('input[type=hidden]').val $(@).val().replace(/(\d{2})\/(\d{2})\/(\d{4})/, "$3-$1-$2")
            $.ajax
                method: 'POST'
                url: Routing.generate 'OpitOpitHrmTravelBundle_expense_show_details', 'trId': $('#travelRequestPreview').attr 'data-tr-id'
                data: 'preview=1&' + $form.serialize()
            .done (data) ->
                $preview = $('<div id="dialog-travelrequest-preview"></div>').html data
                $preview.dialog
                    title: '<i class="fa fa-list-alt"></i> Details'
                    close: ->
                        $preview.dialog "destroy"
                    width: 550
                    maxHeight: $(window).outerHeight()-100
                    modal: on
                    buttons: [
                            text: saveText
                            click: ->
                                $form.submit()
                                return
                        ,
                            text: sendForApprovalText
                            click: ->
                                $form.attr('action', $form.attr('action') + '/1')
                                $form.submit()
                                return
                        ,
                            text: 'Cancel'
                            click: ->
                                $preview.dialog "destroy"
                                return
                    ]
            .fail (jqXHR, textStatus, errorThrown) ->
                $('<div></div>').html('The travel expense could not be saved due to an error.').dialog
                    title: 'Error'

$('#travelRequestPreview').on 'click', ->
    $.ajax
        method: 'POST'
        url: Routing.generate 'OpitOpitHrmTravelBundle_travel_show_details'
        data: 'id': $(@).attr 'data-tr-id'
    .done (data) ->
        $previewTr = $('<div id="dialog-show-details-tr"></div>');
        $previewTr.html(data)
            .dialog
                title: '<i class="fa fa-list-alt"></i> Details'
                close: ->
                    $previewTr.dialog "destroy"
                width: 550
                maxHeight: $(window).outerHeight()-100
                modal: on
        return
    return