<?php

/**
 * Smarty Internal Plugin Smarty Template Compiler Base
 *
 * This file contains the basic classes and methods for compiling Smarty templates with lexer/parser
 *
 *
 * @package Compiler
 * @author Uwe Tews
 */

/**
 * Main abstract compiler class
 *
 *
 * @package Compiler
 */
class Smarty_Internal_Template_Compiler extends Smarty_Compiler
{

    /**
     * current template
     *
     * @var Smarty
     */
    public $tpl_obj = null;

    /**
     * source object
     *
     * @var Smarty_Resource
     */
    public $source = null;


    /**
     * Lexer class name
     *
     * @var string
     */
    public $lexer_class = '';

    /**
     * Flag if caching enabled
     * @var boolean
     */
    public $caching = false;

    /**
     * Parser class name
     *
     * @var string
     */
    public $parser_class = '';

    /**
     * Lexer object
     *
     * @var object
     */
    public $lex = null;

    /**
     * Parser object
     *
     * @var object
     */
    public $parser = null;

    /**
     * line offset to start of template source
     *
     * @var int
     */
    public $line_offset = 0;

    /**
     * inline template code templates
     *
     * @var array
     */
    public static $merged_inline_content_classes = array();

    /**
     * flag for nocache section
     *
     * @var bool
     */
    public $nocache = false;

    /**
     * flag for nocache tag
     *
     * @var bool
     */
    public $tag_nocache = false;

    /**
     * flag for nocache code not setting $has_nocache_flag
     *
     * @var bool
     */
    public $nocache_nolog = false;

    /**
     * suppress generation of nocache code
     *
     * @var bool
     */
    public $suppressNocacheProcessing = false;

    /**
     * flag when compiling inheritance
     *
     * @var bool
     */
    public $isInheritance = false;

    /**
     * flag when compiling inheritance
     *
     * @var bool
     */
    public $isInheritanceChild = false;

    /**
     * force compilation of complete template as nocache
     * 0 = off
     * 1 = observe nocache flags on template type recompiled
     * 2 = force all code to be nocache
     *
     * @var integer
     */
    public $forceNocache = 0;

    /**
     * suppress generation of traceback code
     *
     * @var bool
     */
    public $suppressTraceback = false;

    /**
     * compile tag objects
     *
     * @var array
     */
    public static $_tag_objects = array();

    /**
     * tag stack
     *
     * @var array
     */
    public $_tag_stack = array();


    /**
     * file dependencies
     *
     * @var array
     */
    public $file_dependency = array();

    /**
     * template function properties
     *
     * @var array
     */
    public $template_functions = array();

    /**
     * template function compiled code
     *
     * @var array
     */
    public $template_functions_code = array();

    /**
     * block function properties
     *
     * @var array
     */
    public $inheritance_blocks = array();

    /**
     * block function compiled code
     *
     * @var array
     */
    public $inheritance_blocks_code = array();


    /**
     * block name index
     *
     * @var integer
     */
    public $block_name_index = 0;

    /**
     * inheritance block nesting level
     *
     * @var integer
     */
    public $block_nesting_level = 0;

    /**
     * block nesting info
     *
     * @var array
     */
    public $block_nesting_info = array();

    /**
     * compiled footer code
     *
     * @var array
     */
    public $compiled_footer_code = null;

    /**

    /**
     * plugins loaded by default plugin handler
     *
     * @var array
     */
    public $default_handler_plugins = array();

    /**
     * saved preprocessed modifier list
     *
     * @var mixed
     */
    public $default_modifier_list = null;

    /**
     * suppress Smarty header code in compiled template
     * @var bool
     */
    public $suppressHeader = false;

    /**
     * suppress template property header code in compiled template
     * @var bool
     */
    public $suppressTemplatePropertyHeader = false;

    /**
     * suppress processing of post filter
     * @var bool
     */
    public $suppressPostFilter = false;

    /**
     * flag if compiled template file shall we written
     * @var bool
     */
    public $write_compiled_code = true;

    /**
     * flag if template does contain nocache code sections
     * @var boolean
     */
    public $has_nocache_code = false;

    /**
     * flag if currently a template function is compiled
     * @var bool
     */
    public $compiles_template_function = false;

    /**
     * flag if variable container must be cloned
     * @var bool
     */
    public $must_clone_vars = false;

    /**
     * called subfuntions from template function
     * @var array
     */
    public $called_template_functions = array();

