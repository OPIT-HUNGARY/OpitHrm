addFormDeleteButton = ->
    $deleteButton = $('<div>').html '<i class="fa fa-minus-square"></i>Delete'
    $deleteButton.addClass 'deleteFormFieldsetChild formFieldsetButton'
    $deleteButton.click ->
        $(@).parent().remove()
    return $deleteButton        

$('label.required').each ->
    if $(@).text() is '0' then $(@).remove()
    return
    
generalData = $('<div>').addClass 'formFieldset generalFormFieldset'
generalData.append($('#travelRequest_user_ac,
                      #travelRequest_departure_date,
                      #travelRequest_arrival_date,
                      #travelRequest_customer_related,
                      #travelRequest_opportunity_name,
                      #travelRequest_trip_purpose').parent())
$('#travelRequest').prepend generalData

#add team manager and general manager to formFieldset
requiredApprovals = $('<div>').addClass 'formFieldset marginLeft'
requiredApprovals.append($('<h3>').html('Required approvals'))
requiredApprovals.append($('#travelRequest_team_manager_ac').parent())
requiredApprovals.append($('#travelRequest_general_manager_ac').parent())
$('#travelRequest_general_manager').after requiredApprovals

#add form fieldset class to travelRequest_destinations and travelRequest_accomodations for easier formatting
$('#travelRequest_destinations').parent().addClass 'formFieldset'
$('#travelRequest_accomodations').parent().addClass 'formFieldset'

#set elements to be inlined
$('#travelRequest_departure_date, #travelRequest_arrival_date,
   #travelRequest_customer_related, #travelRequest_opportunity_name,
   #travelRequest_team_manager_ac, #travelRequest_general_manager_ac').parent().addClass 'inlineElements'

#add br tag to break inline
$('#travelRequest_arrival_date').parent().after('<br />')

#change label tags to h3 tags
accomodationsLabel = $('#travelRequest_accomodations').parent().children 'label'
accomodationsLabel.replaceWith('<h3>'+accomodationsLabel.html()+'</h3>')

destinationsLabel = $('#travelRequest_destinations').parent().children 'label'
destinationsLabel.replaceWith('<h3>'+destinationsLabel.html()+'</h3>')

#check if accomodation or destination was set
#if there are more input type text fields than one some data was sent
travelRequestDestinations0 = $('#travelRequest_destinations_0')
if $('#travelRequest_destinations :input[type=text]').length > 1
    $('#travelRequest_destinations').children().each ->
        $(@).addClass 'formFieldsetChild'
        $(@).children().remove 'label'
        $(@).append addFormDeleteButton
#if there is only one input type text field check if it has got any value
else
    if $('#travelRequest_destinations :input[type=text]').val() is ""
        travelRequestDestinations0.parent().remove()
    else
        travelRequestDestinations0.parent().addClass 'formFieldsetChild'
        travelRequestDestinations0.parent().append addFormDeleteButton

travelRequestAccomodations0 = $('#travelRequest_accomodations_0')
if $('#travelRequest_accomodations :input[type=text]').length > 2
    $('#travelRequest_accomodations').children().each ->
        $(@).addClass 'formFieldsetChild'
        $(@).children().remove 'label'
        $(@).append addFormDeleteButton
else
    if $('#travelRequest_accomodations :input[type=text]').val() is ""
        travelRequestAccomodations0.parent().remove()
    else
        travelRequestAccomodations0.parent().addClass 'formFieldsetChild'
        travelRequestAccomodations0.parent().append addFormDeleteButton

#check customer related value
travelOpportunity = $('#travelRequest_opportunity_name')
if travelOpportunity.val() is ''
    travelOpportunity.parent().css {display: 'none'}
else
    $('#travelRequest_customer_related').val('0')

$('#travelRequest_customer_related').change ->
    if $(@).val() is "0"
        travelOpportunity.parent().css {display: 'inline-block'}
        travelOpportunity.attr('required', 'required')
    else 
        travelOpportunity.parent().css {display: 'none'}
        travelOpportunity.removeAttr 'required'

$form = $('#travelRequestForm')

$('#travelRequest_user_ac').autocomplete
    source: Routing.generate 'OpitNotesTravelBundle_travel_userSearch', user: 'all'
    minLength: 2
    response: (event, ui) ->
        return  
    select: (event, ui) ->
        $('#travelRequest_user').val ui.item.id
        return
    
$('#travelRequest_team_manager_ac').autocomplete
    source: Routing.generate 'OpitNotesTravelBundle_travel_userSearch', user: 'team_manager'
    minLength: 2
    select: (event, ui) ->
        $('#travelRequest_team_manager').val ui.item.id
        return
    
$('#travelRequest_general_manager_ac').autocomplete
    source: Routing.generate 'OpitNotesTravelBundle_travel_userSearch', user: 'general_manager'
    minLength: 2
    select: (event, ui) ->
        $('#travelRequest_general_manager').val ui.item.id
        return

#for adding new destination form
$addDestination = $('<div class="addFormFieldsetChild formFieldsetButton"><i class="fa fa-plus-square"></i>Add destination</div>')
$destinationCollection = $('#travelRequest_destinations').append $addDestination
$destinationCollection.data 'index', $destinationCollection.find(':input').length
$addDestination.click (e) ->
    e.preventDefault()
    addForm $destinationCollection, $addDestination
    return
 
#for adding new accomodation form 
$addAccomodation = $('<div class="addFormFieldsetChild formFieldsetButton"><i class="fa fa-plus-square"></i>Add accomodation</div>')
$accomodationCollection = $('#travelRequest_accomodations').append $addAccomodation
$accomodationCollection.data 'index', $accomodationCollection.find(':input').length
$addAccomodation.click (e) ->
    e.preventDefault()
    addForm $accomodationCollection, $addAccomodation
    return

addForm = ($collectionHolder, $addButton) ->
    prototype = $collectionHolder.data 'prototype'
    index = $collectionHolder.data 'index'
    newForm = prototype.replace '<label class="required">__name__label__</label>', ''
    newForm = newForm.replace /__name__/g, index

    $newForm = $(newForm)
    $newForm = $newForm.append addFormDeleteButton
    $newForm.addClass 'formFieldsetChild'

    $collectionHolder.data 'index', index+1
    $addButton.before $newForm

# method to validate form before preview
$form = $('#travelRequestForm')

$errorMessages = $('#travelRequestForm ul');
$errorMessages.remove();

# method to validate if departure date is smaller than arrival date
$.validator.addMethod 'compare', (value, element) ->
    departureDate = $('#travelRequest_departure_date').val()
    arrivalDate = $('#travelRequest_arrival_date').val()
    return departureDate < arrivalDate
, 'Arrival date should be smaller than departure date.'

# check if element and the hidden id field for the element is empty
$.validator.addMethod 'checkId', (value, element) ->
    $element = $(element)
    id = $(element).attr 'id'
    $idElement = $('#'+id.substr(0, id.length-3))
    
    if $element.val()
        if not $idElement.val() then return false else return true
    else return false

, 'This field is required.'

# assing custom validation rules to arrival date, user, general manager
$form.validate
    ignore: []
    rules:
        "travelRequest[arrival_date]": "compare"
        "travelRequest[user_ac]": "checkId"
        "travelRequest[general_manager_ac]": "checkId"

$( '#travelRequest_add_travel_request' ).click ->
    event.preventDefault()
    #validate form on client side
    if $form.valid()
        # if form is valid post ajax request to get the preview
        $.ajax
            method: 'POST'
            url: Routing.generate 'OpitNotesTravelBundle_travel_show_details'
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
            $('<div></div>').html('The travel request could not be saved due to an error.').dialog
                title: 'Error'
    return