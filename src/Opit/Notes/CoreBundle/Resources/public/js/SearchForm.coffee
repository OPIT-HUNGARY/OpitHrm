###*
 *  This file is part of the {Bundle}.
 * 
 *  (c) Opit Consulting Kft. <info@opit.hu>
 * 
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 *
 *  Search form class
 *
 *  @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 *  @version 1.0
 *  @package Opit
 *  @subpackage Notes
###

class SearchForm
    callbacks: []
    debug: off
    defaults:
        formSelector: '#searchFormWrapper'
        titleSelector: '#searchFormTitle'
        buttonSelector: '#searchButton'
        resetSelector: '#resetButton'
        resultSelector: '#list-table'
        callbacks: null
    
    ###*
     * Constructor
     *
     * @param string url The url for the run/reset events
     * @param object options An opject of options (may overwrite defaults)
    ###
    constructor: (@url = null, options = {}) ->
        # Extend/merge custom options
        @options = $.extend true, {}, @defaults, options
        @form = $(@options.formSelector).find 'form'
        @url = @form.attr 'action' if @url?
        
        # Add callbacks passed to constructor
        @addCallback @options.callbacks if @options.callbacks?
        
        console.log "[SearchForm] Search form initialized.", @options if @debug is on
        
    ###*
     * Runs a search with given parameters
    ###
    run: () ->
        $.ajax
            method: 'POST'
            url: @url
            data: @form.serialize()
        .done (response) =>
            $resultContainer = $(@options.resultSelector)
            
            $resultContainer.parent().html response

            # Find column and add related class for sort icon
            $field = $resultContainer.find('[data-field="' + @form.find('#order_field').val() + '"]')
            $field.addClass 'fa-sort-' + @form.find('#order_dir').val()
            
            # Trigger callbacks if set
            do @runCallbacks
            $(document).data('notes').funcs.searchCallback? @form
            
            # Log data if debug enabled
            console.info "[SearchForm] Search form submitted.", "(#{decodeURI(@form.serialize())})" if @debug is on
            return
        return
            
    ###*
     * Resets the form and updates the result container
    ###
    reset: () ->
        $.ajax
            method: 'POST'
            url: @url
            data: 'resetForm': true, 'showList' : 1
        .done (response) =>
            $('#list-table').parent().html response
            
            do @runCallbacks
            
            # Log data if debug enabled
            console.info '[SearchForm] Search form reset.' if @debug is on
            return
        return
        
    ###*
     * Toggles the search form container
     *
     * @param string element A jQuery selector
    ###
    toggle: (element) ->
        $header = $(element || @options.titleSelector)
        
        $header.toggleClass 'closed'
        $nextElement = $header.next()
        # Check if toogle animation is running to prevent a new animation
        if not $nextElement.is(':animated')
            $header.children('i').toggleClass 'fa-chevron-down'
            $nextElement.slideToggle()
        
        # Log data if debug enabled
        console.info "[SearchForm] Search form toggled.", "(#{if $header.hasClass('closed') then 'closed' else 'opened'})" if @debug is on
        
        return
    
    ###*
     * Executes the callback stack
    ###
    runCallbacks: () ->
        do callback for callback in @callbacks

        # Log data if debug enabled
        console.info "[SearchForm] Search form callbacks triggered.", "(count: #{@callbacks.length})" if @debug is on

        return
    
    ###*
     * Adds callback functions triggered by run/reset
     *
     * @param mixed callbacks A function or array of functions representation
    ###
    addCallback: (callbacks) ->
        # Map single function to array
        callbacks = [callbacks] if typeof callbacks isnt 'object'

        for callback in callbacks
            do (callback) =>
                if typeof callback is 'function'
                    @callbacks.push callback
                    console.info "[SearchForm] Callback added.", "(#{typeof callback})" if @debug is on
                else
                    # Log data if debug enabled
                    console.warn "[SearchForm] Callback type missmatch.", "(#{callback})" if @debug is on
    
    ###*
     * Removes a callback from the callback stack
     *
     * @param integer index the index to be removed from the callback stack
    ###
    removeCallback: (index) ->
        @callbacks.splice index, 1
        
        # Log data if debug enabled
        console.info "[SearchForm] Callback removed.", "(index: #{index})" if @debug is on
    
    ###*
     * Register search component events
    ###
    registerEvents: ->
        # Register search button event
        $(@options.buttonSelector).click (event) =>
            event.preventDefault()
            return if not @form.formIsEmpty()
        
            do @run
            return
        
        # Register reset button event
        $(@options.resetSelector).click (event) =>
            do @reset
            return
        
        # Register heading toggle event
        $(@options.titleSelector).click (event) =>
            @toggle event.currentTarget
            return
        
        # Log data if debug enabled
        console.info '[SearchForm] Search form events registered.' if @debug is on
        
        return

# Init the search form
search = new SearchForm

search.addCallback [
    $(document).data('notes').funcs.initDeleteMultipleListener,
    $(document).data('notes').funcs.initListPageListeners,
    $(document).data('notes').funcs.initPager
]
search.registerEvents()
