<?php
/**
 * String helpers
 *
 * @package     Joomla
 * @subpackage  Fabrik.helpers
 * @copyright   Copyright (C) 2005-2013 fabrikar.com - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

/**
 * String helpers
 *
 * @package     Joomla
 * @subpackage  Fabrik.helpers
 * @since       3.0
 */

class FabrikString extends JString
{
	/**
	 * UTF-8 aware - replace the first word
	 *
	 * @param   string  $str         The string to be trimmed
	 * @param   string  $word        The word to trim
	 * @param   string  $whitespace  Ignore but preserve leading whitespace
	 *
	 * @return  string  the trimmed string
	 */

	public static function ltrimword($str, $word = false, $whitespace = false)
	{
		if ($word === false)
		{
			return $str;
		}

		if ($whitespace)
		{
			$word = preg_quote($word, '#');
			$str = preg_replace("#^(\s*)($word)(.*)#i", "$1$3", $str);
		}
		else
		{
			$pos = JString::strpos($str, $word);

			if ($pos === 0)
			{
				$str = JString::substr($str, JString::strlen($word));
			}
		}

		return $str;
	}

	/**
	 * Right trim a word from a string
	 *
	 * @param   string  &$str  The string to be trimmed
	 * @param   string  $word  The word to trim
	 *
	 * @return  string  the trimmed string
	 */

	public static function rtrimword(&$str, $word = false)
	{
		$l = JString::strlen($word);
		$end = JString::substr($str, -$l);

		if ($end === $word)
		{
			return JString::substr($str, 0, JString::strlen($str) - $l);
		}
		else
		{
			return $str;
		}
	}

	/**
	 * UTF-8 aware - remove the first word
	 * CASE INSENSITIVE
	 *
	 * @param   string  $str   The string to be trimmed
	 * @param   string  $word  The word to trim
	 *
	 * @return  string  the trimmed string
	 */

	public static function ltrimiword($str, $word = false)
	{
		$pos = stripos($str, $word);

		if ($pos === 0)
		{
			$str = JString::substr($str, JString::strlen($word));
		}

		return $str;
	}

	/**
	 * Formats a string to return a safe db col name e.g.
	 * table.field is returned as `table`.`field`
	 * table is return as `table`
	 *
	 * @param   string  $col  Col name to format
	 *
	 * @return string in `table`.field` format
	 */

	public static function safeColName($col)
	{
		$db = FabrikWorker::getDbo();
		$col = str_replace('`', '', $col);
		$splitter = '';

		if (strstr($col, '___'))
		{
			$splitter = '___';
		}

		if (strstr($col, '.'))
		{
			$splitter = '.';
		}

		if ($splitter == '')
		{
			return $db->quoteName($col);
		}

		if (strstr($col, $splitter))
		{
			$col = explode($splitter, $col);

			foreach ($col as &$c)
			{
				$c = $db->quoteName($c);
			}

			return implode('.', $col);
		}

		return $col;
	}

	/**
	 * Inverse of safeColName takes `table`.`field`
	 * and returns table___field
	 *
	 * @param   string  $col  In `table`.`field` format
	 *
	 * @return  string  in table___field format
	 */

	public static function safeColNameToArrayKey($col)
	{
		$col = str_replace(array("`.`", "."), '___', $col);
		$col = str_replace("`", "", $col);

		return $col;
	}

	/**
	 * Takes tablename.element or tablename___elementname
	 * (with or without quotes) and returns elementname
	 *
	 * @param   string  $col  Column name to shorten
	 *
	 * @return  string  element name
	 */

	public static function shortColName($col)
	{
		if (strstr($col, '.'))
		{
			$bits = explode('.', $col);
			$col = array_pop($bits);
		}
		elseif (strstr($col, '___'))
		{
			$bits = explode('___', $col);
			$col = array_pop($bits);
		}

		$col = str_replace("`", "", $col);

		return $col;
	}

