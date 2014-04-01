<?php

/**
 * Smarty Internal Plugin Compile Foreach
 *
 * Compiles the {foreach} {foreachelse} {/foreach} tags
 *
 *
 * @package Compiler
 * @author Uwe Tews
 */

/**
 * Smarty Internal Plugin Compile Foreach Class
 *
 *
 * @package Compiler
 */
class Smarty_Internal_Compile_Foreach extends Smarty_Internal_CompileBase
{

    /**
     * Attribute definition: Overwrites base class.
     *
     * @var array
     * @see $tpl_obj
     */
    public $required_attributes = array('from', 'item');

    /**
     * Attribute definition: Overwrites base class.
     *
     * @var array
     * @see $tpl_obj
     */
    public $optional_attributes = array('name', 'key');

    /**
     * Attribute definition: Overwrites base class.
     *
     * @var array
     * @see $tpl_obj
     */
    public $shorttag_order = array('from', 'item', 'key', 'name');

    /**
     * Compiles code for the {foreach} tag
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

        // set flag that variable container must be cloned
        $compiler->must_clone_vars = true;

        $from = $_attr['from'];
        $item = trim($_attr['item'], '\'"');
        if ($item == substr($from, 24, -7)) {
            $compiler->trigger_template_error("'item' variable '\${$item}' may not be the same variable as at 'from'", $compiler->lex->taglineno);
        }

        if (isset($_attr['key'])) {
            $key = trim($_attr['key'], '\'"');
        } else {
            $key = null;
        }

        $this->openTag($compiler, 'foreach', array('foreach', $compiler->nocache, $item, $key));
        // maybe nocache because of nocache variables
        $compiler->nocache = $compiler->nocache | $compiler->tag_nocache;

        $this->iniTagCode($compiler);

        if (isset($_attr['name'])) {
            $name = $_attr['name'];
            $has_name = true;
            $SmartyVarName = '$smarty.foreach.' . trim($name, '\'"') . '.';
        } else {
            $name = null;
            $has_name = false;
        }
        $ItemVarName = '$' . $item . '@';
        // evaluates which Smarty variables and properties have to be computed
        if ($has_name) {
            $usesSmartyFirst = strpos($compiler->lex->data, $SmartyVarName . 'first') !== false;
            $usesSmartyLast = strpos($compiler->lex->data, $SmartyVarName . 'last') !== false;
            $usesSmartyIndex = strpos($compiler->lex->data, $SmartyVarName . 'index') !== false;
            $usesSmartyIteration = strpos($compiler->lex->data, $SmartyVarName . 'iteration') !== false;
            $usesSmartyShow = strpos($compiler->lex->data, $SmartyVarName . 'show') !== false;
            $usesSmartyTotal = strpos($compiler->lex->data, $SmartyVarName . 'total') !== false;
        } else {
            $usesSmartyFirst = false;
            $usesSmartyLast = false;
            $usesSmartyTotal = false;
            $usesSmartyShow = false;
        }

        $usesPropFirst = $usesSmartyFirst || strpos($compiler->lex->data, $ItemVarName . 'first') !== false;
        $usesPropLast = $usesSmartyLast || strpos($compiler->lex->data, $ItemVarName . 'last') !== false;
        $usesPropIndex = $usesPropFirst || strpos($compiler->lex->data, $ItemVarName . 'index') !== false;
        $usesPropIteration = $usesPropLast || strpos($compiler->lex->data, $ItemVarName . 'iteration') !== false;
        $usesPropShow = strpos($compiler->lex->data, $ItemVarName . 'show') !== false;
        $usesPropTotal = $usesSmartyTotal || $usesSmartyShow || $usesPropShow || $usesPropLast || strpos($compiler->lex->data, $ItemVarName . 'total') !== false;
        // generate output code
        $this->php("\$_scope->$item = new Smarty_Variable;")->newline();
        $this->php("\$_scope->{$item}->_loop = false;")->newline();
        if ($key != null) {
            $this->php("\$_scope->$key = new Smarty_Variable;")->newline();
        }
        $this->php("\$_from = $from;")->newline();
        $this->php("if (!is_array(\$_from) && !is_object(\$_from)) {")->newline()->indent()->php("settype(\$_from, 'array');")->newline()->outdent()->php("}")->newline();
        if ($usesPropTotal) {
            $this->php("\$_scope->{$item}->total = \$this->_count(\$_from);")->newline();
        }
        if ($usesPropIteration) {
            $this->php("\$_scope->{$item}->iteration = 0;")->newline();
        }
        if ($usesPropIndex) {
            $this->php("\$_scope->{$item}->index = -1;")->newline();
        }
        if ($usesPropShow) {
            $this->php("\$_scope->{$item}->show = (\$_scope->{$item}->total > 0);")->newline();
        }
        if ($has_name) {
            if ($usesSmartyTotal) {
                $this->php("\$_scope->smarty->value['foreach'][{$name}]['total'] = \$_scope->{$item}->total;")->newline();
            }
            if ($usesSmartyIteration) {
                $this->php("\$_scope->smarty->value['foreach'][{$name}]['iteration'] = 0;")->newline();
            }
            if ($usesSmartyIndex) {
                $this->php("\$_scope->smarty->value['foreach'][{$name}]['index'] = -1;")->newline();
            }
            if ($usesSmartyShow) {
                $this->php("\$_scope->smarty->value['foreach'][{$name}]['show']=(\$_scope->{$item}->total > 0);")->newline();
            }
        }
        $this->php("foreach (\$_from as \$_scope->{$item}->key => \$_scope->{$item}->value){")->indent()->newline();
        $this->php("\$_scope->{$item}->_loop = true;")->newline();
        if ($key != null) {
            $this->php("\$_scope->{$key}->value = \$_scope->{$item}->key;")->newline();
        }
        if ($usesPropIteration) {
            $this->php("\$_scope->{$item}->iteration++;")->newline();
        }
        if ($usesPropIndex) {
            $this->php("\$_scope->{$item}->index++;")->newline();
        }
        if ($usesPropFirst) {
            $this->php("\$_scope->{$item}->first = \$_scope->{$item}->index === 0;")->newline();
        }
        if ($usesPropLast) {
            $this->php("\$_scope->{$item}->last = \$_scope->{$item}->iteration === \$_scope->{$item}->total;")->newline();
        }
        if ($has_name) {
            if ($usesSmartyFirst) {
                $this->php("\$_scope->smarty->value['foreach'][{$name}]['first'] = \$_scope->{$item}->first;")->newline();
            }
            if ($usesSmartyIteration) {
                $this->php("\$_scope->smarty->value['foreach'][{$name}]['iteration']++;")->newline();
            }
            if ($usesSmartyIndex) {
                $this->php("\$_scope->smarty->value['foreach'][{$name}]['index']++;")->newline();
            }
            if ($usesSmartyLast) {
                $this->php("\$_scope->smarty->value['foreach'][{$name}]['last'] = \$_scope->{$item}->last;")->newline();
            }
        }
        return $this->returnTagCode($compiler);
    }

}

/**
 * Smarty Internal Plugin Compile Foreachelse Class
 *
 *
 * @package Compiler
 */
class Smarty_Internal_Compile_Foreachelse extends Smarty_Internal_CompileBase
{

