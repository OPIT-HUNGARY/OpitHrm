$addInfoIcon = '<i class="fa fa-info-circle cursor-pointer margin-left-5"></i>'

$.extend true, $(document).data('notes'),
    funcs:
        helpToolTipDesc: (toolTipDescobj) ->
            $.each toolTipDescobj, (key, value) ->
                $labelId = $('#'+key)
                $labelId.append $addInfoIcon
                $helpTextDiv = $('<div>')
                                .addClass('helpToolTipDescription')
                                .addClass('background-section-color-grey short-description display-none position-absolute padding-5')
                                .html value
                $labelId.append $helpTextDiv
                return

        showHideToolTip: () ->
            #show/hide tool tip on mouse over
            $('.fa-info-circle').on 'mouseover', ->
                    $(@).parent().parent().find('.helpToolTipDescription').removeClass('display-none')
                $('.fa-info-circle').on 'mouseout', ->
                    $('.helpToolTipDescription').addClass 'display-none'