	/**
	 * Get a shortened version of the element label - so that the admin pages
	 * don't get too stretched when we populate dropdowns with the label
	 *
	 * @param   string  $label  Complete element label
	 *
	 * @return  string  shortened element label
	 */

	public static function getShortDdLabel($label)
	{
		$label = strip_tags($label);
		preg_replace('/<[a-z][a-z0-9]*[^<>]*>/', '', $label);

		if (JString::strlen($label) > 50)
		{
			$label = JString::substr($label, 0, 47) . '...';
		}

		$label = trim($label);

		return $label;
	}

	/**
	 * Sanitize db fields names, can't just do regex on A-Z as languages like Chinese should be allowed
	 *
	 * @param   string  $str  Field name
	 *
	 * @since   3.0.7
	 *
	 * @return  string
	 */

	public static function dbFieldName($str)
	{
		$name = JFilterInput::getInstance()->clean($str, 'CMD');

		// Chinese characters?
		if ($name === '')
		{
			$name = str_replace(array(' ', '.', '-'), '', $str);
		}

		return $name;
	}

	/**
	 * is it a raw element name, i.e. ends in _raw
	 * 
	 * @param   string  $str  Element name
	 *
	 * @since   3.3
	 *
	 * @return  bool
	 */
	
	public static function isRawName($str) {
		return substr($str, -4, 4) == '_raw';
	}

	/**
	 * Strip _raw off the end
	 *
	 * @param   string  $str  Element name
	 *
	 * @since   3.3
	 *
	 * @return  bool
	 */
	
	public static function stripRawName($str) {
		return FabrikString::rtrimword($str, '_raw');
	}
	
	/**
	 * Clean variable names for use as fabrik element names
	 * whitespace compressed and replaced with '_'
	 * replace all non-alphanumeric chars except _ and - with '_'
	 * 28/06/2011 replaces umlauts with eu
	 * 22/11/2011 added IGNORE to default enc otherwise iconv chops everything after first unconvertable char
	 * 05/02/2012 changed name to iclean, removed strtolower() and added clean() as wrapper that does strtolower
	 *
	 * @param   string  $str      To clean
	 * @param   string  $fromEnc  From encoding
	 * @param   string  $toEnc    To encoding
	 *
	 * @return  string  cleaned
	 */

	public static function iclean($str, $fromEnc = "UTF-8", $toEnc = "ASCII//IGNORE//TRANSLIT")
	{
		// Replace umlauts
		$out = '';

		for ($i = 0; $i < JString::strlen($str); $i++)
		{
			$ch = ord($str{$i});

			switch ($ch)
			{
				case 195:
					$out .= "";
					break;
				case 164:
					$out .= "ae";
					break;
				case 188:
					$out .= "ue";
					break;
				case 182:
					$out .= "oe";
					break;
				case 132:
					$out .= "Ae";
					break;
				case 156:
					$out .= "Ue";
					break;
				case 150:
					$out .= "Oe";
					break;

				// Fix for cleaning value of 1
				case 0:
					$out = '1';
					break;
				default:
					$out .= chr($ch);
			}
		}

		$str = $out;

		if (function_exists('iconv'))
		{
			/* $$$ rob added @ in case its farsi which creates a notice:
			 * https://github.com/Fabrik/fabrik/issues/72
			 */

			// Replace accented characters with ascii equivalent e.g. é => e
			$str1 = (@iconv($fromEnc, $toEnc, $str));

			if ($str1)
			{
				$str = $str1;
			}

			$str = (str_replace("'", '', $str));
		}
		// Compress internal whitespace and replace with _
		$str = preg_replace('/\s+/', '_', $str);

		// Replace all non-alphanumeric chars except _ and - with '_'
		return preg_replace('/\W+/', '_', $str);
	}

	/**
	 * Wrapper for iclean(), that does strtolower on output of clean()
	 *
	 * @param   string  $str      To clean
	 * @param   string  $fromEnc  From encoding
	 * @param   string  $toEnc    To encoding
	 *
	 * @return  string  cleaned
	 */