    /**
     * template functions called nocache
     * @var array
     */
    public $called_nocache_template_functions = array();

    /**
     * content class name
     * @var string
     */
    public $content_class = '';

    /**
     * required plugins
     * @var array
     * @internal
     */
    public $required_plugins = array('compiled' => array(), 'nocache' => array());

    /**
     * flags for used modifier plugins
     * @var array
     */
    public $modifier_plugins = array();

    /**
     * type of already compiled modifier
     * @var array
     */
    public $known_modifier_type = array();


    /**
     * Code object for generated template code
     * @var Smarty_Internal_code
     */
    public $template_code = null;


    // TODO check this solution
    public $prefix_code = array();
    public $postfix_code = array();
    public $has_code = false;
    public $has_output = false;


    /**
     * Initialize compiler
     *
     * @param string $lexer_class  class name
     * @param string $parser_class class name
     * @param Smarty_Resource $source
     * @param boolean $caching   flag if caching enabled
     * @param Smarty $tpl_obj
     */
    public function __construct($lexer_class, $parser_class, $tpl_obj, $source, $caching)
    {
        //parent::__construct();

        $this->tpl_obj = $tpl_obj;
        $this->source = $source;
        $this->caching = $caching;
        // get required plugins
        $this->lexer_class = $lexer_class;
        $this->parser_class = $parser_class;
        // init code buffer
        $this->template_code = new Smarty_Internal_Code(3);
    }

    /**
     * Method to compile a Smarty template
     *
     * @param  mixed $_content template source
     * @return bool true if compiling succeeded, false if it failed
     */

    /**
     * Method to compile a Smarty template
     *
     * @param  mixed $_content template source
     * @return bool true if compiling succeeded, false if it failed
     */
    protected function doCompile($_content = null)
    {
        /* here is where the compiling takes place. Smarty
          tags in the templates are replaces with PHP code,
          then written to compiled files. */

        if ($this->tpl_obj->_parserdebug)
            $this->parser->PrintTrace();
        // get tokens from lexer and parse them
        while ($this->lex->yylex()) {
            if ($this->tpl_obj->_parserdebug) {
                echo "<pre>Line {$this->lex->line} Parsing  {$this->parser->yyTokenName[$this->lex->token]} Token " .
                    htmlentities($this->lex->value) . "</pre>";
            }
            $this->parser->doParse($this->lex->token, $this->lex->value);
        }

        // finish parsing process
        $this->parser->doParse(0, 0);
        // check for unclosed tags
        if (count($this->_tag_stack) > 0) {
            // get stacked info
            list($openTag, $_data) = array_pop($this->_tag_stack);
            $this->trigger_template_error("unclosed {" . $openTag . "} tag");
        }
        // return compiled code
        // return str_replace(array("? >\n<?php","? ><?php"), array('',''), $this->parser->retvalue);
        return $this->parser->retvalue;
    }

    /**
     * Compiles the template source
     *
     * If the template is not evaluated the compiled template is saved on disk
     *
     * @throws SmartyException in case of compilation errors
     * @throws Exception
     */
    public function compileTemplateSource(Smarty_CompiledResource $compiled)
    {
        $this->isInheritance = $this->isInheritanceChild = $this->tpl_obj->is_inheritance_child;
        if (!$this->source->recompiled) {
            if ($this->source->components) {
                // uses real resource for file dependency
                $source = end($this->source->components);
            } else {
                $source = $this->source;
            }
            $this->file_dependency[$this->source->uid] = array($this->source->filepath, $this->source->timestamp, $source->type);
        }
        if ($this->tpl_obj->debugging) {
            Smarty_Internal_Debug::start_compile($this->tpl_obj);
        }
        // compile locking
        if ($this->tpl_obj->compile_locking && !$this->source->recompiled) {
            if ($saved_timestamp = $compiled->timestamp) {
                touch($compiled->filepath);
            }
        }
        // call compiler
        try {
            $code = $this->compileTemplate();
        } catch (Exception $e) {
            // restore old timestamp in case of error
            if ($this->tpl_obj->compile_locking && !$this->source->recompiled && $saved_timestamp) {
                touch($compiled->filepath, $saved_timestamp);
            }
            throw $e;
        }
        // compiling succeded
        if (!$this->source->recompiled && $this->write_compiled_code) {
            // write compiled template
            $_filepath = $compiled->filepath;
            if ($_filepath === false)
                throw new SmartyException('Invalid filepath for compiled template');
            Smarty_Internal_Write_File::writeFile($_filepath, $code, $this->tpl_obj);
            $compiled->exists = true;
            $compiled->isCompiled = true;
        }
        if ($this->tpl_obj->debugging) {
            Smarty_Internal_Debug::end_compile($this->tpl_obj);
        }
    }

