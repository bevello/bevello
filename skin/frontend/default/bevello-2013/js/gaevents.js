/**
 * Created by mcoffey on 7/1/15.

 Event tracking for google analytics
 */
jQuery(document).ready(function($) {
    //nav clicks
    $('#nav a').live('click', function() {
        var txt = $("span", this).text();
        ga('send', 'event', 'nav', 'click', text);
    });
    //hero slider clicks
    $('.featured-image a').live('click', function() {
        var txt = $(this).attr('eventlabel');
        ga('send', 'event', 'hero-slider', 'click', text);
    });
    //footer ads clicks
    $('.footerSlider a').live('click', function() {
        var txt = $(this).attr('eventlabel');
        ga('send', 'event', 'footer-ad', 'click', text);
    });
    $('#search_mini_form .button').live('click', function() {
        ga('send', 'event', 'search', 'click', 'search');
    });

    $('#search').on('keydown', function(e){
        if (e.which === 13) {

            //if enter key is pressed
            ga('send', 'event', 'header', 'click', 'search');

            $('#search_mini_form').submit();
            return false;

            //track the search terms (added due to search friendly urls on search, no query string)
            //var term = $('#search').val();
            //ga('send', 'pageview', '/catalogsearch/result/?q=' + term);
        }
    });

    $('.amountToFree').live('click', function() {
        ga('send', 'event', 'checkout', 'click', 'free-shipping-upsell');
    });


});