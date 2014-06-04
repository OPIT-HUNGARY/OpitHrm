# declare holiday setting wrapper
leaveSettingWrapper = ''
idIteratorValue = 0
isHadChild = false;

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
    isHasChild = $('#list-settings').children('.formFieldsetChild').length > 0

    # If the form valid, Save it.
    if form.valid() && (isHasChild || isHadChild)
        disableSaveButton(true)
        $.ajax
            method: 'POST'
            data: form.serialize()
            dataType: 'json'
            url: Routing.generate 'OpitNotesLeaveBundle_admin_save_leave_setting'
        .done (data) ->
            disableSaveButton(false)
            response = data
            #refresh the per diem listing
            $.ajax
                type: 'POST'
                url: Routing.generate 'OpitNotesLeaveBundle_admin_list_leave_settings'
                data: "showList" : 1
            .done (data)->
                $('#list-settings').html data
                $(document).data('notes').funcs.showAlert $('#main-content'), response, "create", "Entitlement configuration saved successfully!"
                isHadChild = $('.container').children('.formFieldsetChild').length > 0
            .fail (data) ->
                $(document).data('notes').funcs.showAlert $('#main-content'), response, "create", "Error", true
            return
        .fail (data) ->
                $(document).data('notes').funcs.showAlert $('#main-content'), response, "create", "Error", true
        return
    else
        disableSaveButton(false)
    return


# document ready
$(document).ready () ->
    idIteratorValue = $('.formFieldsetChild').length
    isHadChild = $('.container').children('.formFieldsetChild').length > 0
    $.ajax
        method: 'GET'
        url: Routing.generate 'OpitNotesLeaveBundle_admin_show_leave_setting'
    .done (data) ->
        leaveSettingWrapper = data
        return
    return
    
$(document).ajaxStop () ->
    $(document).data('notes').funcs.makeElementToggleAble 'h3', $('.formFieldset')