<?php
class ApplicationHelper {
  private $application;

  function __construct($application) {
    $this->application = & $application;
    $this->base_url = (strpos(strtolower($_SERVER['SERVER_PROTOCOL']),'https') === FALSE ? 'http' : 'https').'://'.$_SERVER['HTTP_HOST'].'/'.(substr(dirname($_SERVER['SCRIPT_NAME']),1) ? substr(dirname($_SERVER['SCRIPT_NAME']),1) . '/' : '');
  }

  function url_safe($string) {
    return strtolower(str_replace(' ', '-', $string));
  }

  function link_to() {
  }

  function url_for($args = null) {
    $options = array('action', 'controller', 'only_path', 'protocol', 'anchor');

    $protocol = stristr($_SERVER['SERVER_PROTOCOL'], 'https') !== false ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'];
    $path = dirname($_SERVER['SCRIPT_NAME']).'/';

    $params = array();

    if(is_array($args)) {
      // Action is required, at the very least
      if(!array_key_exists('action', $args)) {
        throw new InvalidArgumentException('Expecting an action parameter');
      }

      $action = $args['action'];
      $params += array('action' => $action);
      unset($args['action']);

      // Process options
      if(array_key_exists('controller', $args)) {
        $controller = $args['controller'];
        unset($args['controller']);
      } else {
        # TODO: Need to get current controller
        $controller = null;
      }
      $params += array('controller' => $controller);

      // Add rest of args as parameters to url
      if(count($args) > 0) {
        $params += $args;
      }
    }

    if(is_string($args)) {
      // Process string options
    }

    if(is_object($args)) {
      // Process object to CRUD conventions
    }

    return $protocol.$host.$path.'?'.http_build_query($params);
  }

  function seo_url($string) {
    //cyrylic transcription
    $cyrylicFrom = array('А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ы', 'Ь', 'Э', 'Ю', 'Я', 'а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я');
    $cyrylicTo = array('A', 'B', 'W', 'G', 'D', 'Ie', 'Io', 'Z', 'Z', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'R', 'S', 'T', 'U', 'F', 'Ch', 'C', 'Tch', 'Sh', 'Shtch', '', 'Y', '', 'E', 'Iu', 'Ia', 'a', 'b', 'w', 'g', 'd', 'ie', 'io', 'z', 'z', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'ch', 'c', 'tch', 'sh', 'shtch', '', 'y', '', 'e', 'iu', 'ia');

    $from = array("Á", "À", "Â", "Ä", "Ă", "Ā", "Ã", "Å", "Ą", "Æ", "Ć", "Ċ", "Ĉ", "Č", "Ç", "Ď", "Đ", "Ð", "É", "È", "Ė", "Ê", "Ë", "Ě", "Ē", "Ę", "Ə", "Ġ", "Ĝ", "Ğ", "Ģ", "á", "à", "â", "ä", "ă", "ā", "ã", "å", "ą", "æ", "ć", "ċ", "ĉ", "č", "ç", "ď", "đ", "ð", "é", "è", "ė", "ê", "ë", "ě", "ē", "ę", "ə", "ġ", "ĝ", "ğ", "ģ", "Ĥ", "Ħ", "I", "Í", "Ì", "İ", "Î", "Ï", "Ī", "Į", "Ĳ", "Ĵ", "Ķ", "Ļ", "Ł", "Ń", "Ň", "Ñ", "Ņ", "Ó", "Ò", "Ô", "Ö", "Õ", "Ő", "Ø", "Ơ", "Œ", "ĥ", "ħ", "ı", "í", "ì", "i", "î", "ï", "ī", "į", "ĳ", "ĵ", "ķ", "ļ", "ł", "ń", "ň", "ñ", "ņ", "ó", "ò", "ô", "ö", "õ", "ő", "ø", "ơ", "œ", "Ŕ", "Ř", "Ś", "Ŝ", "Š", "Ş", "Ť", "Ţ", "Þ", "Ú", "Ù", "Û", "Ü", "Ŭ", "Ū", "Ů", "Ų", "Ű", "Ư", "Ŵ", "Ý", "Ŷ", "Ÿ", "Ź", "Ż", "Ž", "ŕ", "ř", "ś", "ŝ", "š", "ş", "ß", "ť", "ţ", "þ", "ú", "ù", "û", "ü", "ŭ", "ū", "ů", "ų", "ű", "ư", "ŵ", "ý", "ŷ", "ÿ", "ź", "ż", "ž");
    $to = array("A", "A", "A", "A", "A", "A", "A", "A", "A", "AE", "C", "C", "C", "C", "C", "D", "D", "D", "E", "E", "E", "E", "E", "E", "E", "E", "G", "G", "G", "G", "G", "a", "a", "a", "a", "a", "a", "a", "a", "a", "ae", "c", "c", "c", "c", "c", "d", "d", "d", "e", "e", "e", "e", "e", "e", "e", "e", "g", "g", "g", "g", "g", "H", "H", "I", "I", "I", "I", "I", "I", "I", "I", "IJ", "J", "K", "L", "L", "N", "N", "N", "N", "O", "O", "O", "O", "O", "O", "O", "O", "CE", "h", "h", "i", "i", "i", "i", "i", "i", "i", "i", "ij", "j", "k", "l", "l", "n", "n", "n", "n", "o", "o", "o", "o", "o", "o", "o", "o", "o", "R", "R", "S", "S", "S", "S", "T", "T", "T", "U", "U", "U", "U", "U", "U", "U", "U", "U", "U", "W", "Y", "Y", "Y", "Z", "Z", "Z", "r", "r", "s", "s", "s", "s", "B", "t", "t", "b", "u", "u", "u", "u", "u", "u", "u", "u", "u", "u", "w", "y", "y", "y", "z", "z", "z");

    $extraFrom = array(" ", "'", "!");
    $extraTo = array("_", "", "");

    $from = array_merge($from, $cyrylicFrom, $extraFrom);
    $to = array_merge($to, $cyrylicTo, $extraTo);

    return str_replace($from, $to, $string);
  }

  function image_tag($src, $options = array()) {
    $src = (string)$src;

    if($src == '') {
      return false;
    }

    // Manipulate src if it isn't an external link
    if(strpos($src, "http://") !== 0) {
      if(strpos($src, "/") === 0) {
        // src is absolute
        $src = $this->base_url.$src;
      } else {
        // src is relative
        $src = "{$this->base_url}images/{$src}";
      }
    }

    $class = array_key_exists('class', $options) ? ' class="'.(string)$options['class'].'"' : '';
    $alt = array_key_exists('alt', $options) ? ' alt="'.(string)$options['alt'].'"' : '';
    $height = array_key_exists('height', $options) ? ' height="'.(string)$options['height'].'"' : '';
    $width = array_key_exists('width', $options) ? ' width="'.(string)$options['width'].'"' : '';

    return <<<EOF
      <img src="{$src}"{$class}{$alt}{$height}{$width}/>
EOF;
  }
}