<?php

require 'Classes/WPCOM_Platform_Redirector.php';

class WPCOM_Platform_RedirectorTest extends PHPUnit_Framework_TestCase {
	
	public function setUp(){
		$this->instance = WPCOM_Platform_Redirector::getInstance();
	}
	
	public function testInstanceIsSame(){
		
		$instance2 = WPCOM_Platform_Redirector::getInstance();
		
		$this->assertTrue( $this->instance === $instance2 );
		
	}
	
	public function testEnumerableMethods(){
		
		// look mom, it's chainable
		$this->instance
			->on( function( $ua ){
				return $ua == 'Awesomesauce Browser 2.1';
			}, 'URL', 'LABEL' )
			->on( function( $ua ){
				return $ua == 'Awesomesauce Browser 2.0';
			}, 'URL2', 'LABEL2');
		
		$this->counter = 0;
		$this->instance->each( function( $item ){
			++ $this->counter;
		});
		$this->assertSame( 2, $this->counter );
		
		$this->assertSame( array( 'URL', 'URL2' ),
			$this->instance->map( function( $item ){
				return $item['href'];
			} )
		);

		$this->assertSame( 'URL2',
			$this->instance->find( function( $item ){
				return preg_match( "/[\d]+/", $item['href'] );
			} )['href']
		);
		
		$this->assertSame( 'URL2',
			$this->instance->findAll( function( $item ){
				return preg_match( "/[\d]+/", $item['href'] );
			} )[0]['href']
		);
		
		$this->assertSame( 7,
			$this->instance->reduce( 0, function( $len, $item ){
				return $len + strlen( $item['href'] );
			} )
		);
		
		
	}
	
}