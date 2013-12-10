$(document).data 'OpitNotesUserBundle', {}
$(document).data 'OpitNotesTravelBundle', {}
$subMenuClone = ''
subMenuCloneClass = '.subMenuClone'
$(document)
    .ready ->
        cloneSubmenu()
        # function to make header menu tabs selectable
        $('.menu .mainMenu')
            .click ->
                $('.menu .mainMenu').removeClass 'active'
                $(@).addClass "active"
                cloneSubmenu()
        # scroll method for sticky header
        $(window).scroll ->
            # if page scroll is below submenu top show submenu clone
            if $('.active').children('.subMenu').offset().top < $(window).scrollTop()
                if $('body').has(subMenuCloneClass).length
                    $subMenuClone.css({display: 'block'})
            # if page scroll is above submenu top hide submenu clone
            if $('.active').children('.subMenu').offset().top > $(window).scrollTop()
                if $('body').has(subMenuCloneClass).length
                    $subMenuClone.css({display: 'none'})

cloneSubmenu = ->
    # if header submenu exists in body delete it
    if $('body').children(subMenuCloneClass).length
        $('body').find(subMenuCloneClass).remove()
    # create clone of submenu
    $subMenuClone = $('.active').children('.subMenu').clone()
    $subMenuClone.addClass 'subMenuClone'
    $('body').append $subMenuClone
    
# Place any jQuery/helper plugins in here.

$.fn.extend
    formIsEmpty: (element) ->
        $el = if element then $(element) else $(@)
        exists = no
        $el.find(':input').each ->
            exists = yes if($(@).val())
        return exists
  
    checkAll: (selector) ->
        $el = if selector then $(selector) else $(@)
        checkAll = if $el.filter(':checked').length is $el.length then false else true
        $el.each ->
            $(@).prop 'checked', checkAll
        