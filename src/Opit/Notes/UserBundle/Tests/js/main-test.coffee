casper.test.begin 'Login page test', 5, suite = (test) ->
    casper.start "http://notes.local/app_dev.php/secured/login", ->

        documentDataFuncs = @.evaluate ->
            $(document).data('OpitNotesUserBundle').funcs.userEdit?
        test.assert documentDataFuncs, "User edit function exists"

        jQueryPlugins = @.evaluate ->
            $.fn.formIsEmpty? and
            $.fn.checkAll?
        test.assert jQueryPlugins, "jQuery plugins exist"

        test.assertTitle "NOTES", "Login page title is the one expected"
        test.assertExists 'form#login', "Login form is found"
        @.fill 'form#login', _username: "test0Name", true
        return

    casper.then ->
        test.assertSelectorHasText "div.error", "Bad credentials"
        return

    casper.run ->
        test.done()
        return
    return