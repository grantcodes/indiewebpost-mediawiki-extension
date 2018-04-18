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
    // Loads the js and css
    $parser->getOutput()->addModules( 'ext.indiewebpost' );
    // Assign variables
    $params = self::extractOptions( $args, $frame );

    if (!isset($params['html'])) {
      return self::err('nohtml');
    }

    $hiddenTabs = array();
    if (isset($params['hide-tabs'])) {
      $hiddenTabs = explode(',', $params['hide-tabs']);
    }

    $output	=	"<div class='indiewebpost'>";

    if (!in_array('html', $hiddenTabs)) {
      $htmlPre = self::getCodeBlock($params['html'], 'html');
      $output .= self::getTab('HTML+mf2', $htmlPre);
    }

    $mf2 = MF2\parse($params['html']);
    if ($mf2 && $mf2['items'] && 1 === count($mf2['items'])) {
      if (!in_array('mf2', $hiddenTabs)) {
        $json = json_encode($mf2['items'][0], JSON_PRETTY_PRINT);
        $mf2Pre = self::getCodeBlock($json, 'json');
        $output .= self::getTab('mf2 JSON', $mf2Pre);
      }
    } else {
      return self::err('mf2parser');
    }

    if (!in_array('micropub', $hiddenTabs)) {
      $micropub = $mf2['items'][0];
      if (isset($params['micropub'])) {
        $micropub = json_decode($params['micropub']);
        if (!$micropub) {
          return self::err('decodemicropub');
        }
        $micropub = json_encode($micropub, JSON_PRETTY_PRINT);
      } else {
        // Simplify the content property by default
        if (isset($micropub['properties']['content'])) {
          foreach($micropub['properties']['content'] as $i => $content) {
            if (is_array($content) && isset($content['value'])) {
              $micropub['properties']['content'][$i] = $content['value'];
            }
          }
        }
        // Remove name property if it is the same as the content
        if (isset($micropub['properties']['content'][0]) && isset($micropub['properties']['name'][0]) && $micropub['properties']['content'][0] == $micropub['properties']['name'][0]) {
          unset($micropub['properties']['name']);
        }
        $micropub = json_encode($micropub, JSON_PRETTY_PRINT);
      }
      $micropubPre = self::getCodeBlock($micropub, 'json');
      $output .= self::getTab('Micropub JSON', $micropubPre);
    }

    if (isset($params['screenshot'])) {
      $screenshot = '<img src="' . $params['screenshot'] .'" alt="Screenshot of a indieweb post" />';
      if (isset($params['screenshot-title'])) {
        $screenshot = $screenshot . '<h5>' . $params['screenshot-title'] . '</h5>';
      }
      $output .= self::getTab('Screenshot', $screenshot);
    }

    if (!in_array('rendered', $hiddenTabs)) {
      $output .= self::getTab('Rendered', $params['html'] . '<p><small>Note: This is displayed using the default wiki css</small></p>');
    }

    $output .= '</div>';

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

  static function err(string $errorString = 'default') {
    return '<div class="errorbox">' . wfMessage('indiewebpost_error_' . $errorString)->text() . '</div>';
  }

  static function getTab(string $title, string $content) {
    $tab = '<div class="indiewebpost__tab">';
    $tab .= '<h4 class="indiewebpost__tab__title">' . $title . '</h4>';
    $tab .= '<div class="indiewebpost__tab__code">' . $content . '</div>';
    $tab .= '</div>';
    return $tab;
  }

  static function getCodeBlock(string $code, string $type) {
    if ($type == 'html') {
      return '<pre class="brush: xml">' . htmlentities($code) . '</pre>';
    }
    if ($type == 'json') {
      // Needs the new lines to avoid mediawiki markup junk
      return "\n\n" . '<pre class="brush: js">' . htmlentities($code) . '</pre>' . "\n\n";
    }
    return '<pre class="">' . htmlentities($code) . '</pre>';
  }
}