    /**
     * Method to compile a Smarty template
     *
     * @return bool true if compiling succeeded, false if it failed
     */
    public function compileTemplate()
    {
        // flag for nochache sections
        //        $this->nocache = false;
        $this->tag_nocache = false;
        // reset has nocache code flag
        $this->has_nocache_code = false;
        // check if content class name already predefine
        if (empty($this->content_class)) {
            $this->content_class = '__Smarty_Content_' . str_replace('.', '_', uniqid('', true));
        }
        $this->tpl_obj->_current_file = $saved_filepath = $this->source->filepath;
        // template header code
        if (!$this->suppressHeader) {
            $template_header = "<?php /* Smarty version " . Smarty::SMARTY_VERSION . ", created on " . strftime("%Y-%m-%d %H:%M:%S") . " compiled from \"" . $this->source->filepath . "\" */\n";
        } else {
            $template_header = '<?php ';
        }

        // make sure that we don't run into backtrack limit errors
        ini_set('pcre.backtrack_limit', -1);
        // init the lexer/parser to compile the template
        $this->lex = new $this->lexer_class(null, $this);
        $this->parser = new $this->parser_class($this->lex, $this);


        // get source and run prefilter if required and pass iit to lexer
        if (isset($this->tpl_obj->autoload_filters['pre']) || isset($this->tpl_obj->registered_filters['pre'])) {
            $this->lex->data = Smarty_Internal_Filter_Handler::runFilter('pre', $this->source->content, $this->tpl_obj);
        } else {
            $this->lex->data = $this->source->getContent();
        }
        // call compiler
        $this->doCompile();

        $this->source->filepath = $saved_filepath;
        // free memory
        $this->parser->compiler = null;
        $this->parser = null;
        $this->lex->compiler = null;
        $this->lex = null;
        self::$_tag_objects = array();
        // return compiled code to template object
        // run postfilter if required on compiled template code
        if (!$this->suppressPostFilter && (isset($this->tpl_obj->autoload_filters['post']) || isset($this->tpl_obj->registered_filters['post']))) {
            $this->template_code->buffer = Smarty_Internal_Filter_Handler::runFilter('post', $this->template_code->buffer, $this->tpl_obj);
        }
        if (!$this->suppressTemplatePropertyHeader) {
            $this->template_code->buffer = $template_header . $this->_createSmartyContentClass($this->tpl_obj);
        }
        return $this->template_code->buffer;
    }

