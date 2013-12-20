<?php
/**
 * @namespace
 */
namespace Engine\Crud\Helper\Grid\Standart;

use Engine\Crud\Grid;

/**
 * Class grid datastore helper
 *
 * @category   Engine
 * @package    Crud
 * @subpackage Helper
 */
class Datastore extends \Engine\Crud\Helper
{
	/**
	 * Generates grid table rows
	 *
	 * @param \Engine\Crud\Grid $grid
	 * @return string
	 */
	static public function _(Grid $grid)
	{
        $code = '
            <tbody>';
        $data = $grid->getDataWithRenderValues();
        foreach ($data as $row) {
            $rowCode = '
                <tr>';
            foreach($row as $value) {
                $rowCode .= '
                    <td>'.$value.'</td>';
            }
            $rowCode .= '
                </tr>';
            $code .= $rowCode;
        }

        $code .= '
        </tbody>';

        return $code;
	}
}