	public static function clean($str, $fromEnc = "UTF-8", $toEnc = "ASCII//IGNORE//TRANSLIT")
	{
		return JString::strtolower(self::iclean($str, $fromEnc, $toEnc));
	}

	/**
	 * truncateHtml can truncate a string up to a number of characters while preserving whole words and HTML tags
	 * 
	 * (ripped off from Cake PHP framework)
	 *
	 * @param  string  $text String to truncate.
	 * @param  integer $length Length of returned string, including ellipsis.
	 * @param  string  $ending Ending to be appended to the trimmed string.
	 * @param  boolean $exact If false, $text will not be cut mid-word
	 * @param  boolean $considerHtml If true, HTML tags would be handled correctly
	 *
	 * @return string Trimmed string.
	 */
	public static function truncateHtml($text, $length = 100, $ending = '...', $exact = false, $considerHtml = true) {
		if ($considerHtml) {
			// if the plain text is shorter than the maximum length, return the whole text
			if (strlen(preg_replace('/<.*?>/', '', $text)) <= $length) {
				return $text;
			}
			// splits all html-tags to scanable lines
			preg_match_all('/(<.+?>)?([^<>]*)/s', $text, $lines, PREG_SET_ORDER);
			$total_length = strlen($ending);
			$open_tags = array();
			$truncate = '';
			foreach ($lines as $line_matchings) {
				// if there is any html-tag in this line, handle it and add it (uncounted) to the output
				if (!empty($line_matchings[1])) {
					// if it's an "empty element" with or without xhtml-conform closing slash
					if (preg_match('/^<(\s*.+?\/\s*|\s*(img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param)(\s.+?)?)>$/is', $line_matchings[1])) {
						// do nothing
						// if tag is a closing tag
					} else if (preg_match('/^<\s*\/([^\s]+?)\s*>$/s', $line_matchings[1], $tag_matchings)) {
						// delete tag from $open_tags list
						$pos = array_search($tag_matchings[1], $open_tags);
						if ($pos !== false) {
							unset($open_tags[$pos]);
						}
						// if tag is an opening tag
					} else if (preg_match('/^<\s*([^\s>!]+).*?>$/s', $line_matchings[1], $tag_matchings)) {
						// add tag to the beginning of $open_tags list
						array_unshift($open_tags, strtolower($tag_matchings[1]));
					}
					// add html-tag to $truncate'd text
					$truncate .= $line_matchings[1];
				}
				// calculate the length of the plain text part of the line; handle entities as one character
				$content_length = strlen(preg_replace('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i', ' ', $line_matchings[2]));
				if ($total_length+$content_length> $length) {
					// the number of characters which are left
					$left = $length - $total_length;
					$entities_length = 0;
					// search for html entities
					if (preg_match_all('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i', $line_matchings[2], $entities, PREG_OFFSET_CAPTURE)) {
						// calculate the real length of all entities in the legal range
						foreach ($entities[0] as $entity) {
							if ($entity[1]+1-$entities_length <= $left) {
								$left--;
								$entities_length += strlen($entity[0]);
							} else {
								// no more characters left
								break;
							}
						}
					}
					$truncate .= substr($line_matchings[2], 0, $left+$entities_length);
					// maximum lenght is reached, so get off the loop
					break;
				} else {
					$truncate .= $line_matchings[2];
					$total_length += $content_length;
				}
				// if the maximum length is reached, get off the loop
				if($total_length>= $length) {
					break;
				}
			}
		} else {
			if (strlen($text) <= $length) {
				return $text;
			} else {
				$truncate = substr($text, 0, $length - strlen($ending));
			}
		}
		// if the words shouldn't be cut in the middle...
		if (!$exact) {
			// ...search the last occurance of a space...
			$spacepos = strrpos($truncate, ' ');
			if (isset($spacepos)) {
				// ...and cut the text in this position
				$truncate = substr($truncate, 0, $spacepos);
			}
		}
		// add the defined ending to the text
		$truncate .= $ending;
		if($considerHtml) {
			// close all unclosed html-tags
			foreach ($open_tags as $tag) {
				$truncate .= '</' . $tag . '>';
			}
		}
		return $truncate;
	}
	
