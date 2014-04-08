<?php

namespace Engine\Builder\Traits;


trait BasicTemplater {

    public $templateClassSimple = "<?php
%s


class %s
{
%s
}
";

    public $templateClassSimpleWithExtend = "<?php
%s


class %s extends %s
{
%s
}
";

    public $templateClassFullStack = "<?php
%s
%s


class %s extends %s
{
%s
}
";

    public $templateThis = "\t\t\$this->%s(%s);\n";

    public $templateEmptyAttribute = "
    /**
     *
     * @var %s
     */
    %s \$%s;
";

    public $templateAttribute = "
    /**
     *
     * @var %s
     */
    %s \$%s = %s;
";

} 