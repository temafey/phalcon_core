<?php
/**
 * Created by Slava Basko.
 * Email: basko.slava@gmail.com
 * Date: 4/1/14
 * Time: 1:44 PM
 */

namespace Engine\Builder\Traits;


trait SimpleFormTemplater {

    public $templateSimpleFormExtends = '\\Engine\\Crud\\Form';

    public $templateSimpleFormTitle = "
    protected \$_title = '%s';
";

    public $templateSimpleFormContainerModel = "
    protected \$_containerModel = '%s';
";

    public $templateSimpleFormAction = "
    protected \$_action = '%s';
";

    public $templateSimpleFormInitFields = "
    protected function _initFields()
    {
        \$this->_fields = [
%s
        ];
    }
";

    public $templateSimpleFormSimpleField = "\t\t\t'%s' => new \\Engine\\Crud\\Form\\Field\\%s('%s', '%s'),\n";

    public $templateSimpleFormComplexField = "\t\t\t'%s' => new \\Engine\\Crud\\Form\\Field\\%s('%s', '%s', %s),\n";

} 