	/**
	 * Truncate text possibly setting a tip to show all of the text
	 *
	 * @param   string  $text  text to truncate
	 * @param   array   $opts  optional options array
	 *
	 * @return  string
	 */

	public static function truncate($text, $opts = array())
	{
		$origText = $text;
		$wordCount = FArrayHelper::getValue($opts, 'wordcount', 10);
		$showTip = FArrayHelper::getValue($opts, 'tip', true);
		$title = FArrayHelper::getValue($opts, 'title', "");
		$strippedText = htmlspecialchars(strip_tags($text), ENT_QUOTES);;
		
		if (JArrayHelper::getValue($opts, 'html_format', false))
		{
			$summary = FabrikString::truncateHtml($text, $wordCount);
		}
		else
		{
			$text = htmlspecialchars(strip_tags($text), ENT_QUOTES);
			$text = explode(' ', $text);
			$summary = array_slice($text, 0, $wordCount);
	
			if (count($text) > $wordCount)
			{
				$summary[] = " ...";
			}
	
			$summary = implode(' ', $summary);
		}
		
		if ($showTip && $origText != $summary)
		{
			FabrikHelperHTML::tips();

			if ($title !== '')
			{
				$title .= "::";
			}

			$tip = htmlspecialchars('<div class="truncate_text">' . $title . $strippedText . '</div>');
			$jOpts = new stdClass;
			$jOpts->notice = true;
			$jOpts->position = FArrayHelper::getValue($opts, 'position', 'top');
			$jOpts = json_encode($jOpts);
			$summary = '<span class="fabrikTip" opts=\'' . $jOpts . '\' title="' . $tip . '">' . $summary . '</span>';
		}

		return $summary;
	}

	/**
	 * Removes a querystring key from a url/queyrstring
	 *
	 * @param   string  $url  Or querystring
	 * @param   string  $key  To remove
	 *
	 * @return  string  url/querystring
	 */

	public static function removeQSVar($url, $key)
	{
		$pair = explode('?', $url);

		if (count($pair) === 2)
		{
			$url = $pair[0];
			$bits = FArrayHelper::getValue($pair, 1);
		}
		else
		{
			$url = '';
			$bits = FArrayHelper::getValue($pair, 0);
		}

		$glue = strstr($bits, '&amp;') ? '&amp;' : '&';
		$bits = explode($glue, $bits);
		$a = array();

		foreach ($bits as $bit)
		{
			if (strstr($bit, '='))
			{
				list($thisKey, $val) = explode('=', $bit);

				if ($thisKey !== $key)
				{
					$a[] = $bit;
				}
			}
		}

		if (!empty($a))
		{
			$url .= '?' . implode($glue, $a);
		}

		return $url;
	}

	/**
	 * Takes a complete URL, and urlencodes any query string args
	 *
	 * @param   string  $url  To encode
	 *
	 * @return  encoded url
	 */

	public static function encodeurl($url)
	{
		if (strstr($url, '?'))
		{
			list($site, $qs) = explode('?', $url);

			if (!empty($qs))
			{
				$new_qs = array();

				foreach (explode('&', $qs) as $arg)
				{
					$bits = explode('=', $arg);
					$key = FArrayHelper::getValue($bits, 0, '');
					$val = FArrayHelper::getValue($bits, 1, '');
					$new_qs[] = $key . "=" . urlencode($val);
				}

				$url = $site . "?" . implode("&", $new_qs);
			}
		}

		if (strstr($url, '{'))
		{
			/* $$$ hugh special case for some Google URL's that use encoded JSON objects in the path part of the URL
			 * so we need to re-encode {, }, " and :.  Except of course for the : in http(s):.
			 */
			list($http, $rest) = explode(':', $url, 2);

			if (!empty($rest))
			{
				$patterns = array('#\{#', '#\}#', '#"#', '#\\\\#', '#:#');
				$replacements = array('%7B', '%7D', '%22', '%5C', '%3A');
				$rest = preg_replace($patterns, $replacements, $rest);
				$url = $http . ':' . $rest;
			}
		}

		return $url;
	}

