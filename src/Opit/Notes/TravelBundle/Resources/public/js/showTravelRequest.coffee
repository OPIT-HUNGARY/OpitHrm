# Where addClass called twice first addClass contains selector(s) and second the actual styles
# custom compare validator to compare length of trip and number of nights
compareDays = () ->
    #get arrival and departure date value
    departureDate = new Date $('#travelRequest_departure_date').val()
    arrivalDate = new Date $('#travelRequest_arrival_date').val()

    #get days between arrival and departure
    diffDays = (arrivalDate.getTime() - departureDate.getTime())/(24*60*60*1000)#one day
    accomodationDays = '0'
    # get all days from all accomodations
    $('#travelRequest_accomodations').find('.number-of-nights').each ->
        accomodationDays = parseInt($(@).val()) + parseInt(accomodationDays)
    $accomodationWrapper = $('#travelRequest_accomodations')

    # check if trip is longer than accomodations
    # and check if the diffDays is a positive value becasue to prevent that case
    # when the difference day between the departure and arrival dates is negative value
    # and there are no any filled up accommodations then this validation will not run.
    if diffDays > 0 and accomodationDays > diffDays
        if $accomodationWrapper.children('label.custom-error').length is 0
            $errorMessage = $('<label>')
                                .html('Total accomodation duration can not exceed travel request duration.')
                                .addClass('error custom-error')
                                .addClass('margin-bottom-10 display-table-important')
                                .css 'margin-left', '10px'
            $accomodationWrapper.prepend $errorMessage
        return false
    else
        $accomodationWrapper.children('label.custom-error').remove()
        return true
        
addFormDeleteButton = ->
    $deleteButton = $('<div>')
                        .html('<i class="fa fa-minus-square"></i>Delete')
                        .addClass('deleteFormFieldsetChild formFieldsetButton')
                        .addClass 'form-fieldset-delete-button'
    $deleteButton.click ->
        $(@).parent().remove()
    return $deleteButton
    
addFormAddButton = (parameters) ->
    parameters.forEach (parameter) ->
            $button = $('<div>')
                        .html("<i class='fa fa-plus-square'></i>#{ parameter.text }")
                        .addClass('addFormFieldsetChild formFieldsetButton')
                        .addClass 'form-fieldset-add-button'

            $collection = parameter.parent.append $button
            $collection.data 'index', $collection.find(':input').length
            $button.click (e) ->
                e.preventDefault()
                addForm $collection, $button, true
                return
                
formFieldsetAddClasses = (formFieldsetIds) ->
    formFieldsetIds.forEach (formFieldsetId) ->
        $parent = $("##{ formFieldsetId }").parent()
                    .addClass('formFieldset')
                    .addClass 'padding-bottom-5 margin-top-20 margin-bottom-20'

        formFieldsetAddHeader(formFieldsetId)
    
formFieldsetAddHeader = (formFieldsetId) ->
    $label = $("##{ formFieldsetId }").parent().children 'label'
    $label.replaceWith '<h3 class="background-color-orange color-white padding-top-2 padding-bottom-2 padding-left-1-em">' + $label.html() + '</h3>'
        
createFormFieldSetChild = ($self) ->
    $self.addClass 'formFieldsetChild'
    $self.addClass 'padding-10 margin-left-1-em margin-bottom-1-em display-inline-block vertical-align-top'
    $self.find('.cost').parent().append $self.find '.currency'
    $self.children().remove 'label'
    $self.append addFormDeleteButton()

    $numberOfNights = $self.find '.number-of-nights'
    if $numberOfNights.length > 0
        $numberOfNights.on 'change', ->
            compareDays()
    
    return $self

reCreateForm = (formFieldsetChildIds) ->
    formFieldsetChildIds.forEach (formFieldsetChildId) ->
        $("##{ formFieldsetChildId }").children().each ->
            createFormFieldSetChild $(@)

addForm = ($collectionHolder, $addButton, addListener) ->
    index = $collectionHolder.data 'index'
    newForm = $collectionHolder.data('prototype')
                .replace('<label class="required">__name__label__</label>', '')
                .replace /__name__/g, index
    $newForm = createFormFieldSetChild $(newForm)

    $newForm.find('.currency option[value=EUR]').attr 'selected','selected'
    $collectionHolder.data 'index', index + 1
    $addButton.before $newForm

