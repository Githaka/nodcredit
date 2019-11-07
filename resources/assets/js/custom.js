window.addEventListener('DOMContentLoaded', function () {

    $('body').on('click', 'a[data-toggle="tab"]', function () {
        location.hash = this.getAttribute("href");
    });

    if (window.location.hash) {
        let $tab = $('a[data-toggle="tab"][href="' + window.location.hash + '"]');
        if ($tab.length) {
            $tab.tab('show');
        }
    }

});