	/**
	 * Prepare a string for presentation in html.
	 *
	 * @param   string  &$string  To convert for html
	 *
	 * @return  void
	 */

	public static function forHtml(&$string)
	{
		// Special chars such as <>
		$string = htmlspecialchars($string, ENT_QUOTES);

		// Show umlauts correctly in ajax error messages.
		$string = mb_convert_encoding($string, 'HTML-ENTITIES', "UTF-8");
	}

	/**
	 * See if it looks like a string uses {table___element} placeholders
	 * Doesn't do any sanity testing to see if it's actually a valid element
	 * name, just goes by pattern patching word___word
	 *
	 * @param   string  $str  String to test
	 *
	 * @return   bool
	 *
	 * @since   3.0.1
	 */

	public static function usesElementPlaceholders($str)
	{
		return preg_match("#\{\w+___\w+\}#", $str);
	}

	/**
	 * Convert standard Fabrik coords string into lat, long, zoom object.
	 * Copied from map element, as we end up needing this elsewhere.
	 *
	 * @param   string  $v          coordinates
	 * @param   int     $zoomlevel  default zoom level
	 *
	 * @return  object  coords array and zoomlevel int
	 */

	public static function mapStrToCoords($v, $zoomlevel = 4)
	{
		$o = new stdClass;
		$o->coords = array('', '');
		$o->zoomlevel = (int) $zoomlevel;

		if (strstr($v, ","))
		{
			$ar = explode(":", $v);
			$o->zoomlevel = count($ar) == 2 ? array_pop($ar) : $zoomlevel;
			$v = self::ltrimword($ar[0], "(");
			$v = rtrim($v, ")");
			$v = str_replace(' ', '', $v);
			$o->coords = explode(",", $v);
		}
		else
		{
			$o->coords = array(0, 0);
		}
		// $$$ hugh - added these as I always think it's what they are!
		$o->lat = $o->coords[0];
		$o->long = $o->coords[1];
		$o->zoom = $o->zoomlevel;

		return $o;
	}

	/**
	 * Covert HEX colour to RGB colour
	 *
	 * @param   string  $hex  HEX colour string
	 *
	 * @return   string  RGB string
	 */

	public static function hex2rgb($hex)
	{
		$hex = str_replace('#', '', $hex);

		if (strlen($hex) === 3)
		{
			$r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
			$g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
			$b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
		}
		else
		{
			$r = hexdec(substr($hex, 0, 2));
			$g = hexdec(substr($hex, 2, 2));
			$b = hexdec(substr($hex, 4, 2));
		}

		return $r . ',' . $g . ',' . $b;
	}

	/**
	 * Translator JText wrapper - removes tags and compares raw text
	 * so "<p>STRING_TO_TRANSLATE</p>" is translated even if wrapped in a <p> tag.
	 *
	 * @param   string  $text  Text to translate
	 *
	 * @return  string
	 */

	public static function translate($text)
	{
		$plain = strip_tags($text);
		$translated = FText::_($plain);

		if ($translated !== $plain)
		{
			$text = str_replace($plain, $translated, $text);
		}

		return $text;
	}

	/**
	 * Is the string a CONCAT statemenet?
	 *
	 * @param   string  $text  Text to test
	 *
	 * @return  bool
	 */

	public static function isConcat($text)
	{
		return preg_match('/^\s*(CONCAT|CONCAT_WS)\b/i', preg_quote($text));
	}

	/**
	 * Strip whitespace (or only spaces) from a string
	 *
	 * @param   string  $text         Text to strip
	 * @param   bool    $only_spaces  If true, only strip spaces (not tabs, etc), default is false
	 *
	 * @return string
	 */

