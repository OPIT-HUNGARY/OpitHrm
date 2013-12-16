showRoleDialog = (groupId, groupName, url, title) ->
    selfDialog = $('<div>')
    selfDialog.html "<label class='margin-top-2 inlineElements' for='group'>Role name: </label><input class='inlineElements' type='text' id='group' name='group' value='#{ groupName }' />"
    selfDialog.dialog
        width: 400
        modal: on
        title: title
        buttons:
            Create: ->
                group = $('#group').val()
                $.ajax
                    type: 'POST'
                    url: Routing.generate url, {id: groupId}
                    data: 'group': group
                .done (data)->
                    if data.duplicate
                        $(document).data('notes').funcs.showAlert data, 'create', 'Role already exists', true
                    else
                        $('#list-table').replaceWith data
                        $(document).data('notes').funcs.showAlert data, 'create', "#{ title } successfully"
                    selfDialog.dialog 'destroy'
            Close: ->
                selfDialog.dialog 'destroy'
              
deleteGroup = (groupId, roleName) ->
    if not not roleName
        selfDialog = $('<div>')
        selfDialog.html "Deleting role(s) \"#{ roleName }\"?"
        selfDialog.dialog
            width: 400
            modal: on
            title: 'Are you sure you want to continue?'
            buttons:
                Continue: ->
                    $.ajax
                        type: 'POST'
                        url: Routing.generate 'OpitNotesUserBundle_admin_groups_delete'
                        data: id: groupId
                    .done (data)->
                        if data.userRelated
                            $(document).data('notes').funcs.showAlert data, 'create', 'Deletion not allowed for roles with relations', true
                        else
                            $('#list-table').replaceWith data
                            $(document).data('notes').funcs.showAlert data, 'create', 'Role deletion successfully!'
                    selfDialog.dialog 'destroy'                        
                Cancel: ->
                    $('#list-table').find('input:checkbox').each ->
                        $(@).attr 'checked', false                
                    selfDialog.dialog 'destroy'

$('#main-wrapper').on 'click','#add', ->
    showRoleDialog('new', '', 'OpitNotesUserBundle_admin_groups_show', 'Create role')
    
$('#main-wrapper').on 'click','.edit-group', ->
    groupId = $(@).closest('tr').children('td:nth-child(2)').html()
    groupName = $(@).closest('tr').children('td:nth-child(3)').html()
    
    showRoleDialog(groupId, groupName, 'OpitNotesUserBundle_admin_groups_show', 'Edit role')
    
$('#main-wrapper').on 'click','.remove-group', ->
    parentTr = $(@).closest('tr')
    roleName = parentTr.children('td:nth-child(3)').html()
    groupId = parentTr.children('td:nth-child(2)').html()
    parentTr.find('input').attr 'checked', true
    
    deleteGroup(groupId, roleName)
        
$('#delete').on 'click', ->
    groupIds = []
    roleName = ''
    $('.list-delete-user').each ->
        if $(@).prop 'checked'
            parentTr = $(@).closest('tr')
            groupIds.push(parentTr.children('td:nth-child(2)').html())
            roleName += parentTr.children('td:nth-child(3)').html() + ', '
    roleName = roleName.substring(0, roleName.length-2)
    
    deleteGroup(groupIds, roleName)
    
$('.width-24-fix .fa-trash-o').on 'click', ->
    $('.list-delete-user:enabled').checkAll()