    /**
     * Compile Tag
     *
     * This is a call back from the lexer/parser
     * It executes the required compile plugin for the Smarty tag
     *
     * @param string $tag       tag name
     * @param array $args      array with tag attributes
     * @param array $parameter array with compilation parameter
     * @return string compiled code
     */
    public function compileTag($tag, $args, $parameter = array())
    {
        // $args contains the attributes parsed and compiled by the lexer/parser
        // assume that tag does compile into code, but creates no HTML output
        $this->has_code = true;
        $this->has_output = false;
        // log tag/attributes
        if (isset($this->tpl_obj->get_used_tags) && $this->tpl_obj->get_used_tags) {
            $this->tpl_obj->used_tags[] = array($tag, $args);
        }
        // check nocache option flag
        if (in_array("'nocache'", $args) || in_array(array('nocache' => 'true'), $args)
            || in_array(array('nocache' => '"true"'), $args) || in_array(array('nocache' => "'true'"), $args)
        ) {
            $this->tag_nocache = true;
        }
        // compile the smarty tag (required compile classes to compile the tag are autoloaded)
        if (($_output = $this->callTagCompiler($tag, $args, $parameter)) === false) {
            if (isset($this->template_functions[$tag])) {
                // template defined by {template} tag
                $args['_attr']['name'] = "'" . $tag . "'";
                $_output = $this->callTagCompiler('call', $args, $parameter);
            }
        }
        if ($_output !== false) {
            if ($_output !== true) {
                // did we get compiled code
                if ($this->has_code) {
                    // Does it create output? TODO
                    if (false && $this->has_output) {
                        $_output .= "\n";
                    }
                    // return compiled code
                    return $_output;
                }
            }
            // tag did not produce compiled code
            return '';
        } else {
            // map_named attributes
            if (isset($args['_attr'])) {
                foreach ($args['_attr'] as $attribute) {
                    if (is_array($attribute)) {
                        $args = array_merge($args, $attribute);
                    }
                }
            }
            // not an internal compiler tag
            if (strlen($tag) < 6 || substr($tag, -5) != 'close') {
                // check if tag is a registered object
                if (isset($this->tpl_obj->registered_objects[$tag]) && isset($parameter['object_method'])) {
                    $method = $parameter['object_method'];
                    if (!in_array($method, $this->tpl_obj->registered_objects[$tag][3]) &&
                        (empty($this->tpl_obj->registered_objects[$tag][1]) || in_array($method, $this->tpl_obj->registered_objects[$tag][1]))
                    ) {
                        return $this->callTagCompiler('private_object_function', $args, $parameter, $tag, $method);
                    } elseif (in_array($method, $this->tpl_obj->registered_objects[$tag][3])) {
                        return $this->callTagCompiler('private_object_block_function', $args, $parameter, $tag, $method);
                    } else {
                        $this->trigger_template_error('unallowed method "' . $method . '" in registered object "' . $tag . '"', $this->lex->taglineno);
                    }
                }
                // check if tag is registered
                foreach (array(Smarty::PLUGIN_COMPILER, Smarty::PLUGIN_FUNCTION, Smarty::PLUGIN_BLOCK) as $plugin_type) {
                    if (isset($this->tpl_obj->registered_plugins[$plugin_type][$tag])) {
                        // if compiler function plugin call it now
                        if ($plugin_type == Smarty::PLUGIN_COMPILER) {
                            return $this->callTagCompiler('private_compiler_plugin', $args, $parameter, $tag);
                        }
                        // compile registered function or block function
                        if ($plugin_type == Smarty::PLUGIN_FUNCTION || $plugin_type == Smarty::PLUGIN_BLOCK) {
                            return $this->callTagCompiler('private_registered_' . $plugin_type, $args, $parameter, $tag);
                        }
                    }
                }
                // check plugins from plugins folder
                foreach ($this->tpl_obj->plugin_search_order as $plugin_type) {
                    if ($plugin_type == Smarty::PLUGIN_COMPILER && $this->tpl_obj->_loadPlugin('smarty_compiler_' . $tag) && (!isset($this->tpl_obj->security_policy) || $this->tpl_obj->security_policy->isTrustedTag($tag, $this))) {
                        $plugin = 'smarty_compiler_' . $tag;
                        if (is_callable($plugin) || class_exists($plugin, false)) {
                            return $this->callTagCompiler('private_compiler_plugin', $args, $parameter, $tag);
                        }
                        $this->trigger_template_error("Plugin '{{$tag}...}' not callable", $this->lex->taglineno);
                    } else {
                        if ($function = $this->getPlugin($tag, $plugin_type)) {
                            if (!isset($this->tpl_obj->security_policy) || $this->tpl_obj->security_policy->isTrustedTag($tag, $this)) {
                                return $this->callTagCompiler('private_' . $plugin_type . '_plugin', $args, $parameter, $tag, $function);
                            }
                        }
                    }
                }
                if (is_callable($this->tpl_obj->default_plugin_handler_func)) {
                    $found = false;
                    // look for already resolved tags
                    foreach ($this->tpl_obj->plugin_search_order as $plugin_type) {
                        if (isset($this->default_handler_plugins[$plugin_type][$tag])) {
                            $found = true;
                            break;
                        }
                    }
                    if (!$found) {
                        // call default handler
                        foreach ($this->tpl_obj->plugin_search_order as $plugin_type) {
                            if ($this->getPluginFromDefaultHandler($tag, $plugin_type)) {
                                $found = true;
                                break;
                            }
                        }
                    }
                    if ($found) {
                        // if compiler function plugin call it now
                        if ($plugin_type == Smarty::PLUGIN_COMPILER) {
                            return $this->callTagCompiler('private_compiler_plugin', $args, $parameter, $tag);
                        } else {
                            return $this->callTagCompiler('private_registered_' . $plugin_type, $args, $parameter, $tag);
                        }
                    }
                }
            } else {
                // compile closing tag of block function
                $base_tag = substr($tag, 0, -5);
                // check if closing tag is a registered object
                if (isset($this->tpl_obj->registered_objects[$base_tag]) && isset($parameter['object_method'])) {
                    $method = $parameter['object_method'];
                    if (in_array($method, $this->tpl_obj->registered_objects[$base_tag][3])) {
                        return $this->callTagCompiler('private_object_block_function', $args, $parameter, $tag, $method);
                    } else {
                        $this->trigger_template_error('unallowed closing tag method "' . $method . '" in registered object "' . $base_tag . '"', $this->lex->taglineno);
                    }
                }
                // registered compiler plugin ?
                if (isset($this->tpl_obj->registered_plugins[Smarty::PLUGIN_COMPILER][$tag])) {
                    return $this->callTagCompiler('private_compiler_pluginclose', $args, $parameter, $tag);
                }
                // registered block tag ?
                if (isset($this->tpl_obj->registered_plugins[Smarty::PLUGIN_BLOCK][$base_tag]) || isset($this->default_handler_plugins[Smarty::PLUGIN_BLOCK][$base_tag])) {
                    return $this->callTagCompiler('private_registered_block', $args, $parameter, $tag);
                }
                // block plugin?
                if ($function = $this->getPlugin($base_tag, Smarty::PLUGIN_BLOCK)) {
                    return $this->callTagCompiler('private_block_plugin', $args, $parameter, $tag, $function);
                }
                if ($this->tpl_obj->_loadPlugin('smarty_compiler_' . $tag)) {
                    return $this->callTagCompiler('private_compiler_pluginclose', $args, $parameter, $tag);
                }
                $this->trigger_template_error("Plugin '{{$tag}...}' not callable", $this->lex->taglineno);
            }
            $this->trigger_template_error("unknown tag '{{$tag}...}'", $this->lex->taglineno);
        }
    }

    /**
     * lazy loads internal compile plugin for tag and calls the compile method
     *
     * compile objects cached for reuse.
     * class name format:  Smarty_Internal_Compile_TagName
     * plugin filename format: Smarty_Internal_Tagname.php
     *
     * @param string $tag   tag name
     * @param array $args   list of tag attributes
     * @param mixed $param1 optional parameter
     * @param mixed $param2 optional parameter
     * @param mixed $param3 optional parameter
     * @return string compiled code
     */
    public function callTagCompiler($tag, $args, $param1 = null, $param2 = null, $param3 = null)
    {
        // re-use object if already exists
        if (isset(self::$_tag_objects[$tag])) {
            // compile this tag
            return self::$_tag_objects[$tag]->compile($args, $this, $param1, $param2, $param3);
        }
        // lazy load internal compiler plugin
        $class_name = 'Smarty_Internal_Compile_' . $tag;
        if ($this->tpl_obj->_loadPlugin($class_name)) {
            // check if tag allowed by security
            if (!isset($this->tpl_obj->security_policy) || $this->tpl_obj->security_policy->isTrustedTag($tag, $this)) {
                // use plugin if found
                self::$_tag_objects[$tag] = new $class_name;
                // compile this tag
                return self::$_tag_objects[$tag]->compile($args, $this, $param1, $param2, $param3);
            }
        }
        // no internal compile plugin for this tag
        return false;
    }

