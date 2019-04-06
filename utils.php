<?php

/**
 * Replaces all occurences of new lines and multiple whitespaces with single whitespace
 *
 * @param string $str String to be proccessed
 * @return string String with only one whitespaces
 */
function simplify_string($str)
{
    $str = preg_replace(["/\n+/", "/ {2,}/"], ' ', $str);
    $str = trim($str);

    return $str;
}

function is_class($name)
{
    if (strpos($name, '::') !== false || strpos($name, '->') !== false) {
        return true;
    } else {
        return false;
    }
}

function rewrite_names($name)
{
    global $rewritename;
    $lname = strtolower($name);
    foreach ($rewritename as $pattern => $newName) {
        if (substr($lname, 0, strlen($pattern)) == $pattern) {
            $name = substr($newName, 0, strlen($pattern)) . substr($name, strlen($pattern));
        }
    }

    return $name;
}

function clear_source_code($source)
{
    $source = trim(html_entity_decode(strip_tags(str_replace(['<br />', '<br/>', '<br>'], "\n", $source))));
    //var_dump($source, substr($source, 0, 6), substr($source, -2));exit;
    if (substr($source, 0, 5) == '<?php') {
        $source = substr($source, 6);
    }
    if (substr($source, -2) == '?>') {
        $source = substr($source, 0, -2);
    }

    // remove \u00a0 empty chars that would be otherwise
    return trim($source);
}

function get_cmd_arg_value($args, $arg)
{
    foreach ($args as $entry) {
        if (strpos($entry, $arg . '=') === 0) {
            return substr($entry, strlen($arg) + 1);
        }
    }

    return null;
}

function extract_formated_text($elms, $xpath = null)
{
    $textParts = [];

    if ($elms->length == 0) {
        return null;
    }
    $dom = new \DOMDocument();

    for ($i = 0; $i < $elms->length; $i++) {
        if ($elms->item($i)->tagName == 'ul') {
            $pText = extract_formated_text($xpath->query('./li', $elms->item($i)), $xpath);
        } else if ($elms->item($i)->tagName == 'table') {
            $pText = extract_formated_text($xpath->query('./tbody/tr', $elms->item($i)), $xpath);
        } else {
            $nodeCopy = $dom->importNode($elms->item($i), true);
            $pText    = $dom->saveXML($nodeCopy);
        }

        $pText = html_entity_decode(strip_tags(str_replace(['<strong><code>', '<var class="varname">', '</var>', '</code></strong>', '<em><code class="parameter">', '</code></em>', '<code>', '<code class="parameter">', '<code class="code">', '</code>', '<em>', '</em>'], '`', $pText)));

        if ($pText == 'Object oriented style' || $pText == 'Procedural style') {
            continue;
        }

        // looks crazy but this matches all words not surrounded by ` and escapes it
        $pText = preg_replace_callback('/(\b(?<![`])[a-zA-Z_][a-zA-Z_0-9]*\b(?![`]))/i', function ($subject) {
            // escape '_' and '*' to avoid malforming function names like 'html_entity_decode' by markdown
            return str_replace(['_', '*'], ['\_', '\*'], $subject[0]);
        }, $pText);

        if (strpos($pText, 'Warning') != 0) {
            $pText = str_replace('Warning:', '**Warning**:', $pText);
        }

        if (strpos($pText, 'Note') == 0) {
            $pText = str_replace('Note:', '**Note**:', $pText);
        }

        $pText = trim($pText);

        if ($pText) {
            $textParts[] = $pText;
        }
    }

    return simplify_string(implode('\n\n', $textParts));
}

function utf8ize($mixed)
{
    if (is_array($mixed)) {
        foreach ($mixed as $key => $value) {
            $mixed[$key] = utf8ize($value);
        }
    } elseif (is_string($mixed)) {
        return mb_convert_encoding($mixed, 'UTF-8', 'UTF-8');
    }

    return $mixed;
}

