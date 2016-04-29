<?php
if (!defined('DOKU_INC'))
        die();

if (!defined('DOKU_PLUGIN'))
        define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');

require_once (DOKU_PLUGIN . 'syntax.php');

class syntax_plugin_tablecalc extends DokuWiki_Syntax_Plugin {
    var $id_index = 0;

    function getInfo() {
        return array(
            'author' => 'Christopher Voltz',
            'email' => 'cjunk@voltz.ws',
            'date' => '2016-04-29',
            'name' => 'Table Calculations Plugin',
            'desc' => 'Adds limited spreadsheet functionalit to tables',
            'url' => 'https://github.com/cvoltz/tablecalc.git');
    }

    function getType() {
        return 'substition';
    }

    function getSort() {
        return 1213;
    }

    function connectTo($mode) {
        $this->Lexer->addSpecialPattern("~~=[_a-z\ A-Z0-9\%\:\.,\\\/\*\-\+\(\)\&\|#><!=;]*~~", $mode, 'plugin_tablecalc');
    }

    function handle($match, $state, $pos, &$handler) {
        global $ID, $ACT, $INFO;

        $signs = "-~=+*.,;\/!|&\(\)";
        $pattern = "/[$signs]*([a-zA-Z]+)\(/is";
        $aAllowed = array("cell", "row", "col", "sum", "average", "count", "nop", "round", "range", "label", "min", "max", "calc", "check", "compare");

        if (preg_match_all($pattern, $match, $aMatches)) {
            foreach ($aMatches[1] as $f) {
                if (!in_array(strtolower($f), $aAllowed)) {
                    $match = preg_replace("/([$signs]*)$f\(/is", "\\1nop(", $match);
                }
            }
        }

        $aNop = array('~~=', '~~');
        foreach ($aNop as $nop) {
            $match = str_replace($nop, '', $match);
        }

        $match = preg_replace("/#([^\(\);,]+)/", "'\\1'", $match);
        $match = preg_replace("/\(([a-z0-9_]+)\)/", "('\\1')", $match);
        $this->id_index++;

        return array('formula' => $match, 'divid' => '__tablecalc' . $this->id_index);
    }

    function render($mode, &$renderer, $data) {
        global $INFO, $ID, $conf;

        if ($mode == 'xhtml') {
            $renderer->doc.= '<span id="' . $data['divid'] . '"><script type="text/javascript" defer="defer">tablecalc("' . $data['divid'] . '","' . $data['formula'] . '");</script></span>';
            return true;
        }

        return false;
    }
}
?>
