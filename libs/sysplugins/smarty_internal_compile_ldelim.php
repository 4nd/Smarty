<?php

/**
 * Smarty Internal Plugin Compile Ldelim
 *
 * Compiles the {ldelim} tag
 *
 *
 * @package Compiler
 * @author Uwe Tews
 */

/**
 * Smarty Internal Plugin Compile Ldelim Class
 *
 *
 * @package Compiler
 */
class Smarty_Internal_Compile_Ldelim extends Smarty_Internal_CompileBase
{

    /**
     * Compiles code for the {ldelim} tag
     *
     * This tag does output the left delimiter
     * @param array $args     array with attributes from parser
     * @param object $compiler compiler object
     * @return string compiled code
     */
    public function compile($args, $compiler)
    {
        $_attr = $this->getAttributes($compiler, $args);
        if ($_attr['nocache'] === true) {
            $compiler->trigger_template_error('nocache option not allowed', $compiler->lex->taglineno);
        }
        // this tag does not return compiled code
        $compiler->has_code = true;

        $this->iniTagCode($compiler);

        $this->php("echo \$_smarty_tpl->left_delimiter;")->newline();

        return $this->returnTagCode($compiler);
    }

}
