UA Matcher
=================

For certain platforms we want to have a way to direct users on those platforms to
the corresponding store that contains our native app. For instance, user goes to

  http://example.com/landing-page
   
And on that page is a button or link that tells the user to click on it to go to the
Apple App Store to download WordPress for iOS.

This class provides a clean way to pair the logic for determining which platform the
visitor is using with the URL and label we want to use for the link we will show them.


Usage
---------------
   
Instantiate and configure the instance:

    $matcher = new WPCOM_Platform_Matcher();
    
Provide a single regex to compare against the User Agent (or whatever string is given):
    
    $matcher->on( "/ios/i", 'http://www.somewhere.com' );

Provide a closure to use to examine the UA to do some fancy comparing:
    
    $matcher->on( function( $ua ){
      if ( strlen( $ua ) > 10 && preg_match( "/something/i", $ua ) ) {
        return true;
      }
      return false;
    }, 'http://somewhereelse.com' );

Provide an instance of any object that has an `__invoke` method:

    class TrueEveryOther {
      
      private $is_true = true;
      
      function __invoke(){
        $this->is_true = !$this->is_true;
        return $is_true;
      }
    }
    
    $matcher->prepend( new TrueEveryOther, "http://maybe.com" );


You can also chain the methods:
    
    $matcher->on( "/[\d]+/", "http://digitsareus.com" )
               ->on( "/wordpress/i", "http://wordpress.com" )
               ->on( "/(one|two|three)/i", "http://numbers.com" );
               

When you want to find a match for a User Agent then you:

    $match = $matcher->matching( 'Mozilla Whatever or something' );
    echo $match;
    
    



