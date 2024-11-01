<?php
/*
Plugin Name: WP-GoogleTrends
Plugin URI: http://darkx-studios.com
Description: Extracts the trends from google.
Author: Neacsu Alexandru
Version: 1.1
Author URI: http://darkx-studios.com/

Place this in your sidebar.php:

<?php if(function_exists("getTrends")){
getTrends(100,"<li>","</li>",true); 
}?>  

*/

$AfterLink = "http://www.google.ro/search?q="; // The page that opens after the trend name is clicked.

function getTrends($nr,$inainte = "<li>",$dupa = "</li>",$IsLink = false){

$googleUrl = 'http://www.google.com/trends/hottrends/atom/hourly';

$xml=xml2ary(file_get_contents($googleUrl));
$string = strip_tags($xml['feed']['_c']['entry']['_c']['content']['_v'], '<span>');
$data = RefTag($string,$inainte,$dupa,$IsLink);

  for ($i=0;$i<$nr;$i++){
  echo $data[$i];
  }
  
}

function RefTag($str,$inainte,$dupa,$IsLink){
    $i = 0;
	$tipar = "%(<span.*?>)(.*?)(<\/span.*?>)%is";
	preg_match_all($tipar,$str,$matches);
	foreach ($matches[2] as $item) {
	  if($IsLink){
	  global $AfterLink;
	  $url = $AfterLink . urlencode($item);
	  $data[$i] =  '<a class="trl" rel="nofollow" href="' . $url . '">' . $inainte . $item  . '</a>' . $dupa .  "\n";
      } else {
	  $data[$i] =  $inainte . $item . $dupa . "\n";
	  }
	  $i++; 
    }
    return $data;
}


function xml2ary(&$string) {
    $parser = xml_parser_create();
    xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
    xml_parse_into_struct($parser, $string, $vals, $index);
    xml_parser_free($parser);

    $mnary=array();
    $ary=&$mnary;
    foreach ($vals as $r) {
        $t=$r['tag'];
        if ($r['type']=='open') {
            if (isset($ary[$t])) {
                if (isset($ary[$t][0])) $ary[$t][]=array(); else $ary[$t]=array($ary[$t], array());
                $cv=&$ary[$t][count($ary[$t])-1];
            } else $cv=&$ary[$t];
            if (isset($r['attributes'])) {foreach ($r['attributes'] as $k=>$v) $cv['_a'][$k]=$v;}
            $cv['_c']=array();
            $cv['_c']['_p']=&$ary;
            $ary=&$cv['_c'];

        } elseif ($r['type']=='complete') {
            if (isset($ary[$t])) { // same as open
                if (isset($ary[$t][0])) $ary[$t][]=array(); else $ary[$t]=array($ary[$t], array());
                $cv=&$ary[$t][count($ary[$t])-1];
            } else $cv=&$ary[$t];
            if (isset($r['attributes'])) {foreach ($r['attributes'] as $k=>$v) $cv['_a'][$k]=$v;}
            $cv['_v']=(isset($r['value']) ? $r['value'] : '');

        } elseif ($r['type']=='close') {
            $ary=&$ary['_p'];
        }
    }    
    
    _del_p($mnary);
    return $mnary;
}

