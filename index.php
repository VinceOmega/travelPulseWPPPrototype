<?php

//prototyped wp extension for displaying top stories on tp.com

error_reporting( E_ALL ); 
include_once('shd_1.8.1/simple_html_dom.php');
set_time_limit(360000);


//for debugging
function prettyPrint( $a ){

	echo "<pre><code>";
	var_dump( $a );
	echo "</code></pre>";

}


//Tell me how much memory this sucker uses
echo memory_get_usage() . "\n";


$url 							= "https://www.travelpulse.com/";
$dom 							= file_get_html($url);
$content 						= array(  );
$contentProps 					= array( 'title' => '', 'href' => '', "image" => '', "preview" => '' );
$idx 							= 0;					

try{

	foreach( $dom->find( "div.top_story" ) as $div ){

		//prettyPrint( $div->find( 'a' ) ); // links
		//prettyPrint( $div->find( 'img' ) ); //imgs
		
		foreach( $div->find( 'a[href]' ) as $storyPage ){

				if( isset( $storyPage->find( 'img', 0 )->title ) || isset( $storyPage->find( 'img', 0 )->src ) ){

					$content[ $idx ] 				= $contentProps;
					// isset( $storyPage->find( 'img', 0 )->title ) && strlen( $storyPage->find( 'img', 0 )->title )  ? var_dump( $storyPage->find( 'img', 0 )->title . "\n" ) : '' . "\n";
					$content[ $idx ][ 'title' ] 	= isset( $storyPage->find( 'img', 0 )->title ) && strlen( $storyPage->find( 'img', 0 )->title ) ? $storyPage->find( 'img', 0 )->title  : $content[ $idx ][ 'title' ];
					$content[ $idx ][ 'href' ] 		= $storyPage->href;
					$content[ $idx ][ 'image' ] 	= isset( $storyPage->find( 'img', 0 )->src ) && strlen( $storyPage->find( 'img', 0 )->src ) ? $storyPage->find( 'img', 0 )->src  : $content[ $idx ][ 'image' ];

					//prettyPrint( $storyPage->find( 'img', 0 )->src );

					$storyDom = file_get_html( $storyPage->href );

					//prettyPrint( $storyDom->find( "article > div.content.aqua_links > p", 0 )->innertext );
					//prettyPrint( file_get_html( $storyPages )->find( "article > div.content.aqua_links" ) );

					$content[ $idx ][ 'preview' ] 	= $storyDom->find( "article > div.content.aqua_links > p", 0 )->innertext;

					unset( $storyDom );

					$idx++;
				}

			}

	
	}

} catch( Expection $e){

	var_dump( $e );

}

unset($dom);

//prettyPrint( $content );



$html = produceOutput( $content );

echo $html ;

unset( $content );


function produceOutput( $content ){

	$output = is_array( $content ) ? produceOutput__Worker( $content ) : 'The parameter passed in is not an array, please pass in an array';

	return $output;

}



function produceOutput__Worker( $content ){

	$html = '';

	foreach( $content as $item ){
		$html .= <<<HMU
			<div class='news-container'>
				<div class="news-container__full">
					{$item['title']}
				<div>
				<div class="news-container__left">
					<img src="{$item[ 'image' ]}" title="{$item[ 'title' ]}" alt='{$item[ 'title' ]}'>
				</div>
				<div class="news-container__right">
					<p>{$item[ 'preview' ]}</p>
					<a href="{$item[ 'href' ]}" target='_blank'>[read more]</a>
				</div>
			</div>
			
HMU; //Thanks, I hate it. I wish there was a way to space the end of a heradoc. Well, there is in PHP 7 but gotta make this backwards comptable.
	}

	return $html;

}


/* Work on abstracting this further */

/*
function produceOutput__Worker( $content ){

	$html 				= '';
	$dom 				= new DOMDocument();

	foreach( $content as $item ){

		$news_container 				= $dom->createElement( 'div' ); $news_container ->setAttribute( 'class', 'news-container' );
		$news_container__full 			= $dom->createElement( 'div' ); $news_container__full->setAttribute( 'class', 'news-container__full' );
		$news_container__full->appendChild( new DomText( $item[ 'title' ] ) );

		$news_container__left 			= $dom->createElement( 'div' ); $news_container__left ->setAttribute( 'class', 'news_container__left' );

		$news_container__left__img  	= $dom->createElement( 'img' ); $news_container__left__img->setAttribute( 'src', $item[ 'image' ] );
		$news_container__left__img->setAttribute( 'title', $item[ 'title' ] );
		$news_container__left__img->setAttribute( 'alt', $item[ 'title' ] );

		$news_container__left->appendChild( $news_container__left__img );

		$news_container__right 			= $dom->createElement( 'div' ); $news_container__right ->setAttribute( 'class', 'news_container__right' );

		$news_container__right__p 		= $dom->createElement( 'div' ); $news_container__right__p->appendChild( new DomText( $item[ 'preview' ] ) );
		$news_container__right__a 		= $dom->createElement( 'a' ); 	$news_container__right__a->setAttribute( 'href', $item[ 'href' ] );
		$news_container__right__a->appendChild( new DomText( '[read more]' ) );
		$news_container__right__a->setAttribute( 'target', '_blank' );

		$news_container__right->appendChild( $news_container__right__p );
		$news_container__right->appendChild( $news_container__right__a );

		$news_container->appendChild( $news_container__full );
		$news_container->appendChild( $news_container__left );
		$news_container->appendChild( $news_container__right );
		$dom->appendChild( $news_container );
		

	}

	ob_start();
	echo $dom->saveHTML();
	$html = ob_get_contents();
	ob_end_clean();
	return $html;

}
*/
