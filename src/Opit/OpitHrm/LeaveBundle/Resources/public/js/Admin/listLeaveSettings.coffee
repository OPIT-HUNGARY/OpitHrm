# declare holiday setting wrapper
leaveSettingWrapper = ''
idIteratorValue = 0

# add new holiday setting form
$('#list-settings').on 'click', '.addLeaveSetting', (event) ->
    event.preventDefault()
    clonedLeaveSettingWrapper = leaveSettingWrapper
    idIteratorValue++
    clonedLeaveSettingWrapper = clonedLeaveSettingWrapper.replace /__index__/g, idIteratorValue
    # get the container from the same level in the DOM tree
    $(@).siblings('.container').append clonedLeaveSettingWrapper
    # set the holiday group id
    groupId = $(@).siblings('.container').find('.holiday_group_id').val()
    $('#leaveSetting' + idIteratorValue + 'leaveGroup').val groupId

# delete holiday setting form
$('#list-settings').on 'click', '.deleteLeaveSetting', ->
    $(@).parent().parent().remove()

# disable or enable the save button
disableSaveButton = (disable) ->
    if disable is true
        $('#save').attr('disabled', true)
    else
        $('#save').removeAttr('disabled')

# save the per diems at the backend
$('#save').click (event) ->
    event.preventDefault()
    form = $('form')

    # If the form valid, Save it.
    if form.valid()
        disableSaveButton(true)
        $.ajax
            method: 'POST'
            data: form.serialize()
            dataType: 'json'
            url: Routing.generate 'OpitOpitHrmLeaveBundle_admin_save_leave_setting'
        .done (data) ->
            disableSaveButton(false)
            response = data
            #refresh the per diem listing
            $.ajax
                type: 'POST'
                url: Routing.generate 'OpitOpitHrmLeaveBundle_admin_list_leave_settings'
                data: "showList" : 1
            .done (data)->
                $('#list-settings').html data
                $(document).data('opithrm').funcs.showAlert $('#main-content'), response, "create", "Entitlement configuration saved successfully!"
            .fail (jqXHR, textStatus, errorThrown) ->
                $(document).data('opithrm').funcs.showAlert $('#main-content'), $.parseJSON(jqXHR.responseText), "create", "Error", true
            return
        .fail (jqXHR, textStatus, errorThrown) ->
                $(document).data('opithrm').funcs.showAlert $('#main-content'), $.parseJSON(jqXHR.responseText), "create", "Error", true
        return
    else
        disableSaveButton(false)
    return


# document ready
$(document).ready () ->
    idIteratorValue = $('.formFieldsetChild').length
    $.ajax
        method: 'GET'
        url: Routing.generate 'OpitOpitHrmLeaveBundle_admin_show_leave_setting'
    .done (data) ->
        leaveSettingWrapper = data
        return
    return
    
$(document).ajaxStop () ->
    $(document).data('opithrm').funcs.makeElementToggleAble 'h3', $('.formFieldset')