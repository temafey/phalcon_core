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
        $columns = array_keys($grid->getColumns());
        $data = $grid->getDataWithRenderValues();
        foreach ($data['data'] as $row) {
            $rowCode = '
                <tr>';
            foreach($columns as $key) {
                $rowCode .= '
                    <td>'.$row[$key].'</td>';
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