<?php
/**
 * This class builds an array of Closures and their corresponding URL's
 * and labels and then provides a method to match a User Agent string by
 * invoking each closure in the order that they are configured.
 *
 * Usage:
 *    
 *   // instantiate and configure the instance
 *	 $redirector = new WPCOM_Platform_Redirector();
 *
 *   // provide a single regex to compare against the ua
 *   $redirector->on( "/ios/i", 'http://www.somewhere.com' );
 * 
 *   // provide a closure to use to examine the ua to do some fancy comparing
 *   $redirector->on( function( $ua ){
 *     if ( strlen( $ua ) > 10 && preg_match( "/something/i", $ua ) ) {
 *       return true;
 *     }
 *     return false;
 *   }, 'http://somewhereelse.com' );
 *
 *   // and it's chainable too
 *   $redirector->on( "/[\d]+/", "http://digitsareus.com" )
 *              ->on( "/wordpress/i", "http://wordpress.com" )
 *              ->on( "/(one|two|three)/i", "http://numbers.com" );
 */
class WPCOM_Platform_Redirector {

	var $matchers = array();
	
	private static $instance;
	
	/**
	 * @return WPCOM_Platform_Redirector instance
	*/
	static function getInstance(){
		if ( self::$instance === null ) {
			self::$instance = new WPCOM_Platform_Redirector();
		}
		return self::$instance;
	}
		
	/**
	 * Finds the first matching item for the given UA string
	 *
	 * @param  string $ua
	 * @return mixed
	*/
	public function matching( $ua ){
		$match = $this->find( function( $matcher ) use ( $ua ) {
			return $matcher['closure']( $ua );
		} );		
		return $match ? $match['memo'] : null;
	}
	
	/**
	 * If $closure is a string it wraps it into a Closure that uses preg_match
	 *
	 * @param  mixed  $closure
	 * @return Closure 
	*/
	private function prepareClosure( $closure ){
		
		if ( is_string( $closure ) ) {
			$compare = $closure;
			$closure = function( $ua ) use ( $compare ){
				return preg_match( $compare, $ua );
			};
		}
		
		return $closure;
	}
	
	/**
	 * Adds a regex/closure/invokable object and matching $href and $label
	 * to the end of the list
	 *
	 * @param  mixed  $closure
	 * @param  string   $href
	 * @param  string   $label
	 * @return WPCOM_Platform_Redirector 
	*/
	public function on( $closure, $memo ){
		
		$matcher = $this->prepareClosure( $closure );
		
		array_push( $this->matchers, array(
			'closure' => $matcher,
			'memo' => $memo
		) );
			
		return $this;
		
	}
	
	/**
	 * Adds a closure to test the UA against as the beginning of the list
	 *
	 * @param  mixed  $closure
	 * @param  string   $href
	 * @param  string   $label
	 * @return WPCOM_Platform_Redirector 
	*/
	public function prepend( $closure, $memo ){
		
		$matcher = $this->prepareClosure( $closure );
		
		array_unshift( $this->matchers, array(
			'closure' => $matcher,
			'memo' => $memo
		) );
			
		return $this;
		
	}
	
	/**
	 * Invokes the $closure witch each item in the matcher array
	 *
	 * @param  Closure  $closure
	 * @return void 
	*/
	public function each( $closure ){
		foreach( $this->matchers as $item ){
			$closure( $item );
		}
	}
	
	/**
	 * Invokes the $closure witch each item in the matcher array
	 * and returns an array that conists of the return values
	 * from $closure
	 *
	 * @param  Closure  $closure
	 * @return Array 
	*/
	public function map( $closure ){
		$mapped = array();
		$this->each( function( $item ) use ( &$mapped, $closure ) {
			array_push( $mapped, $closure( $item ) );
		} );
		return $mapped;
	}
	
	/**
	 * Invokes the $closure with the aggregator and each item in
	 * the matcher array and returns the final aggregated value
	 *
	 * @param  mixed    $aggregator
	 * @param  Closure  $closure
	 * @return mixed 
	*/
	public function reduce( $aggregator, $closure ){
		$this->each( function( $item ) use ( &$aggregator, $closure ) {
			$aggregator = $closure( $aggregator, $item );
		} );
		return $aggregator;
	}
	
	/**
	 * Invokes the $closure with each item in the matcher array and 
	 * and returns the first item when $closure returns a true value
	 *
	 * @param  Closure  $closure
	 * @return mixed 
	*/
	public function find( $closure ){
		foreach( $this->matchers as $item ){
			$val = $closure( $item );
			if ( $val ) return $item;
		}
	}
	
	/**
	 * Invokes the $closure with each item in the matcher array and 
	 * and returns an array of items where the $closure returned a
	 * true value.
	 *
	 * @param  Closure  $closure
	 * @return Array 
	*/
	public function findAll( $closure ){
		return $this->reduce( array(), function( $matches, $item ) use ( $closure ) {
			$val = $closure( $item );
			if ( $val ) array_push( $matches, $item );
			return $matches;
		} );
	}
		
}