    /**
     * Compiles code for the {foreachelse} tag
     *
     * @param array $args array with attributes from parser
     * @param object $compiler compiler object
     * @param array $parameter array with compilation parameter
     * @return string compiled code
     */
    public function compile($args, $compiler, $parameter)
    {
        // check and get attributes
        $_attr = $this->getAttributes($compiler, $args);

        list($openTag, $nocache, $item, $key) = $this->closeTag($compiler, array('foreach'));
        $this->openTag($compiler, 'foreachelse', array('foreachelse', $nocache, $item, $key));

        $this->iniTagCode($compiler);

        $this->outdent()->php("}")->newline();
        $this->php("if (!\$_scope->{$item}->_loop) {")->newline()->indent();

        return $this->returnTagCode($compiler);
    }

}

/**
 * Smarty Internal Plugin Compile Foreachclose Class
 *
 *
 * @package Compiler
 */
class Smarty_Internal_Compile_Foreachclose extends Smarty_Internal_CompileBase
{

    /**
     * Compiles code for the {/foreach} tag
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

        list($openTag, $compiler->nocache, $item, $key) = $this->closeTag($compiler, array('foreach', 'foreachelse'));

        $this->iniTagCode($compiler);

        $this->outdent()->php("}")->newline();

        return $this->returnTagCode($compiler);
    }

}
