<?php
/**
 * @namespace
 */
namespace Engine;

/**
 * Class Tag
 *
 * @category   Engine
 * @package    Tag
 */
class Tag extends \Phalcon\Tag
{

    /**
     * Generates a widget to show a HTML5 audio tag
     *
     * @param array
     * @return string
     */
    static public function multiCheckField($parameters, $data = null)
    {
        // Converting parameters to array if it is not
        if (!is_array($parameters)) {
            $parameters = array($parameters);
        }

        // Determining attributes "id" and "name"
        if (!isset($parameters[0])) {
            $parameters[0] = $parameters["id"];
        }

        $id = $parameters[0];
        if (!isset($parameters["name"])) {
            $parameters["name"] = $id;
        } else {
            if (!$parameters["name"]) {
                $parameters["name"] = $id;
            }
        }

        // Determining widget value,
        // \Phalcon\Tag::setDefault() allows to set the widget value
        if (isset($parameters["value"])) {
            $value = $parameters["value"];
            unset($parameters["value"]);
        } else {
            $value = self::getValue($id);
        }
        $boolIsRadio = $parameters['is_unique'];
        $arrayCheckMultiple = $parameters['options'];

        // Generate the tag code
        $code = '';
        foreach ($arrayCheckMultiple as $value => $label) {
            $code .=  "
        <label>";
            $code .=  "
                ";
            if ($boolIsRadio) {
                $code .= static::radioField([$parameters["name"], 'value' => $value]);
            } else {
                $code .= static::checkField([$parameters["name"], 'value' => $value]);
            }

            $code .=  "
        </label>";

        }

        return $code;
    }

}