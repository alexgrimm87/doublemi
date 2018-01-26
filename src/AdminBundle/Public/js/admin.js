jQuery(document).ready(function() {
    jQuery('.add-prototype-row').click(function(e) {
        e.preventDefault();

        // grab the prototype template
        var newWidget = jQuery(this).parent().attr('data-prototype');
        // replace the "__name__" used in the id and name of the prototype
        // with a number that's unique to your emails
        // end name attribute looks like name="contact[emails][2]"

        // create a new list element and add it to the list
        jQuery(this).before(newWidget);
    });

    function imageErrorHandler(e) {
        $(this).attr('src','/placeholder.svg');
    }
    
    $('img').bind('error', imageErrorHandler);
})