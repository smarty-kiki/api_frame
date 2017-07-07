<?php

function _command_prepare_arguments()
{/*{{{*/
    static $file_name = '';
    static $command = '';
    static $arguments = [];

    if (! $file_name) {
        global $argv;
        $file_name = array_shift($argv);
        $command = array_shift($argv);

        foreach ($argv as $num => $argument) {

            switch (true) {

            case preg_match('/^-([a-zA-Z]+)$/', $argument, $res):
                $arguments[$res[1]] = true;
                break;

            case preg_match('/^--([a-zA-Z_]+)=(.*)$/', $argument, $res):
                $arguments[$res[1]] = $res[2];
                break;
            }
        }
    }

    return [$file_name, $command, $arguments];
}/*}}}*/

function command($rule, $description, closure $action)
{/*{{{*/
    list($file_name, $command, $arguments) = _command_prepare_arguments();

    if ($command === $rule) {
        $parameters = [];

        $reflector = new ReflectionFunction($action);

        foreach ($reflector->getParameters() as $parameter) {
            $parameters[$parameter->name] = $arguments[$parameter->name];
        }

        exit(call_user_func_array($action, $parameters));
    } else {
        command_not_found($rule, $description);
    }
}/*}}}*/

function if_command_not_found(closure $action = null)
{/*{{{*/
    static $container = null;

    if (!empty($action)) {
        return $container = $action;
    }

    return $container;
}/*}}}*/

function command_not_found($rule = null, $description = null)
{/*{{{*/
    static $rules = [];
    static $descriptions = [];

    if (is_null($rule) && is_null($description)) {
        exit(call_user_func(if_command_not_found(), $rules, $descriptions));
    } else {
        $rules[] = $rule;
        $descriptions[] = $description;
    }
}/*}}}*/

function command_read($prompt, $default = true, array $options = [])
{/*{{{*/
    if ($options) {
        $prompt = "$prompt\n\n";
        foreach ($options as $key => $option) {
            $prompt .= "  $key) $option\n";
        }

        $prompt .= "\n> ";

        do {
            fwrite(STDOUT, $prompt);  
            $result = trim(fgets(STDIN));  
            $result = ($result === '')? $default: $result;
        } while (! isset($options[$result]));

        return $options[$result];
    } else {
        $prompt = "$prompt\n> ";
        fwrite(STDOUT, $prompt);  
        $result = trim(fgets(STDIN));  
        return ($result === '')? $default: $result;
    }
}/*}}}*/

function command_read_bool($prompt)
{/*{{{*/
    $res = command_read("$prompt [y/n]?", 'y');

    return ('y' === $res);
}/*}}}*/
