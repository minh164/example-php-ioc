<?php

function l($foo)
{
    echo "<pre style='font-size: 12px;line-height: 1.3;margin: 0;white-space: pre-wrap; word-break: break-word;'>";
    print_r($foo);
    echo "</pre>";
}

$CALL_LOOP = 0;
function xdebug($foo, $all = false)
{
    echo (new Dump($all))->variable($foo) . htmlLine();
}

function xx($foo, $skip = 0)
{
    global $CALL_LOOP;
    if ($skip == $CALL_LOOP) {
        echo (new Dump(false))->variable($foo) . htmlLine();
        exit();
    }
    $CALL_LOOP++;
}

function xxx($foo, $skip = 0)
{
    global $CALL_LOOP;
    if ($skip == $CALL_LOOP) {
        echo (new Dump(true))->variable($foo) . htmlLine();
        exit();
    }
    $CALL_LOOP++;
}

function htmlLine($index = 1, $length = 30)
{
    $trace = debug_backtrace();
    $file = $trace[$index]['file'];
    $line = $trace[$index]['line'];
    $start = $line <= ($length / 2) ? 1 : $line - ($length / 2);
    $end =  $start + $length;
    return htmlFile($file, $start, $end, $line);
}

function xc($foo)
{
    $htmlDebug = (new Dump(true))->variable($foo);
    $htmlDebug = preg_replace("/<style\\b[^>]*>(.*?)<\\/style>/s", "", $htmlDebug);
    $htmlDebug = preg_replace("/<script\\b[^>]*>(.*?)<\\/script>/s", "", $htmlDebug);
    $htmlDebug = preg_replace("/Toggle/", "", $htmlDebug);
    $stringDebug = strip_tags($htmlDebug);
    echo $stringDebug;
    exit();
}

