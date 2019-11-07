$(function () { // DOM ready


    // Toggle open and close nav styles on click
    $('#nav-toggle').click(function () {
        $('nav ul').slideToggle();
    });
    // Hamburger to X toggle
    $('#nav-toggle').on('click', function () {
        this.classList.toggle('active');
    });
}); // end DOM ready

$(document).ready(function () {
    // Add minus icon for collapse element which is open by default
    $(".collapse.show").each(function () {
        $(this).prev(".card-header").find(".fa").addClass("fa-minus").removeClass("fa-plus");
    });

    // Toggle plus minus icon on show hide of collapse element
    $(".collapse").on('show.bs.collapse', function () {
        $(this).prev(".card-header").find(".fa").removeClass("fa-plus").addClass("fa-minus");
    }).on('hide.bs.collapse', function () {
        $(this).prev(".card-header").find(".fa").removeClass("fa-minus").addClass("fa-plus");
    });

    $("body").on('click', '.js-password-toggle', function () {

        const $btn = $(this);
        const $element = $($btn.attr("data-toggle"));

        $btn.toggleClass("fa-eye fa-eye-slash");

        if ($element.attr("type") === "password") {
            $element.attr("type", "text");
        }
        else {
            $element.attr("type", "password");
        }
    });

});

$("#howitworks").click(function () {
    $('html,body').animate({
            scrollTop: $(".how-it-works").offset().top
        },
        'slow');
});
$("#faq").click(function () {
    $('html,body').animate({
            scrollTop: $(".faq").offset().top
        },
        'slow');
});

