<?php

require 'Classes/WPCOM_Platform_Redirector.php';

class WPCOM_Platform_RedirectorTest extends PHPUnit_Framework_TestCase {
	
	public function setUp(){
		$this->matcher = new WPCOM_Platform_Redirector();
		$this->matcher
			->on( function( $ua ){ return $ua == 'Awesomesauce Browser 2.1'; }, array( 'href' => 'URL', 'label' => 'LABEL' ) )
			->on( function( $ua ){ return $ua == 'Awesomesauce Browser 2.0'; }, array( 'href' => 'URL2', 'label' => 'LABEL2') );
		
	}
	
	public function testCompareString(){
		
		$this->matcher
		->on( function( $ua ){
			return preg_match( "/(ipad|iphone|ipod|ios)/i", $ua );
		}, array( 'href' => 'itms://somewhere', 'label' => 'Open WordPress for iOS in the App Store' ) )
		->on( "/android/i", array( 'href' => 'android://something', 'label' => 'Open WordPress for Android in the Google Play store' ) );
		
		$match = $this->matcher->matching( "Mozilla/5.0 (iPad; U; CPU OS 4_3_3 like Mac OS X; en-us) AppleWebKit/533.17.9 (KHTML, like Gecko) Version/5.0.2 Mobile/8J2 Safari/6533.18.5" );
		extract( $match  );
		
		$this->assertSame( 'itms://somewhere', $href );
		$this->assertSame( 'Open WordPress for iOS in the App Store', $label );
		
		extract( $this->matcher->matching( "Mozilla/5.0 (Linux; U; Android 2.2; en-us; Nexus One Build/FRF91) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1" ) );
		
		$this->assertSame( 'android://something', $href );
		$this->assertSame( 'Open WordPress for Android in the Google Play store', $label );
		
	}
	
	public function testPrepend(){
		
		$this->matcher->prepend( "/[\d]/", array( 'href' => 'http://digits.com', 'label' => 'Digits' ) );
		extract( $this->matcher->matching( "Awesomesauce Browser 2.1" ) );
		$this->assertSame( 'http://digits.com', $href );
		$this->assertSame( 'Digits', $label );
		
	}
		
	public function testEach(){
		
		// look mom, it's chainable
		$urls = array();
		$this->matcher->each( function( $item ) use ( &$urls ){
			array_push( $urls, $item['memo']['href'] );
		});
		
		$this->assertSame( array( 'URL', 'URL2' ), $urls );
		
	}
	
	public function testMap(){		
		
		$this->assertSame( array( 'URL', 'URL2' ),
			$this->matcher->map( function( $item ){
				return $item['memo']['href'];
			} )
		);
	}
	
	public function testFind(){
		
		$this->assertSame( 'URL2',
			$this->matcher->find( function( $item ){
				return preg_match( "/[\d]+/", $item['memo']['href'] );
			} )['memo']['href']
		);
		
	}
	
	public function testFindAll(){
		
		$this->assertSame( 'URL2',
			$this->matcher->findAll( function( $item ){
				return preg_match( "/[\d]+/", $item['memo']['href'] );
			} )[0]['memo']['href']
		);
		
	}
	
	public function testReduce(){
		
		$this->assertSame( 7,
			$this->matcher->reduce( 0, function( $len, $item ){
				return $len + strlen( $item['memo']['href'] );
			} )
		);
		
	}
	
	public function testInvokableObject(){
		
		$ua = 'Awesomesauce Browser 2.1' ;
		
		$this->matcher->prepend( new EveryOtherMatcher, array( 'href' => 'http://maybe.com', 'label' => 'Maybe' ) );
		
		$first = $this->matcher->matching( $ua );
		$second = $this->matcher->matching( $ua );
		$third = $this->matcher->matching( $ua );
		
		$this->assertSame( 'URL', $first['href'] );
		$this->assertSame( 'http://maybe.com', $second['href'] );
		$this->assertSame( $first, $third );
	}
	
}

class EveryOtherMatcher {
	
	private $is_true = 1;
	
	public function __invoke( $item ){
		$this->is_true = $this->is_true * -1;
		return $this->is_true > 0;
	}
	
}