<?php
/**
 * Created by Slava Basko.
 * Email: basko.slava@gmail.com
 * Date: 4/1/14
 * Time: 1:44 PM
 */

namespace Engine\Builder\Traits;


trait SimpleFormTemplater {

    public $templateSimpleFormExtends = 'Form';

    public $templateSimpleUseForm = array(
        'Engine\Crud\Form',
        'Engine\Crud\Form\Field'
    );

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
    /**
     * Initialize form fields
     *
     * @return void
     */
    protected function _initFields()
    {
        \$this->_fields = [
%s
        ];
    }
";
    public $templateShortFormSimpleField = "\t\t\t'%s' => new Field\\%s('%s'),\n";

    public $templateSimpleFormSimpleField = "\t\t\t'%s' => new Field\\%s('%s', '%s'),\n";

    public $templateSimpleFormComplexField = "\t\t\t'%s' => new Field\\%s('%s', '%s', %s),\n";

} 