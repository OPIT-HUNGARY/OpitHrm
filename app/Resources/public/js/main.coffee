$(document).data 'notes', {}
$.extend true, $(document).data('notes'),
    funcs:
        deleteAction: (title, message, url, identifier) ->
          if $(identifier).find(':checked').length > 0
            $('<div></div>').html('Are you sure you want to delete the '+message+'?').dialog
              title: title
              buttons:
                  Yes: ->
                      $.ajax
                        method: 'POST'
                        url: url
                        data: $(identifier).serialize()
                      .done (data) ->
                          $(identifier).find(':checked').parent().parent().parent().remove()
                          return
                      .fail () ->
                          $('<div></div>').html('The '+message+' could not be deleted due to an error.').dialog
                              title: 'Error'
                      $(@).dialog 'close'
                      return
                  No: ->
                      $(identifier + ' input:checkbox').attr 'checked', false
                      $(@).dialog 'close'
                      return
              close: ->
                  $(@).dialog 'destroy'
                  return

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