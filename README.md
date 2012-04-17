fooey
=================

In the end, it's pretty much just a user agent detector whose sole purpose is to figure out which store we should link a visitor to (iTunes, Google Play, Amazon)

Usage
-----------------

I would like it to be as easy as configuring it with how to handle different UA's:

    <?php
    
    require('lib.php');
    
    $detector = new Detector();
    
    $detector->on(function(){}, 'url', 'label');
    $detector->on(function(){}, 'url', 'label');


Then using it in a template somewhere:


