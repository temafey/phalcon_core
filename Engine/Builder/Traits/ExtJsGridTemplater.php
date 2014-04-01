<?php
/**
 * Created by Slava Basko.
 * Email: basko.slava@gmail.com
 * Date: 3/14/14
 * Time: 6:48 PM
 */

namespace Engine\Builder\Traits;


trait ExtJsGridTemplater {

    public $templateExtJsGridExtends = '\\Engine\\Crud\\Grid\\Extjs';

    public $templateExtJsGridModulePrefix = "
    /**
     * Content managment system module router prefix
     * @var string
     */
    protected \$_modulePrefix = 'admin';
";

    public $templateExtJsGridModuleName = "
    /**
     * Extjs module name
     * @var string
     */
    protected \$_module = '%s';
";

    public $templateExtJsGridKey = "
    /**
     * Extjs form key
     * @var string
     */
    protected \$_key = '%s';
";

} 