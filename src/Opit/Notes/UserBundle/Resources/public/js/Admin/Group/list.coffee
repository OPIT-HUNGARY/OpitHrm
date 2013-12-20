$('#reply-message').css display:'none'

showRoleDialog = (groupId, groupName, description, url, title, flashMessage) ->
    $('#dialog-edititem h2').html title
    $('.description').html description
    selfDialog = $('<div>')
    selfDialog.html $('#roleForm').html()
    selfDialog.find('#group').val groupName
    selfDialog.dialog
        width: 400
        modal: on
        title: title
        buttons:
            Create: ->
                if selfDialog.find('#group').val()
                    group = selfDialog.find('#group').val()
                    $.ajax
                        type: 'POST'
                        url: Routing.generate url, {id: groupId}
                        data: 'group': group
                    .done (data)->
                        if data.duplicate
                            $(document).data('notes').funcs.showAlert data, 'create', 'Role already exists', true
                        else
                            $('#list-table').replaceWith data
                            $(document).data('notes').funcs.showAlert data, 'create', flashMessage
                        selfDialog.dialog 'destroy'
                else
                    selfDialog.find('#reply-message').css display:'block'
            Close: ->
                selfDialog.dialog 'destroy'
              
deleteGroup = (groupId, roleName) ->
    if not not roleName
        selfDialog = $('<div>')
        selfDialog.html "Are you sure you want to delete role(s) \"#{ roleName }\"?"
        selfDialog.dialog
            width: 400
            modal: on
            title: 'Delete role'
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
                            $(document).data('notes').funcs.showAlert data, 'create', 'Role(s) successfully deleted!'
                    selfDialog.dialog 'destroy'                        
                Cancel: ->
                    $('#list-table').find('input:checkbox').each ->
                        $(@).attr 'checked', false                
                    selfDialog.dialog 'destroy'

$('#main-wrapper').on 'click','#add', ->
    showRoleDialog('new', '', 'Create a new role.', 'OpitNotesUserBundle_admin_groups_show', 'Create role', 'Role successfully created!')
    
$('#main-wrapper').on 'click','.edit-group', ->
    groupId = $(@).closest('tr').children('td:nth-child(2)').html()
    groupName = $(@).closest('tr').children('td:nth-child(3)').html()
    
    showRoleDialog(groupId, groupName, 'Edit selected role.', 'OpitNotesUserBundle_admin_groups_show', 'Edit role', 'Role successfully edited!')
    
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