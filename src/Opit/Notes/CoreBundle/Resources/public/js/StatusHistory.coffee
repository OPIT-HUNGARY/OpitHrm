###
 *  This file is part of the {Bundle}.
 * 
 *  (c) Opit Consulting Kft. <info@opit.hu>
 * 
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 *
 *  StatusHistory renderer class
 *
 *  @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 *  @version 1.0
 *  @package Opit
 *  @subpackage Notes
###
class StatusHistory
    defaults:
        dialogSelector: '#main-wrapper'
        dialogTitle: 'Status History'
        dialogWidth: 650
        mode: null
        debug: off
    
    # Constructor
    constructor: (@url, @options) ->
        throw new Error("No url given.") if not @url?
        
    # Handles dialog creation and status history rendering
    init: ->
        _self = @
        # Extend/merge custom options
        @options = $.extend true, {}, _self.defaults, _self.options
        
        console.log @options if @options.debug is true
        
        # Register the click event listener
        $(_self.options.dialogSelector).on 'click', '.status-history', (event) ->
            event.preventDefault()
            
            # Set required url parameters
            params = 'id': $(@).find('.fa-book').data 'id'
            $.extend params, mode: mode if mode = _self.options.mode
            
            console.log _self.url, params if _self.options.debug is true
            
            # Call the status history action and open a dialog
            $.ajax
                method: 'POST'
                url: Routing.generate _self.url, params
            .done (data) ->
                $('<div id="dialog-status-history"></div>').html(data)
                    .dialog
                        title: '<i class="fa fa-book"></i> ' + _self.options.dialogTitle
                        width: _self.options.dialogWidth
                        maxHeight: $(window).outerHeight() - 100
                        modal: on
                        buttons:
                            Close: ->
                                $('#dialog-status-history').dialog 'destroy'
                                return
                return
            return

window.StatusHistory = StatusHistory