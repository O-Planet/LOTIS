<?php
namespace LTS;

class Space extends Element
{
    static $title = '';
    static $keywords = '';
    static $description = '';
    static $meta = '';
    static $scriptversion = '';

    public $jqueryui = true;
    public $ltsjs = true;

    public function __construct($id = '')
    {
        parent::__construct($id);
        $this->storage['elements'] = [];
    }

    public function html()
    {
        $this->build($this);
    }

    public function build($element)
    {
        $this->DOMBuilder($this->metadata($element, false));
        return $this;
    }

    public function metadata($element, $ret = true)
    {
        $element->compile();
        $element->create($this);
        Events::build();
        
        if(! $ret) return null;

        return $this->get('elements');
    }

    public function DOMBuilder($elements = null)
    {
        // Очистка и инициализация 
        $this->storage['DOM_css'] = [];
        $this->storage['DOM_link'] = [];
        $this->storage['DOM_scripts'] = [];
        $this->storage['DOM_ready'] = [];
//        $this->storage['DOM_eventsready'] = [];
        $this->storage['DOM_body'] = [];
        $this->storage['DOM_childs'] = [];

        if($elements === null)
            $elements = $this->get('elements');

        foreach ($elements as $el) {
            switch ($el['type']) {
                case 'CSS': $this->processCSS($el); break;
                case 'link': $this->processLink($el); break;
                case 'script': $this->processScript($el); break;
                case 'file': $this->processFile($el); break;
                case 'VARS': $this->processVARS($el); break;
                case 'JS': $this->processJS($el); break;
                case 'html': $this->processHTML($el); break;
                default: $this->processOther($el); break;
            }
        }

        $this->__builddoc();
    }

    private function processCSS($el)
    {
        $str = '';
        foreach ($el['style'] as $name => $val) {
            $str .= is_numeric($name) ? $val : "\n{$name}: {$val};";
        }
        $this->set('DOM_css', "{$el['class']} {{$str}\n}");
    }

    private function processLink($el)
    {
        $this->set('DOM_link', "<link href=\"{$el['href']}\" rel=\"{$el['rel']}\">");
    }

    private function processScript($el)
    {
        $attrs = [];
        if (!empty($el['async'])) $attrs[] = 'async';
        if (!empty($el['defer'])) $attrs[] = 'defer';
        $attrStr = $attrs ? ' ' . implode(' ', $attrs) : '';
        $this->set('DOM_link', "<script src=\"{$el['src']}\"{$attrStr}></script>");
    }

    private function processFile($el)
    {
        if (!file_exists($el['filename'])) return;
        $content = file_get_contents($el['filename']);
        $oper = ['body' => $content];
        $id = !empty($el['id']) ? $el['id'] : '';
        $parent = !empty($el['parent']) ? $el['parent'] : '';

        if ($id) {
            if ($parent) { 
                $parentData = $this->get('DOM_body', $parent);
                if($parentData !== false) {
                    $parentData['childs'][] = $id;
                    $this->set('DOM_body', $parent, $parentData);
                }
            }
            $this->set('DOM_body', $id, $oper);
            if (!$parent) $this->set('DOM_childs', $id);
        } else {
            $this->set('DOM_body', $oper);
        }
    }

    private function processVARS($el)
    {
        $this->set('DOM_scripts', "LTS.vars(\"{$el['id']}\", {$el['body']});");
    }

    private function processJS($element)
    {
        $str = '';

        if ($element['area'] === 'script') {
            $str = is_numeric($element['id'])
                ? $element['body'] . ';'
                : "function {$element['id']} {\n    {$element['body']};\n}";
        }
        elseif ($element['area'] === 'ready') {
            if ($element['on']) {
                $parent = $element['parent'];
                $class = strpos($element['class'], '.') === false ? '' : $element['class'];
                $name = $element['id'];
                $p = strpos($name, '(');
                if($p !== false)
                    $name = substr($name, $p);
                $str = "jQuery('#{$parent}{$class}').on('{$name}', function (event) {" . $element['body'] . '; });';
            }
            else {
                $str = is_numeric($element['id'])
                    ? $element['body'] . ';'
                    : "function {$element['id']}(){\n{$element['body']};\n}";
            }
        }

        // Единая точка добавления
        //$target = $element['eventsready'] ? 'DOM_eventsready' : ($element['area'] === 'script' ? 'DOM_scripts' : 'DOM_ready');
        $target = $element['area'] === 'script' ? 'DOM_scripts' : 'DOM_ready';
        if ($str !== '') {
            $this->set($target, $str);
        }
    }

