/**
 * Created by mcoffey on 7/1/15.

 Event tracking for google analytics
 */
jQuery(document).ready(function($) {
    //nav clicks
    $('#nav a').live('click', function() {
        var txt = $("span", this).text();
        ga('send', 'event', 'nav', 'click', txt);
    });
    //hero slider clicks
    $('.featured-image a').live('click', function() {
        var txt = $(this).attr('eventlabel');
        ga('send', 'event', 'hero-slider', 'click', txt);
    });
    //footer ads clicks
    $('.footerSlider a').live('click', function() {
        var txt = $(this).attr('eventlabel');
        ga('send', 'event', 'footer-ad', 'click', txt);
    });
    $('#searchButton').live('click', function() {
        ga('send', 'event', 'header', 'click', 'search');
    });

    $('#search').on('keydown', function(e){
        if (e.which === 13) {

            //if enter key is pressed
            ga('send', 'event', 'header', 'click', 'search');

            $('#search_mini_form').submit();
            return false;

        }
    });

    $('.amountToFree').live('click', function() {
        ga('send', 'event', 'checkout', 'click', 'free-shipping-upsell');
    });


});
