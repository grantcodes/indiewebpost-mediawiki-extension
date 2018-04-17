<?php
/**
 * IndieWebPost
 * IndieWebPost Hooks
 *
 * @author: grant.codes
 * @license: MIT https://opensource.org/licenses/MIT
 * @package: IndieWebPost
 */

require_once __DIR__ . '/lib/mf2-parser.php';

class IndieWebPost {
  /**
   * Sets up this extensions parser functions.
   *
   * @access		public
   * @param		Parser	$parser
   * @return		boolean	true
   */
  static public function onParserFirstCallInit( Parser &$parser ) {
    $parser->setFunctionHook( "indiewebpost", [ __CLASS__, "indieWebPostMagicWord" ], Parser::SFH_OBJECT_ARGS );
    return true;
  }

  /**
   * Parses the <indiewebpost> tag.
   *
   * @access	public
   * @param	Parser	$parser
   * @param	PPFrame	$frame
   * @param	array	$args
   * @return	array	HTML
   */
  static public function indieWebPostMagicWord( Parser &$parser, PPFrame $frame, array $args ) {
    $params = self::extractOptions( $args, $frame );
    $parser->getOutput()->addModules( 'ext.indiewebpost' );
    $html = $params['html'];
    $output	=	"<div class='indiewebpost'>";
    if ($html) {
      $output .= '<div class="indiewebpost__tab">';
        $output .= '<h4 class="indiewebpost__tab__title">Rendered HTML</h4>';
        $output .= '<div class="indiewebpost__tab__code">' . $html . '</div>';
      $output .= '</div>';
    }
    // $mf2 = self::getMF2($output);
    // if (!$mf2) {
    //   return self::err('mf2-parsing');
    // }


      if ($html) {
        //  $pre = $hl->highlight('html', $html);
         $pre = '<pre class="brush: xml">' . htmlentities($html) . '</pre>';
        $output .= '<div class="indiewebpost__tab">';
          $output .= '<h4 class="indiewebpost__tab__title">HTML Markup</h4>';
          $output .= '<div class="indiewebpost__tab__code">' . $pre . '</div>';
        $output .= '</div>';
      }

      $mf2 = MF2\parse($html);

      if ($mf2 && $mf2['items'] && 1 === count($mf2['items'])) {
        // Needs the new lines to avoid mediawiki markup junk
        $pre = "\n\n" . '<pre class="brush: js">' . htmlentities( json_encode( $mf2['items'][0], JSON_PRETTY_PRINT ) ) . '</pre>' . "\n\n";
        $output .= '<div class="indiewebpost__tab">';
          $output .= '<h4 class="indiewebpost__tab__title">Parsed MF2 JSON</h4>';
          $output .= '<div class="indiewebpost__tab__code">' . $pre . '</div>';
        $output .= '</div>';
      }

    $output .= '</div>';
    // return [
    //   'text' => $output,
    //   'noparse' => true
    // ];
    return array(
      'text' => $output,
      'noparse' => true,
      'isHTML' => true,
    );
  }

  /**
   * Extracts a set of parameters
   *
   * @param	array	$options
   * @param	PPFrame	$frame
   * @return	array	Parameters
   */
  static function extractOptions( array $options, PPFrame $frame ) {
    $results = [];
    foreach ( $options as $option ) {
      $pair = explode( '=', $frame->expand( $option ), 2 );
      if ( count( $pair ) === 2 ) {
        $results[trim( $pair[0] )] = trim( $pair[1] );
      } else if ( count ( $pair ) === 1 ) {
        $results['text'] = trim( $pair[0] );
      }
    }
    return $results;
  }
}
