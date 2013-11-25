$(document)
  .ready ->
    $(".menu .mainMenu")
      .click ->
        $(".menu .mainMenu").removeClass("active");
        $(this).addClass("active");