$(document).ready ->
    $('.changeState').on 'change', ->
        $(document).data('notes').funcs.changeStateDialog $(@), $(document).data('notes').funcs.changeTravelRequestStatus, $(@).data('tr')

    if not Modernizr.inputtypes.date
        $arrivalDate = $('#travelRequest_arrival_date')
        $departureDate = $('#travelRequest_departure_date')
        $('#altDatetravelRequest_arrival_date').val $arrivalDate.val()
        $('#altDatetravelRequest_departure_date').val $departureDate.val()
        $arrivalDate.val $arrivalDate.val().replace(/(\d{4})-(\d{2})-(\d{2})/, "$2/$3/$1")
        $departureDate.val $departureDate.val().replace(/(\d{4})-(\d{2})-(\d{2})/, "$2/$3/$1")
        
    $('#travelRequest_user_ac').autocomplete
        source: Routing.generate 'OpitNotesTravelBundle_travel_userSearch', role: 'all'
        minLength: 2
        response: (event, ui) ->
            return
        select: (event, ui) ->
            $('#travelRequest_user').val ui.item.id
            return

    $('#travelRequest_team_manager_ac').autocomplete
        source: Routing.generate 'OpitNotesTravelBundle_travel_userSearch', role: 'team_manager'
        minLength: 2
        select: (event, ui) ->
            $('#travelRequest_team_manager').val ui.item.id
            return

    $('#travelRequest_general_manager_ac').autocomplete
        source: Routing.generate 'OpitNotesTravelBundle_travel_userSearch', role: 'general_manager'
        minLength: 2
        select: (event, ui) ->
            $('#travelRequest_general_manager').val ui.item.id
            return
            
    $('.travel-status-history').click (event) ->
        event.preventDefault()
        $(document).data('notes').funcs.showTravelStatusHistory $(@).data('id'), 'tr'
        
    #check customer related value
    travelCustomer = $('#travelRequest_customer_name')
    if travelCustomer.val() is ''
        travelCustomer.parent().css {visibility: 'hidden'}
    else
        $('#travelRequest_customer_related').val('0')

    $('#travelRequest_customer_related').change ->
        if $(@).val() is "0"
            travelCustomer.parent().css {visibility: 'visible'}
            travelCustomer.attr('required', 'required')
        else
            travelCustomer.parent().css {visibility: 'hidden'}
            travelCustomer.removeAttr 'required'
        
    #add team manager and general manager to formFieldset
    requiredApprovals = $('<div>')
                            .addClass('formFieldset')
                            .addClass('padding-bottom-5 margin-top-20 margin-bottom-20')
    requiredApprovals.append($('<h3>').addClass('background-color-orange color-white padding-top-2 padding-bottom-2 padding-left-1-em').html 'Required approvals')
    $div = $('<div>')
            .addClass('margin-left-1-em')
            .append $('#travelRequest_team_manager_ac').parent()
            .append $('#travelRequest_general_manager_ac').parent()
    $('#travelRequest_general_manager').after requiredApprovals.append($div)

    #set elements to be inlined
    $('#travelRequest_departure_date, #travelRequest_arrival_date,
       #travelRequest_customer_related, #travelRequest_customer_name,
       #travelRequest_team_manager_ac, #travelRequest_general_manager_ac').parent().addClass 'display-inline-block vertical-align-top margin-right-1-em'
        
    formFieldsetAddClasses(['travelRequest_destinations', 'travelRequest_accomodations'])
    reCreateForm(['travelRequest_destinations', 'travelRequest_accomodations'])
    addFormAddButton [{'text': 'Add destination', 'parent': $('#travelRequest_destinations')}, {'text': 'Add accomodations', 'parent': $('#travelRequest_accomodations')}]

    $generalData = $('<div>')
                    .addClass('formFieldset generalFormFieldset')
                    .addClass('padding-bottom-5 margin-top-20 margin-bottom-20')
                    .append($('#travelRequest_user_ac,
                          #travelRequest_departure_date,
                          #travelRequest_arrival_date,
                          #travelRequest_customer_related,
                          #travelRequest_customer_name,
                          #travelRequest_trip_purpose').parent())
    $('#travelRequest').prepend $generalData

    #add br tag to break inline
    $('#travelRequest_arrival_date').parent().after '<br />'
    
    $(document).data('notes').funcs.createButton 'Cancel', 'button display-inline-block', '', $('#travelRequest_add_travel_request').parent(), 'OpitNotesTravelBundle_travel_list'
    $(document).data('notes').funcs.makeElementToggleAble 'h3', $('.formFieldset')
    
    $('.disabled select, .disabled input').each ->
        $(@).attr 'disabled', 'disabled'
    $('.disabled button').each ->
        $(@).addClass('button-disabled').attr 'disabled', 'disabled'

    if $('#travelRequest_add_travel_request').attr 'disabled'
        $('.addFormFieldsetChild').each ->
            $(@).remove()
        $('.deleteFormFieldsetChild').each ->
            $(@).remove()


# method to validate form before preview
$form = $('#travelRequestForm')

# method to validate if arrival date is earlier than departure date
$.validator.addMethod 'compare', (value, element) ->
    departureDate = $('#travelRequest_departure_date').val()
    arrivalDate = $('#travelRequest_arrival_date').val()
    return departureDate <= arrivalDate
, 'Arrival date should not be earlier than departure date.'

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

$( '#travelRequest_add_travel_request' ).click (event) ->
    event.preventDefault()

#    validate form on client side
    if $form.valid() and compareDays()
        # if form is valid post ajax request to get the preview
        $.ajax
            method: 'POST'
            url: Routing.generate 'OpitNotesTravelBundle_travel_show_details'
            data: 'preview=1&' + $form.serialize()
        .done (data) ->
            $preview = $('<div id="dialog-travelrequest-preview"></div>').html data
            $preview.dialog
                title: '<i class="fa fa-list-alt"></i> Details'
                close: ->
                    $preview.dialog "destroy"
                width: 550
                maxHeight: $(window).outerHeight()-100
                modal: on
                buttons:
                    Save: ->
                        $form.submit()
                        $preview.dialog "destroy"
                        return
                    'Save & send for approval': ->
                        if isNaN(window.location.href.slice(-1))
                            $form.attr 'action', $form.attr('action') + '/new/fa'
                        else
                            $form.attr 'action', $form.attr('action') + '/fa'
                        $form.submit()
                        $preview.dialog "destroy"
                        return
                    Cancel: ->
                        $preview.dialog "destroy"
                        return
        .fail () ->
            $('<div></div>').html('The travel request could not be saved due to an error.').dialog
                title: 'Error'
    else
        $('.hasDatepicker').each ->
            if $(@).hasClass 'error'
                $(@).parent().find('.fa-calendar').addClass 'margin-top-12'
    return