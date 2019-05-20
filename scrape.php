<?php

//The top 50 MV Model Dossier Script

error_reporting( E_ALL ); 
include_once('simple_html_dom.php');
set_time_limit(360000);

$url 							= "https://www.manyvids.com/MVGirls/";
$dom 							= file_get_html($url);
$cnt 							= 0;
$rootURL 						= "https://manyvids.com";
$mvStar 						= '';
$mvStarCollection 				= array( );
$mvStarProps 					= array( 'profile' => '', 'thumbnail' => '');
$mvStarVideosCollection			= array( );
$mvStarVideos 					= array( 'video' => '', 'title' => '', 'thumb' => '', 'length' => '', 'price' => '', 'favorites' => '', 'views' => '', 'ratings' => ''  );
$mvStarStoreCollection 			= array( );
$mvStarStore 					= array( 'name' => '', 'price' => '', 'details-link' => '', 'addons' => array( ) );
$mvStarStoreAddonsCollection 	= array( );
$mvStarStoreAddons 				= array( 'item' => '', 'price' => '' );
$mvStarGalleryCollection 		= array( );
$mvStarGallery 					= array( 'pic' => '', 'title' => '' );

//for debugging
function prettyPrint( $a ){

	echo "<pre><code>";
	var_dump( $a );
	echo "</code></pre>";

}

foreach( $dom->find( '#result-list div' ) as $div){

	//get mv star's name
	foreach( $div->find( 'h4.profile-pic-name' ) as $h4 ){
		if($cnt < 50){
			// print_r( $h4->firstChild()->innertext );
			$mvStar = str_replace( " ", "-",  trim( $h4->firstChild()->innertext ) );
			$mvStarCollection[ $mvStar ] = $mvStarProps;
		}
	}

	//get links for profile and thumbnails
	foreach( $div->find( 'a.square-size-8' ) as $href ){
		if($cnt < 50){
			// print_r( $href->attr[ 'href' ] );
			// print_r( $href->firstChild()->attr[ 'src' ] );
			$mvStarCollection[ "$mvStar" ][ "profile" ] 		= $href->attr[ 'href' ];
			$mvStarCollection[ "$mvStar" ][ "thumbnail" ] 		= $href->firstChild()->attr[ 'src' ];

		}
	}

	//check the loop position and break when at 50
	$cnt++;
	if($cnt >= 50){
		break;
	}

}

//free up memory
$dom->clear();

/*
echo "Top 50 MV Girls"."<br>";
echo "<pre>";
print_r($mvStarCollection);
echo "</pre>";
break;
*/

/*
// Test using the top MV model 

$mvModelPage 	= file_get_html( $rootURL.$mvStarCollection[ 'Lana-Rain' ][ 'profile' ] );
$vidList 		= $mvModelPage->find( '#vidlist #result-list div.js-video-card-flex' );
$store 			= $mvModelPage->find( '#store div.flex-list div.-square-img' );
$pics 			= $mvModelPage->find( '#gallery #photos #hero div.primary-content div.hero-masonry div.hero-item' );
*/

// echo "<pre>";
// print_r($pics);
// echo "</pre>";
// break;