    /**
     * Check for plugins and return function name
     *
     * @param string $plugin_name name of plugin or function
     * @param string $plugin_type type of plugin
     * @return string call name of function
     */
    public function getPlugin($plugin_name, $plugin_type)
    {
        $function = null;
        if ($this->caching && ($this->nocache || $this->tag_nocache)) {
            if (isset($this->required_plugins['nocache'][$plugin_name][$plugin_type])) {
                $function = $this->required_plugins['nocache'][$plugin_name][$plugin_type]['function'];
            } else if (isset($this->required_plugins['compiled'][$plugin_name][$plugin_type])) {
                $this->required_plugins['nocache'][$plugin_name][$plugin_type] = $this->required_plugins['compiled'][$plugin_name][$plugin_type];
                $function = $this->required_plugins['nocache'][$plugin_name][$plugin_type]['function'];
            }
        } else {
            if (isset($this->required_plugins['compiled'][$plugin_name][$plugin_type])) {
                $function = $this->required_plugins['compiled'][$plugin_name][$plugin_type]['function'];
            } else if (isset($this->required_plugins['nocache'][$plugin_name][$plugin_type])) {
                $this->required_plugins['compiled'][$plugin_name][$plugin_type] = $this->required_plugins['nocache'][$plugin_name][$plugin_type];
                $function = $this->required_plugins['compiled'][$plugin_name][$plugin_type]['function'];
            }
        }
        if (isset($function)) {
            if ($plugin_type == 'modifier') {
                $this->modifier_plugins[$plugin_name] = true;
            }
            return $function;
        }
        // loop through plugin dirs and find the plugin
        $function = 'smarty_' . $plugin_type . '_' . $plugin_name;
        $file = $this->tpl_obj->_loadPlugin($function, false);

        if (is_string($file)) {
            if ($this->caching && ($this->nocache || $this->tag_nocache)) {
                $this->required_plugins['nocache'][$plugin_name][$plugin_type]['file'] = $file;
                $this->required_plugins['nocache'][$plugin_name][$plugin_type]['function'] = $function;
            } else {
                $this->required_plugins['compiled'][$plugin_name][$plugin_type]['file'] = $file;
                $this->required_plugins['compiled'][$plugin_name][$plugin_type]['function'] = $function;
            }
            if ($plugin_type == 'modifier') {
                $this->modifier_plugins[$plugin_name] = true;
            }
            return $function;
        }
        if (is_callable($function)) {
            // plugin function is defined in the script
            return $function;
        }
        return false;
    }

    /**
     * Check for plugins by default plugin handler
     *
     * @param string $tag         name of tag
     * @param string $plugin_type type of plugin
     * @return boolean true if found
     */
    public function getPluginFromDefaultHandler($tag, $plugin_type)
    {
        $callback = null;
        $script = null;
        $cacheable = true;
        $result = call_user_func_array(
            $this->tpl_obj->default_plugin_handler_func, array($tag, $plugin_type, $this->tpl_obj, &$callback, &$script, &$cacheable)
        );
        if ($result) {
            $this->tag_nocache = $this->tag_nocache || !$cacheable;
            if ($script !== null) {
                if (is_file($script)) {
                    if ($this->caching && ($this->nocache || $this->tag_nocache)) {
                        $this->required_plugins['nocache'][$tag][$plugin_type]['file'] = $script;
                        $this->required_plugins['nocache'][$tag][$plugin_type]['function'] = $callback;
                    } else {
                        $this->required_plugins['compiled'][$tag][$plugin_type]['file'] = $script;
                        $this->required_plugins['compiled'][$tag][$plugin_type]['function'] = $callback;
                    }
                    include_once $script;
                } else {
                    $this->trigger_template_error("Default plugin handler: Returned script file \"{$script}\" for \"{$tag}\" not found");
                }
            }
            if (!is_string($callback) && !(is_array($callback) && is_string($callback[0]) && is_string($callback[1]))) {
                $this->trigger_template_error("Default plugin handler: Returned callback for \"{$tag}\" must be a static function name or array of class and function name");
            }
            if (is_callable($callback)) {
                $this->default_handler_plugins[$plugin_type][$tag] = array($callback, true, array());
                return true;
            } else {
                $this->trigger_template_error("Default plugin handler: Returned callback for \"{$tag}\" not callable");
            }
        }
        return false;
    }

