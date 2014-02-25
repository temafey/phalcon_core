<?php
/**
 * Created by Slava Basko.
 * Email: basko.slava@gmail.com
 * Date: 2/19/14
 * Time: 4:38 PM
 */

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

} 