function xz(...$t)
{
    $style = "<style>
        *{margin: 0;padding:0}
        .trace {font-family: monospace;border-collapse: collapse;width: 100%;margin:0;height: 100%;background:#322931 }
        .trace-left {width: 100%;text-overflow: ellipsis;padding: 0;list-style: none;background: #eee;position: fixed;height: 55px;top: 0;z-index: 9999;white-space: nowrap; overflow-x: visible; overflow-y: hidden;}
        .trace-left li {padding: 10px;cursor: pointer;transition: all 0.1s ease;background: #eeeeee;display: inline-block}
        .trace-left li.active {color: #4288CE;font-weight: bold}
        .trace-index{ font-size: 11px;height: 18px;width: 18px;line-height: 18px;border-radius: 5px;padding: 0 1px 0 1px;text-align: center;display: inline-block;background-color: #2a2a2a;color: #bebebe;float:left}
        .trace-info{margin-left: 24px}
        .trace-right{position: relative;top: 55px}
        .file-line{background-color: #322931;font: 13px monospace;line-height: 1.2em;padding: 3px 10px;text-align: left;color: #b9b5b8;margin: 0;border-bottom: 1px solid #464646;white-space: pre-wrap;word-wrap: break-word;}
        .trace-code{padding-left: 30px;background: #322931;}
        .hidden{display: none}
    </style>";
    $script = "<script>
        var box = document.querySelectorAll('.trace-box');
        var content = document.querySelectorAll('.trace-content');

        function show(event, id) {
            for (var i = 0; i < box.length; i++) {
                box[i].classList.remove('active');
                content[i].classList.add('hidden');
            }
            event.classList.add('active');
            document.getElementById('dump-'+id).classList.remove('hidden');
        }
    </script>";

    $dump = new Dump(true);

    $output = '<div class="trace">';
    $output .= '<ul class="trace-left">';
    foreach ($t as $key => $value) {
        $active = $key == 0 ? 'active' : '';
        $info = gettype($value);
        if($info == 'object'){
            $info .= ' '.get_class($value);
        }
        $output .= "<li onClick='show(this,{$key})' class='trace-box {$active}'>" . "<span class='trace-index'>" . $key . "</span><div class='trace-info'>" . $info . " </div></li>";
    }
    $output .= '</ul>';
    $output .= '<div class="trace-right">';

    foreach ($t as $key => $value) {
        $hidden = $key != 0 ? 'hidden' : '';
        $output .= "<div class='trace-content {$hidden}' id='dump-{$key}'>";
        $output .= $dump->variable($value);
        $output .= '</div>';
    }

    $output .= '<div class="trace-code">' . htmlLine() . '</div>' ;
    $output .= '</div>';

    $output .= '</div>';
    echo $style . $output . $script;

    exit();
}

function xe(\Exception $e){
    $foo = ['mes' => $e->getMessage(),'file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode()];
    echo (new Dump(false))->variable($foo) . htmlLine();
    echo htmlTrace($e->getTrace());
}

function trace()
{
    echo htmlTrace(debug_backtrace());
    exit();
}

function htmlTrace($trace)
{
    $style = "<style>
        *{margin: 0;padding:0}
        .trace {font-family: monospace;border-collapse: collapse;width: 100%;margin:0;height: 100%;background:#322931 }
        .trace-left {width: 32%;text-overflow: ellipsis;padding: 0;list-style: none;background: #eee;float: left;overflow: hidden;height: 100%;}
        .trace-left li {padding: 10px;cursor: pointer;transition: all 0.1s ease;background: #eeeeee;border-bottom: 1px solid rgba(0, 0, 0, .05)}
        .trace-left li.active {box-shadow: inset -5px 0 0 0 #4288CE;color: #4288CE;}
        .trace-index{ font-size: 11px;height: 18px;width: 18px;line-height: 18px;border-radius: 5px;padding: 0 1px 0 1px;text-align: center;display: inline-block;background-color: #2a2a2a;color: #bebebe;float:left}
        .trace-info{margin-left: 24px;margin-bottom:10px}
        .trace-line{font-family:monospace;color: #a29d9d;font-size:12px}
        .trace-right{width: 68%;float: right}
        .file-line{background-color: #322931;font: 13px monospace;line-height: 1.2em;padding: 3px 10px;text-align: left;color: #b9b5b8;margin: 0;border-bottom: 1px solid #464646;white-space: pre-wrap;word-wrap: break-word;}
        .hidden{display: none}
    </style>";

    $script = "<script>
        var box = document.querySelectorAll('.trace-box');
        var content = document.querySelectorAll('.trace-content');

        function show(event, id) {
            for (var i = 0; i < box.length; i++) {
                box[i].classList.remove('active');
                content[i].classList.add('hidden');
            }
            event.classList.add('active');
            document.getElementById('dump-'+id).classList.remove('hidden');
        }
    </script>";

    $dump = new Dump(true);

    $output = '<div class="trace">';
    $output .= '<ul class="trace-left">';
    foreach ($trace as $key => $value) {
        $object = isset($value['object']) ? get_class($value['object']) : null;
        $file = isset($value['file']) ? str_replace(getcwd(), '...', $value['file']) . ":" . $value['line'] : null;
        $active = $key == 1 ? 'active' : '';
        $output .= "<li onClick='show(this,{$key})' class='trace-box {$active}'>" . "<span class='trace-index'>" . $key . "</span><div class='trace-info'>" . $object. '->'. $value['function'] . "() </div> <div class='trace-line'>" . $file . "</div></li>";
    }
    $output .= '</ul>';
    $output .= '<div class="trace-right">';
    foreach ($trace as $key => $value) {
        $file = null;
        if (isset($value['file'])) {
            $start = $value['line'] <= (30 / 2) ? 1 : $value['line'] - (30 / 2);
            $end =  $start + 30;
            $file = htmlFile($value['file'], $start, $end, $value['line']);
        }
        $hidden = $key != 1 ? 'hidden' : '';
        $output .= "<div class='trace-content {$hidden}' id='dump-{$key}'>";
        $output .= "<div class='file-line'>".$file."</div>";
        $output .= $dump->variable($value['object'] ?? '');
        $output .= empty($value['args']) ? '' : $dump->variable($value['args']);
        $output .= '</div>';
    }
    $output .= '</div>';
    $output .= '</div>';
    return $style . $output . $script;
}

function htmlFile($file, $start, $end, $line)
{
    $style = 'background:#322931;font-size:13px;color:#b9b5b8;padding:0;margin:0;border-bottom:1px solid #464646;font-family: monospace;';
    $htmlTrace = sprintf('%s line %d %s', $file, $line, PHP_EOL);
    $htmlTrace = "<pre data-debug='true' style='{$style};color: red;padding: 0 10px'>$htmlTrace</pre>";

    $htmlLine = '';
    $frame = ['file' => $file, 'line' => $line];
    $frameManage = new Frame($frame);
    $range = $frameManage->getFileLines($start, ($end - $start));
    if ($range) {
        $range[$frame['line'] - 1] = '<div style="background: rgba(255, 100, 100, .17)">' . @$range[$frame['line'] - 1] . '&nbsp;</div>';
        $range[$frame['line'] - 2] = '<div style="background: rgba(255, 100, 100, .07)">' . @$range[$frame['line'] - 2] . '</div>';
        $range[$frame['line']] = '<div style="background: rgba(255, 100, 100, .07)">' . @$range[$frame['line']] . '</div>';
        $code = "<li>" . implode('</li><li>', $range) . '</li>';
        $code = "<ol style='margin:0;' start='" . ($start + 1) . "'>" . $code . '</ol>';
        $htmlLine = "<pre style='{$style};padding-left: 25px;white-space: pre-wrap;'><code style='padding:.5em;display: block'>$code</code></pre>";
    }

    return $htmlTrace.$htmlLine;
}

class Dump
{
    private $maxDepth = 3;
    protected $_skips = [
        \Illuminate\Container\Container::class,
        \Illuminate\Support\ServiceProvider::class,
        \Illuminate\Routing\Pipeline::class,
//        \Phalcon\Di::class
    ];
    protected $_methods = [];
    protected $_styles = [
        "pre" => "background-color:#322931;font: 13px monospace;line-height:1.2em;padding:3px 10px;text-align:left;color:#b9b5b8;margin:0;border-bottom:1px solid #464646;white-space:pre-wrap;word-wrap: break-word;",
    ];

    protected $_filters = \ReflectionProperty::IS_PUBLIC | \ReflectionProperty::IS_PROTECTED | \ReflectionProperty::IS_PRIVATE | \ReflectionProperty::IS_STATIC;

    public function __construct($all = true)
    {
        if (!$all) {
            $this->_filters = \ReflectionProperty::IS_PUBLIC;
        }
    }

    public function resetMethod()
    {
        $this->_methods = [];
        return $this;
    }

    public function variable($variable, $name = null)
    {
        $this->resetMethod();

        return $this->getDumpHeader() . "<div style='position: relative'>" . strtr("<pre style=':style'>:output</pre>", [":style" => $this->_styles['pre'], ":output" => $this->output($variable, $name)]) . $this->getDumpFooter() . "<button class='btn-dump' onclick='collapseAll()'>Toggle</button></div>";
    }

    protected function output($variable, $name = null, $tab = 1)
    {
        $space = "  ";
        $output = $name ? $name." " : "";

        if (gettype($variable) == "array") {
            $count = \count($variable);
            if ($count == 0) {
                $output .= "<b class ='arr'>array</b> []";
                return $output;
            }

            $output .= strtr("<b class ='arr'>array</b> [<span class='dump arr' onclick='toggle(this)'>#:count </span><span class='dump-hidden'>\n", [":count" => $count]);

            foreach ($variable as $key => $value) {
                $output .= str_repeat($space, $tab) . strtr("[<span class='arr'>:key</span>] => ", [":key" => $key]);

                $output .= $this->output($value, "", $tab + 1) . "\n";
            }

            return $output . str_repeat($space, $tab - 1) . "</span>]";
        }

        if($variable instanceof \Closure){

            $r = new \ReflectionFunction($variable);

            $params = array();
            foreach($r->getParameters() as $p) {
                $s = '';
                if($p->isArray()) {
                    $s .= 'array ';
                } else if($p->getClass()) {
                    $s .= $p->getClass()->name . ' ';
                }
                if($p->isPassedByReference()){
                    $s .= '&';
                }
                $s .= '$' . $p->name;

                if($p->isDefaultValueAvailable()) {
                    $s .= ' = ' . var_export($p->getDefaultValue(), TRUE);
                }
                $params []= $s;
            }
            $param = implode(', ', $params);

            $output .= strtr("<b class='obj'>closure</b>(<span class='other'>:var</span>) {<span class='dump' onclick='toggle(this)'>#4 </span><span> \n", [":var" => $param]);

            $methods = [
                'returnsReference' => "returnsReference",
                'returnType' => "getReturnType",
                'class' =>  "getClosureScopeClass",
                //'this' => "getClosureThis"
            ];

            foreach ($methods as $k => $m){
                if (method_exists($r, $m) && false !== ($m = $r->$m()) && null !== $m) {
                    $value = $m instanceof \Reflector ? $m->name : $m;

                    $output .= str_repeat($space, $tab) . $this->output($value, $k,$tab + 1) ."\n";
                }
            }

            if ($v = $r->getStaticVariables()) {
                $output .= str_repeat($space, $tab) . $this->output($v, 'use',$tab + 1) ."\n";
            }

            $output .= str_repeat($space, $tab) ."file <span class='other'>". $r->getFileName() ."</span>\n";

            $output .= str_repeat($space, $tab) ."line <span class='order'>". $r->getStartLine().' to '.$r->getEndLine()."</span>\n";

            return $output . str_repeat($space, $tab - 1) . "</span>}";
        }

        if (gettype($variable) == "object") {
            $output .= strtr("class <b class='obj'>:class</b>", [":class" => get_class($variable)]);

            if (get_parent_class($variable)) {
                $output .= strtr(" extends <b class='obj'>:parent</b>", [":parent" => get_parent_class($variable)]);
            }

            $output .= " {";

            foreach ($this->_skips as $skip) {
                if($variable instanceof $skip){
                    return $output . " ... }";
                }
            }

//            if (in_array(get_class($variable), $this->_methods)) {
//                return $output . " listed... }";
//            }

            if ($variable instanceof \stdClass) {
                $attr = get_class_methods($variable);

                $output .= strtr("<span class='dump obj' onclick='toggle(this)'>#:count </span><span>", [":count" => count(get_object_vars($variable))]);

                $output .= "\n";

                foreach (get_object_vars($variable) as $key => $value) {
                    $output .= str_repeat($space, $tab) . strtr("-><span class=':type'>:type</span> <span class='variable'>:key</span> = ", [":key" => $key, ":type" => "public"]);
                    $output .= $this->output($value, "", $tab + 1) . "\n";
                }

                foreach ($attr as $value) {
                    $this->_methods[] = get_class($variable);
                    $output .= str_repeat($space, $tab + 1) . strtr("-><span class='method'>:method</span>();\n", [":method" => $value]);
                }

            } else {
                $this->_methods[] = get_class($variable);

                $reflect = new \ReflectionClass($variable);

                if($tab > $this->maxDepth){
                    return "{ $reflect->name... }";
                }

                $attr = $reflect->getMethods($this->_filters);

                $output .= strtr("<span class='dump obj' onclick='toggle(this)'>#:count </span><span>", [":count" => count($attr)]);
                $output .= "\n";

                foreach ($attr as $value) {
                    $this->_methods[] = $value->class;

                    $type = implode(' ', \Reflection::getModifierNames($value->getModifiers()));
                    $cursor = '->';
                    if (strpos($type, 'static')) {
                        $type = 'static';
                        $cursor = '::';
                    }

                    $params = $reflect->getMethod($value->name)->getParameters();
                    $temp = implode(', ', $params);
                    $temp = preg_replace('!\s+!', ' ', strip_tags($temp));
                    $temp = preg_replace('/Parameter #[0-9] \[ | \]/', '$1', $temp);
                    $temp = preg_replace('/,/', '<span style="color:#fd8b19">,</span>', $temp);
                    $temp = "<span class='other'>" . $temp . '</span>';

                    $output .= str_repeat($space, $tab) . strtr($cursor . "<span class=':type'>:type</span> <b class='method'>:method</b>(:param) \n", [":method" => $value->name, ":param" => $temp, ":type" => $type]);
                }

                $props = $reflect->getProperties($this->_filters);

                foreach ($props as $index => $property) {
                    $property->setAccessible(true);

                    $key = $property->getName();
                    $type = implode(' ', \Reflection::getModifierNames($property->getModifiers()));
                    $cursor = '->';
                    if (strpos($type, 'static')) {
                        $type = 'static';
                        $cursor = '::';
                    }

                    $output .= str_repeat($space, $tab) . strtr($cursor . "<span class=':type'>:type</span> <b class='variable'>$:key</b> = ", [":key" => $key, ":type" => $type]);

                    $output .= $this->output($property->getValue($variable), "", $tab + 1) . "\n";
                }
            }

            return $output . str_repeat($space, $tab - 1) . "</span>}";
        }

        if (is_string($variable)) {

            if (strpos($variable, '<pre data-debug') !== false) {
                return $output . $variable;
            }

            return $output . strtr("<b class='str'>string</b> (<span class='str'>:length</span>) \"<span class='str'>:var</span>\"", [":length" => strlen($variable), ":var" => nl2br(htmlentities($variable, ENT_IGNORE, "utf-8"))]);
        }

        if (is_int($variable)) {
            return $output . strtr("<b class='int'>int</b> (<span class='int'>:var</span>)", [":var" => $variable]);
        }

        if (is_float($variable)) {
            return $output . strtr("<b class='float'>float</b> (<span class='float'>:var</span>)", [":var" => $variable]);
        }

        if (is_numeric($variable)) {
            return $output . strtr("<b class='num'>numeric string</b> (<span class='num'>:length</span>) \"<span class='num'>:var</span>\"", [":length" => strlen($variable), ":var" => $variable]);
        }

        if (is_bool($variable)) {
            return $output . strtr("<b class='bool'>bool</b> (<span class='bool'>:var</span>)", [":var" => ($variable ? "TRUE" : "FALSE")]);
        }

        if (is_null($variable)) {
            return $output . strtr("<b class='null'>NULL</b>", []);
        }

        return $output . strtr("(<span class='other'>:var</span>)", [":var" => $variable]);
    }

    public function toJson($variable)
    {
        return json_encode($variable, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    protected function getDumpHeader()
    {
        return '<style>
            .dump{cursor: pointer;color:#fd8b19!important}
            .dump:hover{font-weight: bold}
            .dump:after{content: \'▼\'}
            .dump + span{}
            .dump.dump-show:after {content: \'▶\'}
            .dump.dump-show + span {display: none}
            .btn-dump{position: absolute;right: 0;top: 0;z-index: 1;font-size: 11px}
            .arr{color:#98fb98}
            .bool{color:#ff6868}
            .float{color:#ffb939}
            .int{color:#ffb939}
            .null{color:#fff}
            .num{color:#ffb939}
            .obj{color:#fdcc59}
            .method{color:#1290bf}
            .variable{color:#7ec699}
            .other{color:#fff}
            .res{color:#BCD42A}
            .other{color:#fff}
            .str{color:#BCD42A}
            .private{color:#d4b1c9}
            .protected{color:#f384d1}
            .public{color:#ff05b2}
            .static{color:#da3966}
            .pre{}
        </style>';
    }

    protected function getDumpFooter()
    {
        return "<script>
            function toggle(e) {
                return e.classList.toggle('dump-show');
            }
            var dumps = document.getElementsByClassName('dump');
            var active = 0;
            function collapseAll(e) {
                if (active === 0) {
                    active = 1;
                    Array.prototype.forEach.call(dumps, function(el) {
                        el.classList.add('dump-show');
                    });
                }else{
                    active = 0;
                    Array.prototype.forEach.call(dumps, function(el) {
                        el.classList.remove('dump-show');
                    });
                }
            }
        </script>";
    }
}

class Frame
{
    protected $frame;
    protected $fileContentsCache;
    protected $comments = [];

    public function __construct(array $frame)
    {
        $this->frame = $frame;
    }

    public function getRawFrame()
    {
        return $this->frame;
    }

    public function getFileLines($start = 0, $length = null)
    {
        if (null !== ($contents = $this->getFileContents())) {
            $lines = explode("\n", $contents);

            if ($length !== null) {
                $start = (int)$start;
                $length = (int)$length;
                if ($start < 1) {
                    $start = 1;
                }

                if ($length <= 0) {
                    throw new InvalidArgumentException(
                        "\$length($length) cannot be lower or equal to 0"
                    );
                }

                $lines = array_slice($lines, $start, $length, true);
            }

            return $lines;
        }
    }

    public function getFileContents()
    {
        if ($this->fileContentsCache === null && $filePath = $this->getFile()) {
            if ($filePath === "Unknown" || $filePath === '[internal]') {
                return null;
            }

            $this->fileContentsCache = file_get_contents($filePath);
        }

        return $this->fileContentsCache;
    }

    public function getFile()
    {
        if (empty($this->frame['file'])) {
            return null;
        }

        $file = $this->frame['file'];

        if (preg_match('/^(.*)\((\d+)\) : (?:eval\(\)\'d|assert) code$/', $file, $matches)) {
            $file = $this->frame['file'] = $matches[1];
            $this->frame['line'] = (int)$matches[2];
        }
        return $file;
    }

    public function equals(Frame $frame)
    {
        if (!$this->getFile() || $this->getFile() === 'Unknown' || !$this->getLine()) {
            return false;
        }
        return $frame->getFile() === $this->getFile() && $frame->getLine() === $this->getLine();
    }

    public function getLine()
    {
        return isset($this->frame['line']) ? $this->frame['line'] : null;
    }
}

class HigherOrderTapProxy
{
    /** @var ReflectionClass  */
    public $reflection;
    public $object;
    public $debugs = [];

    public function __construct($object)
    {
        if(!is_object($object)){
            throw new Exception('Object must be type of class');
        }
        $this->object = $object;
        $this->reflection = new \ReflectionClass(get_class($object));

        try {
            $file = $this->reflection->getFileName();
            $start = $this->reflection->getStartLine();
            $end = $this->reflection->getEndLine();
        } catch (\Exception $e) {
            $file = $start = $end = '';
        }

        $this->debugs[] = [
            '_file' => $file . ' : ' . $start . '-' . $end,
            '_object' => $this->object
        ];
    }

    public function __set($property, $value)
    {
        $this->object->{$property} = $value;
        $this->reflection = new \ReflectionClass(get_class($this->object));
        return $this->object;
    }

    public function __get($property)
    {
        $reflectionProperty = $this->reflection->getProperty($property);
        $reflectionProperty->setAccessible(true);
        $result = $reflectionProperty->getValue($this->object);

        $this->debugs[] = [
            '_property' => $reflectionProperty->class . ' -> ' . $property,
            '_result' => $result,
        ];

        return $result;
    }

    public function __call($method, $params)
    {
        $reflectionMethod = $this->reflection->getMethod($method);
        $reflectionMethod->setAccessible(true);
        $result = $reflectionMethod->invokeArgs($this->object, $params);

        try {
            $file = $reflectionMethod->getFileName();
            $start = $reflectionMethod->getStartLine();
            $end = $reflectionMethod->getEndLine();
            $line = floor(($end - $start) / 2) + $reflectionMethod->getStartLine();
            $html = htmlFile($file, $start - 1, $end, $line);
        } catch (\Exception $e) {
            $file = $start = $end = $html = '';
        }

        $this->debugs[] = [
            '_file' => $file . ' : ' . $start . '-' . $end,
            '_method' => $reflectionMethod->class . ' -> ' . $reflectionMethod->name,
            '_params' => $params,
            '_result' => $result,
            '_html' => $html,
        ];

        return $this->object;
    }

    public function dump()
    {
        return call_user_func_array('xz', $this->debugs);
    }
}

/**
 * @param $object
 * @return HigherOrderTapProxy|mixed
 */
function chase($object)
{
    return new HigherOrderTapProxy($object);
}