    /**
     * Inject inline code for nocache template sections
     *
     * This method gets the content of each template element from the parser.
     * If the content is compiled code and it should be not cached the code is injected
     * into the rendered output.
     *
     * @param string $content content of template element
     * @param boolean $is_code true if content is compiled code
     * @param int $lineno linenumber for traceback
     * @return string content
     */
    public function nocacheCode($content, $is_code, $lineno = 0)
    {
        // If the template is not evaluated and we have a nocache section and or a nocache tag
        if ($is_code && (!empty($this->prefix_code) || !empty($this->postfix_code) || !empty($content) || $lineno)) {
            if ($lineno && !$this->suppressTraceback) {
                $this->template_code->buffer .= "\n" . str_repeat(' ', $this->template_code->saved_indentation * 4) . "/* Line {$lineno} */\n" . str_repeat(' ', $this->template_code->saved_indentation * 4) . "\$_smarty_tpl->trace_call_stack[0][1] = {$lineno};\n";
            }
            // get prefix code
            $prefix_code = '';
            if (!empty($this->prefix_code)) {
                foreach ($this->prefix_code as $code) {
                    $prefix_code .= $code;
                }
                $this->prefix_code = array();
            }
            // get postfix code
            $postfix_code = '';
            if (!empty($this->postfix_code)) {
                foreach ($this->postfix_code as $code) {
                    $postfix_code .= $code;
                }
                $this->postfix_code = array();
            }

            // generate replacement code
            $make_nocache_code = $this->nocache || $this->tag_nocache || $this->forceNocache == 2;
            if ((!($this->source->recompiled) || $this->forceNocache) && $this->caching && !$this->suppressNocacheProcessing &&
                ($make_nocache_code || $this->nocache_nolog)
            ) {
                if ($make_nocache_code) {
                    $this->has_nocache_code = true;
                }
                if ($lineno && !$this->suppressTraceback) {
                    $content = "/* Line {$this->lex->taglineno} */\$_smarty_tpl->trace_call_stack[0][1] = {$lineno};" . $content;
                }
                $content = $prefix_code . $content;
                $this->template_code->php("echo \"/*%%SmartyNocache%%*/" . str_replace(array("^#^", "^##^"), array('"', '$'), addcslashes($content, "\0\t\"\$\\")) . "/*/%%SmartyNocache%%*/\";\n");
                if (!empty($postfix_code)) {
                    $this->template_code->formatPHP($postfix_code);
                }
                // make sure we include modifier plugins for nocache code
                foreach ($this->modifier_plugins as $plugin_name => $dummy) {
                    if (isset($this->required_plugins['compiled'][$plugin_name]['modifier'])) {
                        $this->required_plugins['nocache'][$plugin_name]['modifier'] = $this->required_plugins['compiled'][$plugin_name]['modifier'];
                    }
                }
            } else {
                if (!empty($prefix_code)) {
                    $this->template_code->formatPHP($prefix_code);
                }
                $this->template_code->raw($content);
                if (!empty($postfix_code)) {
                    $this->template_code->formatPHP($prefix_code);
                }
            }
        } else {
            $this->template_code->raw($content);
        }
        $this->modifier_plugins = array();
        $this->suppressNocacheProcessing = false;
        $this->suppressTraceback = false;
        $this->tag_nocache = false;
        $this->nocache_nolog = false;
        return;
    }

    /**
     * display compiler error messages without dying
     *
     * If parameter $args is empty it is a parser detected syntax error.
     * In this case the parser is called to obtain information about expected tokens.
     *
     * If parameter $msg contains a string this is used as error message
     *
     * @param string $msg individual error message or null
     * @param string $line line-number
     * @throws SmartyCompilerException when an unexpected token is found
     */
    public function trigger_template_error($msg = null, $line = null)
    {
        // get template source line which has error
        if (!isset($line)) {
            $line = $this->lex->line;
        } else {
            /** @var $this TYPE_NAME */
            $line = $line - $this->line_offset;
        }
        preg_match_all("/\n/", $this->lex->data, $match, PREG_OFFSET_CAPTURE);
        $start_line = max(1, $line - 2);
        $end_line = min($line + 2, count($match[0]) + 1);
        $source = "<br>";
        for ($i = $start_line; $i <= $end_line; $i++) {
            $from = 0;
            $to = 99999999;
            if (isset($match[0][$i - 2])) {
                $from = $match[0][$i - 2][1];
            }
            if (isset($match[0][$i - 1])) {
                $to = $match[0][$i - 1][1] - $from;
            }
            $substr = substr($this->lex->data, $from, $to);
            $source .= sprintf('%4d : ', $i + $this->line_offset) . htmlspecialchars(trim(preg_replace('![\t\r\n]+!', ' ', $substr))) . "<br>";
        }
        $error_text = "<b>Syntax Error</b> in template <b>'{$this->source->filepath}'</b>  on line " . ($line + $this->line_offset) . "<br>{$source}";
        if (isset($msg)) {
            // individual error message
            $error_text .= "<br><b>{$msg}</b><br>";
        } else {
            // expected token from parser
            $error_text .= "<br> Unexpected '<b>{$this->lex->value}</b>'";
            if (count($this->parser->yy_get_expected_tokens($this->parser->yymajor)) <= 4) {
                foreach ($this->parser->yy_get_expected_tokens($this->parser->yymajor) as $token) {
                    $exp_token = $this->parser->yyTokenName[$token];
                    if (isset($this->lex->smarty_token_names[$exp_token])) {
                        // token type from lexer
                        $expect[] = "'<b>{$this->lex->smarty_token_names[$exp_token]}</b>'";
                    } else {
                        // otherwise internal token name
                        $expect[] = $this->parser->yyTokenName[$token];
                    }
                }
                $error_text .= ', expected one of: ' . implode(' , ', $expect) . '<br>';
            }
        }
        throw new SmartyCompilerException($error_text);
    }

