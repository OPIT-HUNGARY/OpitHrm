# declare per diem wrapper
perDiemWrapper = ''
idIteratorValue = 0
isHadChild = false;

# add new per diem form
$addPerDiem = $('#addPerDiem')

$addPerDiem.click (event) ->
    event.preventDefault()
    clonedPerDiemWrapper = perDiemWrapper
    idIteratorValue++
    clonedPerDiemWrapper = clonedPerDiemWrapper.replace /__index__/g, idIteratorValue
    $('.container').append clonedPerDiemWrapper

# delete per diem form
$('.container').on 'click', '.deletePerDiem', ->
    $(@).parent().parent().remove()

# validation for compare the hours
validationOfHoursCompare = () ->
    isUnique = true
    hoursArray = []
    $('.hours').each (index) ->
        if $(@).val() in hoursArray
            isUnique = false
        else
            hoursArray.push($(@).val())
    return isUnique

#validation to checking the value is greater than 0
validationOfGreaterThan0 = (valueOfAmount) ->
    isGreaterThan0 = true
    if 0 >= valueOfAmount
        isGreaterThan0 = false
    return isGreaterThan0

#check the hours fields
hoursCheck = (hoursElement) ->
    isUnique = validationOfHoursCompare()
    $wrapper = hoursElement.parent()
    if !isUnique
        # check if hour is not repeated value
        if $wrapper.children('label.custom-label-error').length is 0
            errorMessage = $('<label>').html('The value of hours should be unique value!').addClass 'custom-label-error'
            $wrapper.prepend '<br />'
            $wrapper.prepend errorMessage
            disableSaveButton(true)
        return false
    else
        hours = $('form').find('.hours')
        hours.each (index) ->
            $wrapper = $(@).parent()
            $wrapper.children('label.custom-label-error').remove()
            $wrapper.children('br').remove()
            disableSaveButton(false)
         return true

#check hours on keyup event
$(".container").on "keyup", ".hours", ->
    hoursCheck($(@))
#check hours on change event
$(".container").on "change", ".hours", ->
    hoursCheck($(@))

# check the amount fields
checkAmount = (amountElement) ->
    isGreaterThan0 = validationOfGreaterThan0(amountElement)
    if !isGreaterThan0
        return false
    else
        return true

# disable or enable the save button
disableSaveButton = (disable) ->
    if disable is true
        $('#save').attr('disabled', true)
    else
        $('#save').removeAttr('disabled')

# document ready
$(document).ready () ->
    idIteratorValue = $('.formFieldsetChild').length
    isHadChild = $('.container').children('.formFieldsetChild').length > 0
    $.ajax
        method: 'GET'
        url: Routing.generate 'OpitNotesUserBundle_admin_show_perdiem'
    .done (data) ->
        perDiemWrapper = data
        return
    return

# method to validate if departure date is smaller than arrival date
$.validator.addMethod 'checkAmount', (value, element) ->
    return checkAmount(value)
, 'The Amount should be greater than 0!'

$.validator.addClassRules(
    'amount', {checkAmount: {elements: ".amount" }}
);

# save the per diems at the backend
$('#save').click (event) ->
    event.preventDefault()
    form = $('form')
    isHasChild = $('.container').children('.formFieldsetChild').length > 0

    # If the form valid, Save it.
    if form.valid() && validationOfHoursCompare() && (isHasChild || isHadChild)
        disableSaveButton(true)
        $.ajax
            method: 'POST'
            data: form.serialize()
            dataType: 'json'
            url: Routing.generate 'OpitNotesUserBundle_admin_save_perdiem'
        .done (data) ->
            disableSaveButton(false)
            response = data
            #refresh the per diem listing
            $.ajax
                type: 'POST'
                url: Routing.generate 'OpitNotesUserBundle_admin_list_perdiem'
                data: "showList" : 1
            .done (data)->
                $('.container').html data
                $(document).data('notes').funcs.showAlert $('#main-content'), response, "create", "Per diems saved successfully!"
                isHadChild = $('.container').children('.formFieldsetChild').length > 0
            .fail (jqXHR, textStatus, errorThrown) ->
                $(document).data('notes').funcs.showAlert $('#main-content'), $.parseJSON(jqXHR.responseText), "create", "Error", true
            return
        .fail (jqXHR, textStatus, errorThrown) ->
                $(document).data('notes').funcs.showAlert $('#main-content'), $.parseJSON(jqXHR.responseText), "create", "Error", true
        return
    else
        disableSaveButton(false)
    return
