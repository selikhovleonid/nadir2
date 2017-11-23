<?php

namespace nadir2;

/**
 * This namespace contains developer tools.
 * @author Leonid Selikhov.
 */
/** @var integer The constant determines the count of spaces to indent. */
const SPACES_PER_TAB = 4;

/**
 * It returns of string representation of array at the current level of the
 * tree. It's recursive method.
 * @param mixed $var The variable.
 * @param int $depth Optional The max depth of tree print.
 * @param int $level Optional The current level of the tree.
 * @param mixed[] $objects Optional The array of variable ojects.
 * @return string
 */
function _getDumpArrayIteration(
    $var,
    int $depth = 10,
    int $level = 0,
    array& $objects = []
): string {
    $out       = '';
    $spacesOut = str_repeat(' ', SPACES_PER_TAB * $level);
    if ($depth <= $level) {
        $out .= "array\n{$spacesOut}(...)";
    } elseif (empty($var)) {
        $out .= 'array()';
    } else {
        $spacesIn = $spacesOut.str_repeat(' ', SPACES_PER_TAB);
        $out      .= "array\n{$spacesOut}(";
        foreach ($var as $key => $value) {
            $out .= "\n{$spacesIn}"
                ._getDumpIteration($key, $depth, 0, $objects)
                .' => '
                ._getDumpIteration($value, $depth, $level + 1, $objects);
        }
        $out .= "\n{$spacesOut})";
    }
    return $out;
}

/**
 * It returns the string representation of object at the current level of the
 * tree. It's recursive method.
 * @param mixed $var The variable.
 * @param int $depth Optional The max depth of tree print.
 * @param int $level Optional The current level of the tree.
 * @param mixed[] $objects Optional The array of variable ojects.
 * @return string
 */
function _getDumpObjIteration(
    $var,
    int $depth = 10,
    int $level = 0,
    array& $objects = []
): string {
    $out       = '';
    $className = get_class($var);
    $spacesOut = str_repeat(' ', SPACES_PER_TAB * $level);
    if (($objId       = array_search($var, $objects, true)) !== false) {
        $out .= "{$className} object #"
            .($objId + 1)
            ."\n{$spacesOut}(...)";
    } elseif ($depth <= $level) {
        $out .= "{$className} object"
            ."\n{$spacesOut}(...)";
    } else {
        // Возвращает модификаторы свойств объекта.
        $funcGetPropMod = function (\ReflectionProperty $prop) {
            if ($prop->isPublic()) {
                $out = 'public';
            } elseif ($prop->isProtected()) {
                $out = 'protected';
            } else {
                $out = 'private';
            }
            if ($prop->isStatic()) {
                $out .= ' static';
            }
            return $out;
        };

        $objId        = array_push($objects, $var);
        $spacesIn   = $spacesOut.str_repeat(' ', SPACES_PER_TAB);
        $reflection = new \ReflectionClass($className);
        $props      = $reflection->getProperties();
        $out        .= "{$className} object #{$objId}\n{$spacesOut}(";
        foreach ($props as $prop) {
            $prop->setAccessible(true);
            $out .= "\n{$spacesIn}"
                .'['
                ._getDumpIteration($prop->getName(), $depth, 0, $objects)
                .':'.$funcGetPropMod($prop)
                .'] => '
                ._getDumpIteration(
                    $prop->getValue($var),
                    $depth,
                    $level + 1,
                    $objects
                );
            if (!$prop->isPublic()) {
                $prop->setAccessible(false);
            }
        }
        $out .= "\n{$spacesOut})";
        unset($funcGetPropMod);
        unset($reflection);
    }
    return $out;
}

/**
 * The method returns the string representation of current level of the tree.
 * It's recursive.
 * @param mixed $var The variable.
 * @param int $depth Optional The max depth of tree print.
 * @param int $level Optional The current level of the tree.
 * @param mixed[] $objects Optional The array of variable ojects.
 * @return string
 */
function _getDumpIteration(
    $var,
    int $depth = 10,
    int $level = 0,
    array& $objects = []
): string {
    $out = '';
    switch (gettype($var)) {
        case 'NULL':
            $out .= 'NULL';
            break;
        case 'boolean':
            $out .= $var ? 'TRUE' : 'FALSE';
            break;
        case 'integer':
        case 'double':
            $out .= (string) $var;
            break;
        case 'string':
            $out .= "'".addslashes($var)."'";
            break;
        case 'unknown type':
            $out .= '{unknown}';
            break;
        case 'resource':
            $out .= '{resource}';
            break;
        case 'array':
            $out .= _getDumpArrayIteration($var, $depth, $level, $objects);
            break;
        case 'object':
            $out .= _getDumpObjIteration($var, $depth, $level, $objects);
            break;
        default:
            break;
    }
    return $out;
}

/**
 * It returns the human-readable data of the variable (the variable dump).
 * @param mixed $var The variable.
 * @param integer $depth Optional The max depth of tree print.
 * @return string
 */
function getDumpVar($var, int $depth = 10): string
{
    return _getDumpIteration($var, $depth);
}

/**
 * It prints the human-readable data of the variable (the variable dump) with
 * highlighted syntax.
 * @param mixed $var The variable.
 * @param integer $depth Optional The max depth of tree print.
 * @return void
 */
function dumpVar($var, int $depth = 10): void
{
    $out = getDumpVar($var, $depth);
    $raw = highlight_string("<?php\n{$out}", true);
    echo preg_replace('#&lt;\?php<br \/>#', '', $raw, 1);
}