	public static function stripSpace($text, $only_spaces = false)
	{
		if ($only_spaces)
		{
			return str_replace(' ', '', $text);
		}
		else
		{
			return preg_replace('#\s+#', '', $text);
		}
	}

	/**
	 * See if date string is a valid date in MySQL format.
	 *
	 * NOTE - I could have sworn we had a function somewhere to do this, but I can't find it!
	 * Needed it in the main system plugin, for handling J! search plugin dates, as J!
	 * will pitch a fatal error if we pass it an invalid date string.  So if there is
	 * already a way of doing this, feel free to dump this func and modify the system plugin
	 * in onDoContentSearch().
	 *
	 * @param  bool  $time_optional   if set to true, the time part is optional
	 *
	 * @return  bool
	 */
	public static function isMySQLDate($date, $time_optional = false)
	{
		$date_re = '(((\d{4})(-)(0[13578]|10|12)(-)(0[1-9]|[12][0-9]|3[01]))|((\d{4})(-)(0[469]|1??1)(-)([0][1-9]|[12][0-9]|30))|';
		$date_re .= '((\d{4})(-)(02)(-)(0[1-9]|1[0-9]|2[0-8]))|(([02468]??[048]00)(-)(02)(-)(29))|(([13579][26]00)(-)(02)(-)(29))|';
		$date_re .= '(([0-9][0-9][0][48])(-)(0??2)(-)(29))|(([0-9][0-9][2468][048])(-)(02)(-)(29))|(([0-9][0-9][13579][26])(-)(02??)(-)(29)))';
		$time_re = '(\s([0-1][0-9]|2[0-4]):([0-5][0-9]):([0-5][0-9]))';

		if ($time_optional)
		{
			return preg_match("#^" . $date_re . "$#", $date) || preg_match("#^" . $date_re . $time_re . "$#", $date);
		}
		else
		{
			return preg_match("#^" . $date_re . $time_re . "$#", $date);
		}
	}

	/**
	 * Replace last occurance of a string
	 *
	 * @param   string  $search   Text to search for
	 * @param   string  $replace  Text to replace the search string
	 * @param   string  $subject  The text to search in
	 *
	 * @return  string
	 */
	public static function replaceLast($search, $replace, $subject)
	{
		$pos = strripos($subject, $search);

		if ($pos !== false)
		{
			$subject = substr_replace($subject, $replace, $pos, strlen($search));
		}

		return $subject;
	}

	/**
	 * DB value quote a single string or an array of strings, first checking to see if they are
	 * already quoted.  Which the J! $db->quote() doesn't do, unfortunately.
	 * Does NOT modify the input.  Does not quote if value starts with SELECT.
	 *
	 * @param unknown $values
	 * @param bool    $commaSeparated  individually quote a comma separated string of values
	 *
	 * @return   mixed   quoted values
	 */
	public static function safeQuote($values, $commaSeparated = true) {
		$values2 = $values;

		if ($commaSeparated) {
			$values2 = explode(',', $values2);
		}

		if (is_array($values2))
		{
			foreach ($values2 as &$v)
			{
					$v = self::safeQuoteOne($v);
			}
		}
		else
		{
			$values2 = self::safeQuoteOne($values2);
		}

		if ($commaSeparated) {
			$values2 = implode(',', $values2);
		}

		return $values2;
	}

	/**
	 * Return DB value quoted single string.  Does not quote if value starts with SELECT,
	 * or if value is already single quoted.
	 *
	 * @param string  $value
	 *
	 * @return   mixed   quoted values
	 */
	public static function safeQuoteOne($value)
	{
		$value = trim($value);
		if (is_string($value) && !preg_match('/^\s*SELECT\s+/i', $value))
		{

			if (!preg_match("#^'.*'$#", $value))
			{
				$db = JFactory::getDbo();
				$value = $db->quote($value);
			}

		}

		return $value;
	}

