<?php

/**
 * Smarty Internal Plugin Compile Special Smarty Variable
 *
 * Compiles the special $smarty variables
 *
 *
 * @package Compiler
 * @author Uwe Tews
 */

/**
 * Smarty Internal Plugin Compile special Smarty Variable Class
 *
 *
 * @package Compiler
 */
class Smarty_Internal_Compile_Private_Special_Variable extends Smarty_Internal_CompileBase
{

    /**
     * Compiles code for the special $smarty variables
     *
     * @param array $args     array with attributes from parser
     * @param object $compiler compiler object
     * @param string $parameter string with optional array indexes
     * @return string compiled code
     */
    public function compile($args, $compiler, $parameter)
    {
        $_index = preg_split("/\]\[/", substr($parameter, 1, strlen($parameter) - 2));
        $compiled_ref = ' ';
        $variable = trim($_index[0], "'");
        switch ($variable) {
            case 'foreach':
            case 'section':
                return "\$_scope->smarty->value$parameter";
            case 'capture':
                return "Smarty::\$_smarty_vars$parameter";
            case 'now':
                return 'time()';
            case 'cookies':
                if (isset($compiler->tpl_obj->security_policy) && !$compiler->tpl_obj->security_policy->allow_super_globals) {
                    $compiler->trigger_template_error("(secure mode) super globals not permitted");
                    break;
                }
                $compiled_ref = '@$_COOKIE';
                break;

            case 'get':
            case 'post':
            case 'env':
            case 'server':
            case 'session':
            case 'request':
                if (isset($compiler->tpl_obj->security_policy) && !$compiler->tpl_obj->security_policy->allow_super_globals) {
                    $compiler->trigger_template_error("(secure mode) super globals not permitted");
                    break;
                }
                $compiled_ref = '@$_' . strtoupper($variable);
                break;

            case 'template':
                return 'basename($_smarty_tpl->source->filepath)';

            case 'current_dir':
                return 'dirname($_smarty_tpl->source->filepath)';

            case 'is_cached':
                return '$_smarty_tpl->cached->valid';

            case 'is_nocache':
                return '$_smarty_tpl->is_nocache';

            case 'version':
                $_version = Smarty::SMARTY_VERSION;
                return "'$_version'";

            case 'const':
                if (isset($compiler->tpl_obj->security_policy) && !$compiler->tpl_obj->security_policy->allow_constants) {
                    $compiler->trigger_template_error("(secure mode) constants not permitted");
                    break;
                }
                return '@constant(' . $_index[1] . ')';

            case 'config':
                $name = trim($_index[1], "'");
                if (isset($_index[2])) {
                    return "\$_scope->___config_var_{$name}[{$_index[2]}]";
                } else {
                    return "\$_scope->___config_var_{$name}";
                }
            case 'ldelim':
                $_ldelim = $compiler->tpl_obj->left_delimiter;
                return "'$_ldelim'";

            case 'rdelim':
                $_rdelim = $compiler->tpl_obj->right_delimiter;
                return "'$_rdelim'";

            case 'block':
                $output = '';
                if (trim($_index[1], "'") == 'parent') {
                    $output = $compiler->compileTag('private_block_parent', array(), array());
                } elseif (trim($_index[1], "'") == 'child') {
                    $output = $compiler->compileTag('private_block_child', array(), array());
                } else {
                    $compiler->trigger_template_error('$smarty.block.' . trim($_index[1], "'") . ' is invalid');
                }
                return $output;

            default:
                $compiler->trigger_template_error('$smarty.' . trim($_index[0], "'") . ' is invalid');
                break;
        }
        if (isset($_index[1])) {
            array_shift($_index);
            foreach ($_index as $_ind) {
                $compiled_ref = $compiled_ref . "[$_ind]";
            }
        }
        return $compiled_ref;
    }

}
