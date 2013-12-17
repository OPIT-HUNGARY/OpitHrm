$(document).data 'notes', {}
$.extend true, $(document).data('notes'),
    funcs:
        deleteAction: (title, message, url, identifier) ->
          if $(identifier+':checked').length > 0
            $('<div></div>').html('Are you sure you want to delete the '+message+'?').dialog
              title: title
              buttons:
                  Yes: ->
                      $.ajax
                        method: 'POST'
                        url: url
                        data: $(identifier).serialize()
                      .done (data) ->
                          $(identifier+':checked').closest('tr').remove()
                          return
                      .fail () ->
                          $('<div></div>').html('The '+message+' could not be deleted due to an error.').dialog
                              title: 'Error'
                      $(@).dialog 'close'
                      return
                  No: ->
                      $(identifier + ':checkbox').attr 'checked', false
                      $(@).dialog 'close'
                      return
              close: ->
                  $(@).dialog 'destroy'
                  return
                  
        showAlert: (response, actionType, message, forceClass) ->
            $('#reply-message').addClass "alert-message"
            if response[0]? and response[0].response == 'error'
              if "update" == actionType or "create" == actionType
                errorString = "<ul>"
                for i in response[0].errorMessage
                  errorString += "<li>"+i+"</li>"
                errorString += "</ul>"
                $('#reply-message')
                  .html("<i class='fa fa-exclamation-triangle'></i> <strong>Error messages:</strong>"+errorString)
                  .removeClass('success-message')
                  .addClass('error-message')
              else if "delete" == actionType
                $('#list-reply-message')
                  .html("<i class='fa fa-exclamation-triangle'></i> Error, while tried to delete the user(s)! <i class='float-right fa fa-chevron-circle-up'></i> ")
                  .removeClass('success-message')
                  .addClass('error-message')
                  .fadeIn(200)
                  .delay(5000)
                  .slideUp(1000)
              returnVal = off
            else
              $('#list-reply-message')
                .html("<i class='fa fa-check-square'></i> "+message+"! <i class='float-right fa fa-chevron-circle-up'></i> ")
                .addClass("alert-message")
                .addClass('success-message')
                .fadeIn(200)
                .delay(2000)
                .slideUp(1000)
              returnVal = on
              
              if forceClass
                $('#list-reply-message').removeClass('success-message').addClass('error-message')

            return returnVal

###
 * jQuery datepicker extension
 * Datepicker extended by custom rendering possibility
 *
 * @author Sven Henneböle <henneboele@opit.hu>
 * @version 1.0
 * @depends jQuery
 *
 * @param object  options List of options
###
__picker = $.fn.datepicker

$.fn.datepicker = (options) ->
    __picker.apply this, [options]
    $self = @

    options = options or {}
    defaultOptions =
        wrapper: '<span class="relative"></span>'
        indicatorIcon: $('<i>')
    # Merge passed options
    $.extend true, defaultOptions, options
    
    if options.showOn isnt 'button'
        $self.attr
            type: 'text'
            readonly: 'readonly'
        .addClass 'icon-prefix-indent'
        defaultOptions.indicatorIcon.addClass 'fa fa-calendar absolute input-prefix-position pointer'
        defaultOptions.indicatorIcon.click ->
            $(@).parent().parent().children('input').focus()
        $self.before defaultOptions.wrapper
        $self.prev().append defaultOptions.indicatorIcon
    return $self


if not Modernizr.inputtypes.date
    $('input[type=date]').each ->
        name = $(@).attr 'name'
        id = $(@).attr('id')
        $(@).after '<input type="hidden" name="'+name+'" id="altDate'+id+'" />'
        $(@)
        $(@).datepicker {altField:'#altDate'+id, altFormat: 'yy-mm-dd'}