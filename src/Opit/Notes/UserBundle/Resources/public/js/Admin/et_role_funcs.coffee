$('#reply-message').css display:'none'

showRoleDialog = (id, name, description, url, title, flashMessage) ->
    $('#dialog-edititem h2').html title
    $('.description').html description
    selfDialog = $('<div>')
    selfDialog.html $("##{ valueForm }").html()
    selfDialog.find("##{ valueField }").val name
    selfDialog.dialog
        width: 400
        modal: on
        title: title
        buttons:
            Create: ->
                if selfDialog.find("##{ valueField }").val()
                    value = selfDialog.find("##{ valueField }").val()
                    $.ajax
                        type: 'POST'
                        url: Routing.generate url, {id: id}
                        data: 'value': value
                    .done (data)->
                        if data.duplicate
                            $(document).data('notes').funcs.showAlert data, 'create', "#{ propertyName } already exists", true
                        else
                            $('#list-table').replaceWith data
                            $(document).data('notes').funcs.showAlert data, 'create', flashMessage
                        selfDialog.dialog 'destroy'
                else
                    selfDialog.find('#reply-message').css display:'block'
            Close: ->
                selfDialog.dialog 'destroy'
                
    if name
        $('.ui-dialog-buttonset .ui-button:first-child .ui-button-text').text 'Edit'
              
deleteGroup = (id, name) ->
    if not not name
        selfDialog = $('<div>')
        selfDialog.html "Are you sure you want to delete #{ propertyName }(s) \"#{ name }\"?"
        selfDialog.dialog
            width: 400
            modal: on
            title: "Delete #{ propertyName }"
            buttons:
                Continue: ->
                    $.ajax
                        type: 'POST'
                        url: Routing.generate removeUrl
                        data: id: id
                    .done (data)->
                        if data.userRelated
                            $(document).data('notes').funcs.showAlert data, 'create', "Deletion not allowed for #{ propertyName }(s) with relations", true
                        else
                            $('#list-table').replaceWith data
                            $(document).data('notes').funcs.showAlert data, 'create', "#{ propertyNameCapital }(s) successfully deleted!"
                    selfDialog.dialog 'destroy'
                Cancel: ->
                    $('#list-table').find('input:checkbox').each ->
                        $(@).attr 'checked', false
                    selfDialog.dialog 'destroy'

$('#main-wrapper').on 'click','#add', ->
    showRoleDialog('new', '', "Create a new #{ propertyName }.", url, "Create #{ propertyName }", "#{ propertyNameCapital } successfully created!")
    
$('#main-wrapper').on 'click','.edit-group', ->
    id = $(@).closest('tr').children('td:nth-child(2)').html()
    name = $(@).closest('tr').children('td:nth-child(3)').html()
    
    showRoleDialog(id, name, "Edit selected #{ propertyName }.", url, "Edit #{ propertyName }", "#{ propertyNameCapital } successfully edited!")
    
$('#main-wrapper').on 'click','.remove-group', ->
    parentTr = $(@).closest('tr')
    name = parentTr.children('td:nth-child(3)').html()
    id = parentTr.children('td:nth-child(2)').html()
    parentTr.find('input').attr 'checked', true
    
    deleteGroup(id, name)
        
$('#delete').on 'click', ->
    ids = []
    names = ''
    $('.list-delete-user').each ->
        if $(@).prop 'checked'
            parentTr = $(@).closest('tr')
            ids.push(parentTr.children('td:nth-child(2)').html())
            names += parentTr.children('td:nth-child(3)').html() + ', '
    names = names.substring(0, names.length-2)
    
    deleteGroup(ids, names)
    
$('th .fa-trash-o').on 'click', ->
    $('.list-delete-user:enabled').checkAll()
            
inverse = false
$('form').on 'click', '.fa-sort', ->
    inverse = $(document).data('notes').funcs.clientSideListOrdering $(@), inverse
    
$('form').on 'click', '.order-text', ->
    inverse = $(document).data('notes').funcs.clientSideListOrdering $(@).parent().find('i'), inverse