// _Internal: Remove recursion in result array
function _del_p(&$ary) {
    foreach ($ary as $k=>$v) {
        if ($k==='_p') unset($ary[$k]);
        elseif (is_array($ary[$k])) _del_p($ary[$k]);
    }
}

  class myAtomParser
  {
    # keeps track of current and preceding elements
    var $tags = array();

    # array containing all feed data
    var $output = array();

    # return value for display functions
    var $retval = "";

    var $encoding = array();

    # constructor for new object
    function myAtomParser($file)
    {
      # instantiate xml-parser and assign event handlers
      $xml_parser = xml_parser_create("");
      xml_set_object($xml_parser, $this);
      xml_set_element_handler($xml_parser, "startElement", "endElement");
      xml_set_character_data_handler($xml_parser, "parseData");

      # open file for reading and send data to xml-parser
      $fp = @fopen($file, "r") or die("myAtomParser: Could not open $file for input");
      while($data = fread($fp, 4096)) {
        xml_parse($xml_parser, $data, feof($fp)) or die(
          sprintf("myAtomParser: Error <b>%s</b> at line <b>%d</b><br>",
          xml_error_string(xml_get_error_code($xml_parser)),
          xml_get_current_line_number($xml_parser))
        );
      }
      fclose($fp);

      # dismiss xml parser
      xml_parser_free($xml_parser);
    }

    function startElement($parser, $tagname, $attrs)
    {
      if($this->encoding) {
        # content is encoded - so keep elements intact
        $tmpdata = "<$tagname";
        if($attrs) foreach($attrs as $key => $val) $tmpdata .= " $key=\"$val\"";;
        $tmpdata .= ">";
        $this->parseData($parser, $tmpdata);
      } else {
        if($attrs['HREF'] && $attrs['REL'] && $attrs['REL'] == 'alternate') {
          $this->startElement($parser, 'LINK', array());
          $this->parseData($parser, $attrs['HREF']);
          $this->endElement($parser, 'LINK');
        }
        if($attrs['TYPE']) $this->encoding[$tagname] = $attrs['TYPE'];

        # check if this element can contain others - list may be edited
        if(preg_match("/^(FEED|ENTRY)$/", $tagname)) {
          if($this->tags) {
            $depth = count($this->tags);
            list($parent, $num) = each($tmp = end($this->tags));
            if($parent) $this->tags[$depth-1][$parent][$tagname]++;
          }
          array_push($this->tags, array($tagname => array()));
        } else {
          # add tag to tags array
          array_push($this->tags, $tagname);
        }
      }
    }

    function endElement($parser, $tagname)
    {
      # remove tag from tags array
      if($this->encoding) {
        if(isset($this->encoding[$tagname])) {
          unset($this->encoding[$tagname]);
          array_pop($this->tags);
        } else {
          if(!preg_match("/(BR|IMG)/", $tagname)) $this->parseData($parser, "</$tagname>");
        }
      } else {
        array_pop($this->tags);
      }
    }

    function parseData($parser, $data)
    {
      # return if data contains no text
      if(!trim($data)) return;
      $evalcode = "\$this->output";
      foreach($this->tags as $tag) {
        if(is_array($tag)) {
          list($tagname, $indexes) = each($tag);
          $evalcode .= "[\"$tagname\"]";
          if(${$tagname}) $evalcode .= "[" . (${$tagname} - 1) . "]";
          if($indexes) extract($indexes);
        } else {
          if(preg_match("/^([A-Z]+):([A-Z]+)$/", $tag, $matches)) {
            $evalcode .= "[\"$matches[1]\"][\"$matches[2]\"]";
          } else {
            $evalcode .= "[\"$tag\"]";
          }
        }
      }

      if(isset($this->encoding['CONTENT']) && $this->encoding['CONTENT'] == "text/plain") {
        $data = "<pre>$data</pre>";
      }

      eval("$evalcode .= '" . addslashes($data) . "';");
    }

    # display a single feed as HTML
    function display_feed($data, $limit)
    {
      extract($data);
      if($TITLE) {
        # display feed information
        $this->retval .= "<h1>";
        if($LINK) $this->retval .= "<a href=\"$LINK\" target=\"_blank\">";
        $this->retval .= stripslashes($TITLE);
        if($LINK) $this->retval .= "</a>";
        $this->retval .= "</h1>\n";
        if($TAGLINE) $this->retval .= "<P>" . stripslashes($TAGLINE) . "</P>\n\n";
        $this->retval .= "<div class=\"divider\"><!-- --></div>\n\n";
      }
      if($ENTRY) {
        # display feed entry(s)
        foreach($ENTRY as $item) {
          $this->display_entry($item, "FEED");
          if(is_int($limit) && --$limit <= 0) break;
        }
      }
    }

    # display a single entry as HTML
    function display_entry($data, $parent)
    {
      extract($data);
      if(!$TITLE) return;

      $this->retval .=  "<p><b>";
      if($LINK) $this->retval .=  "<a href=\"$LINK\" target=\"_blank\">";
      $this->retval .= stripslashes($TITLE);
      if($LINK) $this->retval .= "</a>";
      $this->retval .=  "</b>";
      if($ISSUED) $this->retval .= " <small>($ISSUED)</small>";
      $this->retval .=  "</p>\n";

      if($AUTHOR) {
        $this->retval .=  "<P><b>Author:</b> " . stripslashes($AUTHOR['NAME']) . "</P>\n\n";
      }
      if($CONTENT) {
        $this->retval .=  "<P>" . stripslashes($CONTENT) . "&lt;/P>\n\n";
      } elseif($SUMMARY) {
        $this->retval .=  "&lt;P>" . stripslashes($SUMMARY) . "&lt;/P>\n\n";
      }
    }

    function fixEncoding($input, $output_encoding)
    {
      $encoding = mb_detect_encoding($input);
      switch($encoding) {
        case 'ASCII':
        case $output_encoding:
          return $input;
        case '':
          return mb_convert_encoding($input, $output_encoding);
        default:
          return mb_convert_encoding($input, $output_encoding, $encoding);
      }
    }

    # display entire feed as HTML
    function getOutput($limit=false, $output_encoding='UTF-8')
    {
      $this->retval = "";;
      $start_tag = key($this->output);

      switch($start_tag) {
        case "FEED":
          foreach($this->output as $feed) $this->display_feed($feed, $limit);
          break;
        default:
          die("Error: unrecognized start tag '$start_tag' in getOutput()");
      }

      return $this->fixEncoding($this->retval, $output_encoding);
    }

    # return raw data as array
    function getRawOutput($output_encoding='UTF-8')
    {
      return $this->fixEncoding($this->output, $output_encoding);
    }
  }

?>