try{
	foreach( $mvStarCollection as $mvStar ){

		$mvModelPage 	= file_get_html( $rootURL.$mvStar[ 'profile' ] );

		$vidList 		= $mvModelPage->find( '#vidlist #result-list div.js-video-card-flex' );
		$store 			= $mvModelPage->find( '#store div.flex-list div.-square-img' );
		$pics 			= $mvModelPage->find( '#gallery #photos #hero div.primary-content div.hero-masonry div.hero-item' );

		//Get Video Info
		foreach( $vidList as $list ){

			foreach( $list->find( 'a.js-video-preview' ) as $href ){

				$mvStarVideos[ 'video' ] 		= $rootURL.$href->attr[ 'href' ];
				$mvStarVideos[ 'title' ] 		= trim( $href->attr[ 'title' ] );
				$mvStarVideos[ 'thumb' ] 		= $href->firstChild()->attr[ 'src' ];
				$mvStarVideos[ 'length' ] 		= trim( $href->find( 'div.vid-length' )[0]->innertext );

			}

			$mvStarVideos[ 'price' ] 		=  ( count( $list->find( 'div.card-block div.card-subtitle span.m-r-1' ) >= 1 ) ) ? trim( $list->find( 'div.card-block div.card-subtitle span.m-r-1' )[0]->innertext ) : '';
			$mvStarVideos[ 'favorites' ] 	= ( count( $list->find( 'div.card-footer div.card-footer-links span.like a.like' ) >= 1 ) ) ? trim( $list->find( 'div.card-footer div.card-footer-links span.like a.like' )[0]->attr[ 'data-likes' ] ) : '';
			$mvStarVideos[ 'views' ] 		= ( count( $list->find( 'div.card-footer div.card-footer-links span i.views-icon' ) >= 1) ) ? trim( $list->find( 'div.card-footer div.card-footer-links span i.views-icon' )[0]->parent()->innertext ) : '';
			if(count( $list->find( 'div.card-footer div.card-footer-links span.review-tab i.star-icon' ) ) >= 1 ){
				$mvStarVideos[ 'ratings' ] 	= ( is_object( $list->find( 'div.card-footer div.card-footer-links span.review-tab i.star-icon' )[0] ) ) ? trim( $list->find( 'div.card-footer div.card-footer-links span.review-tab i.star-icon' )[0]->parent()->innertext ) : '';
			}
			$mvStarVideosCollection[ $mvStarVideos[ 'title' ] ] = $mvStarVideos;

		}

		//Get Store Info
		foreach( $store as $list ){

			$mvStarStore[ 'name' ] 			= trim( $list->find( 'a.inline-video-preview' )[0]->attr[ 'title' ] );
			$mvStarStore[ 'details-link' ] 	= $rootURL.trim( $list->find( 'a.inline-video-preview' )[0]->attr[ 'href' ] );
			$mvStarStore[ 'price' ] 		= trim( $list->find( 'div.card-block div.card-subtitle span.m-r-1' )[0]->innertext );

			$details 						= file_get_html( $mvStarStore[ 'details-link' ] );

			// prettyPrint( $details->find( 'form.mv-controls' )[0]->find( 'input.item_tags' ) );

			if( is_object( $details ) && count( $details->find( 'form.mv-controls' ) >= 1 ) ){
				foreach( $details->find( 'form.mv-controls' )[0]->find( 'label.checkbox' ) as $addons ){

					$mvStarStoreAddons[ 'item' ] 				= ltrim( rtrim( trim( explode( "$", $addons->innertext )[0] ) ) );
					$mvStarStoreAddons[ 'price' ] 				= trim( $addons->find( 'div.checkbox-price' )[0]->innertext );
					array_push( $mvStarStoreAddonsCollection, $mvStarStoreAddons );
					$mvStarStore[ 'addons' ] 					= $mvStarStoreAddonsCollection;

				}
			}

			$mvStarStoreCollection[ $mvStarStore[ 'name' ] ] 	= $mvStarStore;
			$mvStarStoreAddonsCollection = array( );

		}


		/* Don't bother, it gets lazy loaded in via ajax
			//Get Pic Info
			foreach( $pics as $list ){

				foreach( $list->find( 'a' ) as $href ){

					var_dump( $href );

					
					$mvStarGallery[ 'title' ] 	= trim( $href->find( 'p.example-title' )[0]->innertext );
					$mvStarGallery[ 'pic' ] 	= trim( $href->find( 'img' )[0]->attr[ 'src' ] );

					$mvStarGalleryCollection[ $mvStarGallery[ 'title' ] ] 	= $mvStarGallery;
					

				}

			}
		*/

	}
} catch( Expection $e){
	var_dump( $e );
}


prettyPrint( $mvStarVideosCollection );
prettyPrint( $mvStarStoreCollection );
// print_r($mvStarGalleryCollection);