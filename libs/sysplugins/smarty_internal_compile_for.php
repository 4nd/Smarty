<?php

/**
 * Smarty Internal Plugin Compile For
 *
 * Compiles the {for} {forelse} {/for} tags
 *
 *
 * @package Compiler
 * @author Uwe Tews
 */

/**
 * Smarty Internal Plugin Compile For Class
 *
 *
 * @package Compiler
 */
class Smarty_Internal_Compile_For extends Smarty_Internal_CompileBase
{

    /**
     * Compiles code for the {for} tag
     *
     * Smarty 3 does implement two different syntax's:
     *
     * - {for $var in $array}
     * For looping over arrays or iterators
     *
     * - {for $x=0; $x<$y; $x++}
     * For general loops
     *
     * The parser is generating different sets of attribute by which this compiler can
     * determine which syntax is used.
     *
     * @param array $args      array with attributes from parser
     * @param object $compiler  compiler object
     * @param array $parameter array with compilation parameter
     * @return string compiled code
     */
    public function compile($args, $compiler, $parameter)
    {
        if ($parameter == 0) {
            $this->required_attributes = array('start', 'to');
            $this->optional_attributes = array('max', 'step');
        } else {
            $this->required_attributes = array('start', 'ifexp', 'var', 'step');
            $this->optional_attributes = array();
        }
        // check and get attributes
        $_attr = $this->getAttributes($compiler, $args);

        // set flag that variable container must be cloned
        $compiler->must_clone_vars = true;

        $this->iniTagCode($compiler);

        if ($parameter == 1) {
            $var2 = trim($_attr['var'], '\'"');
            foreach ($_attr['start'] as $_statement) {
                $var = trim($_statement['var'], '\'"');
                $this->php("\$_scope->{$var} = new Smarty_Variable ({$_statement['value']});")->newline();
            }
            $this->php("if ({$_attr['ifexp']}){")->newline()->indent();
            $this->php("for (\$_foo=true;{$_attr['ifexp']}; \$_scope->{$var2}->value{$_attr['step']}){")->newline()->indent();
        } else {
            $_statement = $_attr['start'];
            $var = trim($_statement['var'], '\'"');
            $this->php("\$_scope->{$var} = new Smarty_Variable (array());")->newline();
            if (isset($_attr['step'])) {
                $this->php("\$_scope->{$var}->step = {$_attr['step']};")->newline();
            } else {
                $this->php("\$_scope->{$var}->step = 1;")->newline();
            }
            if (isset($_attr['max'])) {
                $this->php("\$_scope->{$var}->total = (int)min(ceil((\$_scope->{$var}->step > 0 ? {$_attr['to']}+1 - ({$_statement['value']}) : {$_statement['value']}-({$_attr['to']})+1)/abs(\$_scope->{$var}->step)),{$_attr['max']});")->newline();
            } else {
                $this->php("\$_scope->{$var}->total = (int)ceil((\$_scope->{$var}->step > 0 ? {$_attr['to']}+1 - ({$_statement['value']}) : {$_statement['value']}-({$_attr['to']})+1)/abs(\$_scope->{$var}->step));")->newline();
            }
            $this->php("if (\$_scope->{$var}->total > 0){")->newline()->indent();
            $this->php("for (\$_scope->{$var}->value = {$_statement['value']}, \$_scope->{$var}->iteration = 1;\$_scope->{$var}->iteration <= \$_scope->{$var}->total;\$_scope->{$var}->value += \$_scope->{$var}->step, \$_scope->{$var}->iteration++){")->newline()->indent();
            $this->php("\$_scope->{$var}->first = \$_scope->{$var}->iteration == 1;")->newline();
            $this->php("\$_scope->{$var}->last = \$_scope->{$var}->iteration == \$_scope->{$var}->total;")->newline();
        }
        $this->openTag($compiler, 'for', array('for', $compiler->nocache));
        // maybe nocache because of nocache variables
        $compiler->nocache = $compiler->nocache | $compiler->tag_nocache;

        return $this->returnTagCode($compiler);
    }

}

/**
 * Smarty Internal Plugin Compile Forelse Class
 *
 *
 * @package Compiler
 */
class Smarty_Internal_Compile_Forelse extends Smarty_Internal_CompileBase
{

    /**
     * Compiles code for the {forelse} tag
     *
     * @param array $args      array with attributes from parser
     * @param object $compiler  compiler object
     * @param array $parameter array with compilation parameter
     * @return string compiled code
     */
    public function compile($args, $compiler, $parameter)
    {
        // check and get attributes
        $_attr = $this->getAttributes($compiler, $args);

        list($openTag, $nocache) = $this->closeTag($compiler, array('for'));
        $this->openTag($compiler, 'forelse', array('forelse', $nocache));

        $this->iniTagCode($compiler);

        $this->outdent()->php("}")->newline();
        $this->outdent()->php("} else {")->newline()->indent();

        return $this->returnTagCode($compiler);
    }

}

/**
 * Smarty Internal Plugin Compile Forclose Class
 *
 *
 * @package Compiler
 */
class Smarty_Internal_Compile_Forclose extends Smarty_Internal_CompileBase
{

    /**
     * Compiles code for the {/for} tag
     *
     * @param array $args      array with attributes from parser
     * @param object $compiler  compiler object
     * @param array $parameter array with compilation parameter
     * @return string compiled code
     */
    public function compile($args, $compiler, $parameter)
    {
        // check and get attributes
        $_attr = $this->getAttributes($compiler, $args);
        // must endblock be nocache?
        if ($compiler->nocache) {
            $compiler->tag_nocache = true;
        }

        list($openTag, $compiler->nocache) = $this->closeTag($compiler, array('for', 'forelse'));

        $this->iniTagCode($compiler);

        $this->outdent()->php("}")->newline();
        if ($openTag != 'forelse') {
            $this->outdent()->php("}")->newline();
        }

        return $this->returnTagCode($compiler);
    }

}
