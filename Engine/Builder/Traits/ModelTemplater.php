<?php
/**
 * Created by Slava Basko.
 * Email: basko.slava@gmail.com
 * Date: 2/19/14
 * Time: 4:47 PM
 */

namespace Engine\Builder\Traits;


trait ModelTemplater {

    public $templateModelRelation = "\t\t\$this->%s('%s', '%s', '%s', %s);\n";

    public $templateModelSetter = "
    /**
     * Method to set the value of field %s
     *
     * @param %s \$%s
     * @return \$this
     */
    public function set%s(\$%s)
    {
        \$this->%s = \$%s;
        return \$this;
    }
";

    public $templateInitialize = "
    /**
     * Initialize method for model.
     */
    public function initialize()
    {
%s
    }
";

    public $templateModelPrimaryColumn = "
    /**
     * Primary model columns
     * @var array|string
     */
    protected \$_primary = '%s';
";

    public $templateModelDefaultTitleColumn = "
    /**
     * Name of column like dafault name column
     * @var string
     */
    protected \$_nameExpr = '%s';
";

    public $templateModelAttribute = "
    /**
     * Model attributes (columns)
     * @var array
     */
    protected \$_attributes = %s;
";

    public $templateModelOrderExpr = "
    /**
     * Default order column
     * @var string
     */
    protected \$_orderExpr = '%s';
";

    public $templateModelOrder = "
    /**
     * Order is asc order direction
     * @var bool
     */
    protected \$_orderAsc = %s;
";

    public $templateModelGetSource = "
    public function getSource()
    {
        return '%s';
    }
";

} 