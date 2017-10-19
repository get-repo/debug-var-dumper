<?php
// @codeCoverageIgnoreStart
if (! function_exists('p')) {

    function p($var, $exit = true, $output = true, $cpt = 0)
    {
        $_cpt = $cpt;
        $is_cli = defined('_CLI') ? _CLI : (php_sapi_name() == 'cli');

        // display params
        $display_type_color = 'purple_bold';
        $display_keys_color = $is_cli ? 'white' : 'gray';
        $display_type_color_additional_infos = $is_cli ? 'white' : 'gray';
        $spaces_array_keys = 5;

        $n = ($is_cli ? chr(10) : '<br>');

        if ($_cpt == 0) {
            $o = $is_cli ? '' : '<pre>';
        } else {
            $o = str_repeat('', $cpt);
        }

        $cpt ++;
        switch (strtolower(getType($var))) {
            case 'null':
                $o .= l('NULL', 'cyan_bold', true);
                break;

            case 'boolean':
                $o .= $var ? l('TRUE', 'green_bold', true) : l('FALSE', 'red_bold', true);
                break;

            case 'double':
            case 'integer':
                $o .= l($var, $is_cli ? 'purple_bold' : 'purple', true);
                break;

            case 'array':
                $o .= l("Array" . l('#' . count($var), $display_type_color_additional_infos, true) . " [$n", $display_type_color, true);
                $ind = str_repeat(str_repeat(' ', $spaces_array_keys), $cpt);
                foreach ($var as $key => $val) {
                    $key = is_numeric($key) ? $key : "'" . htmlentities($key) . "'";
                    $o .= $ind . l($key, $display_keys_color, true) . ' => ';
                    $o .= p($val, false, false, $cpt) . $n;
                }
                $o .= str_repeat(str_repeat(' ', $spaces_array_keys), $cpt - 1) . l(']', $display_type_color, true) . l("d$cpt", $display_type_color_additional_infos, true);
                break;

            case 'object':
                $class_name = $class = get_class($var);
                $rClass = new ReflectionClass($class);
                $filename = $rClass->getFileName();
                $GLOBALS['debug_object_filename'][$class] = $filename;
                if ($filename) {
                    $class_name = $is_cli ? $class : "<a style='text-decoration:none;' href='file://{$filename}'>" . l($class_name, $display_type_color, true) . "</a>";
                }
                $o .= l("Object $class_name {" . $n, $display_type_color, true);
                $ind = str_repeat(str_repeat(' ', $spaces_array_keys), $cpt);
                $rMethods = [
                    // 'getConstants' => null,
                    'getProperties' => function ($rProperty, $var, $color) {
                        if ($rProperty->isPublic()) {
                            $key = $value = '';
                            if ($rProperty->isStatic()) {
                                $key .= l('static ', $color, true);
                                $value = $rProperty->getValue();
                            } else {
                                $value = $rProperty->getValue($var);
                            }
                            $key .= '$' . $rProperty->getName();
                            return [
                                $key,
                                $value
                            ];
                        }
                        return false;
                    }
                ];
                foreach ($rMethods as $rMethod => $callback) {
                    foreach ($rClass->$rMethod() as $key => $val) {
                        if (is_object($val)) {
                            if (! $callback || ($cpt > 2 && isset($GLOBALS['debug_object_filename'][$val->class]))) {
                                break;
                            }
                            $val = call_user_func($callback, $val, $var, $display_type_color_additional_infos);
                            if (! is_array($val) || count($val) != 2) {
                                continue;
                            }
                            $key = $val[0];
                            $val = $val[1];
                        }
                        $o .= $ind . l($key, $display_keys_color, true) . ' = ';
                        $o .= p($val, false, false, $cpt);
                        $o .= $n;
                    }
                }
                $o .= str_repeat(str_repeat(' ', $spaces_array_keys), $cpt - 1) . l('}', $display_type_color, true);
                break;

            case 'string':
            default:
                if (! $is_cli) {
                    $var = htmlentities($var);
                }
                $o .= l($var ? $var : "'{$var}'", null, true);
                break;
        }

        if ($_cpt == 0) {
            $o .= $is_cli ? chr(10) : '</pre>';
        }

        if ($output) {
            print_r($o);
        }

        if ($exit) {
            exit();
        }

        return $o;
    }
}

if (! function_exists('l')) {

    function l($message, $color = null, $return = false)
    {
        $is_cli = defined('_CLI') ? _CLI : (php_sapi_name() == 'cli');
        $default_color = ($is_cli ? 'white_bold' : 'black_bold');

        $color = (string) ($color === null) ? $default_color : strtolower($color);

        $_colors = [
            'white' => [
                37,
                '#EEE'
            ],
            'black' => [
                30,
                '#000'
            ],
            'gray' => [
                90,
                '#999'
            ],
            'red' => [
                31
            ],
            'green' => [
                32
            ],
            'yellow' => [
                33
            ],
            'blue' => [
                34
            ],
            'purple' => [
                35
            ],
            'cyan' => [
                36
            ]
        ];
        // pre defined colors
        $colors = [
            'title' => $is_cli ? '1;33m' : 'color:blue;font-weight:bold;',
            'error' => $is_cli ? '1;41m' : 'color:red;font-weight:bold;'
        ];

        foreach ($_colors as $name => $_color) {
            foreach ([
                '' => null,
                'underline' => 4,
                'blink' => 5
            ] as $sub_name => $_sub_code) {
                $_cli = $_color[0];
                $_html = isset($_color[1]) ? $_color[1] : $name;
                $text_decoration = $sub_name ? $sub_name : 'none';
                $sub_name = $sub_name ? "_$sub_name" : '';
                $colors["$name$sub_name"] = $is_cli ? ($_sub_code ?: 0) . ";${_cli}m" : "color:$_html;text-decoration:$text_decoration;";
                $colors["${name}_bold$sub_name"] = $is_cli ? ($_sub_code ?: 1) . ";${_cli}m" : "color:$_html;font-weight:bold;text-decoration:$text_decoration;";
                $_cli += 10;
                $colors["${name}_bar$sub_name"] = $is_cli ? ($_sub_code ?: 1) . ";${_cli}m" : "color:#FFF;background-color:$_html;font-weight:bold;text-decoration:$text_decoration;";
            }
        }

        $color = isset($colors[$color]) ? $colors[$color] : $colors[$default_color];
        $message = ($is_cli ? "\033[$color" : "<span style='$color'>") . $message . ($is_cli ? "\033[0;0m" : '</span>');
        if (! $return)
            echo $message;

        return $message;
    }
}
// @codeCoverageIgnoreEnd
