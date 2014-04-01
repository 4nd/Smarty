<?php

/**
 * Smarty Internal Plugin Compile Eval
 *
 * Compiles the {eval} tag.
 *
 *
 * @package Compiler
 * @author Uwe Tews
 */

/**
 * Smarty Internal Plugin Compile Eval Class
 *
 *
 * @package Compiler
 */
class Smarty_Internal_Compile_Eval extends Smarty_Internal_CompileBase
{

    /**
     * Attribute definition: Overwrites base class.
     *
     * @var array
     * @see $tpl_obj
     */
    public $required_attributes = array('var');

    /**
     * Attribute definition: Overwrites base class.
     *
     * @var array
     * @see $tpl_obj
     */
    public $optional_attributes = array('assign');

    /**
     * Attribute definition: Overwrites base class.
     *
     * @var array
     * @see $tpl_obj
     */
    public $shorttag_order = array('var', 'assign');

    /**
     * Compiles code for the {eval} tag
     *
     * @param array $args     array with attributes from parser
     * @param object $compiler compiler object
     * @return string compiled code
     */
    public function compile($args, $compiler)
    {
        $this->required_attributes = array('var');
        $this->optional_attributes = array('assign');
        // check and get attributes
        $_attr = $this->getAttributes($compiler, $args);
        if (isset($_attr['assign'])) {
            // output will be stored in a smarty variable instead of beind displayed
            $_assign = $_attr['assign'];
            // set flag that variable container must be cloned
            $compiler->must_clone_vars = true;
        }
        $this->iniTagCode($compiler);

        // create template object
        $this->php("\$tpl_obj = \$_smarty_tpl->createTemplate('eval:'." . $_attr['var'] . ", \$_smarty_tpl);")->newline();
        //was there an assign attribute?
        if (isset($_assign)) {
            $this->php("\$_smarty_tpl->assign($_assign,\$tpl_obj->fetch());")->newline();
        } else {
            $this->php("echo \$tpl_obj->fetch();")->newline();
        }
        $this->php("unset(\$tpl_obj->source, \$tpl_obj->compiled, \$tpl_obj->compiler, \$tpl_obj);")->newline();

        return $this->returnTagCode($compiler);
    }

}
