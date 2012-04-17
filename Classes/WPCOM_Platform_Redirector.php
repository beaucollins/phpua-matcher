<?php


class WPCOM_Platform_Redirector implements Iterator {

	var $matchers = array();
	
	private static $instance;
	private $index = 0;
	
	static function getInstance(){
		if ( self::$instance === null ) {
			self::$instance = new WPCOM_Platform_Redirector();
		}
		return self::$instance;
	}
	
	public function on( $closure, $href, $label ){
		
		array_push( $this->matchers, array(
			'closure' => $closure,
			'href' => $href,
			'label' => $label
		) );
			
		return $this;
		
	}
	
	public function each( $closure ){
		foreach( $this->matchers as $item ){
			$closure->__invoke( $item );
		}
	}
	
	public function map( $closure ){
		$mapped = array();
		foreach( $this->matchers as $item ){
			array_push( $mapped, $closure->__invoke( $item ) );
		}
		return $mapped;
	}
	
	public function reduce( $aggregator, $closure ){
		foreach( $this->matchers as $item ){
			$aggregator = $closure->__invoke( $aggregator, $item );
		}
		return $aggregator;
	}
	
	public function find( $closure ){
		foreach( $this->matchers as $item ){
			$val = $closure->__invoke( $item );
			if ( $val ) return $item;
		}
	}
	
	public function findAll( $closure ){
		$matches = array();
		foreach( $this->matchers as $item ){
			$val = $closure->__invoke( $item );
			if ( $val ) array_push( $matches, $item );
		}
		return $matches;
	}
	
	// Iterator methods
	
	/**
	 * @return mixed
	 */
	public function current(){
		return $this->matchers[$this->index];
	}
	
	/**
	 * @return scalar
	 */
	public function key(){
		return $this->index;
	}
	
	/**
	 * @return void
	 */
	public function next(){
		++ $this->index;
	}
	
	/**
	 * @return void
	 */
	public function rewind(){
		$this->index = 0;
	}
	
	/**
	 * @return boolean
	 */
	public function valid(){
		return isset($this->matchers[$this->position]);
	}

}
