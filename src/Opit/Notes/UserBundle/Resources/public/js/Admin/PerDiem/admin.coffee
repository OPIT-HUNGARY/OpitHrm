# add new per diem form
$addPerDiem = $('#addPerDiem')
$addPerDiem.click (event) ->
    event.preventDefault()
    $.ajax
        method: 'POST'
        url: Routing.generate 'OpitNotesUserBundle_admin_show_perdiem'
    .done (data) ->
        $('.container').append(data)
        return
    return

# delete per diem form
$('.container').on 'click', '.deletePerDiem', ->
    id = $(@).parent().parent().find('#id').val()
    isDelete = $(@).parent().parent().find('#isToDelete')
    if id > 0
        isDelete.val(1)
        $(@).parent().parent().hide()
    else
        $(@).parent().parent().remove()
    return

validationOfHoursCompare = () ->
    isUnique = true
    hoursArray = []
    $('.hours').each (index) ->
        if $(@).val() in hoursArray
            isUnique = false
        else
            hoursArray.push($(@).val())
    return isUnique

validationOfLessThan24 = (valueOfHour) ->
    isLessThan24 = true
    if 24 < valueOfHour
        isLessThan24 = false
    return isLessThan24
    
validationOfGreaterThan0 = (valueOfAmount) ->
    isGreaterThan0 = true
    if 0 >= valueOfAmount
        isGreaterThan0 = false
    return isGreaterThan0

# validation - key up validation for hours
$('.container').on 'change', '.hours', ->
    isLessThen24 = validationOfLessThan24($(@).val())
    isUnique = validationOfHoursCompare()
    $wrapper = $(@).parent()
    # check if hour less than 24
    if !isLessThen24
        if $wrapper.children('label.custom-lablel-error').length is 0
            errorMessage = $('<label>').html('The value of hours should be less than 24!').addClass 'custom-label-error'
            $wrapper.prepend '<br />'
            $wrapper.prepend errorMessage
        return false
    else if !isUnique
        if $wrapper.children('label.custom-label-error').length is 0
            errorMessage = $('<label>').html('The value of hours is a repeated value!').addClass 'custom-label-error'
            $wrapper.prepend '<br />'
            $wrapper.prepend errorMessage
        return false
    else
        $wrapper.children('label.custom-label-error').remove()
        $wrapper.children('br').remove()
        return true

# validation - key up validation for amount
$('.container').on 'change', '.amount', ->
    isGreaterThan0 = validationOfGreaterThan0($(@).val())
    $wrapper = $(@).parent()
    console.log isGreaterThan0
    # check if amount greater than 24
    if !isGreaterThan0
        console.log 'greater'
        if $wrapper.children('label.custom-lablel-error').length is 0
            console.log 'wrapping'
            errorMessage = $('<label>').html('The Amount should be greater than 0!').addClass 'custom-label-error'
            $wrapper.prepend '<br />'
            $wrapper.prepend errorMessage
            disableSaveButton(true)
        return false
    else
        $wrapper.children('label.custom-label-error').remove()
        $wrapper.children('br').remove()
        disableSaveButton(false)
        return true
        
disableSaveButton = (disable) ->
    if disable is true
        $('#save').attr('disabled', true)
    else
        $('#save').removeAttr('disabled')
    
# save the per diems at the backend
$('#save').click (event) ->
    event.preventDefault()
    forms = $('form')
    
    if forms.valid() and validationOfHoursCompare()
        disableSaveButton(true)
        $.ajax
            method: 'POST'
            data: forms.serialize()
            dataType: 'json'
            url: Routing.generate 'OpitNotesUserBundle_admin_save_perdiem'
        .done (data) ->
            disableSaveButton(false)
            response = data
            $.ajax
                type: 'POST'
                url: Routing.generate 'OpitNotesUserBundle_admin_list_perdiem'
                data: "showList" : 1
            .done (data)->
                $('.container').html data.list
                $(document).data('notes').funcs.showAlert response, "create", "Per diems saved successfully!" 
            .fail (data) ->
                $(document).data('notes').funcs.showAlert data.responseText, "create","Error"
            return
        .fail (data) ->
                $(document).data('notes').funcs.showAlert data.responseText, "create","Error"
        return
    else
        disableSaveButton(false)
    return