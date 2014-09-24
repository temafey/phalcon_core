<?php
/**
 * @namespace
 */
namespace Engine\Crud\Tools;

/**
 * Class Multiselect
 *
 * @category    Engine
 * @package     Crud
 * @subcategory Tools
 */
class Multiselect
{
    const EMPTY_CATEGORY = "n/a";
    const EMPTY_ITEM = "n/a";

    /**
     * @param \Engine\Mvc\Model\Query\Builder $queryBuilder
     * @param string $name
     * @param string $category
     * @param string $categoryName
     * @param string $where
     * @param string $emptyCategory
     * @param string $emptyItem
     * @param bool $multiselect
     * @param bool $fields
     * @param null $category_order
     * @return array
     */
    static function prepareOptions(
        \Engine\Mvc\Model\Query\Builder $queryBuilder,
        $name = null,
        $category = null,
        $categoryName = null,
        $where = null,
        $emptyCategory = "n/a",
        $emptyItem = "n/a",
        $multiselect = false,
        &$fields = false,
        $category_order = null
    ) {
        if ($emptyCategory === null) {
            $emptyCategory = self::EMPTY_CATEGORY;
        }
        if ($emptyItem === null) {
            $emptyItem = self::EMPTY_ITEM;
        }

        $model = $queryBuilder->getModel();
        if (null !== $where) {
            if (!is_array($where)) {
                $where = [$where];
            }
            foreach ($where as $whereItem) {
                $queryBuilder->andWhere($whereItem);
            }
        }

        if (null !== $name) {
            $queryBuilder->columnsId()->setColumn($name, 'name');
        } else {
            $queryBuilder->columnsId()->columnsName();
        }

        if ($category) {
            if (!is_array($category)) {
                $category = array($category);
            }
            foreach ($category as $i => $cat) {
                if ($i == 0) {
                    $queryBuilder->columnsJoinOne($cat, 'category');
                    if (null == $category_order) {
                        $queryBuilder->orderBy('category, name');
                    } else {
                        $queryBuilder->orderBy($category_order);
                    }
                } else {
                    $column = is_array($cat) ? end ($cat) : $cat;
                    $queryBuilder->columnsJoinOne($cat, $column);
                }
            }
            $data = (($result = $queryBuilder->getQuery()->execute()) === null) ? [] : $result;
            $options = [];
            foreach ($data as $item) {
                if ($item ['id'] === 0) {
                    continue;
                }
                $category = trim($item['category']) ? $item['category'] : $emptyCategory;
                $options[$category][$item ['id']] = trim($item['name']) ? $item['name'] : $emptyItem;
            }
        } else {
            $queryBuilder->orderNatural();
            //$select->order('name');
            $data = (($result = $queryBuilder->getQuery()->execute()) === null) ? [] : $result;
            $options = [];
            foreach ($data as $item) {
                if ($item ['id'] === 0) {
                    continue;
                }
                $options [$item ['id']] = trim($item ['name']) ? $item ['name'] : $emptyItem;
            }
            if ($multiselect) {
                //$options = [0 => $options];
            }
        }

        return $options;
    }

    static function prepareOptionsAll(\Engine\Mvc\Model\Query\Builder $queryBuilder, $name = null, $category = null, $categoryName = null, $where = null, $emptyCategory = "n/a", $emptyItem = "n/a", $multiselect = false, &$fields = null, $category_order = null)
    {
        if ($emptyCategory === null) {
            $emptyCategory = self::EMPTY_CATEGORY;
        }
        if ($emptyItem === null) {
            $emptyItem = self::EMPTY_ITEM;
        }
        $model = $queryBuilder->getModel();
        if ($where) {
            if (!is_array($where)) {
                $where = array ($where);
            }
            foreach ($where as $whereItem) {
                $queryBuilder->andWhere($whereItem);
            }
        }

        if (null !== $fields) {
            if (is_array($fields)) {
                foreach ($fields as $field => $value) {
                    if (is_array($value)) {
                        if (isset($value['case'])) {
                            $case = self::selectCase($field, $value ['case'], $queryBuilder->getAlias());
                            $queryBuilder->from(null, [$field."_case" => $case]);
                            unset($fields [$field]);
                            $fields [$field."_case"] = $value ['title'];
                        }
                    }
                }
            }
        }

        if ($category) {
            $queryBuilder->columnsJoinOne($category, 'category');
            if (null == $category_order) {
                $queryBuilder->orderBy(['category', 'name']);
            } else {
                $queryBuilder->orderBy($category_order);
            }
            $data = (($result = $queryBuilder->getQuery()->execute()) === null) ? [] : $result;
            $options = [];
            foreach ($data as $item) {
                if ($item ['id'] === 0)
                    continue;
                $category = trim($item ['category']) ? $item ['category'] : $emptyCategory;
                $options [$category] [$item ['id']] = $item;
            }
        } else {
            $queryBuilder->orderNatural();
            $queryBuilder->orderBy('name');
            $data = (($result = $queryBuilder->getQuery()->execute()) === null) ? [] : $result;
            $options = array ();
            foreach ($data as $item) {
                if ($item ['id'] === 0)
                    continue;
                $options [$category] [$item ['id']] = $item;
            }
            if ($multiselect) {
                //$options = [0 => $options];
            }
        }

        return $options;
    }

    static function getFirstOption(&$multiOptions)
    {
        $value = null;
        foreach ($multiOptions as $key => $item) {
            if (is_array($item)) {
                if (! empty($item)) {
                    $value = reset(array_keys($item));
                    break;
                }
            } else {
                $value = $key;
                break;
            }
        }
        return $value;
    }

    static function getNameById(&$multiOptions, $id = null, $default = 'n/a')
    {
        if ($id == null) {
            return $default;
        }
        if ($multiOptions == null) {
            return $default;
        }
        foreach ($multiOptions as $key => $value) {
            if (is_array($value)) {
                if (isset($value [$id])) {
                    return $value [$id];
                }
            } else {
                if ($key == $id) {
                    return $value;
                }
            }
        }

        return $default;
    }

    static function selectCase($field, $options, $table_name = "") {

        if (null !== $table_name) {
            $field = $table_name.".".$field;
        }
        $select = "(CASE ".$field." ";
        foreach ($options as $key => $value) {
            $select .= "WHEN '".$key."' THEN '".$value."' ";
        }
        $select .= " ELSE ".$field;
        $select .= " END)";

        return $select;
    }
}