	/**
	 * Wrapper for safeQuoteName because I'm a dumbass and got my mords wuddled when I created
	 * the safeNameQuote() function.
	 *
	 * @param unknown $values
	 * @param bool    $commaSeparated  individually quote a comma separated string of values
	 *
	 * @return   mixed   quoted values
	 */
	public static function safeQuoteName($values, $commaSeparated = true)
	{
		return self::safeNameQuote($values, $commaSeparated);	
	}
		
	/**
	 * DB name quote a single string or an array of strings, first checking to see if they are
	 * already quoted.  Which the J! $db->quote() doesn't do, unfortunately.
	 * Does NOT modify the input.  Does not quote if value starts with CONCAT.
	 *
	 * @param unknown $values
	 * @param bool    $commaSeparated  individually quote a comma separated string of values
	 *
	 * @return   mixed   quoted values
	 */
	public static function safeNameQuote($values, $commaSeparated = true) {
		$values2 = $values;

		if ($commaSeparated) {
			$values2 = explode(',', $values2);
		}

		if (is_array($values2))
		{
			foreach ($values2 as &$v)
			{
				$v = self::safeNameQuoteOne($v);
			}
		}
		else
		{
			$values2 = self::safeNameQuoteOne($values2);
		}

		if ($commaSeparated) {
			$values2 = implode(',', $values2);
		}

		return $values2;
	}

	/**
	 * Return DB value quoted single string.  Does not quote if value starts with SELECT,
	 * or if value is already single quoted.
	 *
	 * @param string  $value
	 *
	 * @return   mixed   quoted values
	 */
	public static function safeNameQuoteOne($value)
	{
		$value = trim($value);
		if (is_string($value) && !preg_match('/^\s*(CONCAT|CONCAT_WS)\s*\(/i', $value))
		{

			if (!preg_match("#^`.*`$#", $value))
			{
				$db = JFactory::getDbo();
				$value = $db->quoteName($value);
			}

		}

		return $value;
	}

}

/**
 *
 * $$$ hugh JText::_() does funky stuff to strings with commas in them, like
 * truncating everything after the first comma, if what follows the first comma
 * is all "upper case".  But it tests for that using non MB safe code, so any non
 * ASCII strings (like Greek text) with a comma in them get truncated at the comma.
 * Corner case or what!  But we need to work round this behavior.
 *
 * So ... here's a wrapper for JText::_().
 */

class FText extends JText
{
	/**
	 * Translates a string into the current language.
	 *
	 * Examples:
	 * <script>alert(Joomla.JText._('<?php echo FText::_("JDEFAULT", array("script"=>true));?>'));</script>
	 * will generate an alert message containing 'Default'
	 * <?php echo FText::_("JDEFAULT");?> it will generate a 'Default' string
	 *
	 * @param   string   $string                The string to translate.
	 * @param   mixed    $jsSafe                Boolean: Make the result javascript safe.
	 * @param   boolean  $interpretBackSlashes  To interpret backslashes (\\=\, \n=carriage return, \t=tabulation)
	 * @param   boolean  $script                To indicate that the string will be push in the javascript language store
	 *
	 * @return  string  The translated string or the key is $script is true
	 *
	 * @since   11.1
	 */
	public static function _($string, $jsSafe = false, $interpretBackSlashes = true, $script = false)
	{
		/**
		 * In JText::_(), it does the following tests to see if everything following a comma is all upp
		 * case, and if it is, it does Funky Stuff to it.  We ned to avoid that behavior.  So us this
		 * logic, and if it's true, return the string untouched.  We could just check for a comma and not
		 * process anything with commas (unikely to be a translatable phrase), but unless this test adds
		 * too much overhead, might as well do the whole J! test sequence.
		 */

		if (!(strpos($string, ',') === false))
		{
			$test = substr($string, strpos($string, ','));

			if (strtoupper($test) === $test)
			{
				/**
				 * This is where JText::_() would do Funky Stuff, chopping off everything after
				 * the first comma.  So we'll just return the input string untouched.
				 */
				return $string;
			}
		}

		// if we got this far, hand it to JText::_() as normal
		return parent::_($string, $jsSafe, $interpretBackSlashes, $script);
	}
}
