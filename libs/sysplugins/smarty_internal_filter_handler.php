<?php

/**
 * Smarty Internal Plugin Filter Handler
 *
 * Smarty filter handler class
 *
 *
 * @package PluginsInternal
 * @author Uwe Tews
 */

/**
 * Class for filter processing
 *
 *
 * @package PluginsInternal
 */
class Smarty_Internal_Filter_Handler
{

    /**
     * Run filters over content
     *
     * The filters will be lazy loaded if required
     * class name format: Smarty_FilterType_FilterName
     * plugin filename format: filtertype.filtername.php
     * Smarty2 filter plugins could be used
     *
     * @param string $type     the type of filter ('pre','post','output') which shall run
     * @param string $content  the content which shall be processed by the filters
     * @param Smarty $tpl_obj template object
     * @throws SmartyException
     * @return string the filtered content
     */
    public static function runFilter($type, $content, Smarty $tpl_obj)
    {
        $output = $content;
        // loop over autoload filters of specified type
        if (!empty($tpl_obj->autoload_filters[$type])) {
            foreach ((array)$tpl_obj->autoload_filters[$type] as $name) {
                $plugin_name = "Smarty_{$type}filter_{$name}";
                if ($tpl_obj->_loadPlugin($plugin_name)) {
                    if (function_exists($plugin_name)) {
                        // use loaded Smarty2 style plugin
                        $output = $plugin_name($output, $tpl_obj);
                    } elseif (class_exists($plugin_name, false)) {
                        // loaded class of filter plugin
                        $output = call_user_func(array($plugin_name, 'execute'), $output, $tpl_obj);
                    }
                } else {
                    // nothing found, throw exception
                    throw new SmartyException("Unable to load filter {$plugin_name}");
                }
            }
        }
        // loop over registered filters of specified type
        if (!empty($tpl_obj->registered_filters[$type])) {
            foreach ($tpl_obj->registered_filters[$type] as $key => $name) {
                if (is_array($tpl_obj->registered_filters[$type][$key])) {
                    $output = call_user_func($tpl_obj->registered_filters[$type][$key], $output, $tpl_obj);
                } else {
                    $output = $tpl_obj->registered_filters[$type][$key]($output, $tpl_obj);
                }
            }
        }
        // return filtered output
        return $output;
    }

}
