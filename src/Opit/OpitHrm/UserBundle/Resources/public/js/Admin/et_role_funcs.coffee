$('#reply-message').css display:'none'

showRoleDialog = (id, name, description, url, title, flashMessage) ->
    $('#dialog-edititem h2').html(title).addClass 'dialog-h2'
    $('.dialog-description').html description
    $selfDialog = $('<div>').html $("##{ valueForm }").html()
    $selfDialog.find("##{ valueField }").val name
    $selfDialog.dialog
        width: 400
        modal: on
        title: title
        buttons:
            Create: ->
                if $selfDialog.find("##{ valueField }").val()
                    value = $selfDialog.find("##{ valueField }").val()
                    $.ajax
                        type: 'POST'
                        url: Routing.generate url, {id: id}
                        data: 'value': value
                    .done (data)->
                        if data.duplicate
                            $(document).data('opithrm').funcs.showAlert $selfDialog, data, 'create', "#{ propertyName } already exists", true
                        else
                            $('#list-table').replaceWith data
                            $(document).data('opithrm').funcs.initListPageListeners()
                            $(document).data('opithrm').funcs.initDeleteMultipleListener()
                            $(document).data('opithrm').funcs.showAlert $selfDialog, data, 'create', flashMessage
                        $selfDialog.dialog 'destroy'
                else
                    $selfDialog.find('#reply-message').css display:'block'
            Close: ->
                $selfDialog.dialog 'destroy'
                
    if name
        $('.ui-dialog-buttonset .ui-button:first-child .ui-button-text').text 'Edit'
              
deleteGroup = (id, name) ->
    if name
        $selfDialog = $('<div>').html "Are you sure you want to delete #{ propertyName }(s) \"#{ name }\"?"
        $selfDialog.dialog
            width: 400
            modal: on
            title: "Delete #{ propertyName }"
            buttons:
                Yes: ->
                    $.ajax
                        type: 'POST'
                        url: Routing.generate removeUrl
                        data: id: id
                    .done (data)->
                        if data.userRelated
                            $(document).data('opithrm').funcs.showAlert $selfDialog, data, 'create', "Deletion not allowed for #{ propertyName }(s) with relations", true
                        else
                            $('#list-table').replaceWith data
                            $(document).data('opithrm').funcs.initListPageListeners()
                            $(document).data('opithrm').funcs.initDeleteMultipleListener()
                            $(document).data('opithrm').funcs.showAlert $selfDialog, data, 'create', "#{ propertyNameCapital }(s) successfully deleted!"
                    $selfDialog.dialog 'destroy'
                No: ->
                    $('#list-table').find('input:checkbox').each ->
                        $(@).attr 'checked', false
                    $(document).data('opithrm').funcs.initDeleteMultipleListener()
                    $selfDialog.dialog 'destroy'
                    return
            close: ->
                    $('#list-table').find('input:checkbox').each ->
                        $(@).attr 'checked', false
                    $(document).data('opithrm').funcs.initDeleteMultipleListener()
                    $selfDialog.dialog 'destroy'
                    return

$('#main-wrapper #add').on 'click', ->
    showRoleDialog('new', '', "Create a new #{ propertyName }.", url, "Create #{ propertyName }", "#{ propertyNameCapital } successfully created!")
    
$('#main-wrapper').on 'click','.edit-item', ->
    id = $(@).closest('tr').children('td:nth-child(2)').html()
    name = $(@).closest('tr').children('td:nth-child(3)').html()
    
    showRoleDialog(id, name, "Edit selected #{ propertyName }.", url, "Edit #{ propertyName }", "#{ propertyNameCapital } successfully edited!")
    
$('#main-wrapper').on 'click','.remove', ->
    $(document).data('opithrm').funcs.resetAndSelectSingle $(@)
    parentTr = $(@).closest('tr')
    deleteGroup parentTr.children('td:nth-child(2)').html(), parentTr.children('td:nth-child(3)').html()
        
$('#delete').on 'click', ->
    ids = []
    names = ''
    $('.deleteMultiple').each ->
        if $(@).prop 'checked'
            parentTr = $(@).closest('tr')
            ids.push(parentTr.children('td:nth-child(2)').html())
            names += parentTr.children('td:nth-child(3)').html() + ', '
    names = names.substring(0, names.length-2)
    deleteGroup(ids, names)
    

$('#main-wrapper').on 'click', '.order-text', ->
    $(document).data('opithrm').funcs.serverSideListOrdering $(@), $(@).parent().find('i').attr('data-field'), listUrl, 'list-table', 'searchForm'
$('#main-wrapper').on 'click', '.fa-sort', ->
    $(document).data('opithrm').funcs.serverSideListOrdering $(@), $(@).data('field'), listUrl, 'list-table', 'searchForm'
