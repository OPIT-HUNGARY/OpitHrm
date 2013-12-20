createDeleteButton = ->
    $deleteButton = $('<div>')
    $deleteButton.addClass('deleteFormFieldsetChild formFieldsetButton').html '<i class="fa fa-minus-square"></i>Delete'
    $deleteButton.on 'click', ->
        $(@).parent().remove()
        
    return $deleteButton

reCreateExpenses = (self) ->
    $selectedExpense = $('<span>').addClass 'selected-expense'
    $selectedExpense.html self.find('select').find(':selected').text()
    $container = $('<div>').addClass 'formFieldsetChild'
    self.children('label:first').remove()
    $container.append self
    $container.append createDeleteButton()
    $container.prepend $selectedExpense
    
    return $container

addNewForm = (collectionHolder, parent) ->
    event.preventDefault()
    
    #collectionHolder.data 'index', collectionHolder.find(':input').length
    
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
    
    collectionHolder.data 'index', index + 1

    parent.append $formFieldsetChild
    
$(document).ready ->
    $('#travelExpense').children('.formFieldset:nth-child(3)').append $addCompanyTagLink
    $('#travelExpense').children('.formFieldset:nth-child(2)').append $addUserTagLink
    companyPaidExpensesIndex = 0;
    userPaidExpensesIndex = 0;
    
    if $('#travelExpense_companyPaidExpenses').children('div').length > 0
        $('#travelExpense_companyPaidExpenses').children('div').each ->
            $container = reCreateExpenses($(@))
            $('#travelExpense').children('.formFieldset:nth-child(3)').append $container
            companyPaidExpensesIndex++
        
    if $('#travelExpense_userPaidExpenses').children('div').length > 0
        $('#travelExpense_userPaidExpenses').children('div').each ->
            $container = reCreateExpenses($(@))
            $('#travelExpense').children('.formFieldset:nth-child(2)').append $container     
            userPaidExpensesIndex++
    
    $('#travelExpense_companyPaidExpenses').data 'index', companyPaidExpensesIndex
    $('#travelExpense_userPaidExpenses').data 'index', userPaidExpensesIndex
    $('#travelExpense_companyPaidExpenses').parent().children('label').remove()
    $('#travelExpense_userPaidExpenses').parent().children('label').remove()
    
    $('#travelExpense').css display: 'block'

$formFieldset = $('<div>')
$formFieldset.addClass 'formFieldset'

$generalFormFieldset = $formFieldset.clone().addClass 'generalFormFieldset'
$expensesPaidByMe = $formFieldset.clone().append $('<h3>').html 'Expenses paid by me'
$expensesPaidByOpit = $formFieldset.clone().append $('<h3>').html 'Expenses paid by opit'

$('#travelExpense').prepend $expensesPaidByOpit
$('#travelExpense').prepend $expensesPaidByMe
$('#travelExpense').prepend $generalFormFieldset
$('#travelExpense').addClass 'travelForm'
        
$('.formFieldset').on 'change', '.te-expense-type', ->
    $(@).closest('.formFieldsetChild').children('.selected-expense').html $("##{ $(@).attr 'id' } :selected").text()

# move all element with specified class into form fieldset
$('.te-claim').each (index) ->
    $(@).parent().addClass 'inlineElements'
    $generalFormFieldset.append $(@).parent()
    
    # if element has class display-none add class to elements parent node
    if $(@).hasClass 'display-none'
        $(@).removeClass 'display-none'
        $(@).parent().addClass 'display-none'
    # allow two properties in a row
    if index % 2
        $generalFormFieldset.append $('<br>')
        
# create add expenses button, and add on click listeners to them
$addCompanyTagLink = $('<div class="addFormFieldsetChild formFieldsetButton"><i class="fa fa-plus-square"></i>Add company expense</div>')
$addCompanyTagLink.on 'click', ->
    addNewForm($('#travelExpense_companyPaidExpenses'), $('#travelExpense').children('.formFieldset:nth-child(3)'))    
    
$addUserTagLink = $('<div class="addFormFieldsetChild formFieldsetButton"><i class="fa fa-plus-square"></i>Add user expense</div>')
$addUserTagLink.on 'click', ->
    addNewForm($('#travelExpense_userPaidExpenses'), $('#travelExpense').children('.formFieldset:nth-child(2)'))
    
    
$form = $('#travelExpenseForm')    
    
$.validator.addMethod 'compare', (value, element) ->
    departureDate = $('#travelExpense_departureDateTime').val()
    arrivalDate = $('#travelExpense_arrivalDateTime').val()
    return departureDate < arrivalDate
, 'Arrival date should not be smaller than departure date.'    
    
$form.validate
    ignore: []
    rules:
        "travelExpense[arrivalDateTime]": "compare"  
 
$('#travelExpense_add_travel_expense').on 'click', ->
    event.preventDefault()
    if $form.valid()
        console.log 'valid'
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