    /**
     * Create Smarty content class for compiled template files
     *
     * @param Smarty $tpl_obj   template object
     * @param bool $noinstance     flag if code for creating instance shall be suppressed
     * @return string
     */
    public function _createSmartyContentClass(Smarty $tpl_obj, $noinstance = false)
    {
        $template_code = new Smarty_Internal_Code();
        $template_code->php("if (!class_exists('{$this->content_class}',false)) {")->newline()->indent();

        $template_code->php("class {$this->content_class} extends Smarty_Internal_Content" . ($this->isInheritance ? "_Inheritance" : '') . " {")->newline()->indent();
        $template_code->php("public \$version = '" . Smarty::SMARTY_VERSION . "';")->newline();
        $template_code->php("public \$has_nocache_code = " . ($this->has_nocache_code ? 'true' : 'false') . ";")->newline();
        if ($this->isInheritanceChild) {
            $template_code->php("public \$is_inheritance_child = true;")->newline();
        }
        if (!empty($tpl_obj->cached_subtemplates)) {
            $template_code->php("public \$cached_subtemplates = ")->repr($tpl_obj->cached_subtemplates)->raw(';')->newline();
        }
        if (!$noinstance) {
            $template_code->php("public \$file_dependency = ")->repr($this->file_dependency)->raw(';')->newline();
        }
        if (!empty($this->required_plugins['compiled'])) {
            $plugins = array();
            foreach ($this->required_plugins['compiled'] as $tmp) {
                foreach ($tmp as $data) {
                    $plugins[$data['file']] = $data['function'];
                }
            }
            $template_code->php("public \$required_plugins = ")->repr($plugins)->raw(';')->newline();
        }

        if (!empty($this->required_plugins['nocache'])) {
            $plugins = array();
            foreach ($this->required_plugins['nocache'] as $tmp) {
                foreach ($tmp as $data) {
                    $plugins[$data['file']] = $data['function'];
                }
            }
            $template_code->php("public \$required_plugins_nocache = ")->repr($plugins)->raw(';')->newline();
        }

        if (!empty($this->template_functions)) {
            $template_code->php("public \$template_functions = ")->repr($this->template_functions)->raw(';')->newline();
        }
        if (!empty($this->inheritance_blocks)) {
            $template_code->php("public \$inheritance_blocks = ")->repr($this->inheritance_blocks)->raw(';')->newline();
        }
        if (!empty($this->called_nocache_template_functions)) {
            $template_code->php("public \$called_nocache_template_functions = ")->repr($this->called_nocache_template_functions)->raw(';')->newline();
        }
        $template_code->newline()->newline()->php("function get_template_content (\$_smarty_tpl, \$_scope) {")->newline()->indent();
        $template_code->php("ob_start();")->newline()->newline();
        $template_code->raw($this->template_code->buffer);
        if (!empty($this->compiled_footer_code)) {
            $template_code->buffer .= implode('', $this->compiled_footer_code);
        }
        $template_code->php("return ob_get_clean();")->newline();
        $template_code->outdent()->php('}')->newline()->newline();
        foreach ($this->template_functions_code as $code) {
            $template_code->newline()->raw($code)->newline();
        }
        foreach ($this->inheritance_blocks_code as $code) {
            $template_code->newline()->raw($code)->newline();
        }
        $template_code->outdent()->php('}')->newline();
        if (!$noinstance) {
            foreach (self::$merged_inline_content_classes as $key => $inlinetpl_obj) {
                $template_code->newline()->raw($inlinetpl_obj['code']);
                unset(self::$merged_inline_content_classes[$key], $inlinetpl_obj);
            }
        }
        $template_code->outdent()->php('}')->newline()->newline();
        if (!$noinstance) {
            $template_code->php("\$this->smarty_content = new {$this->content_class}(\$tpl_obj, \$this);")->newline()->newline();
        }
        return $template_code->buffer;
    }

}