    private function processHTML($el)
    {
        $tagname = !empty($el['tagname']) ? $el['tagname'] : 'div';
        $id = !empty($el['id']) ? $el['id'] : '';
        $parent = !empty($el['parent']) ? $el['parent'] : '';
        $attr = !empty($el['attr']) && is_array($el['attr']) ? $el['attr'] : null;
        $class = !empty($el['class']) ? ' class="' . htmlspecialchars($el['class']) . '"' : '';
        $caption = !empty($el['caption']) ? $el['caption'] : '';

        $attrStr = '';

        if (!empty($attr)) {
            foreach ($attr as $name => $val) {
                // Исключаем class и id — они обрабатываются отдельно
                if ($name === 'class' || $name === 'id') continue;

                if ($val === '' || $val === true) {
                    $attrStr .= " {$name}";
                } elseif ($val !== false) {
                    $escaped = htmlspecialchars((string)$val, ENT_QUOTES);
                    $attrStr .= " {$name}=\"{$escaped}\"";
                }
            }
        }

        $oper = [
            'tag' => "<{$tagname} id='{$id}'{$class}{$attrStr}>",
            'tagname' => $tagname,
            'parent' => $parent,
            'childs' => []
        ];
        if ($caption !== '') $oper['caption'] = $caption;

        if ($id) {
            if ($parent) {
                $parentData = $this->get('DOM_body', $parent);
                if($parentData !== false) {
                    $parentData['childs'][] = $id;
                    $this->set('DOM_body', $parent, $parentData);
                }
            }
            $this->set('DOM_body', $id, $oper);
            if (!$parent) $this->set('DOM_childs', $id);
        } else {
            $this->set('DOM_body', $oper);
        }
    }

    private function processOther($el)
    {
        if (!empty($el['id'])) {
            $this->set('DOM_body', $el['id'], ['tag' => '', 'tagname' => '', 'childs' => [], 'caption' => '']);
            if (empty($el['parent'])) {
                $this->set('DOM_childs', $el['id']);
            }
        }
    }

    private function __builddoc()
    {
        $head = '';
        $body = '';

        $head .= "<!DOCTYPE html>\n<html lang=\"ru\">\n<head>\n<meta charset=\"utf-8\">";
        $head .= Space::$description ? "\n<meta name=\"description\" content=\"" . htmlspecialchars(Space::$description) . "\">" : '';
        $head .= Space::$keywords ? "\n<meta name=\"keywords\" content=\"" . htmlspecialchars(Space::$keywords) . "\">" : '';
        $head .= Space::$title ? "\n<title>" . htmlspecialchars(Space::$title) . "</title>" : '';
        $head .= Space::$meta ? "\n" . Space::$meta : '';

        global $__lts_classes;
        $version = Space::$scriptversion ? '?version=' . Space::$scriptversion : '';

        $head .= "\n<script src=\"external/jquery/jquery.js\"></script>";
        if ($this->jqueryui) {
            $head .= "\n<link href=\"jquery-ui.css\" rel=\"stylesheet\">";
            $head .= "\n<script src=\"jquery-ui.js\"></script>";
        }
        if ($this->ltsjs) {
            $head .= "\n<script src=\"" . _LOTIS_JSDIR . "lts.js\"></script>";
        }

        foreach ($__lts_classes as $cls) {
            $cssPath = _LOTIS_CSSDIR . $cls . '.css';
            if (file_exists(_LOTIS_DIR . 'CSS/' . $cls . '.css')) {
                $head .= "\n<link href=\"{$cssPath}{$version}\" rel=\"stylesheet\">";
            }
            if (file_exists('CSS/' . $cls . '.css')) {
                $head .= "\n<link href=\"CSS/{$cls}.css{$version}\" rel=\"stylesheet\">";
            }
            if (file_exists($cls . '.css')) {
                $head .= "\n<link href=\"{$cls}.css{$version}\" rel=\"stylesheet\">";
            }
        }

        foreach ($this->get('DOM_link') as $link) $head .= "\n{$link}";
            $head .= "\n<style>\n" . implode("\n", $this->get('DOM_css')) . "\n</style>";

        foreach ($__lts_classes as $cls) {
            //if (in_array($cls, ['jquery', 'jquery-ui', 'lts'])) continue;

            $jsPath = _LOTIS_JSDIR . $cls . '.js';
            if (file_exists(_LOTIS_DIR . 'JS/' . $cls . '.js')) {
                $head .= "\n<script src=\"{$jsPath}{$version}\"></script>";
            }
            if (file_exists('JS/' . $cls . '.js')) {
                $head .= "\n<script src=\"JS/{$cls}.js{$version}\"></script>";
            }
            if (file_exists($cls . '.js')) {
                $head .= "\n<script src=\"{$cls}.js{$version}\"></script>";
            }
        }

        $script = implode("\n", $this->get('DOM_scripts'));
        $ready = implode("\n", $this->get('DOM_ready'));
        //$eventsready = implode("\n", $this->get('DOM_eventsready'));

        $head .= "
        <script language=\"javascript\">
        {$script}
        jQuery(document).ready(function () {
        {$ready}
        });        
        </script>\n</head>\n<body>";
        // {$eventsready}

        foreach ($this->get('DOM_childs') as $id) {
            $body .= $this->__buildchild($id);
        }

        $body .= "\n</body>\n</html>";

        echo $head . $body;
    }

    private function __buildchild($id)
    {
        $child = $this->get('DOM_body', $id);
        if ($child === false) return '';

        $ret = '';

        if (isset($child['body'])) {
            $ret .= $child['body'];
        } else {
            $ret .=  $child['tag'];
            if (isset($child['caption']) && $child['caption'] !== '') {
                $ret .= $child['caption'];
            }
            if (isset($child['childs'])) {
                foreach ($child['childs'] as $cid) {
                    $ret .= $this->__buildchild($cid);
                }
            }
            $ret .=  "</{$child['tagname']}>";
        }

        return $ret;
    }
}
?>
